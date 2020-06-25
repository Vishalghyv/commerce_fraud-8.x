<?php

namespace Drupal\commerce_fraud;

use Drupal\commerce_order\Entity\OrderInterface;

/**
 * Defines the fruad service interface. The service is responsible for
 * handling the fruad rules and will be called by our event
 * subscriber during order placement.
 *
 * This service will choose the fruad rule plugin according to
 * the rule entity, as well as handling of setting fraud score.
 */
interface CommerceFraudRuleServiceInterface {

  /**
   * Generates an bool for the given rule entity. The function
   * is not responsible for saving the fraud count, this must be handled by the
   * calling function. The primary usage of this service class is to be called
   * by our event subscriber during order placement, where the order entity will
   * be finally saved anyway.
   *
   * The implementation is expected to pick the appropriate fraud rule
   * generator plugin, taking care of only accepting an fraud rule, as
   * well as formatting the fraud score according to the rule entity.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order entity.
   * @param string $rule
   *   The rule id.
   *
   * @return bool
   *   Wether the rule apply or not.
   */
  public function setFraudCount(OrderInterface $order, string $rule);

}
