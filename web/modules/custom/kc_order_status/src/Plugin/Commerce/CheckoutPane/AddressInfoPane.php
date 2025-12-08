<?php

namespace Drupal\kc_order_status\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Render\Element;
use Drupal\Core\Ajax\HtmlCommand;

/**
 * Provides a custom message pane.
 *
 * @CommerceCheckoutPane(
 *   id = "kc_address_info_pane",
 *   label = @Translation("Address information new"),
 *   display_label = @Translation("Address information"),
 *   default_step = "address_information",
 *   wrapper_element = "fieldset",
 * )
 */
class AddressInfoPane extends CheckoutPaneBase {
use LoggerChannelTrait;
  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $form_object = $this->entityTypeManager->getFormObject('commerce_order', 'edit');
    $order = $this->order;

    $profile = $this->getDeliveryProfile();

    $form_object->setEntity($order);

    $shipping_type = $order->field_shipping_type->target_id;
    $form = \Drupal::formBuilder()->getForm($form_object);

    $shipping_options = $form['field_shipping_type']['widget']['#options'];
    $selected_shopping_label = $shipping_options[$shipping_type];

    $pane_form['#tree'] = 1;
    $pane_form['selected_shipping'] = [
      '#type' => 'item',
      '#markup' => '<div class="kc-checkout__shipping-info__value">' . $selected_shopping_label . '</div>',
      '#title' => $this->t('Selected shipping method'),
      '#wrapper_attributes' => [
        'class' => ['kc-checkout__shipping-info'],
      ],
    ];

    // Select
    if ($shipping_type == '1') {
      $pane_form['field_pick_up_point'] = $form['field_pick_up_point'];

      unset($pane_form['field_pick_up_point']['widget']['#options']['_none']);
      unset($pane_form['field_pick_up_point']['widget']['_none']);

      $pane_form['field_pick_up_point']['widget']['#required'] = 1;
      $pane_form['field_pick_up_point']['#required'] = 1;
    }
    // Fill the delivery profile.
    elseif ($shipping_type == '2') {
      $profiles = $this->loadUserDeliveryProfiles();

      if (!empty($profiles)) {
        foreach ($profiles as $id => $profile) {
          $options[$id] = $profile->field_street->value;
        }

        $options['add'] = $this->t('Add new address');

        $pane_form['select_delivery_profile'] = [
          '#type' => 'select',
          '#title' => $this->t('Delivery address'),
          '#options' => $options,
          '#ajax' => [
            'callback' => [$this, 'selectDeliveryAjaxCallback'],
          ],
        ];

        $pane_form['delivery_address_wrapper'] = [
          '#type' => 'container',
          '#attributes' => [
            'class' => [
              'kc-checkout__delivery-address',
            ],
          ],
        ];

        $default_profile = !empty($profiles) ? reset($profiles) : NULL;
        $form_elements = $this->buildProfileForm($default_profile);

        $pane_form['delivery_address_wrapper']['delivery_address'] = [
          '#type' => 'container',
          '#tree' => 1,
        ];

        foreach ($form_elements as $key => $form_element) {
          $pane_form['delivery_address_wrapper']['delivery_address'][$key] = $form_element;
        }


      }
    }
    return $pane_form;
  }

  public function selectDeliveryAjaxCallback(array $pane_form, FormStateInterface $form_state) {
    $input = $form_state->getUserInput();

    $id = $input['kc_address_info_pane']['select_delivery_profile'];
    $storage = $this->entityTypeManager->getStorage('profile');

    if (is_numeric($id)) {
      $profile = $storage->load($id);
    }
    else {
      $order = $this->order;
      $uid = $order->getCustomerId();

      $data = [
        'uid' => $uid,
        'type' => 'delivery',
        'is_default' => TRUE,
      ];

      $profile = $storage->create($data);
    }

    $form_object = $this->entityTypeManager->getFormObject('profile', 'edit');
    $form_object->setEntity($profile);

    $form = \Drupal::formBuilder()->getForm($form_object);



    foreach (Element::children($form) as $key) {
      if (str_contains($key, 'field_')) {
        $pane_form['delivery_address_wrapper']['delivery_address'][$key] = $form[$key];
      }
    }

    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('.kc-checkout__delivery-address', $pane_form['delivery_address_wrapper']));

    return $response;
  }

  public function buildProfileForm($profile = NULL) {
    if (empty($profile)) {
      $order = $this->order;
      $uid = $order->getCustomerId();

      $data = [
        'uid' => $uid,
        'type' => 'delivery',
        'is_default' => TRUE,
      ];

      $profile = $storage->create($data);
    }

    $form_object = $this->entityTypeManager->getFormObject('profile', 'edit');
    $form_object->setEntity($profile);

    $form = \Drupal::formBuilder()->getForm($form_object);

    $form_elements = [];
    foreach (Element::children($form) as $key) {
      if (str_contains($key, 'field_')) {
        $form_elements[$key] = $form[$key];
      }
    }

    return $form_elements;
  }

  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $input = $form_state->getUserInput();

    if (!empty($input['kc_address_info_pane']['select_delivery_profile'])) {
      $id = $input['kc_address_info_pane']['select_delivery_profile'];
      $storage = $this->entityTypeManager->getStorage('profile');

      if (is_numeric($id)) {
        $profile = $storage->load($id);
      }
      else {
        $uid = $this->order->getCustomerId();

        $data = [
          'uid' => $uid,
          'type' => 'delivery',
          'is_default' => TRUE,
        ];

        $profile = $storage->create($data);
      }

      $fields = [
        'field_street',
        'field_flat',
        'field_floor',
        'field_entrance',
        'field_intercom',
      ];

      foreach ($fields as $field) {
        if (!empty($input[$field][0]['value'])) {
          $profile->set($field, $input[$field][0]['value']);
        }

        $profile->setDefault(1);
        $profile->save();
      }

      $this->order->set('field_delivery_address', $profile->id());
      $this->order->save();
    }
  }

  public function loadUserDeliveryProfiles() {
    $order = $this->order;
    $uid = $order->getCustomerId();

    $storage = $this->entityTypeManager->getStorage('profile');
    $ids = $storage
      ->getQuery()
      ->accessCheck()
      ->condition('uid', $uid)
      ->condition('type', 'delivery')
      ->sort('changed', 'DESC')
      ->execute();

    if (!empty($ids)) {
      return $storage->loadMultiple($ids);
    }
    else {
      return [];
    }
  }

  public function getDeliveryProfile() {
    $order = $this->order;
    $uid = $order->getCustomerId();

    $storage = $this->entityTypeManager->getStorage('profile');

    if (!$order->get('field_delivery_address')->isEmpty()) {
      $profile = $storage->load($order->field_delivery_address->target_id);
    }
    else {
      $result = $storage
        ->getQuery()
        ->accessCheck()
        ->condition('uid', $uid)
        ->condition('type', 'delivery')
        ->sort('changed', 'DESC')
        ->execute();

      if (!empty($result)) {
        $id = reset($result);
        $profile = $storage->load($id);
      }
      else {
        $data = [
          'uid' => $uid,
          'type' => 'delivery',
          'is_default' => TRUE,
        ];
        $profile = $storage->create($data);
      }
    }

    return $profile;
  }


}
