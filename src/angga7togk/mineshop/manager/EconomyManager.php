<?php

namespace angga7togk\mineshop\manager;

use angga7togk\mineshop\MineShop;
use angga7togk\mineshop\model\OreEconomy;
use pocketmine\item\Item;
use pocketmine\player\Player;

class EconomyManager
{
  public function __construct(private MineShop $plugin) {}

  /**
   * @return array<int, OreEconomy>
   */
  public function getOres(Player $player): array
  {
    $inventory = $player->getInventory();
    $data = [];

    foreach ($inventory->getContents() as $item) {
      if ($item->isNull()) {
        continue;
      }

      $typeId = $item->getTypeId();

      if (array_key_exists($typeId, $this->plugin->getEconomies())) {
        if (isset($data[$typeId])) {
          $data[$typeId]->addAmount($item->getCount());
        } else {
          $data[$typeId] = new OreEconomy($item, $item->getCount());
        }
      }
    }

    return $data;
  }

  public function reduceOre(Player $player, OreEconomy $oreEconomy): void
  {
    $inventory = $player->getInventory();
    $remainingAmount = $oreEconomy->getAmount();

    foreach ($inventory->getContents() as $slot => $item) {
      if ($item->equals($oreEconomy->getItem(), true, true)) {
        $count = $item->getCount();

        if ($count > $remainingAmount) {
          $inventory->setItem($slot, $item->setCount($count - $remainingAmount));
          return;
        } else {
          $remainingAmount -= $count;
          $inventory->setItem($slot, $item->setCount(0));
        }

        if ($remainingAmount <= 0) {
          return;
        }
      }
    }
  }
}
