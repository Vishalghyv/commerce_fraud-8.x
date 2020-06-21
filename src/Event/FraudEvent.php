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
   * @var number
   */
  protected $order_id;

  /**
   * FraudEvent constructor.
   * @param $count
   */
  public function __construct($count ,$order_id) {
    $this->count = $count;
    $this->order_id = $order_id;
  }

  /**
   * Return count
   * @return string
   */
  public function getCount(){
    return $this->count;
  }

  /**
   * Return order_id
   * @return number
   */
  public function getOrderId(){
    return $this->order_id;
  }
}
