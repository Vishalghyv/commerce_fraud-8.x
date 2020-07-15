<?php

namespace Drupal\Tests\commerce_fraud\Kernel\Plugin\Commerce\FraudRule;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_fraud\Entity\Rules;
use Drupal\Tests\commerce_order\Kernel\OrderKernelTestBase;
use Drupal\user\Entity\User;
use Drupal\profile\Entity\Profile;

/**
 * Tests actions source plugin.
 *
 * @coversDefaultClass \Drupal\commerce_fraud\Plugin\Commerce\FraudRule\CheckUserDetailFraudRule
 * @group commerce
 */
class CheckOrderDetailFraudRuleTest extends OrderKernelTestBase {

  /**
   * The test order.
   *
   * @var \Drupal\commerce_order\Entity\OrderInterface
   */
  protected $order;

  protected $user;

  /**
   * The rule field type.
   *
   * @var array
   */
  protected $rule_field_types;

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

    $user = $this->createUser();
    $this->user = $this->reloadEntity($user);

    $this->order = Order::create([
      'type' => 'default',
      'state' => 'completed',
      'mail' => 'test@example.com',
      'ip_address' => '127.0.0.1',
      'order_number' => '6',
      'uid' => $this->user->id(),
      'store_id' => $this->store,
      'order_items' => [],
    ]);

    $billing_profile = Profile::create([
      'type' => 'customer',
      'address' => [
        'country_code' => 'IN',
        'postal_code' => 110045,
        'organization' => 'company name',
        'locality' => 'locality',
        'address_line1' => 'street address',
        'address_line2' => 'Address line 2',
        'administrative_area' => 'admnistrative area',
        'given_name' => 'first name',
        'family_name' => 'last name',
      ],
    ]);
    $billing_profile->save();
    $billing_profile = $this->reloadEntity($billing_profile);
    $this->order->setBillingProfile($billing_profile);
    $this->order->save();

  }

  /**
   * Tests the non-applicable use case.
   *
   * @covers ::apply
   */
  public function testNotApplicableRule() {
    $this->rule_field_types = [
      'order_id' => 123,
      'order_country_code' => 'US',
      'order_first_name' => 'diff first name',
      'order_company_name' => 'diff company name',
      'order_street_address' => 'diff street address',
      'order_administrative_area' => 'diff admnistrative area',
      'order_locality' => 'diff locality',
      'order_area_code' => 110046,
    ];
    $init = 1;

    foreach ($this->rule_field_types as $field_type => $value) {
      $rule = Rules::create([
        'id' => 'example'.(string)$init,
        'label' => 'Check Order Detail',
        'status' => TRUE,
        'plugin' => 'check_order_detail',
        'configuration' => [
          $field_type => $value,
          'order_field' => $field_type,
        ],
        'counter' => 9,
      ]);

      $rule->save();
      $this->assertEquals(FALSE, $rule->getPlugin()->apply($this->order));

      $init = $init + 1;
    }
  }

  /**
   * Tests the applicable use case.
   *
   * @covers ::apply
   */
  public function testApplicableRule() {
    $this->rule_field_types = [
      'order_id' => $this->order->id(),
      'order_country_code' => 'IN',
      'order_first_name' => 'first name',
      'order_company_name' => 'company name',
      'order_street_address' => 'street address',
      'order_administrative_area' => 'admnistrative area',
      'order_locality' => 'locality',
      'order_area_code' => 110045,
    ];
    $init = 1;

    foreach ($this->rule_field_types as $field_type => $value) {
      $rule = Rules::create([
        'id' => 'example'.(string)$init,
        'label' => 'Check Order Detail',
        'status' => TRUE,
        'plugin' => 'check_order_detail',
        'configuration' => [
          $field_type => $value,
          'order_field' => $field_type,
        ],
        'counter' => 9,
      ]);

      $rule->save();
      $this->assertEquals(TRUE, $rule->getPlugin()->apply($this->order));

      $init = $init + 1;
    }
  }

}
