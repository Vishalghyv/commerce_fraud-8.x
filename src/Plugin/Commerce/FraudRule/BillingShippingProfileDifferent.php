<?php

namespace Drupal\commerce_fraud\Plugin\Commerce\FraudRule;

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\commerce_order\Entity\OrderInterface;

/**
 * Provides the fraud rule.
 *
 * @CommerceFraudRule(
 *   id = "billing_shipping_profile_different",
 *   label = @Translation("Check if order billing and shipping profile are different"),
 *   description = @Translation("Checks if order billing and shipping profile are different"),
 * )
 */
class BillingShippingProfileDifferent extends FraudRuleBase {

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
  public function apply(OrderInterface $order) {

    $profiles = $order->collectProfiles();
    if(\Drupal::moduleHandler()->moduleExists('drupal/commerce_shipping')){
      if (isset($profiles['billing']) && isset($profiles['shipping'])) {
        return $profiles['shipping']->equalToProfile($profiles['billing']);
      }
    }
    return FALSE;
  }

}
