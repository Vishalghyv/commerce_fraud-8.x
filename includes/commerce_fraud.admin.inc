<?php

/**
 * @file
 * The admin settings form.
 */

/**
 * Administration form for commerce_fraud.
 */
function commerce_fraud_admin_settings() {
  $form = array();

  /*=========Caps========*/
  $form['commerce_fraud_caps'] = array(
    '#type' => 'fieldset',
    '#collapsible' => TRUE,
    '#title' => t('Commerce Fraud Caps Settings'),
  );

  $form['commerce_fraud_caps']['commerce_fraud_greylist_cap'] = array(
    '#type' => 'textfield',
    '#title' => t('Greylist cap'),
    '#description' => t('If an order has a fraud score greater than the number specified, it will be considered greylisted.'),
    '#default_value' => variable_get('commerce_fraud_greylist_cap', 10),
    '#element_validate' => array('element_validate_integer'),
  );

  $form['commerce_fraud_caps']['commerce_fraud_blacklist_cap'] = array(
    '#type' => 'textfield',
    '#title' => t('Blacklist cap'),
    '#description' => t('If an order has a fraud score greater than the number specified, it will be considered blacklisted.'),
    '#default_value' => variable_get('commerce_fraud_blacklist_cap', 20),
    '#element_validate' => array('element_validate_integer'),
  );

  return system_settings_form($form);
}
