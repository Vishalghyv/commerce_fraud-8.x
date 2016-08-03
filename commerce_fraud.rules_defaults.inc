<?php

/**
 * @file
 * Default fraud rules.
 */

/**
 * Implements hook_default_rules_configuration().
 */
function commerce_fraud_default_rules_configuration() {
  $rules = array();

  $rule = rules_reaction_rule();
  $rule->label = t('Set order status as fraudulent if order is blacklisted.');
  $rule->tags = array('Commerce Fraud');
  $rule->active = FALSE;
  $rule
    ->event('commerce_checkout_complete')
    ->condition('commerce_fraud_is_blacklisted', array(
      'commerce_order:select' => 'commerce_order',
    ))
    ->action('commerce_order_update_status', array(
      'commerce_order:select' => 'commerce_order',
      'order_status' => 'fraudulent'
    ));

  $rules['commerce_fraud_over_threshold_increase'] = $rule;

  $rule = rules_reaction_rule();
  $rule->label = t('Increase Fraud Count if Order has X products.');
  $rule->tags = array('Commerce Fraud');
  $rule->active = FALSE;
  $rule
    ->event('commerce_order_presave')
    ->condition('commerce_order_compare_total_product_quantity', array(
      'commerce_order:select' => 'commerce_order',
      'operator' => 'contains',
      'value' => variable_get('commerce_fraud_store_threshold', 1000),
    ))
    ->action('commerce_fraud_increase_fraud_count', array(
      'commerce_order:select' => 'commerce_order',
    ));

  $rules['commerce_fraud_item_quantity_increase'] = $rule;

  $rule = rules_reaction_rule();
  $rule->label = t('Increase Fraud Count if User IP in Selected Countries');
  $rule->tags = array('Commerce Fraud');
  $rule->active = FALSE;
  $rule
    ->event('commerce_order_presave')
    ->condition('entity_has_field', array(
      'entity:select' => 'commerce-customer-profile',
    ))
    ->condition('commerce_fraud_country_matches_ip', array(
      'country:select' => 'commerce-customer-profile:commerce-customer-address'
    ))
    ->action('commerce_fraud_increase_fraud_count', array(
      'commerce_order:select' => 'commerce_order',
    ));

  $rules['commerce_fraud_country_ip_increase'] = $rule;

  return $rules;
}
