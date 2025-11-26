<?php

use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Implement hook_preprocess_field().
 */
function kc_preprocess_field__block_content__field_slider_items__main_slider(&$variables) {
  foreach ($variables['items'] as $key => $item) {
    $paragraph = $item['content']['#paragraph'];
    $titles[] = !$paragraph->get('field_title')->isEmpty() ? $paragraph->field_title->value : '';
  }
  $variables['#attached']['drupalSettings']['titles'] = $titles;
}

/**
 * Implement hook_preprocess_field().
 */
function kc_preprocess_field__field_img(&$variables) {
  $element = $variables['element'];

  $view_modes = [
    'product_teaser',
    'product_inline_small',
  ];

  if ($element['#entity_type'] == 'commerce_product' && in_array($element['#view_mode'], $view_modes)) {
    $variables['items'] = array_slice($variables['items'], 0, 1);
  }
}

/**
 * Implement hook_preprocess_field().
 */
function kc_preprocess_field__block_content__field_items__recomend(&$variables) {
  $variables['attributes']['class'] = ['row'];
  foreach ($variables['items'] as $key => $item) {
    $variables['items'][$key]['attributes']['class'] = ['col', 'col-md-4', 'col-sm-6', 'col-xs-12', 'mb-5', 'pb-3'];
  }
}

/**
 * Implement hook_preprocess_field().
 */
function kc_preprocess_field__paragraph__field_link__action(&$variables) {
  $variables['attributes']['class'] = ['kc-action-card__link-wrapper'];

  foreach (Element::children($variables['items']) as $key) {
    $variables['items'][$key]['content']['#attributes'] = [
      'class' => [
        'kc-action-card__link',
      ],
    ];
  }
}

/**
 * Implement hook_preprocess_field().
 */
function kc_preprocess_field__block_content(&$variables) {
  $block = $variables['element']['#object'];
  $bundle = $block->bundle();
  $field_name = $variables['field_name'];

// field_promo_items__promo_block
  switch ($bundle) {
    case 'promo_block':
    case 'new_front_block':
    case 'actions':
    case 'hits_front_block':
    case 'discount_products':
    case 'points':
    case 'recomend':

      $field_names = [
        'field_promo_items',
        'field_products_new',
        'field_actions',
        'field_products_hits',
        'field_product_discounted',
        'field_points',
        'field_items',
      ];

      if (in_array($field_name, $field_names)) {
        if (!$block->get('field_heading')->isEmpty()) {
          $variables['heading'] = $block->field_heading->value;
        }

        elseif(!$block->get('field_link')->isEmpty()) {
          $url = $block->field_link->first()->getUrl();
          $link = Link::fromTextAndUrl($block->field_link->first()->getTitle(), $url)->toRenderable();
          $link['#attributes'] = [
            'class' => [
              'kc-slider-items__heading-link',
            ],
          ];

          $variables['heading'] = $link;
        }
      }

      break;
  }
}

/**
 * Implement hook_theme_suggestions_taxonomy_term_alter().
 */
function kc_theme_suggestions_field_alter(array &$suggestions, array $variables) {
  $element = $variables['element'];

  // Check if the field is associated with an entity.
  if (isset($element['#object']) && method_exists($element['#object'], 'getEntityTypeId') && $element['#object']->getEntityTypeId() == 'commerce_product') {
    $field_name = $element['#field_name'];
    $entity_type_id = $element['#object']->getEntityTypeId();

    $suggestions[] = 'field__' . $field_name . '__' . $entity_type_id;

    if (!empty($element['#view_mode'])) {
      $suggestions[] = 'field__' . $field_name . '__' . $entity_type_id . '__' . $element['#view_mode'];
    }
  }
}

/**
 * Implement hook_preprocess_hook().
 */
function kc_preprocess_field__field_img__commerce_product__full(&$variables) {
  $element = $variables['element'];

  foreach ($variables['items'] as $key => $item) {
    if (!empty($item['content']['#item']->entity)) {
      $variables['thumbnails'][] = $item['content']['#item']->entity->field_media_image->entity->getFileUri();
    }
  }
}

/**
 * Implement hook_preprocess_hook().
 */
function kc_preprocess_field__field_skin_type__commerce_product__full(&$variables) {
  $variables['attributes']['class'][] = 'kc-field-display-inline';
  $variables['attributes']['class'][] = 'd-flex';

  foreach (Element::children($variables['items']) as $key) {
    $variables['items'][$key]['content']['#attributes'] = [
      'class' => [
        'kc-link--underlined',
      ],
    ];
  }
}

/**
 * Implement hook_preprocess_hook().
 */
 function kc_preprocess_field__field_components__commerce_product__full(&$variables) {
   $variables['attributes']['class'][] = 'kc-field-display-inline';
   $variables['attributes']['class'][] = 'd-flex';
   $variables['attributes']['class'][] = 'gap-5';

   foreach (Element::children($variables['items']) as $key) {
     $variables['items'][$key]['content']['#attributes'] = [
       'class' => [
         'kc-link--underlined',
       ],
     ];
   }
 }
