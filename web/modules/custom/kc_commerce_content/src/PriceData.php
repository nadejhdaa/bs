<?php

declare(strict_types=1);

namespace Drupal\kc_commerce_content;

use Drupal\commerce_price\CurrencyFormatter;
use Drupal\commerce_price\Repository\CurrencyRepositoryInterface;
use Drupal\commerce_price\Resolver\ChainPriceResolverInterface;
use Drupal\commerce_store\CurrentStoreInterface;
use Drupal\commerce_price\CurrentCurrency;

/**
 * @todo Add class description.
 */
final class PriceData {

  /**
   * Constructs a PriceData object.
   */
  public function __construct(
    private readonly CurrentStoreInterface $commerceStoreCurrentStore,
    private readonly ChainPriceResolverInterface $commercePriceChainPriceResolver,
    private readonly CurrencyFormatter $commercePriceCurrencyFormatter,
    private readonly CurrencyRepositoryInterface $commercePriceCurrencyRepository,
    private readonly CurrentCurrency $currentCurrency,
  ) {}

  /**
   * @todo Add method description.
   */
  public function getPriceFormatted($product_variation) {
    $price = $product_variation->getPrice();

    return $this->getPriceWithSymbol($price->getNumber());
  }

  public function getCurrentSymbol() {
    return $this->currentCurrency->getCurrency()->getSymbol();
  }

  /**
   * @todo Add method description.
   */
  public function getPriceWithSymbol($number) {
    $number = intval($number);
    $parts[] = number_format($number, 0, ' ', ' ');
    $parts[] = $this->getCurrentSymbol();

    return implode(' ', $parts);
  }

}
