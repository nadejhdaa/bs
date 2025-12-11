<?php

namespace Drupal\kc_order_status\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a custom message pane.
 *
 * @CommerceCheckoutPane(
 *   id = "kc_shipping_info_pane",
 *   label = @Translation("Shipping information"),
 *   display_label = @Translation("Another display label"),
 *   default_step = "shipping_information",
 *   wrapper_element = "fieldset",
 * )
 */
class ShippingInfoPane extends CheckoutPaneBase {

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $form_object = \Drupal::entityTypeManager()->getFormObject('commerce_order', 'edit');
    $order = $this->order;
    $form_object->setEntity($order);

    $form = \Drupal::formBuilder()->getForm($form_object);
    $pane_form['field_shipping_type'] = $form['field_shipping_type'];

    $pane_form['field_shipping_type']['widget']['#title'] = '';
    $pane_form['field_shipping_type']['#title'] = '';

    $pane_form['field_shipping_type']['widget']['#title_display'] = 'hidden';
    $pane_form['field_shipping_type']['#title_display'] = 'hidden';

    unset($pane_form['field_shipping_type']['widget']['_none']);
    unset($pane_form['field_shipping_type']['widget']['#options']['_none']);

    $options_keys = array_keys($pane_form['field_shipping_type']['widget']['#options']);
 
    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $input = $form_state->getUserInput();

    if (empty($input['field_shipping_type'])) {
      $form_state->setErrorByName('field_shipping_type', $this->t('Please, select the shipping method.'));
    }
  }

  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $input = $form_state->getUserInput();

    $this->order->set('field_shipping_type', $input['field_shipping_type']);
    $this->order->save();
  }


}
