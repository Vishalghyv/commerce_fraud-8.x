<?php

namespace Drupal\commerce_fraud\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\commerce_fraud\Event\FraudEvent;
use Drupal\Core\Database\Connection;

/**
 * Event subscriber, that acts on the place transition of commerce order
 * entities, in order to generate and set an fraud score.
 */
class FraudScoreUpdatedSubscriber implements EventSubscriberInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a new FraudscoreSubscriber object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection to be used.
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
      'commerce_fraud.fraud_SCORE_insert' => ['addFraudscore'],
      'commerce_fraud.fraud_SCORE_update' => ['changeFraudscore'],
    ];
    return $events;
  }

  /**
   * {@inheritdoc}
   */
  public function addFraudScore(FraudEvent $event) {

    $fields = [
      'fraud_score' => $event->getScore(),
      'order_id' => $event->getOrderId(),
      'note' => $event->getNote(),
    ];

    $this->connection->insert('commerce_fraud_fraud_score')
      ->fields($fields)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function changeFraudScore(FraudEvent $event) {

    $fields = [
      'fraud_score' => $event->getScore(),
      'order_id' => $event->getOrderId(),
      'note' => $event->getNote(),
    ];
    $this->connection->update('commerce_fraud_fraud_score')
      ->fields($fields)
      ->condition('order_id', $event->getOrderId())
      ->execute();
  }

}
