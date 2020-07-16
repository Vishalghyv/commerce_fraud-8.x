<?php

namespace Drupal\Tests\commerce_fraud\Kernel\Plugin\Commerce\FraudRule;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_price\Calculator;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductType;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_fraud\Entity\Rules;
use Drupal\Tests\commerce_order\Kernel\OrderKernelTestBase;

/**
 * Tests commerce fraud rule plugin.
 *
 * @coversDefaultClass \Drupal\commerce_fraud\Plugin\Commerce\FraudRule\ProductAttributeFraudRule
 * @group commerce
 */
class ProductAttributeFraudRuleTest extends OrderKernelTestBase {

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
   * The order item storage.
   *
   * @var \Drupal\commerce_order\OrderItemStorageInterface
   */
  protected $orderItemStorage;

  /**
   * The variation to test against.
   *
   * @var \Drupal\commerce_product\Entity\ProductVariation
   */
  protected $variations = [];

  protected $product_one;
  protected $product_two;

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

    $this->orderItemStorage = $this->container->get('entity_type.manager')->getStorage('commerce_order_item');

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

    $this->variations[0] = ProductVariation::create([
        'type' => 'default',
        'sku' => 'jkfd',
        'price' => [
          'number' => 999,
          'currency_code' => 'USD',
        ],
      ]);
    $this->variations[0]->save();

    $this->variations[1] = ProductVariation::create([
        'type' => 'default',
        'sku' => 'sdf',
        'price' => [
          'number' => 929,
          'currency_code' => 'USD',
        ],
      ]);
    $this->variations[1]->save();

    $this->product_one = Product::create([
      'type' => 'default',
      'title' => 'Example 1',
      'stores' => [$this->store],
      'variations' => [$this->variations[0]],
    ]);
    $this->product_one->save();

    $this->product_two = Product::create([
      'type' => 'default',
      'title' => 'Example 2',
      'stores' => [$this->store],
      'variations' => [$this->variations[1]],
    ]);
    $this->product_two->save();
    // var_dump($this->product_one->uuid());
    // var_dump($this->product_two->uuid());
    // $this->variations[0] = $this->reloadEntity($this->variations[0]);
    // $this->variations[1] = $this->reloadEntity($this->variations[1]);

    $this->rule = Rules::create([
      'id' => 'example',
      'label' => 'Product Attribute',
      'status' => TRUE,
      'plugin' => 'product_attribute',
      'product_conditions' => [
            [
              'plugin' => 'order_item_product',
              'configuration' => [
                'products' => [
                  ['product' => $this->product_one->uuid()],
                ],
              ],
            ],
          ],
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
    $order_item = $this->orderItemStorage->createFromPurchasableEntity($this->variations[1]);
    $order_item->save();
    // var_dump($order_item->uuid());
    $this->order->addItem($order_item);
    $this->order->save();
    $this->assertEquals(FALSE, $this->rule->getPlugin()->apply($this->order));
  }

  /**
   * Tests the applicable use case.
   *
   * @covers ::apply
   */
  public function testApplicableRule() {
    $order_item = $this->orderItemStorage->createFromPurchasableEntity($this->variations[0]);
    $order_item->save();
    $this->order->addItem($order_item);
    $this->order->save();
    $this->assertEquals(TRUE, $this->rule->getPlugin()->apply($this->order));
  }

}
