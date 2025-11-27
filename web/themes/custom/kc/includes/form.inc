<?php

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Implements hook_form_alter().
 */
function kc_form_comment_review_form_alter(array &$form, FormStateInterface $form_state, $form_id) {
  if (!empty($form_state->getFormObject()->getEntity()->getCommentedEntityId())) {
    $product_id = $form_state->getFormObject()->getEntity()->getCommentedEntityId();

    $form['field_target_product']['widget'][0]['target_id']['#type'] = 'hidden';
    $form['field_target_product']['widget'][0]['target_id']['#default_value'] = $product_id;
  }

  $form['actions']['submit']['#value'] = t('Send comment');
  $form['actions']['submit']['#attributes']['class'][] = 'rounded-pill';

  $form['field_review_name']['widget'][0]['value']['#title_display'] = 'hidden';
  $form['field_review_name']['widget'][0]['value']['#placeholder'] = t('Your name');

  $form['field_comment']['widget'][0]['value']['#title_display'] = 'hidden';
  $form['field_comment']['widget'][0]['value']['#placeholder'] = t('Comment');

  $form['field_rating']['widget'][0]['rating']['#title_display'] = 'hidden';
}

/**
 * Implements hook_form_alter().
 */
function kc_form_user_login_form_alter(array &$form, FormStateInterface $form_state, $form_id) {
  $form['name']['#title_display'] = 'none';
  unset($form['name']['#description']);
  $form['name']['#placeholder'] = t('E-mail');
  $form['pass']['#title_display'] = 'none';
  $form['pass']['#placeholder'] = t('Password');

  $form['actions']['submit']['#attributes'] = [
    'class' => [
      'rounded-pill',
      'w-100',
    ],
  ];
}

/**
 * Implements hook_form_alter().
 */
function kc_form_user_register_form_alter(array &$form, FormStateInterface $form_state, $form_id) {
  $form['account']['mail']['#title_display'] = 'none';
  $form['account']['mail']['#placeholder'] = t('E-mail');
  unset($form['account']['mail']['#description']);

  $form['account']['pass']['#title_display'] = 'none';
  $form['account']['pass']['#placeholder'] = t('Set password for a new account');
  unset($form['account']['pass']['#description']);

  $form['actions']['submit']['#attributes'] = [
    'class' => [
      'rounded-pill',
      'w-100',
      'btn-secondary',
    ],
  ];
}

/**
 * Implements hook_form_alter().
 */
