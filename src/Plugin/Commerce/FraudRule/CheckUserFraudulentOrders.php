<?php

namespace Drupal\commerce_fraud\Plugin\Commerce\FraudRule;

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\commerce_order\Entity\OrderInterface;

/**
 * Provides the fraud rule.
 *
 * @CommerceFraudRule(
 *   id = "fraudulent_orders",
 *   label = @Translation("Check number of fraudulent orders from User"),
 *   description = @Translation("Checks number of fraudulent orders from User"),
 * )
 */
class CheckUserFraudulentOrders extends FraudRuleBase {

  /**
   * Database.
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
  public function defaultConfiguration() {
    return [
      'fraudulent_orders' => 3,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form += parent::buildConfigurationForm($form, $form_state);
    $form['#type'] = 'container';
    $form['#title'] = $this->t('Rule');
    $form['#collapsible'] = FALSE;

    $form['number'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Number of Fraudulent Orders limit'),
      '#collapsible' => FALSE,
    ];
    $form['number']['fraudulent_orders'] = [
      '#type' => 'number',
      '#title' => $this->t('Limit'),
      '#default_value' => $this->configuration['fraudulent_orders'],
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
      $this->configuration['fraudulent_orders'] = $values['number']['fraudulent_orders'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function apply(OrderInterface $order) {
    $orders_count = $this->database->select('commerce_order', 'o')
      ->fields('o', ['order_id'])
      ->condition('uid', $order->getCustomerId(), '=')
      ->condition('state', ['fraudulent'], 'IN')
      ->countQuery()
      ->execute()
      ->fetchField();

    if ((int) $orders_count > $this->configuration['fraudulent_orders']) {

      return TRUE;
    }
    return FALSE;
  }

}
