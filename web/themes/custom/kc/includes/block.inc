<?php

use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Implement hook_preprocess_block().
 */
function kc_preprocess_block__block_content__type__main_catalog(&$variables) {
  $variables['attributes']['class'][] = 'main-catalog__wrapper';
}

/**
 * Implement hook_preprocess_block().
 */
function kc_preprocess_block__block_content(&$variables) {
  if (!empty($variables['content']['#block_content'])) {
    $block_content = $variables['content']['#block_content'];
    $bundle = $block_content->bundle();

    switch ($bundle) {
      case 'points':
      case 'recomend':

        if (!$block_content->get('field_heading')->isEmpty()) {
          if ($block_content->get('field_link')->isEmpty()) {
            $variables['label'] = $block_content->field_heading->value;
          }
          else {
            $uri = $block_content->get('field_link')->get(0)->getValue()['uri'];
            $url = Url::fromUri($uri);
            $variables['label'] = Link::fromTextAndUrl($block_content->field_heading->value, $url)->toString();
          }
        }
        break;

      case 'main_catalog':
        $variables['attributes']['class'][] = 'main-catalog__wrapper';
        break;
    }
  }
}
