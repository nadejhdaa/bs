<?php

use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;

/**
 * @file
 * Theme and preprocess functions for menus.
 */

/**
 * Implement hook_preprocess_menu_link_content().
 */
function kc_preprocess_menu_link_content__main_catalog__default(&$variables) {
  $entity = $variables['menu_link_content'];

  if (!$entity->get('field_icon')->isEmpty() && $entity->hasField('field_icon')) {
    $media = Media::load($entity->field_icon->target_id);
    $file = File::load($media->field_media_image->target_id);

    $file_absolute_path = \Drupal::service('file_system')->realpath($file->getFileUri());
    $variables['content']['#icon_path'] = $file_absolute_path;
  }
}

/**
 * Implement hook_preprocess_menu_link_content().
 */
function kc_preprocess_menu__header_catalog(&$variables) {
  $variables['#attached']['library'][] = 'kc/kc-main-catalog';
}


/**
 * Implement hook_preprocess_menu_link_content().
 */
function kc_preprocess_menu__personal_account_menu(&$variables) {
  $variables['attributes']['class'][] = 'kc-perconal-account__menu';
  foreach ($variables['items'] as $key => $item) {
    $variables['items'][$key]['attributes']['class'] = 'kc-perconal-account__menu-item';
  }
}
