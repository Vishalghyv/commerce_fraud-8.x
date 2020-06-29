<?php

namespace Drupal\commerce_fraud\EventSubscriber;

use Drupal\state_machine\Event\WorkflowTransitionEvent;
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
   * Constructs a new FraudSubscriber object.
   * 
   * @param $connection
   */
  public function __construct(EventDispatcherInterface $event_dispatcher, Connection $connection) {
    $this->eventDispatcher = $event_dispatcher;
    $this->connection = $connection;
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

      // Check if status of rule is true.
      if(!$rule->getStatus()) {
        continue;
      }

      // Apply the rule.
      // File contating apply function is plugin-fraud rule.
      $action = $rule->getRule()->apply($order);

      // Check if the rule applied.
      if (!$action) {
        continue;
      }

      // Get the counter and name set in the entity.
      $fraud_count = $rule->getCounter();
      $rule_name = $rule->getRule()->getPluginId();

      // Add a log to order activity/
      $logStorage = \Drupal::entityTypeManager()->getStorage('commerce_log');
      $logStorage->generate($order, 'fraud_rule_name', ['rule_name' => $rule_name])->save();

      // Detail for Fraud Event.
      $note = $rule_name . ": " . $fraud_count;
      $event = new FraudEvent($fraud_count, $order->id(), $note);

      // Dispatch Fraud Event with inserting event.
      $this->eventDispatcher->dispatch(FraudEvents::FRAUD_COUNT_INSERT, $event);
    }

    // Calculating complete fraud score for the order.
    $updated_fraud_score = $this->getFraudScore($order->id());

    // Check if the order fraud score have value more than black list cap set in settings.
    if ($updated_fraud_score <= \Drupal::state()->get('commerce_fraud_blacklist_cap', 10)) {
      return;
    }

    // Check if to set fraudulent status and cancel order since the order is already blacklisted checked above.
    if (\Drupal::state()->get('stop_order', FALSE)) {
      $this->cancelFraudStatus($order);
    }

    // Sending the details of the blacklisted order via mail.
    $this->sendBlackListedOrderMail($order, $updated_fraud_score);

  }

  /**
   * Returns the fraud score.
   *
   * @param order_id
   *
   * @return int
   */
  public function getFraudScore(int $order_id) {
    // Query to get all fraud score for order id.
    $result = $this->connection->select('commerce_fraud_fraud_score', 'f')
      ->fields('f', ['fraud_score'])
      ->condition('order_id', $order_id)
      ->execute();
    $score = 0;
    foreach ($result as $row) {
      $score += $row->fraud_score;
    }

    return $score;
  }

  /**
   * {@inheritdoc}
   */
  public function cancelFraudStatus(OrderInterface $order) {
    // Cancelling the order and setting the status to fraudulent.
    $order->getState()->applyTransitionById('cancel');
    $order->getState()->setValue(['value' => 'fraudulent']);
    $logStorage = \Drupal::entityTypeManager()->getStorage('commerce_log');

    // Creating of log for the order and refreshing it on load.
    $logStorage->generate($order, 'order_fraud')->save();
    $order->setRefreshState(OrderInterface::REFRESH_ON_LOAD);
  }

  /**
   * {@inheritdoc}
   */
  public function sendBlackListedOrderMail(OrderInterface $order, int $fraud_score) {

    $mailManager = \Drupal::service('plugin.manager.mail');

    // Mail details.
    $module = 'commerce_fraud';
    $key = 'send_blacklist';
    $to = \Drupal::state()->get('send_email', \Drupal::config('system.site')->get('mail'));
    // Mail message.
    $params['message'] = $this->getMailMessage($order, $fraud_score);
    $params['order_id'] = $order->id();
    $langcode = \Drupal::languageManager()->getDefaultLanguage()->getId();
    $send = TRUE;

    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);

    // Setting a message about the mail.
    if ($result['result']) {
      drupal_set_message(t('Message about the order has been sent.'));
      return;
    }
    drupal_set_message(t('There was a problem sending message and it was not sent.'), 'error');
  }

  /**
   * {@inheritdoc}
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
