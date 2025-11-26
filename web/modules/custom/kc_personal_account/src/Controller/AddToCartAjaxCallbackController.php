<?php

declare(strict_types=1);

namespace Drupal\kc_personal_account\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\commerce_cart\CartManagerInterface;
use Drupal\commerce_cart\CartProviderInterface;
use Drupal\commerce_order\Resolver\ChainOrderTypeResolverInterface;
use Drupal\commerce_price\Resolver\ChainPriceResolverInterface;
use Drupal\commerce_store\CurrentStoreInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\commerce_store\SelectStoreTrait;
use Drupal\commerce\Context;
use Drupal\Core\Field\FormatterPluginManager;
use Drupal\kc_personal_account\AddToCartLinkBuilderInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\commerce_price\CurrentCurrency;
use Drupal\kc_commerce_content\PriceData;
use Drupal\kc_personal_account\Controller\CartAjaxCallbackTrait;

/**
 * Returns responses for KC Personal account routes.
 */
final class AddToCartAjaxCallbackController extends ControllerBase {

  use SelectStoreTrait;
  use CartAjaxCallbackTrait;

  /**
   * The controller constructor.
   */
  public function __construct(
    private readonly CartManagerInterface $commerceCartCartManager,
    private readonly CartProviderInterface $commerceCartCartProvider,
    private readonly ChainOrderTypeResolverInterface $commerceOrderChainOrderTypeResolver,
    private readonly CurrentStoreInterface $commerceStoreCurrentStore,
    private readonly ChainPriceResolverInterface $commercePriceChainPriceResolver,
    private readonly RequestStack $requestStack,
    private readonly FormatterPluginManager $pluginManagerFieldFormatter,
    private readonly AddToCartLinkBuilderInterface $addToCartLinkBuilder,
    private readonly RendererInterface $renderer,
    private readonly CurrentCurrency $currentCurrency,
    private readonly PriceData $priceData,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('commerce_cart.cart_manager'),
      $container->get('commerce_cart.cart_provider'),
      $container->get('commerce_order.chain_order_type_resolver'),
      $container->get('commerce_store.current_store'),
      $container->get('commerce_price.chain_price_resolver'),
      $container->get('request_stack'),
      $container->get('plugin.manager.field.formatter'),
      $container->get('kc_personal_account.add_to_cart_link_builder'),
      $container->get('renderer'),
      $container->get('commerce_price.current_currency'),
      $container->get('kc_commerce_content.price_data'),
    );
  }

  /**
   * Add to cart product variation.
   *
   * @return mixed
   *   The AjaxResponse().
   */
  public function addToCart() {
    $product_variation_id = $this->request()->get('id');
    $product_id = $this->request()->get('product');

    $response = new AjaxResponse();

    $result = $this->addToCartProductVariation($product_variation_id);
    $quantity = $result['quantity'];

    $cart_link_upd = $this->cartLinkMarkup($product_variation_id, $product_id, $quantity);

    $cart_link_selector = $this->getCartLinkWrapperSelector($product_id);
    $response->addCommand(new HtmlCommand($cart_link_selector, $cart_link_upd));

    // Update cart items total number in the cart block.
    $total_number = $this->getCartItemsTotalQuantities();

    $cart_count_selector = $this->getCartBlockCountSelector();
    $response->addCommand(new HtmlCommand($cart_count_selector, $total_number));
    $response->addCommand(new InvokeCommand($cart_count_selector, 'removeClass', ['hidden']));

    return $response;
  }

  /**
   * Add product variation to cart.
   *
   * @param string $product_variation_id
   *   Product variation id.
   *
   * @return array
   *   The cart items count.
   */
  public function addToCartProductVariation($product_variation_id) {
    $result = [
      'count' => 1,
      'quantity' => 1,
    ];


    $cart = $this->commerceCartCartProvider->getCart('default');

    if (!empty($cart)) {
      $order_items = $this->entityTypeManager()->getStorage('commerce_order_item')->loadByProperties([
        'purchased_entity' => $product_variation_id,
        'order_id' => $cart->id(),
      ]);
    }
 
    if (!empty($order_items)) {
      $order_item = reset($order_items);
    }
    else {
      $product_variation = $this->entityTypeManager()->getStorage('commerce_product_variation')->load($product_variation_id);
      $order_item_storage = $this->entityTypeManager()->getStorage('commerce_order_item');
      $order_item = $order_item_storage->createFromPurchasableEntity($product_variation);
    }

    if (!$order_item->isNew()) {
      $order_item = $order_item->createDuplicate();
    }

    $purchased_entity = $order_item->getPurchasedEntity();
    $store = $this->selectStore($purchased_entity);

    if (!$order_item->isUnitPriceOverridden()) {
      $context = new Context($this->currentUser(), $store);
      $resolved_price = $this->commercePriceChainPriceResolver->resolve($purchased_entity, $order_item->getQuantity(), $context);
      $order_item->setUnitPrice($resolved_price);
      $order_item->save();
    }

    $order_type_id = $this->commerceOrderChainOrderTypeResolver->resolve($order_item);
    $cart = $this->commerceCartCartProvider->getCart($order_type_id);

    if ($cart) {
      $order_item->set('order_id', $cart->id());
    }
    else {
      $cart = $this->commerceCartCartProvider->createCart($order_type_id, $store);
    }

    $this->commerceCartCartManager->addOrderItem($cart, $order_item);

    $count = 0;
    $items = $cart->getItems();

    foreach ($items as $item) {
      $item_quantity = $item->getQuantity();
      $count += $item_quantity;

      if ($item->id() == $product_variation_id) {
        $result['quantity'] = $item_quantity;
      }
    }

    $result['count'] = $count;

    return $result;
  }

  /**
   * Build variation links markup.
   *
   * @param array $product_ids
   *   The product variation ids.
   * @param string $product_id
   *   The product id.
   * @param string $delta
   *   The delta.
   *
   * @return string
   *   The variation links markup string.
   */
  public function variationLinksMarkup($product_ids, $product_id, $delta) {
    $variation_links_markup = '';

    $build = $this->addToCartLinkBuilder->buildVariationLinks($product_ids, $product_id, $delta);

    foreach ($build as $item) {
      $variation_links_markup .= $this->renderer->render($item);
    }

    return $variation_links_markup;
  }


  /**
   * Select product variation ajax callback.
   *
   * @return mixed
   *   The AjaxResponse().
   */
  public function selectVariation() {
    $delta = $this->request()->get('delta');
    $product_variation_id = $this->request()->get('id');
    $product_id = $this->request()->get('product');

    $response = new AjaxResponse();

    $quantity = $this->addToCartLinkBuilder->isProductVariationInCart($product_variation_id);

    $cart_link_upd = $this->cartLinkMarkup($product_variation_id, $product_id, $quantity);
    $cart_link_selector = $this->getCartLinkWrapperSelector($product_id);

    $response->addCommand(new HtmlCommand($cart_link_selector, $cart_link_upd));

    $product = $this->entityTypeManager()->getStorage('commerce_product')->load($product_id);
    $product_variation_ids = $product->getVariationIds();

    $variation_links = $this->variationLinksMarkup($product_variation_ids, $product_id, $delta);
    $variation_links_selector = $this->getVariationLinksWrapperSelector($product_id);

    $response->addCommand(new HtmlCommand($variation_links_selector, $variation_links));

    return $response;
  }


}
