<?php

namespace Drupal\commerce_fraud\Plugin\Commerce\FraudRule;

use Drupal\commerce\Plugin\Commerce\Condition\ConditionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the base class for fraud item rules.
 */
abstract class FraudConditionBase extends FraudRuleBase implements FraudConditionInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'product_conditions' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['product']['product_conditions'] = [
      '#type' => 'commerce_conditions',
      '#title' => $this->t('Applies to'),
      '#parent_entity_type' => 'rules',
      '#entity_types' => ['commerce_order_item'],
      '#default_value' => $this->configuration['product_conditions'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration = [];
      $this->configuration['product_conditions'] = $values['product_conditions'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getConditions() {
    $plugin_manager = \Drupal::service('plugin.manager.commerce_condition');
    $conditions = [];
    foreach ($this->configuration['product_conditions'] as $condition) {
      $conditions[] = $plugin_manager->createInstance($condition['plugin'], $condition['configuration']);
    }
    return $conditions;
  }

  /**
   * {@inheritdoc}
   */
  public function setConditions(array $conditions) {
    $this->configuration['product_conditions'] = [];
    foreach ($conditions as $condition) {
      if ($condition instanceof ConditionInterface) {
        $this->configuration['product_conditions'][] = [
          'plugin' => $condition->getPluginId(),
          'configuration' => $condition->getConfiguration(),
        ];
      }
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getConditionOperator() {
    return 'OR';
  }

}
