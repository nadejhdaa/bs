<?php

namespace Drupal\kc_personal_account\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implement the injection functionality of a file processor.
 *
 * @package Drupal\kc_commerce_content\Plugin
 */
trait CartAjaxCallbackTrait {

  public function request() {
    $request = $this->requestStack->getCurrentRequest();
    return $request->query;
  }

  public function getCart() {
    $carts = $this->commerceCartCartProvider->getCarts();
    if (!empty($carts)) {
      return reset($carts);
    }

    return FALSE;
  }

  public function getOrderItemsCount($cart) {
    $order_items = $cart->getItems();
    return !empty($order_items) ? count($order_items) : 0;
  }

  public function getOrderItemsCountString($items_count) {
    return $this->formatPlural($items_count, '1 product', '@count products');
  }

  public function getTotalPriceString($cart) {
    $price[] = intval($cart->getTotalPrice()->getNumber());
    $price[] = $this->getCurrentSymbol();

    return implode(' ', $price);
  }

  public function getCurrentSymbol() {
    return $this->currentCurrency->getCurrency()->getSymbol();
  }

  public function getOrderItemSelector($order_id) {
    return '.order-item[data-order="' . $order_id . '"]';
  }

  public function getCartTopCountSelector() {
    return 'div[data-selector="total-count"]';
  }

  public function getCartTotalPriceSelector() {
    return 'div[data-selector="total-price"]';
  }

  /**
   * Create link wrapper selector.
   *
   * @param string $product_id
   *   The product id.
   *
   * @return string
   *   Wrapper selector.
   */
  public function getCartLinkWrapperSelector($product_id) {
    return '.kc-add-to-cart-link[data-product="' . $product_id . '"] .kc-add-to-cart-link__link';
  }

  /**
   * Get cart items count wrapper.
   *
   * @return string
   *   Cart items counter selector.
   */
  public function getCartBlockCountSelector() {
    return '.kc-personal-account__link--cart .kc-personal-account__cart__count';
  }

  /**
   * Create link wrapper selector.
   *
   * @param string $product_id
   *   The product id.
   *
   * @return string
   *   Wrapper selector.
   */
  public function getVariationLinksWrapperSelector($product_id) {
    return '.kc-add-to-cart-link[data-product="' . $product_id . '"] .kc-add-to-cart-link__variation_links';
  }

  public function updateCartCountAndPrice($response, $cart) {
    $cart_top_items_count_selector = $this->getCartTopCountSelector();

    $items_count = $this->getOrderItemsCount($cart);
    $items_count_str = $this->getOrderItemsCountString($items_count);

    $response->addCommand(new HtmlCommand($cart_top_items_count_selector, $items_count_str));

    $total_price_selector = $this->getCartTotalPriceSelector();

    $number = $cart->getTotalPrice()->getNumber();
    $total_price = $this->priceData->getPriceWithSymbol($number);

    $response->addCommand(new HtmlCommand($total_price_selector, $total_price));

    $total_quantity = $this->getCartItemsTotalQuantities($cart);
    $cart_block_count_selector = $this->getCartBlockCountSelector();

    if ($total_quantity > 0) {
      $response->addCommand(new HtmlCommand($cart_block_count_selector, $total_quantity));
      $response->addCommand(new InvokeCommand($cart_block_count_selector, 'removeClass', ['hidden']));
    }
    else {
      $response->addCommand(new HtmlCommand($cart_block_count_selector, ''));
      $response->addCommand(new InvokeCommand($cart_block_count_selector, 'addClass', ['hidden']));
    }

    $response->addCommand(new HtmlCommand($cart_block_count_selector, $total_quantity));

    return $response;
  }

  /**
   * Add product variation to cart.
   *
   * @param string $product_variation_id
   *   The product variation id.
   * @param string $product_id
   *   The product id.
   * @param string $quantity
   *   The quantity.
   *
   * @return string
   *   The cart link markup string.
   */
  public function cartLinkMarkup($product_variation_id, $product_id, $quantity, $order_id = NULL) {
    if ($quantity > 0) {
      $cart_link_build = $this->addToCartLinkBuilder->buildVariationIsInTheCart($product_variation_id, $product_id, $quantity, $order_id);
    }
    else {
      $cart_link_build = $this->addToCartLinkBuilder->buildVariationIsNotInTheCart($product_variation_id, $product_id, $order_id);
    }

    $cart_link_upd = '';

    foreach ($cart_link_build as $item) {
      $cart_link_upd .= $this->renderer->render($item);
    }

    return $cart_link_upd;
  }

  /**
   * Get total number of items in the cart.
   *
   * @param string $product_id
   *   The product id.
   *
   * @return string
   *   Wrapper selector.
   */
  public function getCartItemsTotalQuantities($cart = NULL) {
    if (empty($cart)) {
      $cart = $this->getCart();
    }

    $count = 0;

    if (!empty($cart)) {
      $items = $cart->getItems();

      foreach ($items as $item) {
        $count += $item->getQuantity();
      }
    }

    return $count;
  }
}
