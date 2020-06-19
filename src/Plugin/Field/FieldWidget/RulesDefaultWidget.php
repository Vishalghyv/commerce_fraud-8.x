<?php

/**
 * @file
 * Contains \Drupal\commerce_fraud\Plugin\Field\FieldWidget\RuleDefaultWidget.
 */

namespace Drupal\snippets\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;

/**
 * Plugin implementation of the 'rule_default' widget.
 *
 * @FieldWidget(
 *   id = "rule_default",
 *   label = @Translation("Snippets default"),
 *   field_types = {
 *     "rule_item"
 *   }
 * )
 */
class RuleDefaultWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, array &$form_state) {

    $element['source_description'] = array(
          '#title' => t('Description'),
          '#type' => 'textfield',
          '#default_value' => isset($items[$delta]->source_description) ? $items[$delta]->source_description : NULL,
        );
    $element['source_code'] = array(
          '#title' => t('Code'),
          '#type' => 'textarea',
          '#default_value' => isset($items[$delta]->source_code) ? $items[$delta]->source_code : NULL,
        );
    $element['source_lang'] = array(
          '#title' => t('Source language'),
          '#type' => 'textfield',
          '#default_value' => isset($items[$delta]->source_lang) ? $items[$delta]->source_lang : NULL,
        );
    return $element;
  }
}
