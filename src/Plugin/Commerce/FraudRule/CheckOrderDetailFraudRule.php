<?php

namespace Drupal\commerce_fraud\Plugin\Commerce\FraudRule;

use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;

/**
 * Provides the fraud rule.
 *
 * @CommerceFraudRule(
 *   id = "check_order_detail",
 *   label = @Translation("Compares order details with given details"),
 *   description = @Translation("Compare order details with given details"),
 * )
 */
class CheckOrderDetailFraudRule extends FraudRuleBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'order_id' => 0,
      'order_country_code' => '',
      'order_first_name' => '',
      'order_company_name' => '',
      'order_street_address' => '',
      'order_administrative_area' => '',
      'order_locality' => '',
      'order_area_code' => 0,
      'order_field' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form += parent::buildConfigurationForm($form, $form_state);

    $form['#type'] = 'container';
    $form['#title'] = $this->t('Rule');
    $form['#collapsible'] = FALSE;
    
    $parents = array_merge($form['#parents'], ['order_detail', 'type']);
    $order_field = NestedArray::getValue($form_state->getUserInput(), $parents);
    $order_wrapper = Html::getUniqueId('order-detail-wrapper');
    $order_field = $order_field ?? $this->configuration['order_field'];

    $form['order_detail'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Order detail to be checked'),
      '#collapsible' => FALSE,
      '#required' => TRUE,
      '#prefix' => '<div id="' . $order_wrapper . '">',
      '#suffix' => '</div>',
    ];
    $form['order_detail']['type'] = [
      '#type' => 'radios',
      '#collapsible' => FALSE,
      '#options' => [
        'order_id' => $this->t('Order Id'),
        'order_country_code' => $this->t('Order Country'),
        'order_first_name' => $this->t('Order First Name'),
        'order_company_name' => $this->t('Order Company Name'),
        'order_street_address' => $this->t('Order Address line 1'),
        'order_administrative_area' => $this->t('Order Administrative Area'),
        'order_locality' => $this->t('Order Locality'),
        'order_area_code' => $this->t('Order Area Code'),
      ],
      '#default_value' => $order_field,
      '#ajax' => [
        'callback' => [get_called_class(), 'ajaxRefresh'],
        'wrapper' => $order_wrapper,
      ],
    ];

    if ($order_field == 'order_id') {
      $form['order_detail']['order_id'] = [
        '#type' => 'number',
        '#title' => $this->t('Order Id'),
        '#default_value' => $this->configuration['order_id'],
        '#required' => TRUE,
        '#min' => 0,
      ];
    }
    if ($order_field == 'order_country_code') {
      $form['order_detail']['order_country_code'] = [
        '#type' => 'address_country',
        '#title' => $this->t('Order Country'),
        '#default_value' => \Drupal::config('system.date')->get('country.default') ,
        '#required' => TRUE,
      ];
    }
    if ($order_field == 'order_first_name') {
      $form['order_detail']['order_first_name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Order First Name'),
        '#maxlength' => 255,
        '#default_value' => $this->configuration['order_first_name'],
        '#required' => TRUE,
      ];
    }
    if ($order_field == 'order_company_name') {
      $form['order_detail']['order_company_name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Order Company Name'),
        '#maxlength' => 255,
        '#default_value' => $this->configuration['order_company_name'],
        '#required' => TRUE,
      ];
    }
    if ($order_field == 'order_street_address') {
      $form['order_detail']['order_street_address'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Order Address Line 1'),
        '#maxlength' => 255,
        '#default_value' => $this->configuration['order_street_address'],
        '#required' => TRUE,
      ];
    }
    if ($order_field == 'order_administrative_area') {
      $form['order_detail']['order_administrative_area'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Order Administrative Area'),
        '#maxlength' => 255,
        '#default_value' => $this->configuration['order_administrative_area'],
        '#required' => TRUE,
      ];
    }
    if ($order_field == 'order_locality') {
      $form['order_detail']['order_locality'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Order Locality'),
        '#maxlength' => 255,
        '#default_value' => $this->configuration['order_locality'],
        '#required' => TRUE,
      ];
    }
    if ($order_field == 'order_area_code') {
      $form['order_detail']['order_area_code'] = [
        '#type' => 'number',
        '#title' => $this->t('Order Area Code'),
        '#maxlength' => 255,
        '#default_value' => $this->configuration['order_area_code'],
        '#required' => TRUE,
      ];
    }

    return $form;
  }

  /**
   * Ajax callback.
   */
  public static function ajaxRefresh(array $form, FormStateInterface $form_state) {
    $parents = $form_state->getTriggeringElement()['#array_parents'];
    $parents = array_slice($parents, 0, -2);
    return NestedArray::getValue($form, $parents);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $field = $values['order_detail']['type'];
      $this->configuration['order_field'] = $field;
      $this->configuration[$field] = $values['order_detail'][$field];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function apply(OrderInterface $order) {

    $field_type = $this->configuration['order_field'];
    $address = $order->getBillingProfile()->get('address');
    # Default value.
    $field_value = $order->id();

    if ($field_type == 'order_id') {
      $field_value = $order->id();
    }

    if ($field_type == 'order_country_code') {
      $field_value = $address->country_code;
    }

    if ($field_type == 'order_first_name') {
      $field_value = $address->given_name;
    }

    if ($field_type == 'order_company_name') {
      $field_value = $address->organization;
    }

    if ($field_type == 'order_street_address') {
      $field_value = $address->address_line1;
    }

    if ($field_type == 'order_administrative_area') {
      $field_value = $address->administrative_area;
    }

    if ($field_type == 'order_locality') {
      $field_value = $address->locality;
    }

    if ($field_type == 'order_area_code') {
      $field_value = $address->postal_code;
    }

    if($this->configuration[$field_type] == $field_value) {
      return TRUE;
    }
    return FALSE;

  }

}
// locality
// address_line1
// organization
// given_name
// postal_code
// administrative_area
