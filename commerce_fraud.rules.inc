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
        'label' => t('The Commerce Order'),
        'type' => 'commerce_order',
      ),
    ),
  );
  $events['commerce_fraud_calculating_fraud_score'] = array(
    'label' => t('Calculating Fraud Score for Commerce Order'),
    'group' => t('Commerce Fraud'),
    'variables' => array(
      'commerce_order' => array(
        'label' => t('The Commerce Order'),
        'type' => 'commerce_order',
      ),
      'fraud_score' => array(
        'label' => t('The current Fraud score'),
        'type' => 'integer',
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

  $conditions['commerce_fraud_country_matches_ip'] = array(
    'label' => t('The given country matches the IP address'),
    'group' => t('Commerce Fraud'),
    'parameter' => array(
      'country' => array(
        'label' => t('The country'),
        'type' => 'addressfield',
      ),
    ),
    'callbacks' => array(
      'execute' => 'commerce_fraud_country_matches_ip',
    ),
  );

  return $conditions;
}

/**
 * Whitelist condition callback.
 */
function commerce_fraud_rules_is_whitelisted(stdClass $order) {
  if ($fraud_score = commerce_fraud_get_fraud_score_for_order_id($order->order_id)) {
    $whitelist_cap = variable_get('commerce_fraud_whitelist_upper_cap', 10);
    return $fraud_score < $whitelist_cap;
  }
  else {
    return TRUE;
  }
}

/**
 * Greylist condition callback.
 */
function commerce_fraud_rules_is_greylisted(stdClass $order) {
  if ($fraud_score = commerce_fraud_get_fraud_score_for_order_id($order->order_id)) {
    $greylist_lower_cap = variable_get('commerce_fraud_whitelist_upper_cap', 10);
    $greylist_upper_cap = variable_get('commerce_fraud_blacklist_lower_cap', 20);
    return $fraud_score >= $greylist_lower_cap && $fraud_score < $greylist_upper_cap;
  }
  else {
    return FALSE;
  }
}

/**
 * Blacklist condition callback.
 */
function commerce_fraud_rules_is_blacklisted(stdClass $order) {
  if ($fraud_score = commerce_fraud_get_fraud_score_for_order_id($order->order_id)) {
    $blacklist_cap = variable_get('commerce_fraud_blacklist_lower_cap', 20);
    return $fraud_score >= $blacklist_cap;
  }
  else {
    return FALSE;
  }
}

/**
 * Callback for the country matches ip condition.
 */
function commerce_fraud_country_matches_ip($address) {
  $ip_address = ip_address();
  $guessed_country = ip2country_get_country($ip_address);
  if ($guessed_country === FALSE) {
    return TRUE;
  }

  return $address['country'] === $guessed_country;
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
  commerce_fraud_increase_fraud_count($order, $counter);
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
  commerce_fraud_decrease_fraud_count($order, $counter);
}

/**
 * Rules action: resets the fraud count of an order.
 *
 * @param stdClass $order
 *   The current commerce_order.
 */
function commerce_fraud_rules_reset_fraud_count(stdClass $order) {
  commerce_fraud_reset_fraud_count($order);
}