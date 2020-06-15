<?php

namespace Drupal\commerce_fraud\Plugin\Commerce\FraudGenerator;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_price\Price;


/**
 * Provides the infinite order number generator.
 *
 * @CommerceFraudGenerator(
 *   id = "total_price",
 *   label = @Translation("Total Price"),
 *   description = @Translation("Checks Order Total Price"),
 * )
 */
class TotalPriceFraudGenerator extends FraudGeneratorBase {

  /**
   * @inheritDoc
   */
  public function generate() {
    drupal_set_message('This message is from plugin rules');
    $order_number = 5;
    return $order_number;
  }

  public function apply(OrderInterface $order) {
    // $this->assertEntity($entity);
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    // $order = $entity;
    $order_price = $order->getTotalPrice();
    $new_price = new Price('1000', 'INR');
    drupal_set_message("{$new_price}");
    if ($order_price->greaterThan($new_price)) {
    // do something.
    drupal_set_message('Price is greater than 1000 INR increase the fraud count');
    }
  }

}
