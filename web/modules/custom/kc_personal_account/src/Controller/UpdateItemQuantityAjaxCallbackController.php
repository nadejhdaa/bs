<?php

declare(strict_types=1);

namespace Drupal\kc_personal_account\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Field\FormatterPluginManager;
use Drupal\Core\Render\RendererInterface;
use Drupal\commerce_cart\CartManagerInterface;
use Drupal\commerce_cart\CartProviderInterface;
use Drupal\commerce_order\Resolver\ChainOrderTypeResolverInterface;
use Drupal\commerce_price\Resolver\ChainPriceResolverInterface;
use Drupal\commerce_store\CurrentStoreInterface;
use Drupal\kc_personal_account\AddToCartLinkBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\kc_personal_account\Controller\CartAjaxCallbackTrait;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\commerce_price\CurrentCurrency;
use Drupal\kc_commerce_content\PriceData;

/**
 * Returns responses for KC commerce content routes.
 */
final class UpdateItemQuantityAjaxCallbackController extends ControllerBase {

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
   * Add product variation to cart.
   *
   * @param string $type
   *   Increase or decrease.
   *
   * @return mixed
   *   The AjaxResponse().
   */
  public function __invoke(string $type) {
    $product_variation_id = $this->request()->get('id');
    $product_id = $this->request()->get('product');
    $order_id = $this->request()->get('order_id');

    $response = new AjaxResponse();

    $cart = $this->getCart();

    $order_items = $cart->getItems();

    foreach ($order_items as $order_item) {
      if ($order_item->getPurchasedEntityId() == $product_variation_id) {
        $current_quantity = $order_item->getQuantity();

        $new_quantity = $type == 'plus' ? $current_quantity + 1 : $current_quantity - 1;

        if ($new_quantity < 0) {
          $new_quantity = 0;
        }

        // Save new quantity.
        $order_item->setQuantity($new_quantity);
        $order_item->save();

        // Recalculate the order if necessary (e.g., if pricing is affected)
        $order = $order_item->getOrder();
        $order->recalculateTotalPrice();

        // If new quantity is 0, the item will be removed automatically upon order save.
        // To explicitly remove, or remove regardless of quantity:
        // $order->removeItem($order_item_to_modify);
        $order->save();
      }
    }

    // Update order links or remove order row from cart.
    if (!empty($order_id) && empty($new_quantity)) {
      $order_item_selector = $this->getOrderItemSelector($order_id);
      $response->addCommand(new removeCommand($order_item_selector));
    }
    else {
      $cart_link_upd = $this->cartLinkMarkup($product_variation_id, $product_id, $new_quantity, $order_id);
      $cart_link_selector = $this->getCartLinkWrapperSelector($product_id);

      $response->addCommand(new HtmlCommand($cart_link_selector, $cart_link_upd));
    }


    $response = $this->updateCartCountAndPrice($response, $cart);

    return $response;
  }

}
