<?php

declare(strict_types=1);

namespace Drupal\kc_catalog;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\menu_link_content\Entity\MenuLinkContent;

/**
 * @todo Add class description.
 */
final class CatalogMenu {

  /**
   * Constructs a CatalogMenu object.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * @todo Add method description.
   */
  public function updateMenuLinks() {
    $active_tids = $this->getActiveTids();
    $active_tids = array_keys($active_tids);

    $menu_name = 'main-catalog';
    $parameters = new MenuTreeParameters();

    // Optionally limit to enabled items.
    $parameters->onlyEnabledLinks();

    // Optionally set active trail.
    $menu_active_trail = \Drupal::service('menu.active_trail')->getActiveTrailIds($menu_name);
    $parameters->setActiveTrail($menu_active_trail);

    // Load the tree.
    $tree = \Drupal::menuTree()->load($menu_name, $parameters);

    foreach ($tree as $key => $element) {
      if (!empty($element->subtree)) {
        $link = $element->link;
        $this->checkSubtree($element->subtree, $active_tids);
      }
    }
  }

  public function checkSubtree($subtree, $active_tids) {
    $menu_link_manager = \Drupal::service('plugin.manager.menu.link');

    foreach ($subtree as $element) {
      $menu_link_content = $element->link->getEntity();
      $menu_name = $menu_link_content->getMenuName();

      if (!$menu_link_content->get('field_vocabulary')->isEmpty()) {

        $uuid = $menu_link_content->getPluginId();
        $vid = $menu_link_content->field_vocabulary->getString();
        $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree($vid, 0, NULL, TRUE);


        foreach ($terms as $term) {
          $tid = $term->id();


          $result = $this->entityTypeManager->getStorage('menu_link_content')->loadByProperties([
            'link.uri' => "entity:taxonomy_term/$tid",
          ]);

          if (!empty($result)) {
            $menu_link_content = reset($result);

            if (!in_array($tid, $active_tids)) {
              $menu_link_content->setUnpublished();
            }
            else {
              $menu_link_content->setPublished();
            }

            $menu_link_content->set('parent', $uuid);
            $menu_link_content->save();
          }
          else {
            if (in_array($tid, $active_tids)) {
              $menu_link_content = MenuLinkContent::create([
                'title' => $term->getName(),
                'link' => ['uri' => 'entity:taxonomy_term/' . $tid],
                'menu_name' => $menu_name,
                'weight' => $term->getWeight(),
                'parent' => $uuid,
              ]);

              $menu_link_content->save();
            }
          }
        }
      }
    }
  }

  public function getActiveTids($product_type = NULL) {
    $tids = [];
    $fields_info = $this->getFieldsInfo();

    if (!empty($product_type)) {
      $products = $this->entityTypeManager->getStorage('commerce_product')->loadByProperties(['type' => $product_type]);
    }
    else {
      $products = $this->entityTypeManager->getStorage('commerce_product')->loadMultiple();
    }

    foreach ($products as $product) {
      $bundle = $product->bundle();

      if (!empty($fields_info[$bundle])) {
        $fields = $fields_info[$bundle];

        foreach ($fields as $field) {
          if (!$product->get($field['machine_name'])->isEmpty()) {
            foreach ($product->get($field['machine_name'])->getValue() as $key => $value) {

              $tid = $value['target_id'];
              $tids[$tid] = [
                'tid' => $tid,
                'vid' => $field['vid'],
              ];
            }
          }
        }
      }
    }

    return $tids;
  }

  public function getFieldsInfo() {
    $result = $this->entityTypeManager->getStorage('field_config')->loadByProperties([
      'field_type' => 'entity_reference',
      'entity_type' => 'commerce_product',
    ]);

    foreach ($result as $field) {
      if ($field->getFieldStorageDefinition()->getSettings()['target_type'] == 'taxonomy_term') {
        $fields_info[$field->getTargetBundle()][] = [
          'machine_name' => $field->getName(),
          'label' => $field->getLabel(),
          'vid' => reset($field->getSettings('handler')['handler_settings']['target_bundles']),
        ];
      }
    }

    return $fields_info;
  }

}
