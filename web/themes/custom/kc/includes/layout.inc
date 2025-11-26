<?php

use Drupal\Core\Render\Element;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\Core\Path\PathMatcherInterface;

/**
 * Implement kc_preprocess_layout().
 */
function kc_preprocess_section_slider(&$variables) {
  foreach (Element::children($variables['content']['main']) as $key) {
    $block = $variables['content']['main'][$key]['content']['#block_content'];

    $media = Media::load($block->field_img->target_id);
    $file = File::load($media->field_media_image->target_id);

    $brand_term = $block->field_brand->entity;
    $link = [
      'title' => $brand_term->getName(),
      'tid' => $brand_term->id(),
    ];

    $variables['content']['main'][$key]['content'] = [
      '#theme' => 'brand_promo_info',
      '#img' => $file->getFileUri(),
      '#link' => $link,
      '#description' => $block->body->value,
      '#items' => $variables['content']['main'][$key]['content']['field_products'],
    ];
  }
}

/**
 * Implement kc_preprocess_region().
 */
function kc_preprocess_region__content(&$variables) {
  $is_front = \Drupal::service('path.matcher')->isFrontPage();

  if (!$is_front) {
    $variables['attributes']['class'][] = 'container';
  }
}
