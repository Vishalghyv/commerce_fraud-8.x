<?php

namespace Drupal\commerce_fraud\Plugin\Commerce\FraudRule;

/**
 * Defines the interface for fraud item rule.
 *
 * Fraud Rule have conditions, which are used to determine which
 * order should be passed by the rule.
 */
interface FraudConditionInterface extends FraudRuleInterface {

  /**
   * Gets the conditions.
   *
   * @return \Drupal\commerce\Plugin\Commerce\Condition\ConditionInterface[]
   *   The conditions.
   */
  public function getConditions();

  /**
   * Sets the conditions.
   *
   * @param \Drupal\commerce\Plugin\Commerce\Condition\ConditionInterface[] $conditions
   *   The conditions.
   *
   * @return $this
   */
  public function setConditions(array $conditions);

  /**
   * Gets the condition operator.
   *
   * @return string
   *   The condition operator. Possible values: AND, OR.
   */
  public function getConditionOperator();

}
