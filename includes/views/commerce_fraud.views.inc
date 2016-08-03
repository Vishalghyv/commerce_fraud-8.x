<?php

/**
 * Implements hook_views_data().
 */
function commerce_fraud_views_data() {
  $data['commerce_fraud']['table']['group'] = t('Commerce Fraud');
  $data['commerce_fraud']['table']['join'] = array(
    // Exist in all views.
    '#global' => array(),
  );

  $data['commerce_fraud']['commerce_fraud_level'] = array(
    'title' => t('Commerce Fraud Level'),
    'help' => t('Shows the fraud level.'),
    'field' => array(
      'handler' => 'views_handler_commerce_fraud_level',
    ),
  );

  return $data;
}
