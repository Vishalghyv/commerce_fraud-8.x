<?php

namespace Drupal\commerce_fraud;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Default fraud rule service implementation.
 */
class CommerceFraudRuleService implements CommerceFraudRuleServiceInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The fraud rule manager.
   *
   * @var \Drupal\commerce_fraud\CommerceFraudManager
   */
  protected $commerceFraudManager;

  /**
   * Constructs a new CommerceFraudRuleService object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\commerce_fraud\CommerceFraudManager $commerce_fraud_manager
   *   The order number rule manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, CommerceFraudManager $commerce_fraud_manager) {
    $this->configFactory = $config_factory;
    $this->commerceFraudManager = $commerce_fraud_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function setFraudCount(OrderInterface $order, string $rule) {
    drupal_set_message("This is coming from CommerceFraudRuleService {$rule}");

    $rule = $this->commerceFraudManager->createInstance($rule);
    $action = $rule->apply($order);
    return $action;
  }

}
