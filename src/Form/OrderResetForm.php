<?php

namespace Drupal\commerce_fraud\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Routing\CurrentRouteMatch;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;

/**
 * Provides a confirmation form for unlocking orders.
 */
class OrderResetForm extends ConfirmFormBase {

  /**
   * The current order.
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
   * The ID of the item to delete.
   *
   * @var string
   */
  protected $database;

  public function __construct() {
    $this->order = \Drupal::routeMatch()->getParameter('commerce_order');
    $this->order_id = $this->order->id();
    $this->database = \Drupal::database();
  }
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
    return t('Do you want to delete %id?', array('%id' => $this->order_id));
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
    return t('Do this to make the fraud score of the order to 0');
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
   *
   * @param int $id
   *   (optional) The ID of the item to be deleted.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    return parent::buildForm($form, $form_state);
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
