<?php

namespace Drupal\kc_order_status\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Utility\Token;
use Drupal\commerce_checkout\Attribute\CommerceCheckoutPane;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a custom message pane.
 *
 * @CommerceCheckoutPane(
 *   id = "kc_shipping_completed",
 *   label = @Translation("The order information"),
 *   display_label = @Translation("The order information"),
 *   default_step = "completed",
 *   wrapper_element = "fieldset",
 * )
 */
class Completed extends CheckoutPaneBase {

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $order = $this->order;

    $state_label = $this->getStateLabel();

    $created_date = date('d.m.Y', $order->getCreatedTime());

    $pane_form['message'] = [
      '#theme' => 'kc_order_info',
      '#order_entity' => $this->order,
      '#state_label' => $this->t($state_label),
      '#created_date' => $created_date,
    ];

    return $pane_form;
  }

  public function getStateLabel() {
    $order_state = $this->order->getState();
    return $order_state->getLabel();

    return '';
  }

}
