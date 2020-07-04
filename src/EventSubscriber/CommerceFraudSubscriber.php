<?php

namespace Drupal\commerce_fraud\EventSubscriber;

use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\commerce_fraud\Event\FraudEvents;
use Drupal\commerce_fraud\Event\FraudEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;

/**
 * Event subscriber, that acts on the place transition of commerce order
 * entities, in order to set fraud score.
 */
class CommerceFraudSubscriber implements EventSubscriberInterface {

  /**
   * The event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new FraudSubscriber object.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection to be used.
   */
  public function __construct(EventDispatcherInterface $event_dispatcher, MessengerInterface $messenger, Connection $connection) {
    $this->eventDispatcher = $event_dispatcher;
    $this->connection = $connection;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('event_dispatcher'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
      'commerce_order.place.pre_transition' => ['setFraudScore'],
    ];
    return $events;
  }

  /**
   * Sets the Fraud score on placing the order.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The transition event.
   */
  public function setFraudScore(WorkflowTransitionEvent $event) {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    // Get Order.
    $order = $event->getEntity();

    // Get Rules.
    $rules = \Drupal::entityTypeManager()->getStorage('rules');

    // Load Rules.
    foreach ($rules->loadMultiple() as $rule) {

      // Apply the rule.
      // File contating apply function is plugin-fraud rule.
      $action = $rule->getPlugin()->apply($order);

      // Check if the rule applied.
      if (!$action) {
        continue;
      }

      // Get the score and name set in the entity.
      $fraud_score = $rule->getScore();
      $rule_name = $rule->getPLugin()->getLabel();

      // Add a log to order activity.
      $logStorage = \Drupal::entityTypeManager()->getStorage('commerce_log');
      $logStorage->generate($order, 'fraud_rule_name', ['rule_name' => $rule_name])->save();

      // Detail for Fraud Event.
      $note = $rule_name . ": " . $fraud_score;
      $event = new FraudEvent($fraud_score, $order->id(), $note);

      // Dispatch Fraud Event with inserting event.
      $this->eventDispatcher->dispatch(FraudEvents::FRAUD_SCORE_INSERT, $event);
    }

    // Calculating complete fraud score for the order.
    $updated_fraud_score = $this->getFraudScore($order->id());

    // Compare order fraud score with block list cap set in settings.
    if ($updated_fraud_score <= \Drupal::state()->get('commerce_fraud_blocklist_cap', 10)) {
      return;
    }

    // Cancel order if set in settings.
    if (\Drupal::state()->get('stop_order', FALSE)) {
      $this->cancelFraudulentOrder($order);
    }

    // Sending the details of the blocklisted order via mail.
    $this->sendBlockListedOrderMail($order, $updated_fraud_score);

  }

  /**
   * Returns the fraud score as per order id.
   *
   * @param int $order_id
   *   Order Id.
   *
   * @return int
   *   Fraud Score.
   */
  public function getFraudScore(int $order_id) {
    // Query to get all fraud score for order id.
    $query = $this->connection->select('commerce_fraud_fraud_score');
    $query->condition('order_id', $order_id);
    $query->addExpression('SUM(fraud_score)', 'fraud');
    $result = $query->execute()->fetchCol();

    return $result[0] ?? 0;
  }

  /**
   * Cancels the order and sets its status to fradulent.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   Order.
   */
  public function cancelFraudulentOrder(OrderInterface $order) {
    // Cancelling the order and setting the status to fraudulent.
    $order->getState()->applyTransitionById('cancel');
    $order->getState()->setValue(['value' => 'fraudulent']);
    $logStorage = \Drupal::entityTypeManager()->getStorage('commerce_log');

    // Creating of log for the order and refreshing it on load.
    $logStorage->generate($order, 'order_fraud')->save();
    $order->setRefreshState(OrderInterface::REFRESH_ON_LOAD);
  }

  /**
   * Sends email about blocklisted orders to the email choosen un settings.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   Order.
   * @param int $fraud_score
   *   Fraud Score.
   */
  public function sendBlockListedOrderMail(OrderInterface $order, int $fraud_score) {

    $mailManager = \Drupal::service('plugin.manager.mail');

    // Mail details.
    $module = 'commerce_fraud';
    $key = 'send_blocklist';
    $to = \Drupal::state()->get('send_email', \Drupal::config('system.site')->get('mail'));
    // Mail message.
    $params['message'] = $this->getMailMessage($order, $fraud_score);
    $params['order_id'] = $order->id();
    $langcode = \Drupal::languageManager()->getDefaultLanguage()->getId();
    $send = TRUE;

    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);

    // Setting a message about the mail.
    if ($result['result']) {
      $this->messenger->addMessage(t('Message about the order has been sent.'));
      return;
    }
    $this->messenger->addWarning(t('There was a problem sending message and it was not sent.'), MessengerInterface::TYPE_WARNING);
  }

  /**
   * Return message with details about order.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   Order.
   * @param int $fraud_score
   *   Fraud Score.
   *
   * @return string[]
   *   Message.
   */
  public function getMailMessage(OrderInterface $order, int $fraud_score) {
    $breakdown = '';
    $breakdown .= '<br>Order With order Uid ' . $order->getCustomerId();
    $breakdown .= '<br>Current order status is ' . $order->getState()->getId();
    $breakdown .= '<br>This order was placed at ' . date('m/d/Y H:i:s', $order->getPlacedTime());
    $breakdown .= '<br> With Ip address ' . $order->getIpAddress() . '<br>';
    $breakdown .= '<br> With fraud score: ' . $fraud_score . '<br>';
    return $breakdown;
  }

}
