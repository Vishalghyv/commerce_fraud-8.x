<?php

namespace Drupal\commerce_fraud\Plugin\Commerce\FraudRule;

use Drupal\commerce\ConditionGroup;
use Drupal\commerce\ConditionManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\commerce_order\Entity\OrderInterface;

/**
 * Provides the infinite order number generator.
 *
 * @CommerceFraudRule(
 *   id = "product_attribute",
 *   label = @Translation("Check Product Attribute"),
 *   description = @Translation("Checks Product Attribute"),
 * )
 */
class ProductAttributeFraudRule extends FraudOfferBase {

  /**
   * The condition manager.
   *
   * @var \Drupal\commerce\ConditionManagerInterface
   */
  protected $conditionManager;

  /**
   * Constructs a new Total Quantity object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The pluginId for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConditionManagerInterface $condition_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->conditionManager = $condition_manager;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.commerce_condition')
    );
  }

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
    $form += parent::buildConfigurationForm($form, $form_state);
    // Remove the main fieldset.
    $form['#type'] = 'container';

    $form['product'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Product Attributes'),
      '#collapsible' => FALSE,
    ];
    $form['product']['conditions'] = [
      '#type' => 'commerce_conditions',
      '#title' => $this->t('Matching any of the following'),
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
      $this->configuration['product_conditions'] = $values['product']['conditions'];
    }
  }

  /**
   *
   */
  public function apply(OrderInterface $order) {
    // $this->assertEntity($entity);
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    // $order = $entity;
    $order_item = $order->getItems();
    $quantity = 0;
    $product_conditions = $this->buildConditionGroup($this->configuration['product_conditions']);
    dpm($product_conditions);
    // Foreach ($order_item as $item) {
    //   $quantity += number_format($item->getQuantity());
    // }
    // If ($quantity > $this->configuration['buy_quantity']) {
    //   // Do something.
    //   drupal_set_message('Quantity is greater than 10 increase the fraud count');
    //   return TRUE;
    // }.
    return TRUE;
  }

  /**
   * Builds a condition group for the given condition configuration.
   *
   * @param array $condition_configuration
   *   The condition configuration.
   *
   * @return \Drupal\commerce\ConditionGroup
   *   The condition group.
   */
  protected function buildConditionGroup(array $condition_configuration) {
    $conditions = [];
    foreach ($condition_configuration as $condition) {
      if (!empty($condition['plugin'])) {
        $conditions[] = $this->conditionManager->createInstance($condition['plugin'], $condition['configuration']);
      }
    }

    return new ConditionGroup($conditions, 'OR');
  }

}
