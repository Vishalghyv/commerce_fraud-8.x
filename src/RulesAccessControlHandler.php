<?php

namespace Drupal\commerce_fraud;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Rules entity.
 *
 * @see \Drupal\commerce_fraud\Entity\Rules.
 */
class RulesAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\commerce_fraud\Entity\RulesInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished rules entities');
        }


        return AccessResult::allowedIfHasPermission($account, 'view published rules entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit rules entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete rules entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add rules entities');
  }


}
