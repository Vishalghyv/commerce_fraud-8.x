<?php

namespace Drupal\Tests\commerce_fraud\Functional;

use Drupal\commerce_price\Entity\Currency;
use Drupal\Tests\commerce\Functional\CommerceBrowserTestBase;
use Drupal\Core\Url;

/**
 * Tests the currency UI.
 *
 * @group commerce
 */
class FraudulentLocalityTest extends CommerceBrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'commerce_fraud',
  ];

  /**
   * Tests the initial currency creation.
   */
  // public function testInitialCurrency() {
  //   // We are expecting commerce_price_install() to import 'USD'.
  //   $currency = Currency::load('USD');
  //   $this->assertNotEmpty($currency);
  // }

  /**
   * Tests importing a currency.
   */
  public function testCurrencyImport() {
    $this->drupalGet('admin/commerce/config/commerce_fraud');
    // $edit = array(
    //   'commerce_fraudulent_locality[Fraudulent Locality]' => TRUE,
    // );
    $this->submitForm([], 'edit-submit');

  }
}
