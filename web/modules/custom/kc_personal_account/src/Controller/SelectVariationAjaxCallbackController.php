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
final class SelectVariationAjaxCallbackController extends ControllerBase {

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
   * Select product variation ajax callback.
   *
   * @return mixed
   *   The AjaxResponse().
   */
  public function __invoke() {
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

    $product_variation = $this->buildProductVariation($product_variation_id);
    $response->addCommand(new HtmlCommand('.kc-add-to-cart-link__variation-info', $product_variation));

    return $response;
  }

  public function buildProductVariation($product_variation_id) {
    $entity_id = 'commerce_product_variation';

    $entity = $this->entityTypeManager->getStorage($entity_id)->load($product_variation_id);
    $view_mode = 'default';
    return $this->entityTypeManager->getViewBuilder($entity_id)->view($entity, $view_mode);
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

}
