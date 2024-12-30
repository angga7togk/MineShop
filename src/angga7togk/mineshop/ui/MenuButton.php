<?php

namespace angga7togk\mineshop\ui;

use angga7togk\mineshop\MineShop;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
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
    $lore = [TextFormat::GRAY . 'Hi: ' . TextFormat::GOLD . $player->getName()];
    foreach (MineShop::getInstance()->getEconomy()->getPlayerEconomies($player) as $item => $count) {
      $lore[] = TextFormat::GRAY . $item->getName() . TextFormat::GOLD . ' x' . $count;
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


  /**
   * @param array{
   *     item: Item,
   *     prices: array<string, int>
   * } $itemSelling
   */
  public static function getItemButton(int $index, array $itemSelling): Item
  {
    $lore = [TextFormat::WHITE . 'Needs:'];
    foreach ($itemSelling['prices'] as $itemNamesId => $price) {
      $lore[] = ' ' . TextFormat::GRAY . StringToItemParser::getInstance()->parse($itemNamesId)->getName() . TextFormat::GOLD . ' x' . $price;
    }
    return $itemSelling['item']
      ->setNamedTag((new CompoundTag)->setString('mineshop', 'selling')->setInt('mineshop_index', $index))
      ->setLore($lore);
  }
}
