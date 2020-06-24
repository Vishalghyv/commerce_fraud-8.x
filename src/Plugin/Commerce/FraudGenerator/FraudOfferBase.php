<?php

namespace Drupal\commerce_fraud\Plugin\Commerce\FraudGenerator;

use Drupal\commerce_order\PriceSplitterInterface;
use Drupal\commerce_price\RounderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the base class for order offers.
 */
abstract class FraudOfferBase extends FraudGeneratorBase implements FraudOfferInterface {

  /**
   * Constructs a new FraudOfferBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The pluginId for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *   The splitter.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

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
