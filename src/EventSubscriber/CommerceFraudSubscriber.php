<?php

namespace Drupal\commerce_fraud\EventSubscriber;

use Drupal\commerce_fraud\CommerceFraudGenerationServiceInterface;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\commerce_fraud\Event\FraudEvents;
use Drupal\commerce_fraud\Event\FraudEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Event subscriber, that acts on the place transition of commerce order
 * entities, in order to generate and set an order number.
 */
class CommerceFraudSubscriber implements EventSubscriberInterface {

  /**
   * The event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

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
  public function __construct(EventDispatcherInterface $event_dispatcher, CommerceFraudGenerationServiceInterface $commerce_fraud_generation_service) {
    $this->eventDispatcher = $event_dispatcher;
    $this->commerceFraudGenerationService = $commerce_fraud_generation_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('event_dispatcher')
    );
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
    $rules = \Drupal::entityTypeManager()->getStorage('rules');

    foreach ($rules->loadMultiple() as $rule) {
      $this->commerceFraudGenerationService->generateAndSetFraudCount($order, $rule->getRule()->getPluginId(), $rule->getCounter());
    }
    drupal_set_message("gd{$order->getOrderNumber()}");
    $count = 5; 
    $event = new FraudEvent($count, $order->getOrderNumber());

    $this->eventDispatcher->dispatch(FraudEvents::FRAUD_COUNT_UPDATED, $event);
  }

}
