<?php

namespace Drupal\commerce_fraud\Plugin\Commerce\FraudGenerator;

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_price\Price;
/**
 * Provides the infinite order number generator.
 *
 * @CommerceFraudGenerator(
 *   id = "total_price",
 *   label = @Translation("Compare Total Price with Given Price"),
 *   description = @Translation("Checks Order Total Price"),
 * )
 */
class TotalPriceFraudGenerator extends FraudOfferBase {

  /**
   * Constructs a new Total Price object.
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
      'buy_price' => 100,
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
      '#title' => $this->t('Price limit'),
      '#description' => 'This value will be checked according to currency code of the order',
      '#collapsible' => FALSE,
    ];
    $form['buy']['price'] = [
      '#type' => 'number',
      '#title' => $this->t('Price'),
      '#default_value' => $this->configuration['buy_price'],
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
    $order_price = $order->getTotalPrice();
    drupal_set_message("Currency code{$order_price->getCurrencyCode()}");
    $price = $this->configuration['buy_price'];
    $new_price = new Price($price, $order_price->getCurrencyCode());

    drupal_set_message("nv{$new_price}");
    if ($order_price->greaterThan($new_price)) {
      // Do something.
      drupal_set_message('Price is greater than 1000 INR increase the fraud count');
      return TRUE;
    }
    return FALSE;
  }

}
