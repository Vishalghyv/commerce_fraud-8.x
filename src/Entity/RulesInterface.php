<?php

namespace Drupal\commerce_fraud\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\commerce_fraud\Plugin\Commerce\FraudRule\FraudRuleInterface;

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
   * Gets the rule.
   *
   * @return \Drupal\commerce_fraud\Plugin\Commerce\FraudRule\FraudRuleInterface
   */
  public function getRule();

  /**
   * Gets the Rules counter.
   *
   * @return int
   */
  public function getCounter();

  /**
   * Sets the Rules counter.
   *
   * @param int
   */
  public function setCounter(int $counter);

  /**
   * Gets the Rules Status.
   *
   * @return bool
   */
  public function getStatus();

  /**
   * Sets the Rules Status.
   *
   * @param bool
   */
  public function setStatus(bool $status);

  /**
   * Sets the rule.
   *
   * @param \Drupal\commerce_fraud\Plugin\Commerce\FraudRule\FraudRuleInterface $rule
   *   The rule.
   *
   * @return $this
   */
  public function setRule(FraudRuleInterface $rule);

}
