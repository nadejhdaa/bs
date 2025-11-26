<?php

declare(strict_types=1);

namespace Drupal\kc_personal_account\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce\Context;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_store\SelectStoreTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Template\Attribute;
use Drupal\kc_personal_account\AddToCartLinkBuilderInterface;
use Drupal\commerce_price\Entity\Currency;
use Drupal\Core\Url;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\kc_commerce_content\ProductData;
use Drupal\kc_commerce_content\PriceData;
use Drupal\flag\FlagLinkBuilder;
use Drupal\kc_personal_account\Controller\CartAjaxCallbackTrait;

/**
 * Provides a KC Personal account form.
 */
final class CartForm extends FormBase {
  use SelectStoreTrait;
  use CartAjaxCallbackTrait;

  const FLAG_NAME = 'favorite';

  /**
   * The cart manager.
   *
   * @var \Drupal\commerce_cart\CartManagerInterface
   */
  protected $cartManager;

  /**
   * The cart provider.
   *
   * @var \Drupal\commerce_cart\CartProviderInterface
   */
  protected $cartProvider;

  /**
   * The order type resolver.
   *
   * @var \Drupal\commerce_order\Resolver\OrderTypeResolverInterface
   */
  protected $orderTypeResolver;

  /**
   * The current store.
   *
   * @var \Drupal\commerce_store\CurrentStoreInterface
   */
  protected $currentStore;

  /**
   * The chain base price resolver.
   *
   * @var \Drupal\commerce_price\Resolver\ChainPriceResolverInterface
   */
  protected $chainPriceResolver;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The current user.
   *
   * @var Drupal\kc_personal_account\AddToCartLinkBuilderInterface
   */
  protected $addToCartLinkBuilder;

  /**
   * The product_data service.
   *
   * @var Drupal\kc_commerce_content\ProductData
   */
  protected $productData;

  /**
   * The price_data service.
   *
   * @var Drupal\kc_commerce_content\PriceData
   */
  protected $priceData;

  /**
   * The "flag.link_builder" service.
   *
   * @var Drupal\kc_commerce_content\PriceData
   */
  protected $flagLinkBuilder;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->cartManager = $container->get('commerce_cart.cart_manager');
    $instance->cartProvider = $container->get('commerce_cart.cart_provider');
    $instance->orderTypeResolver = $container->get('commerce_order.chain_order_type_resolver');
    $instance->currentStore = $container->get('commerce_store.current_store');
    $instance->chainPriceResolver = $container->get('commerce_price.chain_price_resolver');
    $instance->currentUser = $container->get('current_user');
    $instance->addToCartLinkBuilder = $container->get('kc_personal_account.add_to_cart_link_builder');
    $instance->productData = $container->get('kc_commerce_content.product_data');
    $instance->priceData = $container->get('kc_commerce_content.price_data');
    $instance->flagLinkBuilder = $container->get('flag.link_builder');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'kc_personal_account_cart';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#prefix'] = '<div class="kc-cart__wrapper">';
    $form['#suffix'] = '</div>';

    $carts = $this->cartProvider->getCarts();

