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

  /*=========Store Settings========*/
  $form['commerce_fraud_store_settings'] = array(
    '#type' => 'fieldset',
    '#collapsible' => TRUE,
    '#title' => t('Thresholds and Penalties'),
  );

  $form['commerce_fraud_store_settings']['commerce_fraud_order_total'] = array(
    '#type' => 'fieldset',
    '#collapsible' => TRUE,
    '#title' => t('Order Total'),
  );

  $form['commerce_fraud_store_settings']['commerce_fraud_order_total']['commerce_fraud_order_total_threshold'] = array(
    '#type' => 'textfield',
    '#title' => t('Threshold'),
    '#description' => t('This number represents an unusual order total amount.
      The closer the order total is to this value, the higher the penalty will be.'),
    '#default_value' => variable_get('commerce_fraud_store_threshold', 500),
    '#element_validate' => array('element_validate_integer'),
  );

  $form['commerce_fraud_store_settings']['commerce_fraud_order_total']['commerce_fraud_order_total_penalty'] = array(
    '#type' => 'textfield',
    '#title' => t('Penalty'),
    '#description' => t('This value will be added to the fraud score for orders with totals over the Order Total Threshold.'),
    '#default_value' => variable_get('commerce_fraud_order_total_penalty', 0),
    '#element_validate' => array('element_validate_integer'),
  );

  $form['commerce_fraud_store_settings']['commerce_fraud_order_item_count'] = array(
    '#type' => 'fieldset',
    '#collapsible' => TRUE,
    '#title' => t('Order Item Count'),
  );

  $form['commerce_fraud_store_settings']['commerce_fraud_order_item_count']['commerce_fraud_order_item_count_threshold'] = array(
    '#type' => 'textfield',
    '#title' => t('Threshold'),
    '#description' => t('This number represents an unusual order item count.
      The closer the order total is to this value, the higher the penalty will be.'),
    '#default_value' => variable_get('commerce_fraud_order_item_count_threshold', 50),
    '#element_validate' => array('element_validate_integer'),
  );

  $form['commerce_fraud_store_settings']['commerce_fraud_order_item_count']['commerce_fraud_order_item_count_penalty'] = array(
    '#type' => 'textfield',
    '#title' => t('Penalty'),
    '#description' => t('This value will be added to the fraud score for orders with item counts over the Order Item Count Threshold.'),
    '#default_value' => variable_get('commerce_fraud_order_item_count_penalty', 0),
    '#element_validate' => array('element_validate_integer'),
  );

  $form['commerce_fraud_store_settings']['commerce_fraud_po_box_penalty'] = array(
    '#type' => 'textfield',
    '#title' => t('P.O. Box Penalty'),
    '#description' => t('This value will be added to the fraud score for orders with a P.O. Box.'),
    '#default_value' => variable_get('commerce_fraud_po_box_penalty', 0),
    '#element_validate' => array('element_validate_integer'),
  );

  /*=========Caps========*/
  $form['commerce_fraud_caps'] = array(
    '#type' => 'fieldset',
    '#collapsible' => TRUE,
    '#title' => t('Commerce Fraud Caps Settings'),
  );

  $form['commerce_fraud_caps']['commerce_fraud_whitelist_upper_cap'] = array(
    '#type' => 'textfield',
    '#title' => t('Whitelist upper cap'),
    '#description' => t('The upper cap for an order to be considered whitelisted.'),
    '#default_value' => variable_get('commerce_fraud_whitelist_upper_cap', 10),
    '#element_validate' => array('element_validate_integer'),
  );

  $form['commerce_fraud_caps']['commerce_fraud_blacklist_lower_cap'] = array(
    '#type' => 'textfield',
    '#title' => t('Blacklist lower cap'),
    '#description' => t('The lower cap for an order to be considered blacklisted.'),
    '#default_value' => variable_get('commerce_fraud_blacklist_lower_cap', 20),
    '#element_validate' => array('element_validate_integer'),
  );

  return system_settings_form($form);
}
