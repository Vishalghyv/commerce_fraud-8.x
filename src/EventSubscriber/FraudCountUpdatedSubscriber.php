<?php

namespace Drupal\commerce_fraud\EventSubscriber;

use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\commerce_fraud\Event\FraudEvents;
use Drupal\commerce_fraud\Event\FraudEvent;
use Drupal\Core\Database\Connection;

/**
 * Event subscriber, that acts on the place transition of commerce order
 * entities, in order to generate and set an order number.
 */
class FraudCountUpdatedSubscriber implements EventSubscriberInterface {

  /**
   * The event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $connection;

  /**
   * Constructs a new OrderNumberSubscriber object.
   *
   * @param \Drupal\commerce_fraud\CommerceFraudGenerationServiceInterface $commerce_fraud_generation_service
   *   The order number generation service.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
      'commerce_fraud.fraud_count_updated' => ['changeFraudCount'],
    ];
    return $events;
  }

  /**
   * Sets the order number on placing the order.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The transition event.
   */
  public function changeFraudCount(FraudEvent $event) {
    drupal_set_message("This is coming from event {$event->getCount()}");
    $fields = [
      'fraud_score' => $event->getCount(),
      'order_id' => $event->getOrderId(),
      'note' => 'Action by commerce_fraud',
    ];
    $id = $this->connection->insert('commerce_fraud_fraud_score')
      ->fields($fields)
      ->execute();
  }

}
