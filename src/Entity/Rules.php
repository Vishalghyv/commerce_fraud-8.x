<?php

namespace Drupal\commerce_fraud\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;
use Drupal\commerce_fraud\Plugin\Commerce\FraudRule\FraudRuleInterface;

/**
 * Defines the Rules entity.
 *
 * @ingroup commerce_fraud
 *
 * @ContentEntityType(
 *   id = "rules",
 *   label = @Translation("Rules"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\commerce_fraud\RulesListBuilder",
 *     "views_data" = "Drupal\commerce_fraud\Entity\RulesViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\commerce_fraud\Form\RulesForm",
 *       "add" = "Drupal\commerce_fraud\Form\RulesForm",
 *       "edit" = "Drupal\commerce_fraud\Form\RulesForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\entity\Routing\AdminHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\commerce_fraud\RulesAccessControlHandler",
 *   },
 *   base_table = "rules",
 *   translatable = FALSE,
 *   admin_permission = "administer rules entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/rules/{rules}",
 *     "add-form" = "/admin/commerce/rules/add",
 *     "edit-form" = "/admin/commerce/rules/{rules}/edit",
 *     "delete-form" = "/admin/commerce/rules/{rules}/delete",
 *     "collection" = "/admin/commerce/rules",
 *   },
 * )
 */
class Rules extends ContentEntityBase implements RulesInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRule() {
    if (!$this->get('rule')->isEmpty()) {
      return $this->get('rule')->first()->getTargetInstance();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setRule(FraudRuleInterface $rule) {
    $this->set('rule', [
      'target_plugin_id' => $rule->getPluginId(),
    ]);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCounter() {
    return $this->get('counter')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCounter(int $counter) {
    $this->set('counter', $counter);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStatus() {
    return $this->get('status')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setStatus(bool $status) {
    $this->set('status', $status);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Rules entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Rules entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['rule'] = BaseFieldDefinition::create('commerce_fraud_item:commerce_fraud_rule')
      ->setLabel(t('Rule type'))
      ->setCardinality(1)
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'commerce_plugin_select',
        'weight' => -3,
      ]);

    $fields['counter'] = BaseFieldDefinition::create('decimal')
      ->setLabel(t('Counter'))
      ->setDescription(t('Fraud count to be increased or decreased on application of this rule'))
      ->setDefaultValue(1)
      ->setDisplayOptions('form', [
        'type' => 'commerce_number',
        'weight' => -2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status']->setDescription(t('A boolean indicating whether the Rules is active.'))
      ->setLabel(t('Status'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -1,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
