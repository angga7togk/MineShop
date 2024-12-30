<?php

namespace angga7togk\mineshop\economy;

use angga7togk\mineshop\MineShop;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;

class MineEconomy
{
  public function __construct(private MineShop $plugin) {}

  /** @return array<Item, int> */
  public function getPlayerEconomies(Player $player): array {
    $inv = $player->getInventory();
    $economies = $this->getEconomies();
    $data = [];
    foreach($inv->getContents() as $slot => $item){
      if(in_array($item->getTypeId(), $economies)){
        if(isset($data[$item])){
          $data[$item]++;
        }else{
          $data[$item] = 1;
        }
      }
    }
    return $data;
  }

  public function reducePlayerEconomy(Player $player, Item $itemEconomy, int $amountPrice): void {
    $inv = $player->getInventory();
    foreach($inv->getContents() as $slot => $item){
      if($item->equals($itemEconomy)){
        $count = $item->getCount();
        if($count >= $amountPrice){
          $inv->setItem($slot, $item->setCount($count - $amountPrice));
        }else{
          $inv->setItem($slot, $item->setCount(0));
        }
      }
    }
  }
  
  /** @return array<int, Item> */
  public function getEconomies(): array {
    $data = [];
    foreach($this->plugin->getEconomies() as $itemString){
      $item = StringToItemParser::getInstance()->parse($itemString);
      if($item !== null) $data[$item->getTypeId()] = $item;
    }
    return $data;
  }
}
