<?php
/**
 * @file
 * Contains \Drupal\commerce_fraud\Form\CommerceFraudAdminForm
 */
namespace Drupal\commerce_fraud\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides Admin form for Commerce Fraud.
 */

class CommerceFraudAdminForm extends FormBase {
    /**
     * (@inheritdoc)
     * @return string Form ID
     */
    public function getFormId() {
        return 'commerce_fraud';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
        $form['number'] = [
          '#type' => 'commerce_number',
          '#title' => $this->t('Amount'),
          '#default_value' => '99.99',
          '#min' => 2,
          '#max' => 100,
          '#required' => TRUE,
        ];
        $form['submit'] = [
          '#type' => 'submit',
          '#value' => $this->t('Submit'),
        ];
        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        drupal_set_message(t('Thank you for your RSVP, you are on the list for the event.'));
    }
}
