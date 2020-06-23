<?php

namespace Drupal\commerce_fraud\Form;

use Drupal\commerce_fraud\CommerceFraudManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * 
.
 *
 * Configure example settings for this site.
 */
class CommerceFraudSettingsForm extends ConfigFormBase {
  /**
   * 
.
   *
   * Config settings.
   *
   * @var string
   */

  /**
   * 
.
   *
   * The order number generator manager.
   *
   * @var \Drupal\commerce_order_number\OrderNumberGeneratorManager
   */
  // protected $commerceFraudManager;

  /**
   * 
.
   *
   * {@inheritdoc}
   */

  /**
   *
   */
  protected function getEditableConfigNames() {
    return ['commerce_fraud.settings'];
  }

  /**
   * 
.
   *
   * {@inheritdoc}
   */

  /**
   *
   */
  public function getFormId() {
    return 'commerce_fraud_admin_settings';
  }

  /**
   * 
.
   *
   * Constructs a new SettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\commerce_order_number\OrderNumberGeneratorManager $order_number_generator_manager
   *   The order number generator manager.
   * @param \Drupal\commerce_order_number\OrderNumberFormatterInterface $order_number_formatter
   *   The order number formatter.
   */

  /**
   *
   */
  // public function __construct(ConfigFactoryInterface $config_factory, CommerceFraudManager $commerce_fraud_manager) {
  //   parent::__construct($config_factory);

  //   $this->commerceFraudManager = $commerce_fraud_manager;
  // }

  /**
   * 
.
   *
   * {@inheritdoc}
   */

  /**
   *
   */
  // public static function create(ContainerInterface $container) {
  //   return new static($container->get('config.factory'), $container->get('plugin.manager.commerce_fraud_generator'));
  // }

  /**
   * 
.
   *
   * {@inheritdoc}
   */

  /**
   *
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // $config = $this->config('commerce_fraud.settings');

    // $form['generator'] = ['#type' => 'details', '#title' => $this->t('Fraud generation'), '#open' => TRUE];
    // drupal_set_message('df');

    // $generator_plugins = array_map(function ($definition) {
    //   return sprintf('%s (%s)', $definition['label'], $definition['description']);
    // }, $this->commerceFraudManager->getDefinitions());

    // $form['generator']['generator'] = ['#type' => 'select', '#options' => $generator_plugins, '#required' => TRUE, '#default_value' => $config->get('generator'), '#title' => $this->t('Generator plugin'), '#description' => $this->t('Choose the plugin to be used for fraud generation.')];

    $form['commerce_fraud_caps'] = ['#type' => 'fieldset', '#collapsible' => TRUE, '#title' => t('Commerce Fraud Caps Settings')];

    $form['commerce_fraud_caps']['commerce_fraud_greylist_cap'] = ['#type' => 'textfield', '#title' => t('Greylist cap'), '#description' => t('If an order has a fraud score greater than the number specified, it will be considered greylisted.'), '#default_value' => \Drupal::state()->get('commerce_fraud_greylist_cap', 10), '#element_validate' => ['element_validate_integer']];

    $form['commerce_fraud_caps']['commerce_fraud_blacklist_cap'] = ['#type' => 'textfield', '#title' => t('Blacklist cap'), '#description' => t('If an order has a fraud score greater than the number specified, it will be considered blacklisted.'), '#default_value' => \Drupal::state()->get('commerce_fraud_blacklist_cap', 20), '#element_validate' => ['element_validate_integer']];

    return parent::buildForm($form, $form_state);
  }

  /**
   * 
.
   */

  /**
   *
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $greyListValue = $form_state->getValue('commerce_fraud_greylist_cap');
    $blackListValue = $form_state->getValue('commerce_fraud_blacklist_cap');
    if ($greyListValue >= $blackListValue) {
      $form_state->setErrorByName('commerce_fraud_caps][commerce_fraud_greylist_cap', t('Grey List value cannot be equal to or more than Black List value'));
    }
  }

  /**
   * 
.
   *
   * {@inheritdoc}
   */

  /**
   *
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // $this->config('commerce_fraud.settings')->set('generator', $form_state->getValue('generator'))->save();
    // Set the submitted configuration setting.
    \Drupal::state()->set('commerce_fraud_blacklist_cap', $form_state->getValue('commerce_fraud_blacklist_cap'));
    \Drupal::state()->set('commerce_fraud_greylist_cap', $form_state->getValue('commerce_fraud_greylist_cap'));

    parent::submitForm($form, $form_state);
  }

}
