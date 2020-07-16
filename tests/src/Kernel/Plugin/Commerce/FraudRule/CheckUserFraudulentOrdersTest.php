<?php

namespace Drupal\Tests\commerce_fraud\Kernel\Plugin\Commerce\FraudRule;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_fraud\Entity\Rules;
use Drupal\Tests\commerce_order\Kernel\OrderKernelTestBase;
use Drupal\user\Entity\User;

/**
 * Tests commerce fraud rule plugin.
 *
 * @coversDefaultClass \Drupal\commerce_fraud\Plugin\Commerce\FraudRule\CheckUserFraudulentOrders
 * @group commerce
 */
class CheckUserFraudulentOrdersTest extends OrderKernelTestBase {

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
      'state' => 'fraudulent',
      'mail' => 'test@example.com',
      'ip_address' => '127.0.0.1',
      'order_number' => '6',
      'uid' => $this->user->id(),
      'store_id' => $this->store,
      'order_items' => [],
    ]);
    $this->order->save();

    $this->order2 = Order::create([
      'type' => 'default',
      'state' => 'fraudulent',
      'mail' => 'test@example.com',
      'ip_address' => '127.0.0.1',
      'order_number' => '6',
      'uid' => $this->user->id(),
      'store_id' => $this->store,
      'order_items' => [],
    ]);
    $this->order2->save();

    $this->new_order = Order::create([
      'type' => 'default',
      'state' => 'draft',
      'mail' => 'test@example.com',
      'ip_address' => '127.0.0.1',
      'order_number' => '6',
      'uid' => $this->user->id(),
      'store_id' => $this->store,
      'order_items' => [],
    ]);
    $this->new_order->save();

  }

  /**
   * Tests the non-applicable use case.
   *
   * @covers ::apply
   */
  public function testNotApplicableRule() {
    $rule = Rules::create([
      'id' => 'example',
      'label' => 'Fraudulent Orders',
      'status' => TRUE,
      'plugin' => 'fraudulent_orders',
      'configuration' => [
        'fraudulent_orders' => 2,
      ],
      'counter' => 9,
    ]);

    $rule->save();
    $this->assertEquals(FALSE, $rule->getPlugin()->apply($this->new_order));
  }

  /**
   * Tests the applicable use case.
   *
   * @covers ::apply
   */
  public function testApplicableRule() {
    $rule = Rules::create([
      'id' => 'example',
      'label' => 'Fraudulent Orders',
      'status' => TRUE,
      'plugin' => 'fraudulent_orders',
      'configuration' => [
        'fraudulent_orders' => 1,
      ],
      'counter' => 9,
    ]);

    $rule->save();
    $this->assertEquals(TRUE, $rule->getPlugin()->apply($this->new_order));
  }

}
