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
  $current_path = \Drupal::service('path.current')->getPath();

  $full_width_pages = [
    '/catalog',
    '/front',
  ];

  if (!in_array($current_path, $full_width_pages)) {
    $variables['attributes']['class'][] = 'container';
  }
}

/**
 * Implement kc_preprocess_layout().
 */
function kc_preprocess_layout__twocol_section(&$variables) {
  $current_path = \Drupal::service('path.current')->getPath();

  if (str_contains($current_path, '/personal')) {
    $user = \Drupal::currentUser()->getAccount();

    $variables['attributes']['class'] = [
      'kc-personal-account__cols',
      'row',
    ];
    $variables['region_attributes']['first']['class'] = [
      'kc-personal-account__first',
      'col-md-3',
    ];
    $variables['region_attributes']['second']['class'] = [
      'kc-personal-account__second',
      'col-md-9',
    ];
  }
}

/**
 * Implement kc_preprocess_page().
 */
function kc_preprocess_page(&$variables) {
  $variables['content_classes_theme'] = 'kc-page__content';
  $current_path = \Drupal::service('path.current')->getPath();

  if (str_contains($current_path, '/user')) {
    $variables['content_classes_theme'] .= ' col-md-4 col-sm-12 mx-auto my-5 kc-page__content--user';
  }
}

/**
 * Implement kc_preprocess_html().
 */
function kc_preprocess_html(&$variables) {
  $variables['body_classes'][] = 'kc-page';
  $current_path = \Drupal::service('path.current')->getPath();

  if (str_contains($current_path, '/user')) {
    $variables['body_classes'][] = 'kc-page--user';
  }
}
