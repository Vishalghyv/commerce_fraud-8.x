<?php

namespace Drupal\commerce_fraud\Plugin\Commerce\FraudGenerator;

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\commerce_order\Entity\OrderInterface;

/**
 * Provides the infinite order number generator.
 *
 * @CommerceFraudGenerator(
 *   id = "total_quantity",
 *   label = @Translation("Compare Total Quantity with Given Quantity"),
 *   description = @Translation("Checks Order Total Quantity"),
 * )
 */
class TotalQuantityFraudGenerator extends FraudOfferBase {

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
  public function defaultConfiguration() {
    return [
      'buy_quantity' => 10,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form += parent::buildConfigurationForm($form, $form_state);
    // Remove the main fieldset.
    $form['#type'] = 'container';

    $form['buy'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Quantity limit'),
      '#collapsible' => FALSE,
    ];
    $form['buy']['quantity'] = [
      '#type' => 'number',
      '#title' => $this->t('Quantity'),
      '#default_value' => $this->configuration['buy_quantity'],
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
    $order_item = $order->getItems();
    $quantity = 0;
    foreach ($order_item as $item) {
      $quantity += number_format($item->getQuantity());
    }

    if ($quantity > $this->configuration['buy_quantity']) {
      // Do something.
      drupal_set_message('Quantity is greater than 10 increase the fraud count');
      return TRUE;
    }
    return FALSE;
  }

}
