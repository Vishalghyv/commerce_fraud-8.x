<?php

namespace Drupal\commerce_fraud\Plugin\Commerce\FraudRule;

/**
 * Defines the interface for order item offers.
 *
 * Order item offers have conditions, which are used to determine which
 * order items should be passed to the offer.
 */
interface FraudOfferInterface extends FraudRuleInterface {}
