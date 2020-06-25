<?php

namespace Drupal\commerce_fraud\Plugin\Commerce\FraudRule;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the base class for order offers.
 */
abstract class FraudOfferBase extends FraudRuleBase implements FraudOfferInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

}
