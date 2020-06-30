<?php

namespace Drupal\commerce_fraud\Plugin\Commerce\FraudRule;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Defines the interface for order number generators.
 */
interface FraudRuleInterface extends ConfigurableInterface, PluginFormInterface, PluginInspectionInterface {

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
   * Gets the offer entity type ID.
   *
   * This is the entity type ID of the entity passed to apply().
   *
   * @return string
   *   The offer's entity type ID.
   */
  public function getEntityTypeId();

  /**
   * Applies the offer to the given entity.
   *
   * @param \Drupal\Core\Entity\OrderInterface $entity
   *   The entity.
   */
  public function apply(OrderInterface $entity);

}
