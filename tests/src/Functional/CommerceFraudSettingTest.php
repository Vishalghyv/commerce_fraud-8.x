<?php

namespace Drupal\Tests\commerce_fraud\Functional;

use Drupal\Tests\commerce\Functional\CommerceBrowserTestBase;

/**
 * Tests the commerce fraud fraudulent locality feature.
 *
 * @group commerce
 */
class CommerceFraudSettingTest extends CommerceBrowserTestBase {

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
      'administer site configuration',
    ], parent::getAdministratorPermissions());
  }

  /**
   * Tests Commerce Fraud Fraudulent Locality Feature.
   */
  public function testCommerceFraydSetting() {
    $this->drupalGet('/admin/commerce/config/commerce_fraud');
    $page = $this->getSession()->getPage();

    $this->assertSession()->pageTextContains('Commerce Fraud Caps Settings');

    $this->assertSession()->pageTextContains('Activate this to stop the blocklisted orders from completeing checkout');

    $this->assertSession()->pageTextContains('Email');

    $this->assertSession()->checkboxNotChecked('stop_order');


    $edit = array(
    'commerce_fraud_checklist_cap' => 20,
    'commerce_fraud_blocklist_cap' => 15,
    );

    $this->submitForm($edit, 'Save configuration');

    $this->assertSession()->pageTextContains(t('Check List value cannot be equal to or more than Block List value'));

    $this->assertSession()->pageTextContains(t('The configuration options have been saved.'));

    $edit = array(
    'commerce_fraud_checklist_cap' => 20,
    'commerce_fraud_blocklist_cap' => 15,
    );

    $this->submitForm($edit, 'Save configuration');

  }

}
