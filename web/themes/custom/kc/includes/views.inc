<?php

use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Implement hook_preprocess_views().
 */
function kc_preprocess_views_view(&$variables) {
  $view_id = $variables['id'];
  $display_id = $variables['display_id'];

  $views_ids = [
    'brands_listing',
    'products_marketing',
    'discount_page',
  ];

  if (in_array($view_id, $views_ids)) {
    $variables['content_class'] = 'row';
  }
}

/**
 * Implement hook_preprocess_views_view_unformatted().
 */
function kc_preprocess_views_view_unformatted__brands_on_front(&$variables) {
  $url = Url::fromUserInput('/brands');
  $title = t('Brands');
  $variables['heading'] = Link::fromTextAndUrl($title, $url)->toString();
}