    if (!empty($carts)) {
      $cart = reset($carts);
      $form_state->set('cart', $cart);

      $items = $this->getCartItems($cart);

      $count = 0;

      if (!empty($items)) {
        foreach ($items as $item) {
          $count ++;
        }

        $form['top'] = [
          '#type' => 'container',
          '#attributes' => [
            'class' => [
              'd-flex',
              'kc-cart__top',
              'justify-content-between',
            ],
          ],
        ];

        $form['top']['total_count'] = [
          '#type' => 'html_tag',
          '#value' => $this->formatPlural($count, '1 product', '@count products'),
          '#tag' => 'div',
          '#attributes' => [
            'class' => [
              'kc-cart__total-count',
            ],
            'data-selector' => 'total-count',
          ],
        ];

        $form['top']['clear_cart'] = [
          '#type' => 'button',
          '#value' => $this->t('Clear cart'),
          '#attributes' => [
            'class' => [
              'btn',
              'kc-cart__clear-cart-button',
              'btn-sm',
              'btn-light',
              'rounded-pill',
            ],
          ],
          '#ajax' => [
            'callback' => '::clearCartAjaxCallback',
            'effect' => 'fade',
          ],
          '#wrapper_attributes' => [
            'class' => [
              'kc-cart__top__clear-cart-wrapper',
            ],
          ],
        ];

        // Order items list.
        $form['items'] = [
          '#type' => 'container',
        ];

        foreach ($items as $key => $item) {
          $product = $item['product'];
          $product_id = $product->id();
          $order_id = $item['order_id'];

          $product_variation = $item['product_variation'];
          $product_variation_id = $product_variation->id();

          $item_title = $this->getIteTitle($product, $product_variation);

          $quantity = $item['quantity'];

          $img_uri = !$product->get('field_img')->isEmpty() ? $product->get('field_img')->entity->field_media_image->entity->getFileUri() : FALSE;

          $url = $product->toUrl()->setAbsolute()->toString();

          $attributes = $this->buildProductInfoAttributes($item);

          $price = $this->priceData->getPriceFormatted($product_variation);

          $product_type = $this->productData->getTypeString($product);

          $form['items'][$order_id]['product'] = [
            '#type' => 'container',
            '#attributes' => [
              'class' => ['kc-cart__item', 'order-item'],
              'data-order' => $order_id,
            ],
          ];

          $favorite_link = $this->buildAddToFavoriteLink($product);

          $form['items'][$order_id]['product']['product_info'] = [
            '#type' => 'component',
            '#component' => 'kc_personal_account:product_in_cart',
            '#props' => [
              'title' => $item_title,
              'product_id' => $product_id,
              'product_variation_id' => $product_variation_id,
              'order_id' => $order_id,
              'img_uri' => $img_uri,
              'url' => $url,
              'product_type' => $product_type,
              'attributes' => $attributes,
              'favorite_link' => $favorite_link,
            ],
            '#prefix' => '<div class="kc-cart__item__product-info">',
            '#suffix' => '</div>',
          ];

          $form['items'][$order_id]['product']['buttons'] = [
            '#type' => 'container',
            '#attributes' => [
              'class' => ['kc-cart__item__actions'],
            ],
            [
              'order_id' => [
                '#type' => 'hidden',
                '#default_value' => $order_id,
                '#name' => 'cart_item_order_id_' . $order_id,
              ],
              'minus' => [
                '#type' => 'button',
                '#value' => '-',
                '#attributes' => [
                  'class' => ['kc-cart__item__minus'],
                  'data-order' => $order_id,
                  'data-product-variation' => $product_variation_id,
                  'data-product' => $product_id,
                  'data-action' => 'minus',
                ],
                '#name' => 'cart_item_order_id_' . $order_id . '_minus',
                '#ajax' => [
                  'callback' => '::updOrderItemAjaxCallback',
                  'effect' => 'fade',
                  'wrapper' => 'kc-personal-account-cart',
                ],
              ],
              'quantity' => [
                '#type' => 'html_tag',
                '#tag' => 'div',
                '#value' => $quantity,
                '#attributes' => [
                  'class' => [
                    'kc-cart__item__quantity',
                    'quantity',
                  ],
                ],
                '#name' => 'cart_item_order_id_' . $order_id . '_quantity',
              ],
              'plus' => [
                '#type' => 'button',
                '#value' => '+',
                '#attributes' => [
                  'class' => ['kc-cart__item__plus'],
                  'data-order' => $order_id,
                  'data-product-variation' => $product_variation_id,
                  'data-product' => $product_id,
                  'data-action' => 'plus',
                ],
                '#name' => 'cart_item_order_id_' . $order_id . '_plus',
                '#ajax' => [
                  'callback' => '::updOrderItemAjaxCallback',
                  'effect' => 'fade',
                ],
              ],
            ]
          ];

          $form['items'][$order_id]['product']['price'] = [
            '#type' => 'html_tag',
            '#tag' => 'div',
            '#value' => $price,
            '#attributes' => [
              'class' => [
                'kc-cart__item__price'
              ],
            ],
          ];
        }

        // Actions.
        $form['sidebar'] = [
          '#type' => 'container',
        ];

        if ($this->currentUser()->isAnonymous()) {
          $form['sidebar']['order'] = [
            '#type' => 'link',
            '#title' => $this->t('Place an order'),
            '#url' => Url::fromRoute('<front>'),
            '#attributes' => [
              'class' => [
                'rounded-pill',
                'w-100',
                'btn',
                'btn-primary',
                'mb-5',
              ],
              'data-bs-toggle' => 'modal',
              'data-bs-target' => '#kcLoginModal',
            ],
          ];
        }
        else {
          $form['sidebar']['order'] = [
            '#type' => 'submit',
            '#submit' => ['::createOrder'],
            '#value' => $this->t('Place an order'),
            '#attributes' => [
              'class' => [
                'rounded-pill',
                'w-100',
              ],
            ],
          ];
        }

        $total_number = $cart->getTotalPrice()->getNumber();
        $total_price = $this->priceData->getPriceWithSymbol($total_number);

        $form['sidebar']['order_info'] = [
          '#theme' => 'kc_cart_order_info',
          '#total_price' => $total_price,
        ];
      }

      else {
        $form['empty'] = $this->buildEmptyCartElements();
      }
    }

    else {
      $form['empty'] = $this->buildEmptyCartElements();
    }

    $form['#theme'] = 'kc_cart';

