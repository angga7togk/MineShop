<?php

namespace angga7togk\mineshop\model;

use pocketmine\item\Item;

class ItemShop
{

  /**
   * @param OreEconomy[] $prices
   */
  public function __construct(private Item $item, private readonly array $prices) {}

  public function getItem(): Item
  {
    return $this->item;
  }

  /**
   * @return OreEconomy[]
   */
  public function getPrices(): array
  {
    return $this->prices;
  }
}
