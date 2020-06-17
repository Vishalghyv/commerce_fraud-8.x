<?php

namespace Drupal\commerce_fraud\Plugin\Commerce\FraudGenerator;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\commerce_order\Entity\OrderInterface;

/**
 * Defines the interface for order number generators.
 */
interface FraudGeneratorInterface extends PluginInspectionInterface {

  /**
   * Gets the order number generator label.
   *
   * @return string
   *   The order number generator label.
   */
  public function getLabel();

  /**
   * Gets the order number generator description.
   *
   * @return string
   *   The order number generator description.
   */
  public function getDescription();

  /**
   * Generates an order number value object, given the last known order number
   * as parameter.
   *
   *
   * @return int
   */
  public function generate();

  /**
   * Applies the offer to the given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param \Drupal\commerce_promotion\Entity\PromotionInterface $promotion
   *   THe parent promotion.
   */
  public function apply(OrderInterface $entity);

}