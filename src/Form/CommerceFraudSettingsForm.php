<?php

namespace Drupal\commerce_fraud\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure commerce fraud settings for this site.
 */
class CommerceFraudSettingsForm extends ConfigFormBase {

  /**
   * Database.
   *
   * @var string
   */
  protected $database;

  /**
   * The profile storage.
   *
   * @var \Drupal\profile\ProfileStorageInterface
   */
  protected $profileStorage;

  /**
   * Constructs a new SettingsForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->profileStorage = $entity_type_manager->getStorage('profile');
    $this->database = \Drupal::database();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('database')
    );
  }

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

    $options = array();

     foreach ($this->suspectLocalities() as $localities) {
       $options[$localities['locality']] = t('@locality - @country - @num_of_occurences', array(
         '@locality' => $localities['locality'],
         '@country' => $localities['country'],
         '@num_of_occurences' => $localities['num_of_occurences'],
       ));
     }

    $form['suspected_locality'] = array(
      '#type' => 'fieldset',
      '#title' => t('Suspected Locality'),
      '#description' => t('Selecting these locality will add them to the watch list, Orders from these localities will have their fraud score increased by 5 points  (locality Name - Country Code - Number of Occurrence in fraudulent orders)'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );

    $form['suspected_locality']['commerce_fraudulent_locality'] = array(
      '#type' => 'checkboxes',
      '#default_value' => \Drupal::state()->get('checklisted_locality', []),
      '#options' => $options,
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function suspectLocalities() {
    $localities = [];
    $query = $this->database->select('commerce_order', 'o')
      ->fields('o', array('billing_profile__target_id'))
      ->condition('state', ['fraudulent'], 'IN');
    $fraudulent_orders = $query->execute()->fetchCol();

    foreach ($fraudulent_orders as $billing_id) {
      $address =$this->profileStorage->load($billing_id)->get('address');

      if (!$address->locality || !preg_match("/^[a-zA-Z ]*$/", $address->locality)) {
        continue;
      }

      $locality = ucwords(strtolower($address->locality));

      $country = $address->country_code;

      $complete_name = $locality . '-' . $country;

      $localities[$complete_name] = array(
        'num_of_occurences' => isset($localities[$complete_name]) ? $localities[$complete_name]['num_of_occurences'] + 1 : 1,
        'locality' => $locality,
        'country' => $country,
      );
    }

    return $localities;
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
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $selected_locality = array_filter($form_state->getValue('commerce_fraudulent_locality'));
    \Drupal::state()->set('checklisted_locality', $selected_locality);
    // Set the submitted configuration setting.
    \Drupal::state()->set('commerce_fraud_blocklist_cap', $form_state->getValue('commerce_fraud_blocklist_cap'));
    \Drupal::state()->set('commerce_fraud_checklist_cap', $form_state->getValue('commerce_fraud_checklist_cap'));
    \Drupal::state()->set('stop_order', $form_state->getValue('stop_order'));
    \Drupal::state()->set('send_email', $form_state->getValue('send_email'));
    parent::submitForm($form, $form_state);
  }

}
