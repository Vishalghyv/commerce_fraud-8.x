<?php

namespace Drupal\commerce_fraud\EventSubscriber;

use Drupal\commerce_fraud\CommerceFraudGenerationServiceInterface;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Event subscriber, that acts on the place transition of commerce order
 * entities, in order to generate and set an order number.
 */
class CommerceFraudSubscriber implements EventSubscriberInterface {

  /**
   * The order number generation service.
   *
   * @var \Drupal\commerce_fraud\OrderNumberGenerationServiceInterface
   */
  protected $commerceFraudGenerationService;

  /**
   * Constructs a new OrderNumberSubscriber object.
   *
   * @param \Drupal\commerce_fraud\CommerceFraudGenerationServiceInterface $commerce_fraud_generation_service
   *   The order number generation service.
   */
  public function __construct(CommerceFraudGenerationServiceInterface $commerce_fraud_generation_service) {
    $this->commerceFraudGenerationService = $commerce_fraud_generation_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
      'commerce_order.place.pre_transition' => ['setOrderNumber'],
    ];
    return $events;
  }

  /**
   * Sets the order number on placing the order.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The transition event.
   */
  public function setOrderNumber(WorkflowTransitionEvent $event) {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $event->getEntity();
    $rules = \Drupal::entityTypeManager()->getStorage('rules')->loadByProperties(['offer' => 'total_price']);
    foreach ($rules as $rule) {
     $this->commerceFraudGenerationService->generateAndSetFraudCount($order,$rule->getOffer());
    }
  }

}
