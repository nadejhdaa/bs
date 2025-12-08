<?php

use Drupal\commerce_price\Price;
use Drupal\Core\Render\Markup;

/**
 * Implement hook_preprocess_commerce_product__view_mode().
 */
function kc_preprocess_commerce_product__product_teaser(&$variables) {
  $product = $variables['product_entity'];
  $variations = $product->getVariations();

  $product_data = \Drupal::service('kc_commerce_content.product_data');

  $variables['type'] = $product_data->getTypeString($product);

  $view_mode = 'product_teaser';
  $variables['flag_link'] = kc_build_flag_link($product->id(), $view_mode);
}

/**
 * Implement hook_preprocess_commerce_product__view_mode().
 */
function kc_preprocess_commerce_product__full(&$variables) {
  $product = $variables['product_entity'];
  $variations = $product->getVariations();

  $product_data = \Drupal::service('kc_commerce_content.product_data');

  $variables['type'] = $product_data->getTypeString($product);

  $view_mode = 'product_full';
  $variables['flag_link'] = kc_build_flag_link($product->id(), $view_mode);

  $variables['comment_count'] = 0;
  if ($product->hasField('field_reviews') && !$product->get('field_reviews')->isEmpty()) {
    $field_reviews_value = $product->get('field_reviews')->getValue();
    $variables['comment_count'] = reset($field_reviews_value)['comment_count'];
  }

  $tabs = [];

  if (!$product->body->isEmpty()) {
    $tabs['body'] = [
      'title' => t('Description'),
      'value' => Markup::create($product->body->value),
    ];
  }

  if (!$product->field_composition->isEmpty()) {

    $tabs['composition'] = [
      'title' => t('Composition'),
      'value' => Markup::create($product->field_composition->value),
    ];
  }

  if (!$product->field_how_to_use->isEmpty()) {
    $tabs['how_to_use'] = [
      'title' => t('How to use'),
      'value' => Markup::create($product->field_how_to_use->value),
    ];
  }

  if (!empty($variables['product']['field_brand'])) {
    $tabs['brand'] = [
      'title' => t('Brand'),
      'value' => $variables['product']['field_brand'],
    ];
  }

  $variables['tabs'] = $tabs;

  $variables['title'] = $product->getTitle();

  $product_variations = $product->getVariations();

  if (!empty($product_variations)) {
    $default_product_variation = reset($product_variations);
    $variables['title'] .= ' ' . $default_product_variation->getTitle();
  }
}

/**
 * Implement hook_preprocess_commerce_product_variation__view_mode().
 */
function kc_preprocess_commerce_product_variation__teaser(&$variables) {
  $product_data = \Drupal::service('kc_commerce_content.product_data');

  $product_variation = $variables['product_variation_entity'];
  $product = $product_variation->product_id->entity;
  $variables['product'] = $product;
  $variables['type'] = $product_data->getTypeString($product);
}

/**
 * Implement hook_preprocess_commerce_order__default__lk_short().
 */
function kc_preprocess_commerce_order__lk_short(&$variables) {
  $order = $variables['elements']['#commerce_order'];

  $order_state = $order->getState();
  $translated_label = $order_state->getLabel();
}
