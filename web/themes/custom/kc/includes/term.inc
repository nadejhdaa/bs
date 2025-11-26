<?php

/**
 * Implement hook_theme_suggestions_taxonomy_term_alter().
 */
function kc_theme_suggestions_taxonomy_term_alter(array &$suggestions, array $variables) {
  if (!empty($variables['elements']['#view_mode'])) {
    $view_mode = $variables['elements']['#view_mode'];
    $term = $variables['elements']['#taxonomy_term'];
    $vocabulary = $term->bundle();

    $suggestions[] = 'taxonomy_term__' . $view_mode;
    $suggestions[] = 'taxonomy_term__' . $vocabulary . '__' . $view_mode;
  }
}
