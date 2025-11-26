<?php

/**
 * Build flag link.
 */
function kc_build_flag_link($product_id, $view_mode = '') {
  $flag_link_service = \Drupal::service('flag.link_builder');
  $flag_link = $flag_link_service->build('commerce_product', $product_id, 'favorite');
  $flag_link['#attributes']['class'][] = $view_mode == 'product_full' ? 'kc-product-full__favorite-link' :'kc-product-card__favorite-link';
  $flag_link['#view_mode'] = $view_mode;

  return $flag_link;
}
