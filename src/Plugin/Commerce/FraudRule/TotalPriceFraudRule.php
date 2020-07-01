<?php

namespace Drupal\commerce_fraud\Plugin\Commerce\FraudRule;

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_price\Calculator;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;

/**
 * Provides the fraud rule.
 *
 * @CommerceFraudRule(
 *   id = "total_price",
 *   label = @Translation("Compare Total Price with Given Price"),
 *   description = @Translation("Checks Order Total Price"),
 * )
 */
class TotalPriceFraudRule extends FraudRuleBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'price_type' => 'percentage',
      'buy_percentage' => 0,
      'buy_amount' => NULL,
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

    $selected_price_type = $this->configuration['price_type'];
    $buy_wrapper = Html::getUniqueId('buy-price-wrapper');
    $form['buy'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Price limit'),
      '#description' => 'This value will be checked according to currency code of the order',
      '#collapsible' => FALSE,
      '#prefix' => '<div id="' . $buy_wrapper . '">',
      '#suffix' => '</div>',
    ];
    $form['buy']['type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Discounted by a'),
      '#title_display' => 'invisible',
      '#options' => [
        'percentage' => $this->t('Percentage'),
        'fixed_amount' => $this->t('Fixed amount'),
      ],
      '#ajax' => [
        'callback' => [get_called_class(), 'ajaxRefresh'],
        'wrapper' => $buy_wrapper,
      ],
    ];
    if ($selected_price_type == 'percentage') {
      $form['buy']['percentage'] = [
        '#type' => 'commerce_number',
        '#title' => $this->t('Percentage off'),
        '#default_value' => Calculator::multiply($this->configuration['buy_percentage'], '100'),
        '#maxlength' => 255,
        '#min' => 0,
        '#max' => 100,
        '#size' => 4,
        '#field_suffix' => $this->t('%'),
        '#required' => TRUE,
      ];
    }
    else {
      $form['buy']['amount'] = [
        '#type' => 'commerce_price',
        '#title' => $this->t('Amount off'),
        '#default_value' => $this->configuration['buy_amount'],
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

      if ($this->configuration['buy_type'] == 'percentage') {
        $this->configuration['buy_percentage'] = Calculator::divide((string) $values['buy']['percentage'], '100');
        $this->configuration['buy_amount'] = NULL;
      }
      else {
        $this->configuration['buy_percentage'] = NULL;
        $this->configuration['buy_amount'] = $values['buy']['amount'];
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function apply(OrderInterface $order) {
    $order_price = $order->getTotalPrice();

    // $price = $this->configuration['buy_price'];
    // $new_price = new Price($price, $order_price->getCurrencyCode());

    // if ($order_price->greaterThan($new_price)) {

    //   return TRUE;
    // }
    return FALSE;
  }

}
