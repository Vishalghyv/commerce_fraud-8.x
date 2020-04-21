<?php

/**
 * @file
 * Custom views handler definition.
 */

/**
 * Custom handler class.
 *
 * @group views_field_handlers
 */
class views_handler_commerce_fraud_level extends views_handler_field {

  /**
   * {@inheritdoc}
   */
  public function query() {}

  /**
   * {@inheritdoc}
   */
  public function render($values) {
    require_once drupal_get_path('module', 'commerce_fraud') . '/commerce_fraud.rules.inc';
    $order = commerce_order_load($values->order_id);
    if (commerce_fraud_is_whitelisted($order)) {
      return t('Whitelisted');
    }
    elseif (commerce_fraud_is_greylisted($order)) {
      return t('Greylisted');
    }
    elseif (commerce_fraud_is_blacklisted($order)) {
      return t('Blacklisted');
    }
  }

}
