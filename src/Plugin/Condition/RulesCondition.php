<?php

namespace Drupal\commerce_fraud\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;

/**
 * Provides a 'Rules condition' condition to enable a condition based in module selected status.
 *
 * @Condition(
 *   id = "rules_condition",
 *   label = @Translation("Rules condition"),
 *   context = {
 *     "commerce_order" = @ContextDefinition("entity:commerce_order", required = TRUE , label = @Translation("commerce_order"))
 *   }
 * )
 *
 */
class RulesCondition extends ConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // Sort all modules by their names.
    $modules = system_rebuild_module_data();
    uasort($modules, 'system_sort_modules_by_info_name');

    $options = [NULL => t('Select a module')];
    foreach ($modules as $module_id => $module) {
      $options[$module_id] = $module->info['name'];
    }

    $form['module'] = [
      '#type' => 'select',
      '#title' => $this->t('Select a module to validate'),
      '#default_value' => $this->configuration['module'],
      '#options' => $options,
      '#description' => $this->t('Module selected status will be use to evaluate condition.'),
    ];

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['module'] = $form_state->getValue('module');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['module' => ''] + parent::defaultConfiguration();
  }

  /**
   * Evaluates the condition and returns TRUE or FALSE accordingly.
   *
   * @return bool
   *   TRUE if the condition has been met, FALSE otherwise.
   */
  public function evaluate() {
    if (empty($this->configuration['module']) && !$this->isNegated()){
        return TRUE;
    }

    $module = $this->configuration['module'];
    $modules = system_rebuild_module_data();

    return $modules[$module]->status;
  }

  /**
   * Provides a human readable summary of the condition's configuration.
   */
  public function summary() {
    $module = $this->getContextValue('module');
    $modules = system_rebuild_module_data();

    $status = ($modules[$module]->status)?t('enabled'):t('disabled');

    return t('The module @module is @status.', ['@module' => $module, '@status' => $status]);
  }

}