<?php

declare(strict_types=1);

namespace Drupal\kc_commerce_content;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageDefault;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\commerce_price\CurrencyFormatter;
use Drupal\commerce_price\Repository\CurrencyRepositoryInterface;

/**
 * @todo Add class description.
 */
final class ProductData {

  /**
   * Constructs a ProductData object.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly CurrencyRepositoryInterface $commercePriceCurrencyRepository,
    private readonly CurrencyFormatter $commercePriceCurrencyFormatter,
    private readonly LanguageDefault $languageDefault,
    private readonly LanguageManagerInterface $languageManager,
  ) {}

  /**
   * Get taxonomy term with product type name.
   *
   * @param object $product
   *   Product entity.
   *
   * @return string
   *   The taxonomy term name.
   */
  public function getTypeString($product) {
    switch ($product->bundle()) {
      case 'home':
        return $product->field_type_home->entity->getName();
        break;

      case 'makeup':
        return $product->field_type_makeup->entity->getName();
        break;

      case 'kids':
        return $product->field_type_kids->entity->getName();
        break;

      case 'apteka':
        return $product->field_type_apteka->entity->getName();
        break;

      case 'food':
        return $product->field_type_food->entity->getName();
        break;

      case 'hair':
        return $product->field_hair_type->entity->getName();
        break;

      case 'oris':
        return $product->field_type_oris->entity->getName();
        break;

      case 'body':
        if (!empty($product->field_type_body->entity)) {
          $variables['type'] = $product->field_type_body->entity->getName();
        }

        break;

      case 'face':
        if (!$product->get('field_type')->isEmpty()) {
          return $product->field_type->entity->getName();
        }
        return '';

        break;
    }

    return '';
  }

}
