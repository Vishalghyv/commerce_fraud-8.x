<?php

namespace Drupal\commerce_fraud\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure commerce fraud settings for this site.
 */
class CommerceFraudSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['commerce_fraud.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'commerce_fraud_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['commerce_fraud_caps'] = ['#type' => 'fieldset', '#collapsible' => TRUE, '#title' => t('Commerce Fraud Caps Settings')];

    $form['commerce_fraud_caps']['commerce_fraud_greylist_cap'] = [
      '#title' => t('Greylist cap'),
      '#description' => t('If an order has a fraud score greater than the number specified, it will be considered greylisted.'),
      '#default_value' => \Drupal::state()->get('commerce_fraud_greylist_cap', 10),
      '#type' => 'number',
      '#min' => 0,
      '#step' => 1,
    ];

    $form['commerce_fraud_caps']['commerce_fraud_blacklist_cap'] = [
      '#title' => t('Blacklist cap'),
      '#description' => t('If an order has a fraud score greater than the number specified, it will be considered blacklisted.'),
      '#default_value' => \Drupal::state()->get('commerce_fraud_blacklist_cap', 20),
      '#type' => 'number',
      '#min' => 0,
      '#step' => 1,
    ];

    $form['stop_order'] = [
      '#type' => 'checkbox',
      '#title' => $this
        ->t('Activate this to stop the blacklisted orders from completeing checkout'),
      '#default_value' => \Drupal::state()->get('stop_order', FALSE),
    ];

    $form['send_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#default_value' => \Drupal::state()->get('send_email', \Drupal::config('system.site')->get('mail')),
      '#required' => TRUE,
      '#description' => t('If an order is listed as blacklist its detail will be send to this email'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $greyListValue = $form_state->getValue('commerce_fraud_greylist_cap');
    $blackListValue = $form_state->getValue('commerce_fraud_blacklist_cap');
    if ($greyListValue >= $blackListValue) {
      $form_state->setErrorByName('commerce_fraud_caps][commerce_fraud_greylist_cap', t('Grey List value cannot be equal to or more than Black List value'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Set the submitted configuration setting.
    \Drupal::state()->set('commerce_fraud_blacklist_cap', $form_state->getValue('commerce_fraud_blacklist_cap'));
    \Drupal::state()->set('commerce_fraud_greylist_cap', $form_state->getValue('commerce_fraud_greylist_cap'));
    \Drupal::state()->set('stop_order', $form_state->getValue('stop_order'));
    \Drupal::state()->set('send_email', $form_state->getValue('send_email'));
    parent::submitForm($form, $form_state);
  }

}
