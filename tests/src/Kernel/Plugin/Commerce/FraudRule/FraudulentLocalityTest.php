<?php

namespace Drupal\Tests\commerce_fraud\Kernel\Plugin\Commerce\FraudRule;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_fraud\Entity\Rules;
use Drupal\Tests\commerce_order\Kernel\OrderKernelTestBase;
use Drupal\profile\Entity\Profile;
use Drupal\user\Entity\User;

/**
 * Tests commerce fraud rule plugin.
 *
 * @coversDefaultClass \Drupal\commerce_fraud\Plugin\Commerce\FraudRule\FraudulentLocality
 * @group commerce
 */
class FraudulentLocalityTest extends OrderKernelTestBase {

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
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('rules');
    $this->installConfig(['commerce_fraud']);
    $this->installSchema('commerce_fraud', ['commerce_fraud_fraud_score']);

    $this->order = Order::create([
      'type' => 'default',
      'state' => 'completed',
      'mail' => 'test@example.com',
      'ip_address' => '127.0.0.1',
      'order_number' => '6',
      'uid' => $this->createUser(),
      'store_id' => $this->store,
      'order_items' => [],
    ]);

    $billing_profile = Profile::create([
      'type' => 'customer',
      'address' => [
        'country_code' => 'US',
        'postal_code' => '53177',
        'locality' => 'Fraudulent',
        'address_line1' => 'From Same place',
        'address_line2' => 'Address line 2',
        'administrative_area' => 'WI',
        'given_name' => 'Frederick',
        'family_name' => 'Pabst',
      ],
    ]);
    $billing_profile->save();
    $billing_profile = $this->reloadEntity($billing_profile);
    $this->order->setBillingProfile($billing_profile);
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
   * Tests the non-applicable use case.
   *
   * @covers ::apply
   */
  public function testNotApplicableRule() {
    $this->assertEquals(FALSE, $this->rule->getPlugin()->apply($this->order));
  }

  /**
   * Tests the applicable use case.
   *
   * @covers ::apply
   */
  public function testApplicableRule() {
    $selected_locality = ['Fraudulent' => 'Fraudulent'];
    \Drupal::state()->set('checklisted_locality', $selected_locality);
    $this->assertEquals(TRUE, $this->rule->getPlugin()->apply($this->order));
  }

}
