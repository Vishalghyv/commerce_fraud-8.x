<?php

namespace Drupal\commerce_fraud\Plugin\Commerce\FraudGenerator;

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\commerce_order\Entity\OrderInterface;

/**
 * Provides the infinite order number generator.
 *
 * @CommerceFraudGenerator(
 *   id = "last_minute",
 *   label = @Translation("Compare Last Minute with Given Minute"),
 *   description = @Translation("Checks Order Last Minute"),
 * )
 */
class LastMinuteFraudGenerator extends FraudOfferBase {

  /**
   * The ID of the item to delete.
   *
   * @var string
   */
  protected $database;

  /**
   * Constructs a new Last Minute object.
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
      'last_minute' => 5,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form += parent::buildConfigurationForm($form, $form_state);
    // Remove the main fieldset.
    $form['#type'] = 'container';

    $form['time'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Time limit'),
      '#collapsible' => FALSE,
    ];
    $form['time']['last_minute'] = [
      '#type' => 'number',
      '#title' => $this->t('Last Minute'),
      '#default_value' => $this->configuration['last_minute'],
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
    $customer_id = $order->getCustomerId();
    dpm($customer_id);
    $query = $this->database->select('commerce_order', 'o')
      ->fields('o', ['order_id'])
      ->condition('uid', $customer_id, '=')
      ->condition('state', ['completed'], 'IN')
      ->condition('placed', $this->timestamp_from_minutes($this->configuration['last_minute']), '>=');

    if (!empty($query->execute()->fetchAssoc())) {
      // Do something.
      drupal_set_message('Last order was placed within 5 minutes increase the fraud count');
      return TRUE;
    }
    return FALSE;
  }

  /**
 * Returns a timestamp matching x days before today.
 *
 * @param $minutes
 *
 * @return int
 */
  public function timestamp_from_minutes($minutes) {
  $date = new \DateTimeImmutable();
  $date = $date->modify('- ' . $minutes . ' minutes');
  dpm($date->getTimestamp());
  return $date->getTimestamp();
}

}
