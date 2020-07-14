<?php

namespace Drupal\commerce_fraud\Plugin\Commerce\FraudRule;

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\profile\Entity\Profile;

/**
 * Provides the fraud rule.
 *
 * @CommerceFraudRule(
 *   id = "fradulent_locality",
 *   label = @Translation("Check if order locality in one of check listed locality set in settings"),
 *   description = @Translation("Checks if order locality in one of check listed locality set in settings"),
 * )
 */
class FraudulentLocality extends FraudRuleBase {

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

    $order_locality = $order->getBillingProfile()->get('address')->locality;

    $locality = \Drupal::state()->get('checklisted_locality', []);

    return in_array($order_locality, $locality);
  }

}
