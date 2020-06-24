<?php

namespace Drupal\commerce_fraud\Plugin\Commerce\FraudGenerator;

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\commerce_order\Entity\OrderInterface;

/**
 * Provides the infinite order number generator.
 *
 * @CommerceFraudGenerator(
 *   id = "po_box",
 *   label = @Translation("Check if order address have Po Box"),
 *   description = @Translation("Checks Order Address for Po Box"),
 * )
 */
class PoBoxFraudGenerator extends FraudOfferBase {

  /**
   * Constructs a new Po Box object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The pluginId for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
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

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form += parent::buildConfigurationForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
    }
  }

  /**
   * @inheritDoc
   */
  public function generate() {
    drupal_set_message('This message is from plugin rules');
    $order_number = 5;
    return $order_number;
  }

  /**
   *
   */
  public function apply(OrderInterface $order) {
    // $this->assertEntity($entity);
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    // $order = $entity;
    $order_profile = $order->billing_profile->entity->address->getValue();

    if ($this->contains_po_box($order_profile[0]['address_line1']) || $this->contains_po_box($order_profile[0]['address_line2'])) {
      // Do something.
      drupal_set_message('Po Box is present in the order increase the fraud count');
      return TRUE;
    }
    return FALSE;
  }

  /**
   *
   */
  public function contains_po_box(string $address) {
    return preg_match("/\s*((?:P(?:OST)?.?\s*(?:O(?:FF(?:ICE)?)?)?.?\s*(?:B(?:IN|OX)?)?)+|(?:B(?:IN|OX)+\s+)+)\s*\d+\s*(^|\s|$)/i", $address);
  }

}
