<?php

namespace Drupal\commerce_fraud;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Rules entities.
 *
 * @ingroup commerce_fraud
 */
class RulesListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Rules ID');
    $header['name'] = $this->t('Name');
    $header['rule_name'] = $this->t('Rule name');
    $header['score'] = $this->t('Score');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\commerce_fraud\Entity\Rules $entity */
    $row['label'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.rules.edit_form',
      ['rules' => $entity->id()]
    );
    $row['rule_name'] = $entity->getPlugin()->getLabel();
    $row['score'] = $entity->getScore();
    return $row + parent::buildRow($entity);
  }

}
