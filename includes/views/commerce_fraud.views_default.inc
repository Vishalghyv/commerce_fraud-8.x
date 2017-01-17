<?php

/**
 * @file
 * Commerce Fraud default views.
 */

/**
 * Implements hook_views_default_views_alter().
 */
function commerce_fraud_views_default_views_alter(&$views) {
  if (array_key_exists('commerce_orders', $views)) {
    // Add Commerce Fraud Level to Commerce Orders View.
    $views['commerce_orders']->display['default']->display_options['fields']['commerce_fraud_level']['id'] = 'commerce_fraud_level';
    $views['commerce_orders']->display['default']->display_options['fields']['commerce_fraud_level']['table'] = 'commerce_fraud';
    $views['commerce_orders']->display['default']->display_options['fields']['commerce_fraud_level']['field'] = 'commerce_fraud_level';
  }
  if (array_key_exists('commerce_backoffice_orders', $views)) {
    // Add Commerce Fraud Level to Commerce Backoffice Orders View.
    $views['commerce_backoffice_orders']->display['default']->display_options['fields']['commerce_fraud_level']['id'] = 'commerce_fraud_level';
    $views['commerce_backoffice_orders']->display['default']->display_options['fields']['commerce_fraud_level']['table'] = 'commerce_fraud';
    $views['commerce_backoffice_orders']->display['default']->display_options['fields']['commerce_fraud_level']['field'] = 'commerce_fraud_level';
  }
}
