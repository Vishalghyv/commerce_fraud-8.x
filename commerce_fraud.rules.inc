<?php

/**
 * @file
 * Commerce Fraud custom rules.
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

  return $events;
}

/**
 * Implements hook_rules_condition_info().
 */
function commerce_fraud_rules_condition_info() {
  $conditions = array();

  $conditions['commerce_fraud_compare_fraud_level'] = array(
    'label' => t('Order has fraud level'),
    'group' => t('Commerce Fraud'),
    'parameter' => array(
      'commerce_order' => array(
        'type' => 'commerce_order',
        'label' => t('Order'),
      ),
      'commerce_fraud_level_function' => array(
        'type' => 'text',
        'label' => t('Operator'),
        'options list' => 'commerce_fraud_order_condition_fraud_level_operator_options',
      ),
    ),
    'callbacks' => array(
      'execute' => 'commerce_fraud_rules_compare_fraud_level',
    ),
  );

  $conditions['commerce_fraud_has_po_box'] = array(
    'label' => t('Order shipping address has po box'),
    'group' => t('Commerce Fraud'),
    'parameter' => array(
      'commerce_order' => array(
        'label' => t('The commerce order'),
        'type' => 'commerce_order',
      ),
    ),
    'callbacks' => array(
      'execute' => 'commerce_fraud_rules_has_po_box',
    ),
  );

  $conditions['commerce_fraud_country_matches_ip'] = array(
    'label' => t('The given country matches the users IP address'),
    'group' => t('Commerce Fraud'),
    'parameter' => array(
      'country' => array(
        'label' => t('The country'),
        'type' => 'addressfield',
      ),
    ),
    'callbacks' => array(
      'execute' => 'commerce_fraud_rules_country_matches_ip',
    ),
  );

  $conditions['commerce_fraud_user_ip_in_country'] = array(
    'label' => t('The users IP address is in specified countries'),
    'group' => t('Commerce Fraud'),
    'parameter' => array(
      'countries' => array(
        'type' => 'text',
        'label' => t('Countries'),
        'description' => t('Two character Country Codes'),
      ),
    ),
    'callbacks' => array(
      'execute' => 'commerce_fraud_rules_user_ip_in_country',
    ),
  );

  $conditions['commerce_fraud_last_order'] = array(
    'label' => t('Order placed within x minutes from most recent order'),
    'group' => t('Commerce Fraud'),
    'parameter' => array(
      'commerce_order' => array(
        'label' => t('The current commerce order'),
        'type' => 'commerce_order',
      ),
      'minutes' => array(
        'label' => t('Number of minutes'),
        'type' => 'integer',
      ),
      'order_statuses' => array(
        'label' => t('Order statuses to compare'),
        'type' => 'list<text>',
        'options list' => 'commerce_order_status_options_list',
      ),
    ),
    'callbacks' => array(
      'execute' => 'commerce_fraud_rules_last_order',
    ),
  );

  return $conditions;
}

/**
 * Whitelist condition callback.
 *
 * @param $order
 *
 * @return bool
 */
function commerce_fraud_rules_is_whitelisted($order) {
  return commerce_fraud_is_whitelisted($order);
}

/**
 * Greylist condition callback.
 *
 * @param $order
 *
 * @return bool
 */
function commerce_fraud_rules_is_greylisted($order) {
  return commerce_fraud_is_greylisted($order);
}

/**
 * Blacklist condition callback.
 *
 * @param $order
 *
 * @return bool
 */
function commerce_fraud_rules_is_blacklisted($order) {
  return commerce_fraud_is_blacklisted($order);
}

/**
 * Fraud level condition callback.
 *
 * @param $order
 * @param $fn
 *
 * @return mixed
 */
function commerce_fraud_rules_compare_fraud_level($order, $fn) {
  return $fn($order);
}

/**
 * PO Box condition callback.
 *
 * @param $order
 *
 * @return bool
 */
function commerce_fraud_rules_has_po_box($order) {
  return commerce_fraud_order_has_pobox($order);
}

/**
 * Callback for the country matches ip condition.
 *
 * @param $address
 *
 * @return bool
 */
function commerce_fraud_rules_country_matches_ip($address) {
  $ip_address = ip_address();
  $guessed_country = ip2country_get_country($ip_address);
  if ($guessed_country === FALSE) {
    return TRUE;
  }

  return $address['country'] === $guessed_country;
}

/**
 * Callback for the user ip in countries condition.
 *
 * @param $countries
 *
 * @return bool
 */
function commerce_fraud_rules_user_ip_in_country($countries) {
  $ip_address = ip_address();
  $guessed_country = ip2country_get_country($ip_address);
  if ($guessed_country === FALSE) {
    return TRUE;
  }

  return strpos($countries, $guessed_country) !== FALSE;
}

/**
 * Callback for last order condition.
 *
 * @param $order
 * @param $minutes
 * @param $order_statuses
 *
 * @return bool
 */
function commerce_fraud_rules_last_order($order, $minutes, $order_statuses) {
  return commerce_fraud_last_order($order, $minutes, $order_statuses);
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
        'default value' => 1,
      ),
      'description' => array(
        'type' => 'text',
        'label' => t('Rule description'),
        'description' => t('Brief description of the rule that will be displayed in the fraud points breakdown when viewing the order.'),
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
        'default value' => 1,
      ),
      'description' => array(
        'type' => 'text',
        'label' => t('Rule description'),
        'description' => t('Brief description of the rule that will be displayed in the fraud points breakdown when viewing the order.'),
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
 * @param $order
 *   The current commerce_order.
 * @param int $counter
 *   By how much to increase the fraud count.
 * @param $description
 *   Description for fraud points breakdown.
 */
function commerce_fraud_rules_increase_fraud_count($order, $counter, $description) {
  commerce_fraud_write_record($order, $counter, $description);
}

/**
 * Rules action: decreases the fraud count of an order.
 *
 * @param $order
 *   The current commerce_order.
 * @param int $counter
 *   By how much to decrease the fraud count.
 */
function commerce_fraud_rules_decrease_fraud_count($order, $counter, $description) {
  commerce_fraud_write_record($order, -$counter, $description);
}

/**
 * Rules action: resets the fraud count of an order.
 *
 * @param $order
 *   The current commerce_order.
 */
function commerce_fraud_rules_reset_fraud_count($order) {
  commerce_fraud_reset_fraud_count($order);
}