    return $form;
  }

  public function createOrder(array &$form, FormStateInterface $form_state) {
    $cart = $form_state->get('cart');

    $this->cartProvider->finalizeCart($cart, FALSE);
    $url = Url::fromRoute('commerce_checkout.checkout');
    $form_state->setRedirectUrl($url);
  }

  public function buildEmptyCartElements() {
    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'd-flex',
          'flex-column',
          'align-items-center',
          'py-5',
          'px-5',
          'kc-cart__empty',
        ],
      ],

      'empty_msg' => [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $this->t('The cart is empty'),
        '#attributes' => [
          'class' => ['kc-cart__msg', 'kc-cart__msg--empty-cart'],
        ],
      ],

      'empty_cart_links' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'd-flex',
            'justify-content-center',
            'kc-cart__links',
          ],
        ],

        'catalog' => [
          '#type' => 'link',
          '#title' => $this->t('Go to the catalog'),
          '#url' => Url::fromRoute('<front>'),
          '#attributes' => [
            'class' => [
              'btn',
              'btn-primary',
              'rounded-pill',
            ],
          ],
        ],
      ],
    ];
  }



  public function updOrderItemAjaxCallback(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    $triggering_element = $form_state->getTriggeringElement();

    $attributes = $triggering_element['#attributes'];

    $action = $attributes['data-action'];
    $order_id = $attributes['data-order'];

    $cart = $form_state->get('cart');

    $order_items = $cart->getItems();

    foreach ($order_items as $order_item) {
      if ($order_item->id() == $order_id) {

        $quantity = $order_item->getQuantity();
        $quantity = $action == 'minus' ? $quantity - 1 : $quantity + 1;

        $order_item->setQuantity($quantity);
        $order_item->save();

        // Recalculate the order if necessary (e.g., if pricing is affected)
        $cart->recalculateTotalPrice();
        $cart->save();

        $form_state->set('cart', $cart);

        $order_selector = '.order-item[data-order="' . $order_id . '"]';

        if ($quantity > 0) {
          $selector = $order_selector . ' .quantity';
          $response->addCommand(new HtmlCommand($selector, $quantity));
        }
        else {
          $response->addCommand(new RemoveCommand($order_selector));
        }

        break;
      }
    }

    $response = $this->updateCartCountAndPrice($response, $cart);

    $selector = '[data-selector="kc-cart-order-info"]';

    $total_number = $cart->getTotalPrice()->getNumber();

    $total_number = intval($total_number);
    $total_price = $this->priceData->getPriceWithSymbol($total_number);

    $total_markup = [
      '#theme' => 'kc_cart_order_info',
      '#total_price' => $total_price,
    ];
    $response->addCommand(new HtmlCommand($selector, $total_markup));

    return $response;
  }

  public function clearCartAjaxCallback(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    $cart = $form_state->get('cart');
    $this->cartManager->emptyCart($cart);
    $form_state->set('cart', $cart);

    unset($form['items']);
    unset($form['sidebar']);
    unset($form['top']);

    $form['empty'] = $this->buildEmptyCartElements();
    $response->addCommand(new HtmlCommand('.kc-cart__wrapper', $form));

    $cart_block_count_selector = $this->getCartBlockCountSelector();

    $response->addCommand(new HtmlCommand($cart_block_count_selector, ''));
    $response->addCommand(new InvokeCommand($cart_block_count_selector, 'addClass', ['hidden']));

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->messenger()->addStatus($this->t('The message has been sent.'));
    // $form_state->setRedirect('<front>');
  }

  public function getCartItems($cart) {
    $items = [];
    $cart_items = $cart->getItems();

    if (!empty($cart_items)) {
      foreach ($cart_items as $key => $cart_item) {
        $product_variation = $cart_item->getPurchasedEntity();
        $product = $product_variation->getProduct();

        $items[$key] = [
          'order_id' => $cart_item->id(),
          'product' => $product,
          'product_variation' => $product_variation,
          'quantity' => intval($cart_item->getQuantity()),
        ];
      }
    }

    return $items;
  }

  public function getIteTitle($product, $product_variation) {
    $parts = [];
    $parts[] = $product->getTitle();
    $parts[] = $product_variation->getTitle();

    return implode(' ', $parts);
  }

  public function buildProductInfoAttributes($item) {
    $attributes = new Attribute();

    $order_id = $item['order_id'];

    $attributes->setAttribute('data-order', $order_id);

    $attributes->addClass([
      'kc-cart__order-item',
      'kc-cart__order-item--order-' . $order_id,
      'd-flex',
    ]);

    return $attributes;
  }

  public function buildAddToFavoriteLink($product) {
    return $this->flagLinkBuilder->build($product->getEntityTypeId(), $product->id(), self::FLAG_NAME);
  }

}
