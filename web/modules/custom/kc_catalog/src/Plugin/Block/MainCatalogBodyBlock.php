<?php

declare(strict_types=1);

namespace Drupal\kc_catalog\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a main catalog makup block.
 */
#[Block(
  id: 'kc_catalog_main_catalog_body_block',
  admin_label: new TranslatableMarkup('Main Catalog Body'),
  category: new TranslatableMarkup('Custom'),
)]
final class MainCatalogBodyBlock extends BlockBase implements ContainerFactoryPluginInterface {

  const VID = 'for_body';

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
  public function defaultConfiguration(): array {
    return [
      'example' => $this->t('Body!'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    $form['example'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Example'),
      '#default_value' => $this->configuration['example'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    $this->configuration['example'] = $form_state->getValue('example');
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $terms_data = $this->getSkincareTermsData();

    $build['content'] = [
      '#theme' => 'main_catalog_body_link',
      '#terms_data' => $terms_data,
    ];

    return $build;
  }

  public function getSkincareTermsData() {
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
      'vid' => self::VID,
    ]);

    foreach ($terms as $term) {
      $tid = $term->id();

      if (empty($term->parent->target_id)) {
        $weight = $term->getWeight();

        if (empty($term_data[$weight])) {
          $term_data[$weight] = [
            'parent' => [
              'tid' => $tid,
              'name' => $term->getName(),
            ],
          ];
        }
      }
      else {
        $parent_id = $term->parent->target_id;
        $parent = $terms[$parent_id];
        $weight = $parent->getWeight();

        $term_data[$weight]['children'][$tid] = [
          'tid' => $tid,
          'name' => $term->getName(),
        ];

        if (empty($term_data[$weight]['parent'])) {
          $term_data[$weight] = [
            'parent' => [
              'tid' => $parent_id,
              'name' => $parent->getName(),
            ],
          ];
        }
      }
    }

    ksort($term_data);

    return $term_data;
  }

}
