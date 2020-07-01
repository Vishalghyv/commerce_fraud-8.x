<?php

namespace Drupal\Tests\commerce_fruad\Kernel\Plugin\Commerce\FraudRule;

use Drupal\Tests\commerce_order\Kernel\OrderKernelTestBase;

/**
 * Tests the "Buy X Get Y" offer.
 *
 * @coversDefaultClass \Drupal\commerce_promotion\Plugin\Commerce\PromotionOffer\BuyXGetY
 *
 * @group commerce
 */
class AnonymousUserFraudRuleTest extends OrderKernelTestBase {

  /**
   * The test order.
   *
   * @var \Drupal\commerce_order\Entity\OrderInterface
   */
  protected $order;

  /**
   * The test promotion.
   *
   * @var \Drupal\commerce_promotion\Entity\PromotionInterface
   */
  protected $promotion;

  /**
   * The test variations.
   *
   * @var \Drupal\commerce_product\Entity\ProductVariationInterface[]
   */
  protected $variations = [];

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

    // $this->installEntitySchema('rules');
    // // $this->installConfig(['commerce_fraud']);
    // $this->installSchema('commerce_fraud_fraud_score', ['rules']);
    // $product_type = ProductType::create([
    //   'id' => 'test',
    //   'label' => 'Test',
    //   'variationType' => 'default',
    // ]);
    // $product_type->save();
    // For ($i = 0; $i < 4; $i++) {
    //   $this->variations[$i] = ProductVariation::create([
    //     'type' => 'default',
    //     'sku' => $this->randomMachineName(),
    //     'price' => [
    //       'number' => Calculator::multiply('10', $i + 1),
    //       'currency_code' => 'USD',
    //     ],
    //   ]);
    //   $this->variations[$i]->save();
    // }
    // $first_product = Product::create([
    //   'type' => 'test',
    //   'title' => $this->randomMachineName(),
    //   'stores' => [$this->store],
    //   'variations' => [$this->variations[0]],
    // ]);
    // $first_product->save();
    // $second_product = Product::create([
    //   'type' => 'default',
    //   'title' => $this->randomMachineName(),
    //   'stores' => [$this->store],
    //   'variations' => [$this->variations[1]],
    // ]);
    // $second_product->save();
    // $third_product = Product::create([
    //   'type' => 'default',
    //   'title' => 'Hat 1',
    //   'stores' => [$this->store],
    //   'variations' => [$this->variations[2]],
    // ]);
    // $third_product->save();
    // $fourth_product = Product::create([
    //   'type' => 'default',
    //   'title' => 'Hat 2',
    //   'stores' => [$this->store],
    //   'variations' => [$this->variations[3]],
    // ]);
    // $fourth_product->save();
    // $this->order = Order::create([
    //   'type' => 'default',
    //   'state' => 'completed',
    //   'mail' => 'test@example.com',
    //   'ip_address' => '127.0.0.1',
    //   'order_number' => '6',
    //   'uid' => $this->createUser(),
    //   'store_id' => $this->store,
    //   'order_items' => [],
    // ]);
    // // Buy 6 "test" products, get 4 hats.
    // $this->promotion = Promotion::create([
    //   'name' => 'Promotion 1',
    //   'order_types' => [$this->order->bundle()],
    //   'stores' => [$this->store->id()],
    //   'offer' => [
    //     'target_plugin_id' => 'order_buy_x_get_y',
    //     'target_plugin_configuration' => [
    //       'buy_quantity' => 6,
    //       'buy_conditions' => [
    //         [
    //           'plugin' => 'order_item_product_type',
    //           'configuration' => [
    //             'product_types' => ['test'],
    //           ],
    //         ],
    //       ],
    //       'get_quantity' => 4,
    //       'get_conditions' => [
    //         [
    //           'plugin' => 'order_item_product',
    //           'configuration' => [
    //             'products' => [
    //               ['product' => $third_product->uuid()],
    //               ['product' => $fourth_product->uuid()],
    //             ],
    //           ],
    //         ],
    //       ],
    //       'offer_type' => 'fixed_amount',
    //       'offer_amount' => [
    //         'number' => '1.00',
    //         'currency_code' => 'USD',
    //       ],
    //     ],
    //   ],
    //   'status' => FALSE,
    // ]);
  }

  /**
   * Tests the non-applicable use cases.
   *
   * @covers ::apply
   */
  public function testNotApplicable() {
    /** @var \Drupal\commerce_order\OrderItemStorageInterface $order_item_storage */
    // $order_item_storage = \Drupal::entityTypeManager()->getStorage('commerce_order_item');
    // $first_order_item = $order_item_storage->createFromPurchasableEntity($this->variations[0], [
    //   'quantity' => '2',
    // ]);
    // $first_order_item->save();
    // $second_order_item = $order_item_storage->createFromPurchasableEntity($this->variations[1], [
    //   'quantity' => '4',
    // ]);
    // $second_order_item->save();
    // $this->order->setItems([$first_order_item, $second_order_item]);
    // $this->order->save();
    // // Insufficient purchase quantity.
    // // Only the first order item is counted (due to the product type condition),
    // // and its quantity is too small (2 < 6).
    // $this->promotion->apply($this->order);
    // $this->assertEmpty($this->order->collectAdjustments());
    // // Sufficient purchase quantity, but no offer order item found.
    // $first_order_item->setQuantity(6);
    // $first_order_item->save();
    // $this->order->save();
    // $this->promotion->apply($this->order);
    // this->assertEmpty($this->order->collectAdjustments());
    $this->assertEmpty(5, 5);
  }

}
