<?php

namespace Drupal\Tests\commerce_fraud\Kernel\Plugin\Commerce\FraudRule;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_fraud\Entity\Rules;
use Drupal\Tests\commerce_order\Kernel\OrderKernelTestBase;
use Drupal\user\Entity\User;

/**
 * Tests actions source plugin.
 *
 * @coversDefaultClass \Drupal\commerce_fraud\Plugin\Commerce\FraudRule\CheckUserIpFraudRule
 * @group commerce
 */
class CheckUserIpFraudRuleTest extends OrderKernelTestBase {

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

    $user = $this->createUser();
    $this->user = $this->reloadEntity($user);

    $this->order = Order::create([
      'type' => 'default',
      'state' => 'completed',
      'mail' => 'test@example.com',
      'ip_address' => '127.0.0.1',
      'order_number' => '6',
      'uid' => $this->user,
      'store_id' => $this->store,
      'order_items' => [],
    ]);

    $this->new_order = Order::create([
      'type' => 'default',
      'state' => 'draft',
      'mail' => 'test@example.com',
      'ip_address' => '127.0.0.1',
      'order_number' => '6',
      'uid' => $this->user,
      'store_id' => $this->store,
      'order_items' => [],
    ]);

    $this->rule = Rules::create([
      'id' => 'example',
      'label' => 'Check User IP',
      'status' => TRUE,
      'plugin' => 'check_user_ip',
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
    $this->assertEquals(FALSE, $this->rule->getPlugin()->apply($this->new_order));
  }

  /**
   * Tests the applicable use case.
   *
   * @covers ::apply
   */
  public function testApplicableRule() {
    $ip_address = '127.0.0.2';
    $this->order->setIpAddress($ip_address);
    $this->order->save();
    $this->assertEquals(TRUE, $this->rule->getPlugin()->apply($this->new_order));
  }

}
