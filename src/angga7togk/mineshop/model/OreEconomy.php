<?php

namespace angga7togk\mineshop\model;

use pocketmine\item\Item;

class OreEconomy
{
  public function __construct(
    private readonly Item $item,
    private int $amount
  ) {}

  public function getItem(): Item
  {
    return $this->item;
  }

  public function getAmount(): int
  {
    return $this->amount;
  }

  public function addAmount(int $amount): void
  {
    $this->amount += $amount;
  }
}
