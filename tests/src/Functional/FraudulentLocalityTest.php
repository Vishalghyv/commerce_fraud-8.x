<?php

namespace Drupal\Tests\commerce_fraud\Functional;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_fraud\Entity\Rules;
use Drupal\Tests\commerce\Functional\CommerceBrowserTestBase;
use Drupal\profile\Entity\Profile;

/**
 * Tests the commerce fraud fraudulent locality feature.
 *
 * @group commerce
 */
class FraudulentLocalityTest extends CommerceBrowserTestBase {

  /**
   * The test order.
   *
   * @var \Drupal\commerce_order\Entity\OrderInterface
   */
  protected $order;

  /**
   * The test rule.
   *
   * @var \Drupal\commerce_fraud\Entity\RulesInterface
   */
  protected $rule;

  /**
   * The Billing Profile.
   *
   * @var \Drupal\profile\Entity\Profile
   */
  protected $billing_profile;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'commerce_fraud',
  ];

  /**
   * {@inheritdoc}
   */
  protected function getAdministratorPermissions() {
    return array_merge([
      'administer site configuration',
    ], parent::getAdministratorPermissions());
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->order = Order::create([
      'type' => 'default',
      'state' => 'fraudulent',
      'mail' => 'test@example.com',
      'ip_address' => '127.0.0.1',
      'order_number' => '6',
      'uid' => $this->createUser(),
      'store_id' => $this->store,
      'order_items' => [],
    ]);

    $this->billing_profile = Profile::create([
      'type' => 'customer',
      'address' => [
        'country_code' => 'US',
        'postal_code' => '53177',
        'locality' => 'Fraudulent Locality',
        'address_line1' => 'From Same place',
        'address_line2' => 'Address line 2',
        'administrative_area' => 'WI',
        'given_name' => 'Frederick',
        'family_name' => 'Pabst',
      ],
    ]);
    $this->billing_profile->save();
    $this->billing_profile = $this->reloadEntity($this->billing_profile);
    $this->order->setBillingProfile($this->billing_profile);
    $this->order->save();

    $this->rule = Rules::create([
      'id' => 'example',
      'label' => 'Fraudulent Locality',
      'status' => TRUE,
      'plugin' => 'fradulent_locality',
      'counter' => 9,
    ]);

    $this->rule->save();
  }

  /**
   * Tests Commerce Fraud Fraudulent Locality Feature.
   */
  public function testFraudulentLocality() {
    $this->drupalGet('/admin/commerce/config/commerce_fraud');
    $page = $this->getSession()->getPage();

    $edit = array(
    'commerce_fraudulent_locality[Fraudulent Locality]' => TRUE,
    );

    $this->submitForm($edit, 'Save configuration');

    $this->assertSession()->pageTextContains(t('The configuration options have been saved.'));

    $new_order = Order::create([
      'type' => 'default',
      'state' => 'fraudulent',
      'mail' => 'test@example.com',
      'ip_address' => '127.0.0.1',
      'order_number' => '6',
      'uid' => $this->createUser(),
      'store_id' => $this->store,
      'order_items' => [],
    ]);

    $this->billing_profile = $this->reloadEntity($this->billing_profile);
    $new_order->setBillingProfile($this->billing_profile);
    $new_order->save();

    $this->assertEquals(TRUE, $this->rule->getPlugin()->apply($this->order));

  }

}
