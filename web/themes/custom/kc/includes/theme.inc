<?php

use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Implements hook_theme().
 */
function kc_theme($existing, $type, $theme, $path) {
  return [
    'brand_promo_info' => [
      'variables' => [
        'img' => '',
        'link' => '',
        'description' => '',
        'items' => [],
      ],
      'path' => $path . '/templates/theme',
    ],
  ];
}
