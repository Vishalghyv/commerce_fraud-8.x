<?php

namespace Drupal\commerce_fraud\Controller;

use Drupal\Core\Controller\ControllerBase;
use \Drupal\Core\Routing;

/**
 * Controller routines for Lorem ipsum pages.
 */
class ResetController extends ControllerBase {
  // Todo To make this content more descriptive.

  /**
   *
   */
  public function content() {
    $current_path = \Drupal::service('path.current')->getPath();
    $parameters = \Drupal::routeMatch()->getParameter('commerce_order');
    dpm($parameters->id());
    return ['#type' => 'markup', '#markup' => t('Detects potentially fraudulous orders')];
  }

}
