<?php

declare(strict_types=1);

namespace Drupal\kc_personal_account\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\commerce_cart\CartProviderInterface;
use Drupal\flag\FlagServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;

/**
 * Provides a personal account block.
 */
#[Block(
  id: 'kc_personal_account_personal_account',
  admin_label: new TranslatableMarkup('Personal account'),
  category: new TranslatableMarkup('Custom'),
)]
final class PersonalAccountBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The Drupal account to use for checking for access to block.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  // protected $account;

  /**
   * Constructs the plugin instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly CartProviderInterface $commerceCartCartProvider,
    private readonly FlagServiceInterface $flag,
    private readonly AccountInterface $account,
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
      $container->get('commerce_cart.cart_provider'),
      $container->get('flag'),
      $container->get('current_user'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $markup = $this->buildItems();

    $build['content'] = $markup;
    return $build;
  }

  public function getUserName() {
    $username = '';

    $profile_ids = $this->entityTypeManager->getStorage('profile')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('uid', $this->account->id())
      ->condition('type', 'customer')
      ->sort('created', 'DESC')
      ->execute();

    if (!empty($profile_ids)) {
      $profile_id = reset($profile_ids);
      $profile = $this->entityTypeManager->getStorage('profile')->load($profile_id);

      $fio = [];
      if (!$profile->get('field_name')->isEmpty()) {
        $fio[] = $profile->field_name->value;
      }

      if (!$profile->get('field_surname')->isEmpty()) {
        $fio[] = $profile->field_surname->value;
      }

      $username = implode(' ', $fio);
    }
    else {
      $username = $this->account->getEmail();
    }

    return $username;
  }

  public function buildItems() {
    $user = User::load($this->account->id());
    $profile = $user->get('customer_profiles')->entity;

    $authenticated = $this->account->isAuthenticated() ? 1 : FALSE;
    $name = $authenticated ? $this->account->getEmail() : '';
    $name = $this->getUserName();

    $cart_count = $this->getCartItemsCount();

    $orders_ids = $this->entityTypeManager->getStorage('commerce_order')
      ->getQuery()
      ->accessCheck()
      ->condition('uid', $this->account->id())
      ->execute();

    $orders = !empty($orders_ids) ? count($orders_ids) : '';

    return [
      '#theme' => 'kc_personal_account_block',
      '#authenticated' => $authenticated,
      '#username' => $name,
      '#cart_count' => $cart_count,
      '#orders' => $orders,
    ];
  }

  public function getCartItemsCount() {
    $carts = $this->commerceCartCartProvider->getCarts();

    $count = 0;
    if (!empty($carts)) {
      $cart = reset($carts);

      foreach ($cart->getItems() as $item) {
        $count += $item->getQuantity();
      }
    }

    return $count;
  }

}
