<?php

/**
 * @file
 * commerce_fraud custom rules.
 */

/**
 * Implements hook_rules_event_info().
 */
function commerce_fraud_rules_event_info() {
  $events = array();

  $events['commerce_fraud_count_changed'] = array(
    'label' => t('Fraud count changed'),
    'group' => t('Commerce Fraud'),
    'variables' => array(
      'commerce_order' => array(
        'label' => t('The commerce order'),
        'type' => 'commerce_order',
      ),
    ),
  );

  return $events;
}

/**
 * Implements hook_rules_condition_info().
 */
function commerce_fraud_rules_condition_info() {
  $conditions = array();

  $conditions['commerce_fraud_is_whitelisted'] = array(
    'label' => t('Order is whitelisted'),
    'group' => t('Commerce Fraud'),
    'parameter' => array(
      'commerce_order' => array(
        'label' => t('The commerce order'),
        'type' => 'commerce_order',
      ),
    ),
    'callbacks' => array(
      'execute' => 'commerce_fraud_rules_is_whitelisted',
    ),
  );

  $conditions['commerce_fraud_is_greylisted'] = array(
    'label' => t('Order is greylisted'),
    'group' => t('Commerce Fraud'),
    'parameter' => array(
      'commerce_order' => array(
        'label' => t('The commerce order'),
        'type' => 'commerce_order',
      ),
    ),
    'callbacks' => array(
      'execute' => 'commerce_fraud_rules_is_greylisted',
    ),
  );

  $conditions['commerce_fraud_is_blacklisted'] = array(
    'label' => t('Order is blacklisted'),
    'group' => t('Commerce Fraud'),
    'parameter' => array(
      'commerce_order' => array(
        'label' => t('The commerce order'),
        'type' => 'commerce_order',
      ),
    ),
    'callbacks' => array(
      'execute' => 'commerce_fraud_rules_is_blacklisted',
    ),
  );

  return $conditions;
}

/**
 * Whitelist condition callback.
 */
function commerce_fraud_rules_is_whitelisted(stdClass $order) {
  if (!isset($order->data[COMMERCE_FRAUD_KEY])) {
    return TRUE;
  }
  $whitelist_cap = variable_get('commerce_fraud_whitelist_upper_cap', 10);
  return $order->data[COMMERCE_FRAUD_KEY] < $whitelist_cap;
}

/**
 * Greylist condition callback.
 */
function commerce_fraud_rules_is_greylisted(stdClass $order) {
  if (!isset($order->data[COMMERCE_FRAUD_KEY])) {
    return FALSE;
  }
  $greylist_lower_cap = variable_get('commerce_fraud_greylist_lower_cap', 10);
  $greylist_upper_cap = variable_get('commerce_fraud_greylist_upper_cap', 20);
  $count = $order->data[COMMERCE_FRAUD_KEY];
  return $count >= $greylist_lower_cap && $count < $greylist_upper_cap;
}

/**
 * Blacklist condition callback.
 */
function commerce_fraud_rules_is_blacklisted(stdClass $order) {
  if (!isset($order->data[COMMERCE_FRAUD_KEY])) {
    return FALSE;
  }
  $blacklist_cap = variable_get('commerce_fraud_blacklist_lower_cap', 20);
  return $order->data[COMMERCE_FRAUD_KEY] >= $blacklist_cap;
}

/**
 * Implements hook_rules_action_info().
 */
function commerce_fraud_rules_action_info() {
  $actions = array();

  $actions['commerce_fraud_increase_fraud_count'] = array(
    'label' => t('Increase the fraud count'),
    'group' => t('Commerce Fraud'),
    'parameter' => array(
      'commerce_order' => array(
        'type' => 'commerce_order',
        'label' => t('The commerce order'),
      ),
      'counter' => array(
        'type' => 'integer',
        'label' => t('The counter'),
        'description' => t('Defines by how much the fraud count is increased.'),
        'optional' => TRUE,
        'default value' => 1,
      ),
    ),
    'callbacks' => array(
      'execute' => 'commerce_fraud_rules_increase_fraud_count',
    ),
    'access callback' => 'commerce_order_rules_access',
  );

  $actions['commerce_fraud_decrease_fraud_count'] = array(
    'label' => t('Decrease the fraud count'),
    'group' => t('Commerce Fraud'),
    'parameter' => array(
      'commerce_order' => array(
        'type' => 'commerce_order',
        'label' => t('The commerce order'),
      ),
      'counter' => array(
        'type' => 'integer',
        'label' => t('The counter'),
        'description' => t('Defines by how much the fraud count is increased.'),
        'optional' => TRUE,
        'default value' => 1,
      ),
    ),
    'callbacks' => array(
      'execute' => 'commerce_fraud_rules_decrease_fraud_count',
    ),
    'access callback' => 'commerce_order_rules_access',
  );

  $actions['commerce_fraud_reset_fraud_count'] = array(
    'label' => t('Reset the fraud count'),
    'group' => t('Commerce Fraud'),
    'parameter' => array(
      'commerce_order' => array(
        'type' => 'commerce_order',
        'label' => t('The commerce order'),
      ),
    ),
    'callbacks' => array(
      'execute' => 'commerce_fraud_rules_reset_fraud_count',
    ),
    'access callback' => 'commerce_order_rules_access',
  );

  return $actions;
}

/**
 * Rules action: increases the fraud count of an order.
 *
 * @param stdClass $order
 *   The current commerce_order.
 * @param integer $counter
 *   By how much to increase the fraud count.
 */
function commerce_fraud_rules_increase_fraud_count(stdClass $order, $counter) {
  if (!isset($order->data[COMMERCE_FRAUD_KEY])) {
    $order->data[COMMERCE_FRAUD_KEY] = 0;
  }
  $order->data[COMMERCE_FRAUD_KEY] = $order->data[COMMERCE_FRAUD_KEY] + $counter;
  rules_invoke_event('commerce_fraud_count_changed', $order);
}

/**
 * Rules action: decreases the fraud count of an order.
 *
 * @param stdClass $order
 *   The current commerce_order.
 * @param integer $counter
 *   By how much to decrease the fraud count.
 */
function commerce_fraud_rules_decrease_fraud_count(stdClass $order, $counter) {
  if (!isset($order->data[COMMERCE_FRAUD_KEY])) {
    $order->data[COMMERCE_FRAUD_KEY] = 0;
  }
  $order->data[COMMERCE_FRAUD_KEY] = $order->data[COMMERCE_FRAUD_KEY] - $counter;
  rules_invoke_event('commerce_fraud_count_changed', $order);
}

/**
 * Rules action: resets the fraud count of an order.
 *
 * @param stdClass $order
 *   The current commerce_order.
 */
function commerce_fraud_rules_reset_fraud_count(stdClass $order) {
  $order->data[COMMERCE_FRAUD_KEY] = 0;
  rules_invoke_event('commerce_fraud_count_changed', $order);
}