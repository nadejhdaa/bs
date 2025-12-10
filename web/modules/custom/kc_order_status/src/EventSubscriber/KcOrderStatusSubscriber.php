<?php

declare(strict_types=1);

namespace Drupal\kc_order_status\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\commerce_order\Event\OrderEvents;
use Drupal\commerce_order\Event\OrderEvent;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Drupal\commerce_checkout\Event\CheckoutEvents;

/**
 * @todo Add description for this subscriber.
 */
final class KcOrderStatusSubscriber implements EventSubscriberInterface {

  /**
   * Constructs a KcOrderStatusSubscriber object.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Kernel request event handler.
   */
  public function onKernelRequest(RequestEvent $event): void {

  }

  /**
   * Kernel response event handler.
   */
  public function onKernelResponse(ResponseEvent $event): void {
    // @todo Place your code here.
  }

  public function test(OrderEvent $event) {
    $order = $event->getOrder();

  }

  public function test2(OrderEvent $event) {
    $order = $event->getOrder();
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events = [];
    $events[OrderEvents::ORDER_UPDATE][] = ['test', 0];
    // $events['commerce_order.place.pre_transition'][] = ['test2', 0];
    // $events['commerce_order.place.post_transition'][] = ['test2', 0];
    // $events[CheckoutEvents::COMPLETION][] = ['test2', 0];
    return $events;
  }


}
