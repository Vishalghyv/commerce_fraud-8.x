<?php

namespace Drupal\Tests\commerce_fraud\Kernel\Entity;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderType;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_price\RounderInterface;
use Drupal\commerce_fraud\Entity\Rules;
use Drupal\commerce_price\Price;
use Drupal\commerce_fraud\Plugin\Commerce\FraudRule\TotalPriceFraudRule;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\Tests\commerce_order\Kernel\OrderKernelTestBase;

/**
 * Tests the Promotion entity.
 *
 * @coversDefaultClass \Drupal\commerce_fraud\Entity\Rules
 *
 * @group commerce
 */
class RulesTest extends OrderKernelTestBase {

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
  }

  /**
   * @covers ::getCounter
   * @covers ::setCounter
   * @covers ::getPlugin
   * @covers ::getPluginId
   * @covers ::setPluginId
   * @covers ::getPluginCollections
   * @covers ::getPluginConfiguration
   * @covers ::setPluginConfiguration
   */
  public function testPromotion() {
    $order = Order::create([
      'type' => 'default',
      'state' => 'completed',
      'mail' => 'test@example.com',
      'ip_address' => '127.0.0.1',
      'order_number' => '6',
      'uid' => $this->createUser(),
      'store_id' => $this->store,
      'order_items' => [],
    ]);

    $rule = Rules::create([
      'id' => 'example',
      'label' => 'Total Price',
      'status' => TRUE,
      'plugin' => 'total_price',
      'configuration' => [
        'buy_amount' => [
          'number' => 10,
          'currency_code' => 'USD',
        ],
      ],
      'counter' => 9,
    ]);

    $rule->save();

    $order_item = OrderItem::create([
      'type' => 'default',
      'quantity' => 4,
      'unit_price' => new Price('12.00', 'USD'),
    ]);
    $order_item->save();
    $order->addItem($order_item);
    $order->save();

    $rule->setCounter(12);
    $this->assertEquals(12, $rule->getCounter());

    $this->assertEquals('total_price', $rule->getPluginId());

    $buy_amount = ["buy_amount"=>
      [
        "number" => 10,
        "currency_code" => "USD",
      ]
    ];
    $this->assertEquals($buy_amount, $rule->getPluginConfiguration());

    $this->assertEquals(TRUE, $rule->getPlugin()->apply($order));

    $rule->setPluginId('total_quantity');
    $this->assertEquals('total_quantity', $rule->getPluginId());

    $buy_quantity = ["buy_quantity" => 3];

    $rule->setPluginConfiguration($buy_quantity);
    $this->assertEquals($buy_quantity, $rule->getPluginConfiguration());

    $this->assertEquals(TRUE, $rule->getPlugin()->apply($order));
  }

}
