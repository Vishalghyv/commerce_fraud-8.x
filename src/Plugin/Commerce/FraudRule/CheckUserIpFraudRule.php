<?php

namespace Drupal\commerce_fraud\Plugin\Commerce\FraudRule;

use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_order\Entity\OrderInterface;

/**
 * Provides the fraud rule.
 *
 * @CommerceFraudRule(
 *   id = "check_user_ip",
 *   label = @Translation("Checks If user have completed orders form different Ip address"),
 *   description = @Translation("Checks Order User IP address"),
 * )
 */
class CheckUserIpFraudRule extends FraudOfferBase {

  /**
   * The ID of the item to delete.
   *
   * @var string
   */
  protected $database;

  /**
   * Constructs a new Check User Ip object.
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
    $this->database = \Drupal::database();

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database')
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
    $customer_ip = $order->getIpAddress();
    dpm($customer_id);
    $orders_count = db_select('commerce_order', 'o')
      ->fields('o', ['hostname'])
      ->condition('uid', $order->getCustomerId(), '=')
      ->condition('hostname', [$customer_ip], 'NOT IN')
      ->distinct()
      ->countQuery()
      ->execute()
      ->fetchField();

    if ($orders_count) {
      // Do something.
      drupal_set_message('Order Customer have placed order form different Ip increase the fraud count');
      return TRUE;
    }
    return FALSE;
  }

}
