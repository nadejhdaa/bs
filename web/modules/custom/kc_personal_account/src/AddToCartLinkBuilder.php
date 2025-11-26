<?php

declare(strict_types=1);

namespace Drupal\kc_personal_account;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\commerce_cart\CartProviderInterface;
use Drupal\Core\Url;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Template\Attribute;

/**
 * @todo Add class description.
 */
final class AddToCartLinkBuilder implements AddToCartLinkBuilderInterface {

  use StringTranslationTrait;

  /**
   * Constructs an AddToCartLinkBuilder object.
   */
  public function __construct(
    private readonly RendererInterface $renderer,
    private readonly AccountProxyInterface $currentUser,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly CartProviderInterface $commerceCartCartProvider,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function build($items, $display_variations = TRUE, $delta = 0) {
    $data = [
      '#theme' => 'kc_product_cart_link',
      '#id' => '',
    ];

    if (!empty(count($items))) {
      $item = $items[$delta];

      $product = $item->getEntity();
      $product_id = $product->id();

      $product_variation_id = $item->target_id;
      $product_variation = $item->entity;

      $data['#id'] = $product_variation_id;

      $attributes = $this->buildAttributes($product_id, $product_variation_id);
      $data['#attributes'] = $attributes;

      // Build variations links.
      if ($display_variations) {
        if (count($items) > 1) {
          // Build product varitaions ids.
          foreach ($items as $i => $item) {
            $product_variation_ids[] = $item->entity->id();
          }

          $data['#variations'] = $this->buildVariationLinks($product_variation_ids, $product_id, $delta);
        }

        $data['#variation_info'] = $this->buildVariationInfo($item);
      }

      // Check variation in cart.
      $quantity = $this->isProductVariationInCart($product_variation_id);

      if ($quantity) {
        $cart_link_data = $this->buildVariationIsInTheCart($product_variation_id, $product_id, $quantity);
      }
      else {
        $cart_link_data = $this->buildVariationIsNotInTheCart($product_variation_id, $product_id);
      }

      $data['#add_to_cart'] = $cart_link_data;
    }

    $element[$delta] = $data;

    return $element;
  }

  public function buildVariationInfo($item) {
    $entity_id = 'commerce_product_variation';
    $item_id = $item->target_id;

    $entity = $this->entityTypeManager->getStorage($entity_id)->load($item_id);
    $view_mode = 'default';
    return $this->entityTypeManager->getViewBuilder($entity_id)->view($entity, $view_mode);
  }

  /**
   * Build cart link render array if product variation is in the cart.
   *
   * @param string $product_variation_id
   *   The product variation id.
   * @param string $product_id
   *   The product id.
   * @param int $quantity
   *   The product variation items in cart quantity.
   * @param int $order_id
   *   The comerce_order_item id.
   *
   * @return array
   *   The render array.
   */
  public function buildVariationIsInTheCart($product_variation_id, $product_id, $quantity, $order_id = NULL) {
    $attributes = $this->buildCartLinkAttributes($product_id, $product_variation_id, $order_id);

    $url_options = [
      'id' => $product_variation_id,
      'product' => $product_id,
      'type' => 'minus',
    ];

    if ($order_id) {
      $url_options['order_id'] = $order_id;
    }

    $minus = [
      '#type' => 'link',
      '#title' => '-',
      '#url' => Url::fromRoute('kc_personal_account.update_item_quantity_ajax_callback', $url_options),
      '#attributes' => [
        'class' => [
          'use-ajax',
          'kc-add-to-cart-link__quantity-link',
          'kc-add-to-cart-link__quantity-link--minus',
        ],
      ],
    ];

    $url_options['type'] = 'plus';

    $plus = [
      '#type' => 'link',
      '#title' => '+',
      '#url' => Url::fromRoute('kc_personal_account.update_item_quantity_ajax_callback', $url_options),
      '#attributes' => [
        'class' => ['use-ajax', 'kc-add-to-cart-link__quantity-link', 'kc-add-to-cart-link__quantity-link--plus'],
      ]
    ];

    return [
      '#theme' => 'kc_add_to_cart',
      '#attributes' => $attributes,
      '#minus' => $minus,

      '#quantity' => [
        '#type' => 'html_tag',
        '#value' => $this->t('@quantity pcs.', ['@quantity' => $quantity]),
        '#tag' => 'div',
        '#attributes' => [
          'data-quantity' => $quantity,
          'class' => ['kc-add-to-cart-link__quantity'],
        ],
        '#attached' => [
          'library' => [
            'core/drupal.ajax'
          ],
        ],
      ],

      '#plus' => $plus,
    ];
  }

  /**
   * Build cart link render array if product variation is in the cart.
   *
   * @param string $product_variation_id
   *   The product variation id.
   * @param string $product_id
   *   The product id.
   *
   * @return array
   *   The render array.
   */
  public function buildVariationIsNotInTheCart($product_variation_id, $product_id, $order_id = NULL) {
    $attributes = $this->buildCartLinkAttributes($product_id, $product_variation_id, $order_id);

    $url_options = [
      'id' => $product_variation_id,
      'product' => $product_id,
    ];

    if ($order_id) {
      $url_options['order_id'] = $order_id;
    }

    return [
      '#theme' => 'kc_add_to_cart',
      '#attributes' => $attributes,
      '#add_to_cart_link' => [
        '#type' => 'link',
        '#title' => $this->t('Add to cart'),
        '#url' => Url::fromRoute('kc_personal_account.add_to_cart_ajax_callback', $url_options),
        '#attributes' => [
          'class' => [
            'use-ajax',
            'kc-add-to-cart-link__add-to-cart-link',
            'btn',
            'btn-primary',
            'rounded-pill',
          ],
        ],
        '#attached' => [
          'library' => [
            'core/drupal.ajax'
          ],
        ],
      ],
    ];
  }

  /**
   * Build variations select render array.
   *
   * @param string $product_variation_id
   *   The product variation id.
   * @param string $product_id
   *   The product id.
   * @param int $delta
   *   The active product variation.
   *
   * @return array
   *   The render array.
   */
  public function buildVariationLinks($product_variation_ids, $product_id, $delta) {
    $variation_links = [];

    $product_variations = $this->entityTypeManager->getStorage('commerce_product_variation')->loadMultiple($product_variation_ids);

    $i = 0;
    foreach ($product_variations as $product_variation_id => $product_variation) {
      $variation_links[$i] = [
        '#type' => 'link',
        '#title' => $product_variation->getTitle(),
        '#url' => Url::fromRoute('kc_personal_account.select_variation_ajax_callback', [
          'delta' => $i,
          'id' => $product_variation_id,
          'product' => $product_id,
        ]),
        '#attributes' => [
          'data-variation-id' => $product_variation_id,
          'data-delta' => $i,
          'class' => [
            'use-ajax',
            'kc-add-to-cart-link__variation-link',
            'kc-add-to-cart-link__variation-link--delta-' . $i,
            'kc-add-to-cart-link__variation-link--variation-id-' . $product_variation_id,
          ],
        ],
      ];

      if ($i == $delta) {
        $variation_links[$i]['#attributes']['class'][] = 'active';
      }

      $i++;
    }

    return $variation_links;
  }

  /**
   * Check product variation is in the cart.
   *
   * @param string $product_variation_id
   *   The product variation id.
   *
   * @return int
   *   The items in cart quantity.
   */
  public function isProductVariationInCart($product_variation_id) {
    $quantity = FALSE;

    $carts = $this->commerceCartCartProvider->getCarts();
    if (!empty($carts)) {
      $cart = reset($carts);

      $items = $cart->getItems();
      foreach ($items as $item) {
        if ($item->getPurchasedEntityId() == $product_variation_id) {
          $quantity = $item->getQuantity();
          $quantity = (int) $quantity;
          $quantity = round($quantity);
          break;
        }
      }
    }

    return $quantity;
  }

  /**
   * Build link attributes.
   *
   * @param string $product_id
   *   The product id.
   * @param string $product_variation_id
   *   The product variation id.
   *
   * @return mixed
   *   The Attribute().
   */
  public function buildAttributes($product_id, $product_variation_id) {
    $attributes = new Attribute();

    $attributes->addClass(['kc-add-to-cart-link']);
    $attributes->setAttribute('data-product', $product_id);
    $attributes->setAttribute('product-variation', $product_variation_id);

    return $attributes;
  }


  public function buildCartLinkAttributes($product_id, $product_variation_id, $order_id = NULL) {
    $attributes = new Attribute();

    $attributes->addClass(['kc-add-to-cart-link__link']);

    if ($order_id) {
      $attributes->setAttribute('data-order', $order_id);
    }

    return $attributes;
  }

  public function quantityLinkWrapperClassName() {
    return 'kc-add-to-cart-link__cart-links-wrapper';
  }

}
