<?php

namespace Drupal\commerce_fraud\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a Contact entity.
 * @ingroup commerce_fraud
 */
interface RulesInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}

?>
