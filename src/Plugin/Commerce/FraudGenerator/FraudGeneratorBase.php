<?php

namespace Drupal\commerce_fraud\Plugin\Commerce\FraudGenerator;

use Drupal\Core\Plugin\PluginBase;

/**
 * Abstract base class for order number generators.
 */
abstract class FraudGeneratorBase extends PluginBase implements FraudGeneratorInterface {

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->pluginDefinition['description'];
  }

}
