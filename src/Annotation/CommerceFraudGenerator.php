<?php

namespace Drupal\commerce_fraud\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines the fraud generator plugin annotation object.
 *
 * Plugin namespace: Plugin\Commerce\FraudGeneratorBase.
 *
 * @see plugin_api
 *
 * @Annotation
 */
class CommerceFraudGenerator extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The offer entity type ID.
   *
   * This is the entity type ID of the entity passed to the plugin during execution.
   * For example: 'commerce_order'.
   *
   * @var string
   */
  public $entity_type;

}