function kc_form_commerce_checkout_flow_multistep_default_alter(array &$form, FormStateInterface $form_state, $form_id) {

  $form['payment_information']['billing_information']['field_flat']['#prefix'] = '<div class="row">';
  $form['payment_information']['billing_information']['field_entrance']['#suffix'] = '</div>';

  $form['payment_information']['billing_information']['field_flat']['#attributes']['class'][] = 'col-md-3';
  $form['payment_information']['billing_information']['field_flat']['widget'][0]['value']['#title_display'] = 'hidden';

  $form['payment_information']['billing_information']['field_intercom']['#attributes']['class'][] = 'col-md-3';
  $form['payment_information']['billing_information']['field_intercom']['widget'][0]['value']['#title_display'] = 'hidden';

  $form['payment_information']['billing_information']['field_floor']['#attributes']['class'][] = 'col-md-3';
  $form['payment_information']['billing_information']['field_floor']['widget'][0]['value']['#title_display'] = 'hidden';

  $form['payment_information']['billing_information']['field_entrance']['#attributes']['class'][] = 'col-md-3';
  $form['payment_information']['billing_information']['field_entrance']['widget'][0]['value']['#title_display'] = 'hidden';

  $form['payment_information']['billing_information']['field_street']['widget'][0]['value']['#title_display'] = 'hidden';

  $form['payment_information']['billing_information']['field_name']['widget'][0]['value']['#title_display'] = 'hidden';

  $form['payment_information']['billing_information']['field_patronymic']['widget'][0]['value']['#title_display'] = 'hidden';

  $form['payment_information']['billing_information']['field_surname']['widget'][0]['value']['#title_display'] = 'hidden';

  $form['payment_information']['billing_information']['field_email']['widget'][0]['value']['#title_display'] = 'hidden';

  $form['customer_comments']['comments']['#attributes']['rows'] = 3;

  $form['actions']['next']['#value'] = t('Continue');
  $form['actions']['next']['#attributes']['class'][] = 'w-100';
  $form['actions']['next']['#attributes']['class'][] = 'rounded-pill';

  $form['sidebar']['coupon_redemption']['form']['apply']['#attributes']['class'][] = 'w-100';
  $form['sidebar']['coupon_redemption']['form']['apply']['#attributes']['class'][] = 'rounded-pill';
  $form['sidebar']['coupon_redemption']['form']['apply']['#attributes']['class'][] = 'btn-light';

  $current_user = \Drupal::currentUser();
  $entity_manager = \Drupal::entityTypeManager();

  $profile = $entity_manager->getStorage('profile')->loadByProperties([
    'uid' => $current_user->id(),
  ]);

  if (!empty($form_state->getBuildInfo()['callback_object'])) {
    $order = $form_state->getFormObject()->getOrder();
    $step_id = $order->checkout_step->value;
    $prev = $form_state->getFormObject()->getPreviousStepId($step_id);

    $url = Url::fromRoute('commerce_checkout.form', ['commerce_order' => $order->id(), 'step' => $prev]);

    $svg = '<svg width="24" height="14" viewBox="0 0 24 14" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M3.60276e-09 6.99071C0.00201412 7.24973 0.104558 7.49785 0.286 7.68271L0.292 7.69471L6.292 13.6947C6.4806 13.8769 6.7332 13.9777 6.9954 13.9754C7.2576 13.9731 7.50841 13.8679 7.69382 13.6825C7.87923 13.4971 7.9844 13.2463 7.98667 12.9841C7.98895 12.7219 7.88816 12.4693 7.706 12.2807L3.414 7.98671H23C23.2652 7.98671 23.5196 7.88135 23.7071 7.69382C23.8946 7.50628 24 7.25193 24 6.98671C24 6.7215 23.8946 6.46714 23.7071 6.27961C23.5196 6.09207 23.2652 5.98671 23 5.98671H3.414L7.706 1.69471C7.88816 1.50611 7.98895 1.25351 7.98667 0.991311C7.9844 0.729114 7.87923 0.478301 7.69382 0.292893C7.50841 0.107485 7.2576 0.00231622 6.9954 3.78026e-05C6.7332 -0.00224062 6.4806 0.0985537 6.292 0.280712L0.292 6.28071L0.286 6.29071C0.196977 6.38091 0.126334 6.48756 0.0780001 6.60471C0.0265202 6.72549 -1.12881e-05 6.85542 3.60276e-09 6.98671V6.99071Z" fill="black"/>
    </svg>';

    $form['back'] = [
      '#type' => 'link',
      '#title' => Markup::create($svg . '<span>' .  t('Go back') . '</span>'),
      '#url' => $url,
      '#attributes' => [
        'class' => [
          'kc-checkout__back-link',
        ],
      ],
    ];
  }

  $form['#attributes']['class'][] = 'kc-checkout__form';

  if (!empty($form['actions']['next']['#suffix'])) {
    unset($form['actions']['next']['#suffix']);
  }

  if ($profile) {
    $profile = reset($profile);

    if (!$profile->get('field_name')->isEmpty()) {
      $form['payment_information']['billing_information']['field_name']['widget'][0]['value']['#default_value'] = $profile->field_name->value;
    }

    if (!$profile->get('field_patronymic')->isEmpty()) {
      $form['payment_information']['billing_information']['field_patronymic']['widget'][0]['value']['#default_value'] = $profile->field_patronymic->value;
    }

    if (!$profile->get('field_surname')->isEmpty()) {
      $form['payment_information']['billing_information']['field_surname']['widget'][0]['value']['#default_value'] = $profile->field_surname->value;
    }

    if (!$profile->get('field_email')->isEmpty()) {
      $form['payment_information']['billing_information']['field_email']['widget'][0]['value']['#default_value'] = $profile->field_email->value;
    }

    if (!$profile->get('field_street')->isEmpty()) {
      $form['payment_information']['billing_information']['field_street']['widget'][0]['value']['#default_value'] = $profile->field_street->value;
    }

    if (!$profile->get('field_flat')->isEmpty()) {
      $form['payment_information']['billing_information']['field_flat']['widget'][0]['value']['#default_value'] = $profile->field_flat->value;
    }

    if (!$profile->get('field_entrance')->isEmpty()) {
      $form['payment_information']['billing_information']['field_entrance']['widget'][0]['value']['#default_value'] = $profile->field_entrance->value;
    }

    if (!$profile->get('field_floor')->isEmpty()) {
      $form['payment_information']['billing_information']['field_floor']['widget'][0]['value']['#default_value'] = $profile->field_floor->value;
    }

    if (!$profile->get('field_intercom')->isEmpty()) {
      $form['payment_information']['billing_information']['field_intercom']['widget'][0]['value']['#default_value'] = $profile->field_intercom->value;
    }
  }
}
