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
final class RemoveItemFromOrderAjaxCallbackController extends ControllerBase {

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
    private readonly AddToCartLinkBuilderInterface $kcCommerceContentAddToCartLinkBuilder,
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
   * Builds the response.
   */
  public function __invoke() {
    $order_id = $this->request()->get('order_id');

    $response = new AjaxResponse();

    $cart = $this->getCart();

    $order_items = $cart->getItems();

    if (!empty($order_items)) {
      foreach ($order_items as $order_item) {
        if ($order_item->id() == $order_id) {
          $this->commerceCartCartManager->removeOrderItem($cart, $order_item);
          $cart->save();

          $order_item_selector = $this->getOrderItemSelector($order_id);
          $response->addCommand(new RemoveCommand($order_item_selector));

          break;
        }
      }
    }

    $selector = '[data-selector="kc-cart-order-info"]';

    $total_number = $cart->getTotalPrice()->getNumber();

    $total_number = intval($total_number);
    $total_price = $this->priceData->getPriceWithSymbol($total_number);

    $total_markup = [
      '#theme' => 'kc_cart_order_info',
      '#total_price' => $total_price,
    ];
    $response->addCommand(new HtmlCommand($selector, $total_markup));

    $response = $this->updateCartCountAndPrice($response, $cart);

    return $response;
  }

}
