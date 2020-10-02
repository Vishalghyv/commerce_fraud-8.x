<?php

namespace Drupal\commerce_fraud\Plugin\Commerce\FraudRule;

use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_payment\CreditCard;
use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_order\Entity\OrderInterface;

/**
 * Provides the fraud rule.
 *
 * @CommerceFraudRule(
 *   id = "check_avs",
 *   label = @Translation("Compare order AVS code"),
 *   description = @Translation("Compares order AVS code"),
 * )
 */
class CheckAddressVerificationSystemFraudRule extends FraudRuleBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'avs_codes' => [
        'visa' => [],
        'mastercard' => [],
        'amex' => [],
        'discover' => [],
        'maestro' => [],
      ],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form += parent::buildConfigurationForm($form, $form_state);

    $form['#type'] = 'container';
    $form['#title'] = $this->t('AVS Code');
    $form['#collapsible'] = FALSE;

    $form['avs_code'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('AVS Code'),
      '#collapsible' => FALSE,
    ];

    $avs_code_meanings = CreditCard::getAvsResponseCodeMeanings();

    $form['avs_code']['visa_codes'] = [
      '#type' => 'select',
      '#title' => $this->t('Visa Codes'),
      '#options' => $avs_code_meanings['visa'],
      '#multiple' => 1,
      '#required' => 1,
      '#default' => $this->configuration['avs_codes']['visa'],
    ];
    $form['avs_code']['mastercard_codes'] = [
      '#type' => 'select',
      '#title' => $this->t('Mastercard Codes'),
      '#options' => $avs_code_meanings['mastercard'],
      '#multiple' => 1,
      '#required' => 1,
      '#default' => $this->configuration['avs_codes']['mastercard'],
    ];
    $form['avs_code']['amex_codes'] = [
      '#type' => 'select',
      '#title' => $this->t('Amex Codes'),
      '#options' => $avs_code_meanings['amex'],
      '#multiple' => 1,
      '#required' => 1,
      '#default' => $this->configuration['avs_codes']['amex'],
    ];
    $form['avs_code']['discover_codes'] = [
      '#type' => 'select',
      '#title' => $this->t('Discover Codes'),
      '#options' => $avs_code_meanings['discover'],
      '#multiple' => 1,
      '#required' => 1,
      '#default' => $this->configuration['avs_codes']['discover'],
    ];
    $form['avs_code']['maestro_codes'] = [
      '#type' => 'select',
      '#title' => $this->t('Maestro Codes'),
      '#options' => $avs_code_meanings['maestro'],
      '#multiple' => 1,
      '#required' => 1,
      '#default' => $this->configuration['avs_codes']['maestro'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration['avs_codes']['visa'] = $values['avs_code']['visa_codes'];
      $this->configuration['avs_codes']['mastercard'] = $values['avs_code']['mastercard_codes'];
      $this->configuration['avs_codes']['amex'] = $values['avs_code']['amex_codes'];
      $this->configuration['avs_codes']['discover'] = $values['avs_code']['discover_codes'];
      $this->configuration['avs_codes']['maestro'] = $values['avs_code']['maestro_codes'];
    }

  }

  /**
   * {@inheritdoc}
   */
  public function apply(OrderInterface $order) {
    // Invalid application of rule.
    return FALSE;

  }

  /**
   * Checks payment AVS code response.
   */
  public function applyPaymentRule(PaymentInterface $payment) {
    if (!$payment->getPaymentMethod() || !$payment->getAvsResponseCode()) {
      return FALSE;
    }
    $card_type = $payment->getPaymentMethod()->getType();

    $avs_response_code = $payment->getAvsResponseCode();

    if (isset($this->configuration['avs_codes'][$card_type][$avs_response_code])) {
      return TRUE;
    }

    return FALSE;
  }

}
