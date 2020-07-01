<?php

namespace Drupal\commerce_fraud\Form;

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\ConfirmFormBase;

/**
 * Provides a confirmation form for reseting orders.
 */
class OrderResetForm extends ConfirmFormBase {

  /**
   * The current order id.
   *
   * @var \Drupal\commerce_order\Entity\OrderInterface
   */
  protected $order_id;

  /**
   * The current order.
   *
   * @var \Drupal\commerce_order\Entity\OrderInterface
   */
  protected $order;

  /**
   * Database.
   *
   * @var database
   */
  protected $database;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->order = \Drupal::routeMatch()->getParameter('commerce_order');
    $this->order_id = $this->order->id();
    $this->database = \Drupal::database();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'commerce_order_reset_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Do you want to reset order fraud score order -id: %id?', ['%id' => $this->order_id]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->order->toUrl('collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return t("Reset this order's fraud score to 0");
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Reset Fraud Score');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelText() {
    return t('Cancel');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->database->delete('commerce_fraud_fraud_score')
      ->condition('order_id', $this->order_id)
      ->execute();

    $this->messenger()->addMessage($this->t('The order has been reseted.'));
    $form_state->setRedirectUrl($this->order->toUrl('collection'));
  }

}
