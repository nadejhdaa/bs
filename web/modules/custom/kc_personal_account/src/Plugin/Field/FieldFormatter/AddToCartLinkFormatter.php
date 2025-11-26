<?php

declare(strict_types=1);

namespace Drupal\kc_personal_account\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\kc_personal_account\AddToCartLinkBuilderInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'Add to cart link' formatter.
 */
#[FieldFormatter(
  id: 'kc_personal_account_add_to_cart_link',
  label: new TranslatableMarkup('Add to cart link'),
  field_types: ['entity_reference'],
)]
class AddToCartLinkFormatter extends FormatterBase {

  /**
   * The add to cart links builder.
   *
   * @var \Drupal\kc_personal_account\AddToCartLinkBuilderInterface
   */
  protected $addToCartLinkBuilder;

  /**
   * Constructs a StringFormatter instance.
   *
   * @param string $plugin_id
   *   The plugin ID for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\kc_personal_account\AddToCartLinkBuilderInterface $add_to_cart_links_builder
   *   The add to cart links builder.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    AddToCartLinkBuilderInterface $add_to_cart_links_builder
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->addToCartLinkBuilder = $add_to_cart_links_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('kc_personal_account.add_to_cart_link_builder'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings(): array {
    return [
      'display_variations' => TRUE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state): array {
    $form = parent::settingsForm($form, $form_state);
// dsm($form_state->getFormObject()->getEntity());
    $form['display_variations'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show a choice of variations'),
      '#default_value' => $this->getSetting('display_variations'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary(): array {
    $summary = [];

    if (!empty($this->getSetting('display_variations'))) {
      $summary[] = $this->t('Show a choice of variations');
    }
    else {
      $summary[] = $this->t('Do not show a choice of variations');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array {
    $display_variations = $this->getSetting('display_variations');

    $element = $this->addToCartLinkBuilder->build($items, $display_variations, $delta = 0);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $has_cart = \Drupal::moduleHandler()->moduleExists('commerce_cart');
    $entity_type = $field_definition->getTargetEntityTypeId();
    $field_name = $field_definition->getName();
    return $has_cart && $entity_type == 'commerce_product' && $field_name == 'variations';
  }

}
