<?php

use Drupal\Component\Utility\Color;

/**
 * Implement hook_preprocess_paragraph_hook().
 */
function kc_preprocess_paragraph__slider_item(&$variables) {
  $paragraph = $variables['paragraph'];

  $variables['bg_color'] = '';
  if ($paragraph->hasField('field_bg') && !$paragraph->get('field_bg')->isEmpty()) {
    $bg_color = $paragraph->field_bg->color;
    $styles[] = "background: $bg_color";
    //
    $rgb_array = Color::hexToRgb($bg_color);

    if ($paragraph->hasField('field_opacity') && !$paragraph->get('field_opacity')->isEmpty()) {
      $rgb_array[] = $paragraph->field_opacity->value;
    }
    else {
      $rgb_array[] = '0.5';
    }

    $variables['bg_color'] = 'rgb(' . implode(',', $rgb_array) . ')';
  }
}

 /**
  * Implement hook_preprocess_paragraph_hook().
  */
function kc_preprocess_paragraph__action(&$variables) {
  $paragraph = $variables['paragraph'];
  $styles = [];

  if (!$paragraph->get('field_bg_color')->isEmpty()) {
    $bg_color_items = $paragraph->get('field_bg_color')->getValue();
    if (count($bg_color_items) > 1) {
      $styles[] = 'background-image: linear-gradient(to top right, ' . $bg_color_items[0]['color'] . ', ' . $bg_color_items[1]['color'] . ')';
    }
    else {
      $styles[] = 'background-color: ' . $bg_color_items[0]['color'];
    }
  }

  if (!$paragraph->get('field_color')->isEmpty()) {
    $styles[] = 'color: ' . $paragraph->field_color->color;
  }

  if (!empty($styles)) {
    $style = implode(';', $styles);
    $variables['attributes']['style'] = $style;
  }
}
