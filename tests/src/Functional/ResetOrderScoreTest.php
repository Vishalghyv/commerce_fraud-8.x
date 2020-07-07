<?php

namespace Drupal\Tests\commerce_fraud\Functional;

use Drupal\Tests\commerce_order\Functional\OrderBrowserTestBase;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\Tests\commerce_order\FunctionalJavascript\OrderWebDriverTestBase;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the commerce_order entity forms.
 *
 * @group commerce
 */
class ResetOrderScoreTest extends OrderBrowserTestBase {

  /**
   * A test order.
   *
   * @var \Drupal\commerce_order\Entity\OrderInterface
   */
  protected $order;

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
      'administer commerce_order',
      'administer profile',
    ], parent::getAdministratorPermissions());
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // $this->installEntitySchema('rules');
    // $this->installConfig(['commerce_fraud']);
    // $this->installSchema('commerce_fraud', ['commerce_fraud_fraud_score']);

    $order_item = $this->createEntity('commerce_order_item', [
      'type' => 'default',
      'unit_price' => [
        'number' => '999',
        'currency_code' => 'USD',
      ],
    ]);
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $this->order = $this->createEntity('commerce_order', [
      'type' => 'default',
      'mail' => $this->loggedInUser->getEmail(),
      'uid' => $this->loggedInUser->id(),
      'order_items' => [$order_item],
      'store_id' => $this->store,
    ]);

  }

  /**
   * Tests deleting an order programaticaly and through the UI.
   */
  public function testResetOrder() {
    // $this->drupalGet($this->order->toUrl('reset-fraud-score-form'));
    $this->drupalGet('/admin/commerce/orders/'.$this->order->id().'/reset_fraud');
    $this->assertSession()->pageTextContains(t("Do you want to reset order fraud score order -id: @id?", ['@id' => $this->order->id()]));
    $this->assertSession()->pageTextContains(t("Reset this order's fraud score to 0"));
    $this->submitForm([], 'Reset Fraud Score');
    $collection_url = $this->order->toUrl('collection', ['absolute' => TRUE]);
    $this->assertSession()->addressEquals($collection_url);
    $this->assertSession()->pageTextContains(t('The order has been reseted.'));
    
  }

}
