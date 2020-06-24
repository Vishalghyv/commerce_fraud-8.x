<?php

namespace Drupal\commerce_fraud\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Routing\CurrentRouteMatch;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a confirmation form for unlocking orders.
 */
class OrderResetForm extends FormBase {

  /**
   * The current order.
   *
   * @var \Drupal\commerce_order\Entity\OrderInterface
   */
  protected $order_id;

  // public function __construct(CurrentRouteMatch $current_route_match) {
  //   $this->order_id = $current_route_match->getParameter('commerce_order');
  // }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'commerce_order_reset_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // $customer = $this->order_id;
    // if ($customer->isAnonymous()) {
    //   $current_customer = $this->t('anonymous user with the email %email', [
    //     '%email' => $this->order->getEmail(),
    //   ]);
    // }
    // else {
    //   // If the display name has been altered to not be the email address,
    //   // show the email as well.
    //   if ($customer->getDisplayName() != $customer->getEmail()) {
    //     $customer_link_text = $this->t('@display (@email)', [
    //       '@display' => $customer->getDisplayName(),
    //       '@email' => $customer->getEmail(),
    //     ]);
    //   }
    //   else {
    //     $customer_link_text = $customer->getDisplayName();
    //   }

    //   $current_customer = $this->order->getCustomer()->toLink($customer_link_text)->toString();
    // }

    $form['current_customer'] = [
      '#type' => 'item',
      '#markup' => $this->t('The order is currently assigned to @customer.', [
        '@customer' => $customer,
      ]),
    ];
    // $form += $this->buildCustomerForm($form, $form_state, $this->order);

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Reset Fraud Score'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->submitCustomerForm($form, $form_state);

    $values = $form_state->getValues();
    /** @var \Drupal\user\UserInterface $user */
    // $user = $this->userStorage->load($values['uid']);
    // $this->orderAssignment->assign($this->order, $user);
    $this->messenger()->addMessage($this->t('The order %label has been assigned to customer %customer.', [
      '%label' => $this->order->label(),
      '%customer' => $this->order->getCustomer()->label(),
    ]));
    $form_state->setRedirectUrl($this->order->toUrl('collection'));
  }

}
