<?php

declare(strict_types=1);

namespace Drupal\kc_catalog\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;


 /**
  * Provides a 'My Custom Form Block'.
  *
  * @Block(
  *   id = "main_catalog_body_brands_form_block",
  *   admin_label = @Translation("Brands Form Block"),
  *   category = @Translation("Custom")
  * )
  */
class MainCatalogBodyBrandsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  const VID = 'brands';

  /**
   * The form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Constructs the plugin instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $terms_data = $this->getTermsData();

    $build['content'] = [
      '#theme' => 'main_catalog_brand_links',
      '#terms_data' => $terms_data,
    ];

    return $build;
  }

  public function getTermsData($string = '') {
    $products = $this->entityTypeManager->getStorage('commerce_product')->loadByProperties(['status' => 1]);

    foreach ($products as $product) {
      if (!$product->get('field_brand')->isEmpty()) {
        $tids[] = $product->field_brand->target_id;
      }
    }
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
      'vid' => self::VID,
      'tid' => $tids
    ]);

    if (!empty($string)) {
      $search = strtolower($string);
    }

    foreach ($terms as $term) {
      $term_name = $term->getName();
      $tid = $term->id();

      if (!empty($search)) {
        if (!str_contains(strtolower($term_name), $search)) {
          continue;
        }
      }


      $first_letter = substr($term_name, 0, 1);
      if (is_numeric($first_letter)) {
        $first_letter = '0..9';
      }
      else {
        $first_letter = strtoupper($first_letter);
      }

      $term_data['alphabetical'][$first_letter][$tid] = $term_name;

      if (!$term->get('field_brand_type')->isEmpty()) {
        $term_data['popular'][$tid] = $term_name;
      }
    }

    ksort($term_data['alphabetical']);

    return $term_data;
  }

}
