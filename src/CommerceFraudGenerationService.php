<?php

namespace Drupal\commerce_fraud;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Default order number service implementation.
 */
class CommerceFraudGenerationService implements CommerceFraudGenerationServiceInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The order number generator manager.
   *
   * @var \Drupal\commerce_order_number\OrderNumberGeneratorManager
   */
  protected $commerceFraudManager;

  /**
   * Constructs a new CommerceFraudGenerationService object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\commerce_fraud\CommerceFraudManager $commerce_fraud_manager
   *   The order number generator manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, CommerceFraudManager $commerce_fraud_manager) {
    $this->configFactory = $config_factory;
    $this->commerceFraudManager = $commerce_fraud_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function generateAndSetFraudCount(OrderInterface $order, string $rule) {
    drupal_set_message("This is coming from CommerceFraudGenerationService {$rule}");
    // $customer_id = $order->getCustomerId();
    $generator = $this->commerceFraudManager->createInstance($rule);
    $action = $generator->apply($order);
    return $action;
  }

}
