<?php

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Implements hook_form_alter().
 */
function kc_form_search_block_form_alter(array &$form, FormStateInterface $form_state) {
  $form['keys']['#attributes']['placeholder'] = t('Search');
  $form['actions']['submit']['#prefix'] = '<div class="kc-submit kc-submit--search">';
  $form['actions']['submit']['#suffix'] = '</div>';
}

function kc_form_search_form_alter(array &$form, FormStateInterface $form_state) {
  unset($form['help_link']);
  $form['#attributes']['class'][] = 'kc-search-form';
  $form['basic']['keys']['#title_display'] = 'none';
  $form['basic']['keys']['#attributes']['placeholder'] = t('Search');
  $form['basic']['actions']['submit']['#prefix'] = '<div class="kc-submit kc-submit--search">';
  $form['basic']['actions']['submit']['#suffix'] = '</div>';
}

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
function kc_form_user_pass_alter(array &$form, FormStateInterface $form_state, $form_id) {
  $form['name']['#title_display'] = 'none';
  $form['name']['#placeholder'] = t('E-mail');
  $form['actions']['submit']['#attributes'] = [
    'class' => [
      'rounded-pill',
      'w-100',
      'btn-secondary',
    ],
  ];
}
