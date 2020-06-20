<?php

namespace Drupal\commerce_fraud\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Link;
use Drupal\entity\Form\EntityDuplicateFormTrait;

/**
 * Form controller for Rules edit forms.
 *
 * @ingroup commerce_fraud
 */
class RulesForm extends ContentEntityForm {

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    $instance = parent::create($container);
    $instance->account = $container->get('current_user');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var \Drupal\commerce_fraud\Entity\Rules $entity */
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $form['#tree'] = TRUE;
    // By default an offer is preselected on the add form because the field
    // is required. Select an empty value instead, to force the user to choose.
    if ($this->operation == 'add' && $this->entity->get('rule')->isEmpty()) {
      if (!empty($form['rule']['widget'][0]['target_plugin_id'])) {
        $form['rule']['widget'][0]['target_plugin_id']['#empty_value'] = '';
        $form['rule']['widget'][0]['target_plugin_id']['#default_value'] = '';
        if (empty($form_state->getValue(['rule', 0, 'target_plugin_id']))) {
          unset($form['rule']['widget'][0]['target_plugin_configuration']);
        }
      }
    }

    $translating = !$this->isDefaultFormLangcode($form_state);
    $hide_non_translatable_fields = $this->entity->isDefaultTranslationAffectedOnly();
    // The second column is empty when translating with non-translatable
    // fields hidden, so there's no reason to add it.
    if ($translating && $hide_non_translatable_fields) {
      return $form;
    }

    $form['#theme'] = ['commerce_promotion_form'];
    $form['#attached']['library'][] = 'commerce_promotion/form';
    $form['advanced'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['entity-meta']],
      '#weight' => 99,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Rules.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Rules.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.rules.canonical', ['rules' => $entity->id()]);
  }

}
