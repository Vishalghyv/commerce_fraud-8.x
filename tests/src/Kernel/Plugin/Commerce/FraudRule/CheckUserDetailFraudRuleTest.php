<?php

namespace Drupal\Tests\commerce_fraud\Kernel\Plugin\Commerce\FraudRule;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_fraud\Entity\Rules;
use Drupal\Tests\commerce_order\Kernel\OrderKernelTestBase;
use Drupal\user\Entity\User;

/**
 * Tests commerce fraud rule plugin.
 *
 * @coversDefaultClass \Drupal\commerce_fraud\Plugin\Commerce\FraudRule\CheckUserDetailFraudRule
 * @group commerce
 */
class CheckUserDetailFraudRuleTest extends OrderKernelTestBase {

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

    $user = $this->createUser([
      'name' => 'name',
      'mail' => 'example@example.com'
    ]);
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

  }

  /**
   * Tests the non-applicable use case.
   *
   * @covers ::apply
   */
  public function testNotApplicableRule() {
    $this->rule_field_types = [
      'user_id' => 0,
      'user_email' => 'not_same@example.com',
      'user_name' => 'not same',
    ];
    $init = 1;

    foreach ($this->rule_field_types as $field_type => $value) {
      $rule = Rules::create([
        'id' => 'example'.(string)$init,
        'label' => 'Check User Detail',
        'status' => TRUE,
        'plugin' => 'check_user_detail',
        'configuration' => [
          $field_type => $value,
          'user_field' => $field_type,
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
      'user_id' => 1,
      'user_email' => 'example@example.com',
      'user_name' => 'name',
    ];
    $init = 1;

    foreach ($this->rule_field_types as $field_type => $value) {
      $rule = Rules::create([
        'id' => 'example'.(string)$init,
        'label' => 'Check User Detail',
        'status' => TRUE,
        'plugin' => 'check_user_detail',
        'configuration' => [
          $field_type => $value,
          'user_field' => $field_type,
        ],
        'counter' => 9,
      ]);

      $rule->save();
      $this->assertEquals(TRUE, $rule->getPlugin()->apply($this->order));

      $init = $init + 1;
    }
  }

}
