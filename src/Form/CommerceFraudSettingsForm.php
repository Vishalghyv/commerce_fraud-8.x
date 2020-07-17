<?php

namespace Drupal\commerce_fraud\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;

/**
 * Configure commerce fraud settings for this site.
 */
class CommerceFraudSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['commerce_fraud.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'commerce_fraud_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['commerce_fraud_caps'] = [
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#title' => t('Commerce Fraud Caps Settings'),
    ];

    $form['commerce_fraud_caps']['commerce_fraud_checklist_cap'] = [
      '#title' => t('Checklist cap'),
      '#description' => t('If an order has a fraud score greater than the number specified, it will be considered checklisted.'),
      '#default_value' => \Drupal::state()->get('commerce_fraud_checklist_cap', 10),
      '#type' => 'number',
      '#min' => 0,
      '#step' => 1,
    ];

    $form['commerce_fraud_caps']['commerce_fraud_blocklist_cap'] = [
      '#title' => t('Blocklist cap'),
      '#description' => t('If an order has a fraud score greater than the number specified, it will be considered blocklisted.'),
      '#default_value' => \Drupal::state()->get('commerce_fraud_blocklist_cap', 20),
      '#type' => 'number',
      '#min' => 0,
      '#step' => 1,
    ];

    $form['stop_order'] = [
      '#type' => 'checkbox',
      '#title' => $this
        ->t('Activate this to stop the blocklisted orders from completeing checkout'),
      '#default_value' => \Drupal::state()->get('stop_order', FALSE),
    ];

    $form['send_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#default_value' => \Drupal::state()->get('send_email', \Drupal::config('system.site')->get('mail')),
      '#required' => TRUE,
      '#description' => t('If an order is blocklisted its details will be sent to this email'),
    ];
    $enable = $form_state->getValue('api_status');

    $enable = $enable ?: (\Drupal::state()->get('commerce_fraud_aws_model', FALSE) ? 1 : 0);

    $wrapper_id = Html::getUniqueId('commerce-fraud-aws-model');
    $form['commerce_fraud_aws_model'] = [
      '#title' => $this
        ->t('Activate this add you AWS model to evaluate commerce orders'),
      '#size' =>100,
      '#prefix' => '<div id="' . $wrapper_id . '">',
      '#suffix' => '</div>',
    ];

    $form['commerce_fraud_aws_model']['api_status'] = [
      '#type' => 'radios',
      '#title' => $this
        ->t('Poll status'),
      '#default_value' => $enable,
      '#options' => [
        1 => $this
          ->t('Enable'),
        0 => $this
          ->t('Disable'),
      ],
       '#ajax' => [
        'callback' => [get_class(), 'ajaxRefresh'],
        'wrapper' => $wrapper_id,
      ],
    ];

    if ($enable == 1) {

      $api = \Drupal::state()->get('commerce_fraud_aws_api', '');

      $form['commerce_fraud_aws_model']['commerce_fraud_api'] = [
        '#type' => 'textfield',
        '#title' => $this->t('AWS Api of Fraud Model'),
        '#required' => TRUE,
        '#default_value' => $api,
      ];

      $order_format = t("   [
        'commerce_order' => [
            'order_id' => number,
             'user_id' => number,
             'total_price' => commerce_price,
             'total_quantity' => number,
             'hostname' => number,
             'address' => [
                'address_line_1' => string,
                'country_code' => string,
                'locality' => string,
                ]
            'products' => [
                'product_id' => number,
                'product_name' => string,
                'product_price' => commerce_price,
                ]
            ]
      ]");

      $form['commerce_fraud_aws_model']['orders_format'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Format of Order details send'),
        '#default_value' => $order_format,
        '#disabled' => TRUE,
        '#rows' => 19,
      ];

      $receive_format = t("   [
        'commerce_fraud' => [
            'fraud_count' => number,
            'apply' => bool,
          ]
      ]");

      $form['commerce_fraud_aws_model']['receive_format'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Format of Post details received'),
        '#default_value' => $receive_format,
        '#disabled' => TRUE,
        '#rows' => 6,
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * Ajax callback.
   */
  public static function ajaxRefresh(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $checkListValue = $form_state->getValue('commerce_fraud_checklist_cap');
    $blockListValue = $form_state->getValue('commerce_fraud_blocklist_cap');
    if ($checkListValue >= $blockListValue) {
      $form_state->setErrorByName('commerce_fraud_caps][commerce_fraud_checklist_cap', t('Check List value cannot be equal to or more than Block List value'));
    }
    if ($form_state->getValue('api_status') == 1) {
        if (!\Drupal::service('module_handler')->moduleExists('key')) {
            $form_state->setErrorByName('commerce_fraud_model', t('Key Module needs to be enabled to keep the api safe'));     
        }
    }

    if ($form_state->getValue('commerce_fraud_api')) {
      if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $form_state->setErrorByName('commerce_fraud_api', t('Api is not a valid URL'));
      }

      if (!str_contains($url, 'api')) {
        $form_state->setErrorByName('commerce_fraud_api', t('Api is not a valid URL'));
      }

      if (!str_contains($url, 'aws')) {
        $form_state->setErrorByName('commerce_fraud_api', t('Api is not a valid URL'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Set the submitted configuration setting.
    \Drupal::state()->set('commerce_fraud_blocklist_cap', $form_state->getValue('commerce_fraud_blocklist_cap'));
    \Drupal::state()->set('commerce_fraud_checklist_cap', $form_state->getValue('commerce_fraud_checklist_cap'));
    \Drupal::state()->set('stop_order', $form_state->getValue('stop_order'));
    \Drupal::state()->set('send_email', $form_state->getValue('send_email'));
    \Drupal::state()->set('commerce_fraud_aws_model', (bool) $form_state->getValue('api_status')) ;
    if (\Drupal::state()->get('commerce_fraud_model') == TRUE) {
      $key = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
      $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = sodium_crypto_secretbox($form_state->getValue('commerce_fraud_api'), $nonce, $key);
      $encoded = base64_encode($nonce . $ciphertext);
      \Drupal::state()->set('commerce_fraud_aws_api', $encoded) ;
    }
    parent::submitForm($form, $form_state);
  }

}
