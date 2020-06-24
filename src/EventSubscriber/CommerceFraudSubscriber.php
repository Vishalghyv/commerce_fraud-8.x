<?php

namespace Drupal\commerce_fraud\EventSubscriber;

use Drupal\commerce_fraud\CommerceFraudGenerationServiceInterface;
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
 * entities, in order to generate and set fraud score.
 */
class CommerceFraudSubscriber implements EventSubscriberInterface {

  /**
   * The event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The fraud generation service.
   *
   * @var \Drupal\commerce_fraud\CommerceFraudGenerationServiceInterface
   */
  protected $commerceFraudGenerationService;

  /**
   * The event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $connection;

  /**
   * Constructs a new FraudSubscriber object.
   *
   * @param \Drupal\commerce_fraud\CommerceFraudGenerationServiceInterface $commerce_fraud_generation_service
   *   The fraud generation service.
   */
  public function __construct(EventDispatcherInterface $event_dispatcher, CommerceFraudGenerationServiceInterface $commerce_fraud_generation_service, Connection $connection) {
    $this->eventDispatcher = $event_dispatcher;
    $this->commerceFraudGenerationService = $commerce_fraud_generation_service;
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
      'commerce_order.place.pre_transition' => ['setFraudNumber'],
    ];
    return $events;
  }

  /**
   * Sets the Fraud number on placing the order.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The transition event.
   */
  public function setFraudNumber(WorkflowTransitionEvent $event) {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $event->getEntity();
    $rules = \Drupal::entityTypeManager()->getStorage('rules');

    foreach ($rules->loadMultiple() as $rule) {
      $action = $this->commerceFraudGenerationService->generateAndSetFraudCount($order, $rule->getRule()->getPluginId());

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

    $this->checkFraudStatus($order);

  }

  /**
   * Sets the Fraud number on placing the order.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The transition event.
   */
  public function checkFraudStatus(OrderInterface $order) {
    $result = $this->connection->select('commerce_fraud_fraud_score', 'f')
      ->fields('f', ['fraud_score'])
      ->condition('order_id', $order->id())
      ->execute();
    $score = 0;
    foreach ($result as $row) {
      $score += $row->fraud_score;
    }
    drupal_set_message("Check fraud status");
    dpm($score);
    if ($score > \Drupal::state()->get('commerce_fraud_greylist_cap', 10)) {
      $order->setRefreshState('Fraudulent');
      // $this->eventDispatcher->stopPropagation();
    }
    return $score;
  }

}
