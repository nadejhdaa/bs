<?php


/**
 * Implement hook_preprocess_paragraph_hook().
 */
function kc_preprocess_profile__customer__default(&$variables) {
  $variables['content']['field_surname']['#prefix'] = '<div class="d-flex flex-wrap gap-2">';
  $variables['content']['field_patronymic']['#suffix'] = '</div>';
}
