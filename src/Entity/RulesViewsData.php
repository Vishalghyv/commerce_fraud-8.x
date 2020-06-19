<?php

namespace Drupal\commerce_fraud\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Rules entities.
 */
class RulesViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
