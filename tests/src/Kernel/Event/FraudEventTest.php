<?php

namespace Drupal\Tests\commerce_fraud\Kernel\Event;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order_number\OrderNumberFormatterInterface;
use Drupal\Tests\commerce\Kernel\CommerceKernelTestBase;
use Drupal\user\Entity\User;
use Drupal\Core\Database\Database;

/**
 * Tests OrderNumberSubscriber class.
 *
 * @coversDefaultClass \Drupal\commerce_fraud\EventSubscriber\FraudEvent
 *
 * @group commerce
 */
class FraudEventTest extends CommerceKernelTestBase {

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
    'entity_reference_revisions',
    'path',
    'profile',
    'state_machine',
    'commerce_product',
    'commerce_order',
    'commerce_test',
    'commerce_fraud',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('profile');
    $this->installEntitySchema('commerce_order');
    $this->installEntitySchema('commerce_order_item');
    $this->installEntitySchema('commerce_product');
    $this->installEntitySchema('commerce_product_variation');
    $this->installEntitySchema('rules');
    $this->installSchema('commerce_fraud', ['commerce_fraud_fraud_score']);
    $this->installConfig([
      'commerce_product',
      'commerce_order',
      'commerce_fraud',
    ]);
    $this->rule = Rules::create([
      'id' => 'example',
      'label' => 'ANONYMOUS',
      'status' => TRUE,
      'plugin' => 'anonymous_user',
      'counter' => 9,
    ]);

    $this->rule->save();
  }

  /**
   * Tests setting the order number on place transition.
   *
   */
  public function testFraudEvent() {
    $user = $this->createUser();

    /** @var \Drupal\commerce_order\Entity\Order $order1 */
    $order = Order::create([
      'type' => 'default',
      'state' => 'draft',
      'mail' => 'text@example.com',
      'uid' => $user->id(),
      'ip_address' => '127.0.0.1',
      'store_id' => $this->store->id(),
    ]);
    $order->save();

    $fraud_count = $rule->getCounter();
    $rule_name = $rule->getPLugin()->getLabel();

    $note = $rule_name . ": " . $fraud_count;
    $event = new FraudEvent($fraud_count, $order->id(), $note);

    $this->eventDispatcher->dispatch(FraudEvents::FRAUD_COUNT_INSERT, $event);
    // Query to get all fraud score for order id.
    $query = Database::getConnection()->select('commerce_fraud_fraud_score');
    $query->condition('order_id', $order1->id());
    $query->addExpression('SUM(fraud_score)', 'fraud');
    $result = $query->execute()->fetchCol();

    $this->assertEquals(11, $result[0]);

    $this->assertEquals('place',$order1->getState());
    // // Now, test force override option.
    // $config->set('force', TRUE)->save();
    // /** @var \Drupal\commerce_order\Entity\Order $order2 */
    // $order3 = Order::create([
    //   'type' => 'default',
    //   'state' => 'draft',
    //   'mail' => 'text@example.com',
    //   'uid' => $user->id(),
    //   'ip_address' => '127.0.0.1',
    //   'order_number' => '8888',
    //   'store_id' => $this->store->id(),
    // ]);
    // $order3->save();

    // $transition = $order3->getState()->getTransitions();
    // $order3->getState()->applyTransition($transition['place']);
    // $order3->save();
    // $this->assertEquals('#00002', $order3->getOrderNumber(), 'Explicitly set order number should be overridden, if force option is active in configuration.');
  }

}
