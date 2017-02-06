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
class views_handler_commerce_fraud_points extends views_handler_field {

  /**
   * {@inheritdoc}
   */
  public function query() {
  }

  /**
   * {@inheritdoc}
   */
  public function render($values) {
    require_once drupal_get_path('module', 'commerce_fraud') . '/commerce_fraud.rules.inc';
    return commerce_fraud_get_fraud_notes_for_order($values->order_id);
  }
}
