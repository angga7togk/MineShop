<?php

namespace angga7togk\mineshop\manager;

use angga7togk\mineshop\MineShop;
use angga7togk\mineshop\model\ItemShop;
use angga7togk\mineshop\model\OreEconomy;
use angga7togk\mineshop\utils\Utils;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\utils\Config;

class ShopManager
{

  private Config $shop;

  public function __construct(private MineShop $plugin)
  {
    $this->plugin->saveResource('shop.yml');
    $this->shop = new Config($this->plugin->getDataFolder() . 'shop.yml', Config::YAML, []);
  }

  /**
   * @return ItemShop[]
   */
  public function getShop(): array
  {
    $shops = $this->shop->getAll();
    $formattedShops = [];

    foreach ($shops as $shop) {
      $prices = [];
      foreach ($shop['prices'] as $itemTypeId => $price) {
        if(isset($this->plugin->getEconomies()[$itemTypeId])){
          $prices[] = new OreEconomy($this->plugin->getEconomies()[$itemTypeId], $price);
        }
      }
      $formattedShops[] = new ItemShop(Utils::deserializeItem($shop['item']), $prices);
    }
    return $formattedShops;
  }


  /** @param OreEconomy[] $prices */
  public function sellItem(Item $item, array $prices): void
  { 
    $__prices = [];
    foreach ($prices as $price) {
      if ($price->getAmount() > 0) $__prices[$price->getItem()->getTypeId()] = $price->getAmount();
    }

    $shops = $this->shop->getAll();
    $shops[] = ['item' => Utils::serializeItem($item), 'prices' => $__prices];
    $this->shop->setAll($shops);
    $this->shop->save();
  }

  public function unsellItem(int $shopIndex): void
  {
    $shops = $this->shop->getAll();
    if (isset($shops[$shopIndex])) {
      unset($shops[$shopIndex]);
      $this->shop->setAll($shops);
      $this->shop->save();
    }
  }
}
