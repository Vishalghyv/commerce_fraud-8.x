<?php

namespace Drupal\commerce_fraud\EventSubscriber;

use Drupal\commerce_fraud\CommerceFraudRuleServiceInterface;
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
   * The fraud rule service.
   *
   * @var \Drupal\commerce_fraud\CommerceFraudRuleServiceInterface
   */
  protected $commerceFraudRuleService;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a new FraudSubscriber object.
   *
   * @param \Drupal\commerce_fraud\CommerceFraudRuleServiceInterface $commerce_fraud_rule_service
   *   The fraud generation service.
   * @param $commerce_fraud_rule_service
   * @param $connection
   */
  public function __construct(EventDispatcherInterface $event_dispatcher, CommerceFraudRuleServiceInterface $commerce_fraud_rule_service, Connection $connection) {
    $this->eventDispatcher = $event_dispatcher;
    $this->commerceFraudRuleService = $commerce_fraud_rule_service;
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
    $order = $event->getEntity();
    $rules = \Drupal::entityTypeManager()->getStorage('rules');

    foreach ($rules->loadMultiple() as $rule) {

      if(!$rule->getStatus()) {
        continue;
      }
      $action = $rule->getRule()->apply($order);

      if (!$action) {
        continue;
      }
      $fraud_count = $rule->getCounter();

      $rule_name = $rule->getRule()->getPluginId();
      $logStorage = \Drupal::entityTypeManager()->getStorage('commerce_log');
      $logStorage->generate($order, 'fraud_rule_name', ['rule_name' => $rule_name])->save();

      $note = $rule_name . ": " . $fraud_count;
      $event = new FraudEvent($fraud_count, $order->id(), $note);

      $this->eventDispatcher->dispatch(FraudEvents::FRAUD_COUNT_INSERT, $event);
    }

    $updated_fraud_score = $this->getFraudScore($order->id());

    if ($updated_fraud_score <= \Drupal::state()->get('commerce_fraud_blacklist_cap', 10)) {
      return;
    }

    if (\Drupal::state()->get('stop_order', FALSE)) {
      $this->cancelFraudStatus($order);
    }

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
    // dpm($order->getState()->getPossibleValues());
    $order->getState()->applyTransitionById('cancel');
    $order->getState()->setValue(['value' => 'fraudulent']);
    $logStorage = \Drupal::entityTypeManager()->getStorage('commerce_log');
    $logStorage->generate($order, 'order_fraud')->save();
    $order->setRefreshState(OrderInterface::REFRESH_ON_LOAD);
  }

  /**
   * {@inheritdoc}
   */
  public function sendBlackListedOrderMail(OrderInterface $order, int $fraud_score) {
    $mailManager = \Drupal::service('plugin.manager.mail');

    $module = 'commerce_fraud';
    $key = 'send_blacklist';
    $to = \Drupal::state()->get('send_email', \Drupal::config('system.site')->get('mail'));
    $params['message'] = $this->getMailMessage($order, $fraud_score);
    $params['order_id'] = $order->id();
    $langcode = \Drupal::languageManager()->getDefaultLanguage()->getId();
    $send = TRUE;

    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);

    if ($result['result']) {
      drupal_set_message(t('Your message has been sent.'));
      return;
    }
    drupal_set_message(t('There was a problem sending your message and it was not sent.'), 'error');
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
