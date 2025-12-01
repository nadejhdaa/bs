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

      $profile = \Drupal::entityTypeManager()->getStorage('profile')->loadByUser($user, 'customer');
      if (!empty($profile)) {
        if (!$profile->get('field_surname')->isEmpty()) {
          $fio[] = $profile->field_surname->value;
        }

        if (!$profile->get('field_name')->isEmpty()) {
          $fio[] = $profile->field_name->value;
        }

        if (!$profile->get('field_name')->isEmpty()) {
          $fio[] = $profile->field_patronymic->value;
        }

        if (!empty($fio)) {
          $variables['title'] = implode(' ', $fio);
        }
      }

      $url = Url::fromRoute('user.logout');
      $link = Link::fromTextAndUrl(t('Logout'), $url, ['attributes' => ['class' => ['s']]])->toRenderable();

      $variables['link'] = $link;

      $variables['title_prefix'] = Markup::create('<div class="d-flex justify-content-between align-items-center">');
      $variables['title_suffix'] = Markup::create('</div>');
    }
  }
}
