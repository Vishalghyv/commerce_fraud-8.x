<?php

namespace Drupal\commerce_fraud\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines the order number generator plugin annotation object.
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
   * The order number generator label.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The order number generator description.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

}
