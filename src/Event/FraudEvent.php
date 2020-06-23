<?php

namespace Drupal\commerce_fraud\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Defines the fraud event.
 *
 * @see \Drupal\commerce_fraud\Event\FraudEvents
 */
class FraudEvent extends Event {

  /**
   * Count.
   *
   * @var int
   */
  protected $count;

  /**
   * Order id.
   *
   * @var int
   */
  protected $order_id;

  /**
   * Note.
   *
   * @var int
   */
  protected $note;

  /**
   * FraudEvent constructor.
   *
   * @param $count
   */
  public function __construct($count, $order_id, $note) {
    $this->count = $count;
    $this->order_id = $order_id;
    $this->note = $note;
  }

  /**
   * Return count.
   *
   * @return string
   */
  public function getCount() {
    return $this->count;
  }

  /**
   * Return order_id.
   *
   * @return int
   */
  public function getOrderId() {
    return $this->order_id;
  }

  /**
   * Return note.
   *
   * @return int
   */
  public function getNote() {
    return $this->note;
  }

}
