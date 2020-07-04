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
   * score.
   *
   * @var int
   */
  protected $score;

  /**
   * Order id.
   *
   * @var int
   */
  protected $orderId;

  /**
   * Note.
   *
   * @var int
   */
  protected $note;

  /**
   * FraudEvent constructor.
   *
   * @param int $score
   *   Score.
   * @param int $orderId
   *   Order ID.
   * @param string $note
   *   Note.
   */
  public function __construct($score, $orderId, $note) {
    $this->score = $score;
    $this->orderId = $orderId;
    $this->note = $note;
  }

  /**
   * Return score.
   *
   * @return string
   *   Score.
   */
  public function getscore() {
    return $this->score;
  }

  /**
   * Return orderId.
   *
   * @return int
   *   Order Id.
   */
  public function getOrderId() {
    return $this->orderId;
  }

  /**
   * Return note.
   *
   * @return int
   *  Note.
   */
  public function getNote() {
    return $this->note;
  }

}
