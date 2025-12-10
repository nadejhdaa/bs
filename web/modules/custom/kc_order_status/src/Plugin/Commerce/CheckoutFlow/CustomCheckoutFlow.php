<?php

namespace Drupal\kc_order_status\Plugin\Commerce\CheckoutFlow;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowWithPanesBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_checkout\Event\CheckoutEvents;
use Drupal\commerce_order\Event\OrderEvent;
use Drupal\commerce_order\Entity\OrderInterface;

/**
 * @CommerceCheckoutFlow(
 *  id = "custom_checkout_flow",
 *  label = @Translation("Custom checkout flow"),
 * )
 */
class CustomCheckoutFlow extends CheckoutFlowWithPanesBase {

  /**
   * {@inheritdoc}
   */
  public function getSteps() {
    return [
      'login' => [
        'label' => $this->t('Login'),
        'next_label' => $this->t('Login'),
        'has_sidebar' => TRUE,
      ],
      'shipping_information' => [
        'label' => $this->t('Method of receiving'),
        'next_label' => $this->t('Select the method of receiving'),
        'has_sidebar' => TRUE,
      ],
      'customer_information' => [
        'label' => $this->t('Receiver information'),
        'next_label' => $this->t('Receiver of order'),
        'has_sidebar' => TRUE,
      ],
      'address_information' => [
        'label' => $this->t('The address of receiving'),
        'next_label' => $this->t('Add the address of receiving'),
        'has_sidebar' => TRUE,
      ],
      'review' => [
        'label' => $this->t('Order review'),
        'next_label' => $this->t('Review the order'),
        'has_sidebar' => TRUE,
      ],
      'validation' => [
        'label' => $this->t('The order is placed'),
        'next_label' => $this->t('Complete your order'),
        'has_sidebar' => FALSE,
      ],
      'order_information' => [
        'label' => $this->t('The order information'),
        'next_label' => $this->t('The order information'),
        'has_sidebar' => FALSE,
      ],
    ];
  }

  protected function onStepChange($step_id) {
    if ($step_id == 'review') {
      $order = $this->getOrder();
      $order_state = $order->getState();
      $order_state_transitions = $order_state->getTransitions();

    }

    if ($step_id == 'validation') {
      $order = $this->getOrder();
      $event = new OrderEvent($order);
      // $this->eventDispatcher->dispatch($event, CheckoutEvents::COMPLETION);
      $order_state = $order->getState();
      $order->getState()->applyTransitionById('place');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getStepId($requested_step_id = NULL) {
    // Customers can't edit orders that have already been placed.
    if ($this->getOrder()->getState()->getId() != 'draft') {
      return 'order_information';
    }
  }

}
