<?php

/**
 * Implements hook_preprocess_hook().
 */
function kc_preprocess_commerce_product_variation(&$variables) {
  $entity = $variables['product_variation_entity'];
  $content = $variables['product_variation'];

  if (!empty($content['field_color'])) {
    if ($entity->hasField('field_color') && !$entity->get('field_color')->isEmpty()) {
      $variables['color'] = [
        'label' => $content['field_color']['#title'],
        'color' => $entity->field_color->color,
      ];
    }

    if ($entity->hasField('field_color_name') && !$entity->get('field_color_name')->isEmpty()) {
      $variables['color']['color_name'] = $entity->field_color_name->value;
    }
  }

  if (!empty($content['field_sizes'])) {
    $variables['sizes'] = $content['field_sizes'];
  }
}
