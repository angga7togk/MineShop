<?php

namespace angga7togk\mineshop\ui;

use angga7togk\mineshop\MineShop;
use angga7togk\mineshop\model\ItemShop;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class MenuButton
{

  public static function getPreviousButton(int $currentPage, int $maxPage): Item
  {
    return VanillaItems::REDSTONE_DUST()
      ->setNamedTag((new CompoundTag())->setString('mineshop', 'previous'))
      ->setCustomName(TextFormat::BOLD . TextFormat::BLUE . 'Previous Page' . TextFormat::RESET . TextFormat::GRAY . ' [' . $currentPage . '/' . $maxPage . "]");
  }

  public static function getInfoButton(Player $player): Item
  {
    $lore = [TextFormat::GRAY . 'Hi ' . TextFormat::GOLD . $player->getName()];
    foreach (MineShop::getInstance()->getEconomyManager()->getOres($player) as $typeId => $oreEco) {
      $lore[] = TextFormat::GRAY . $oreEco->getItem()->getName() . TextFormat::GOLD . ' x' . $oreEco->getAmount();
    }
    return VanillaItems::BOOK()
      ->setNamedTag((new CompoundTag)->setString('mineshop', 'info'))
      ->setCustomName(TextFormat::BOLD . TextFormat::BLUE . 'Your Info')
      ->setLore($lore);
  }

  public static function getNextButton(int $currentPage, int $maxPage): Item
  {
    return VanillaItems::EMERALD()
      ->setNamedTag((new CompoundTag)->setString('mineshop', 'next'))
      ->setCustomName(TextFormat::BOLD . TextFormat::BLUE . 'Next Page' . TextFormat::RESET . TextFormat::GRAY . ' [' . $currentPage . '/' . $maxPage . "]");
  }



  public static function getItemButton(int $index, ItemShop $itemShop, bool $unsellMode = false): Item
  {
    $item = $itemShop->getItem();
    if ($item->hasNamedTag()) {
      $item->getNamedTag()->setString('mineshop', 'selling')->setInt('mineshop_index', $index);
    } else {
      $item->setNamedTag((new CompoundTag)->setString('mineshop', 'selling')->setInt('mineshop_index', $index));
    }
    $lore = [''];
    if ($unsellMode) {
      $lore[] = TextFormat::RED . 'click to unsell the item!';
    } else {
      $lore[] = TextFormat::YELLOW . 'Need Ores:';
      foreach ($itemShop->getPrices() as $oreEco) {
        $lore[] = ' ' . TextFormat::GRAY . $oreEco->getItem()->getName() . TextFormat::GOLD . ' x' . $oreEco->getAmount();
      }
    }
    return $item->setLore($lore);
  }
}
