<?php

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Render\Markup;

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

/**
 * Implements hook_theme().
 */
function kc_preprocess_page_title(&$variables) {
  $current_path = \Drupal::service('path.current')->getPath();

  if (str_contains($current_path, '/personal')) {
    $user = \Drupal::currentUser()->getAccount();

    if ($user->isAuthenticated()) {
      $variables['attributes']['class'][] = 'd-flex';
      $variables['attributes']['class'][] = 'justify-content-between';
      $variables['attributes']['class'][] = 'align-items-center';

      $url = Url::fromRoute('user.logout');

      $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M247.1 233.4c12.5 12.5 12.5 32.8 0 45.3l-160 160c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3L179.2 256 41.9 118.6c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l160 160z"/></svg>';
      $title = Markup::create('<span>' . t('Logout') . '</span>' . $svg);

      // $link = Link::fromTextAndUrl($title, $url)->toRenderable();
      // $link['#attributes'] = [
      //   'class' => [
      //     'kc-link',
      //     'kc-link--with-icon',
      //   ],
      // ];
      //
      // $variables['link'] = $link;

      $variables['title_prefix'] = Markup::create('<div class="d-flex justify-content-between align-items-center">');
      $variables['title_suffix'] = Markup::create('</div>');
    }
  }
}
