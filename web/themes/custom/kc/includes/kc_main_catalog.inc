<?php

use Drupal\Core\Render\Element;

/**
 * Implement hook_preprocess_menu_link_content().
 */
function kc_preprocess_kc_main_catalog(&$variables) {
  $index = 1;
  $item_number = 0;
  $main_id = 'mainCatalog';

  foreach ($variables['content']['main_body'] as $key => $item) {
    $id = 'mainCatalogItem' . $index;
    $heading_id = 'mainCatalogHeading' . $index;
    $aria_id = 'mainCatalogCollapse' . $index;

    $prefix = '<div id="' . $aria_id . '" class="main-catalog__body-item accordion-collapse collapse' . ($index == 1 ? ' show': ''). '" aria-labelledby="' . $heading_id . '" data-bs-parent="#' . $main_id . '">';
    $variables['content']['main_body'][$key]['#prefix'] = $prefix;
    $variables['content']['main_body'][$key]['#suffix'] = '</div>';

    $main_item = reset($variables['content']['main_items']);

    if (!empty($main_item['content'][0][$item_number]['#menu_link_content'])) {
      $variables['content']['main_body'][$key]['content']['content']['#title_link'] = [
        'title' => $main_item['content'][0][$item_number]['#menu_link_content']->getTitle(),
        'uri' => $main_item['content'][0][$item_number]['#menu_link_content']->getUrlObject()->toString(),
      ];
    }


    $index ++;
    $item_number ++;
  }
}
