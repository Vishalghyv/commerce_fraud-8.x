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
 *   id = "check_user_detail",
 *   label = @Translation("Compares user details with given details"),
 *   description = @Translation("Compare user details with given details"),
 * )
 */
class CheckUserDetailFraudRule extends FraudRuleBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'user_id' => 0,
      'user_email' => '',
      'user_name' => '',
      'user_field' => '',
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
    
    $parents = array_merge($form['#parents'], ['user', 'type']);
    $user_field = NestedArray::getValue($form_state->getUserInput(), $parents);
    $user_wrapper = Html::getUniqueId('user-detail-wrapper');
    $user_field = $user_field ?? $this->configuration['user_field'];
    dpm($user_field);
    $form['user'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('User detail to be checked'),
      '#collapsible' => FALSE,
      '#prefix' => '<div id="' . $user_wrapper . '">',
      '#suffix' => '</div>',
    ];

    $form['user']['type'] = [
      '#type' => 'radios',
      '#title' => $this->t('User detail to be checked'),
      '#collapsible' => FALSE,
      '#options' => [
        'user_id' => $this->t('User Id'),
        'user_name' => $this->t('User Name'),
        'user_email' => $this->t('User Email'),
      ],
      '#default_value' => $user_field,
      '#ajax' => [
        'callback' => [get_called_class(), 'ajaxRefresh'],
        'wrapper' => $user_wrapper,
      ],
    ];
    if ($user_field == 'user_id') {
      $form['user']['user_id'] = [
        '#type' => 'number',
        '#title' => $this->t('User Id'),
        '#default_value' => $this->configuration['user_id'],
        '#required' => TRUE,
        '#min' => 0,
      ];
    }
    if ($user_field == 'user_name') {
      $form['user']['user_name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('User Name'),
        '#maxlength' => 255,
        '#default_value' => $this->configuration['user_name'],
        '#required' => TRUE,
      ];
    }
    if ($user_field == 'user_email') {
      $form['user']['user_email'] = [
        '#type' => 'email',
        '#title' => $this->t('User Email'),
        '#default_value' => $this->configuration['user_email'],
        '#required' => TRUE,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $field = $values['user']['type'];
      $this->configuration['user_field'] = $field;
      $this->configuration[$field] = $values['user'][$field];
    }
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
  public function apply(OrderInterface $order) {

    $field_type = $this->configuration['user_field'];
    $customer = $order->getCustomer();
    # Default value.
    $field_value = $customer->id();

    if ($field_type == 'user_id') {
      $field_value = $customer->id();
    }

    if ($field_type == 'user_name') {
      $field_value = $customer->getDisplayName();
    }

    if ($field_type == 'user_email') {
      $field_value = $customer->getEmail();
    }

    if($this->configuration[$field_type] == $field_value) {
      return TRUE;
    }
    return FALSE;

  }

}
