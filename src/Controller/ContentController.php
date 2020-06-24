<?php

namespace Drupal\commerce_fraud\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller routines for Lorem ipsum pages.
 */
class ContentController extends ControllerBase {
  // Todo To make this content more descriptive.

  /**
   *
   */
  public function content() {
    return ['#type' => 'markup', '#markup' => t('Detects potentially fraudulous orders')];
  }

}
