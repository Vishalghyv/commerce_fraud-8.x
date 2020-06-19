<?php

namespace Drupal\commerce_fraud\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\commerce_fraud\Plugin\Commerce\FraudGenerator\FraudGeneratorInterface;

/**
 * Provides an interface for defining Rules entities.
 *
 * @ingroup commerce_fraud
 */
interface RulesInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Rules name.
   *
   * @return string
   *   Name of the Rules.
   */
  public function getName();

  /**
   * Sets the Rules name.
   *
   * @param string $name
   *   The Rules name.
   *
   * @return \Drupal\commerce_fraud\Entity\RulesInterface
   *   The called Rules entity.
   */
  public function setName($name);

  /**
   * Gets the Rules creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Rules.
   */
  public function getCreatedTime();

  /**
   * Sets the Rules creation timestamp.
   *
   * @param int $timestamp
   *   The Rules creation timestamp.
   *
   * @return \Drupal\commerce_fraud\Entity\RulesInterface
   *   The called Rules entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the offer.
   *
   * @return \Drupal\commerce_promotion\Plugin\Commerce\PromotionOffer\PromotionOfferInterface|null
   *   The offer, or NULL if not yet available.
   */
  public function getOffer();

  /**
   * Sets the offer.
   *
   * @param \Drupal\commerce_promotion\Plugin\Commerce\PromotionOffer\PromotionOfferInterface $offer
   *   The offer.
   *
   * @return $this
   */
  public function setOffer(FraudGeneratorInterface $offer);
}
