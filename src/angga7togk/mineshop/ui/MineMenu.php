<?php

namespace angga7togk\mineshop\ui;

use angga7togk\mineshop\MineShop;
use jojoe77777\FormAPI\CustomForm;
use muqsit\invmenu\InvMenu;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class MineMenu
{
  public function __construct(private readonly MineShop $plugin) {}

  public function open(Player $player, int $page = 1): void
  {
    $schema = $this->plugin->getGUISchema();
    $shops = $this->plugin->getShop();

    $menu = InvMenu::create($schema['type'] === 'CHEST' ? InvMenu::TYPE_CHEST : InvMenu::TYPE_DOUBLE_CHEST);
    $menu->setName($schema['name']);
    $maxSlotForItemSell = $schema['max_slot'];

    $totalItems = count($shops);
    $maxPage = (int)ceil($totalItems / $maxSlotForItemSell);
    $startIndex = ($page - 1) * $maxSlotForItemSell;
    $endIndex = min($startIndex + $maxSlotForItemSell, $totalItems);

    $i = 0; // index inventory
    $i_shop = $startIndex; // index item in shop
    foreach ($schema['slots'] as $nameId) {
      $_ni = explode(':', $nameId); // nameId splited
      $by = $_ni[0]; // like 'minecraft' and 'mineshop'
      $value = $_ni[1];
      switch ($by) {
        case 'minecraft':
          $item = StringToItemParser::getInstance()->parse($nameId)->setCustomName('/');
          if ($item !== null) $menu->getInventory()->setItem($i, $item);
          break;
        case 'mineshop':
          switch ($value) {
            case 'slot':
              if ($i_shop < $endIndex) {
                $item = $shops[$i_shop]['item'];
                $menu->getInventory()->setItem($i, MenuButton::getItemButton($i_shop, $shops[$i_shop]));
                $i_shop++;
              }
              break;
            case 'info':
              $menu->getInventory()->setItem($i, MenuButton::getInfoButton($player));
              break;
            case 'previous':
              // if ($page > 1) {
                $menu->getInventory()->setItem($i, MenuButton::getPreviousButton($page, $maxPage));
              // }
              break;
            case 'next':
              // if ($page < $maxPage) {
                $menu->getInventory()->setItem($i, MenuButton::getNextButton($page, $maxPage));
              // }
              break;
          }
          break;
      }
      $i++;
    }

    $menu->send($player);
  }

  public function sellMenu(Player $player): void{
    $economies = $this->plugin->getEconomies();
    $form = new CustomForm(function(Player $player, ?array $data) use($economies){
      $item = $player->getInventory()->getItemInHand();
      if($item->isNull()) {
        $player->sendMessage(MineShop::$PREFIX . TextFormat::RED . 'Please hold item in your hand!');
        return;
      }

      $prices = [];
      for($i = 0; $i < count($economies); $i++){
        $economyItemNameId = $economies[$i];
        if(!is_numeric($data[$i + 1])){
          $player->sendMessage(MineShop::$PREFIX . TextFormat::RED . 'The price of ore must be a number!');
          return;
        }
        $economyItemPrice = (int) $data[$i + 1];

        if($economyItemNameId > 0){
          $prices[$economyItemNameId] = $economyItemPrice;
        }
      }
      if(count($prices) < 1){
        $player->sendMessage(MineShop::$PREFIX . TextFormat::RED . 'Set one of the ore prices!');
        return;
      }

      $this->plugin->sellItem($item, $prices);
      $player->sendMessage(MineShop::$PREFIX . TextFormat::RED . 'Set one of the ore prices!');
    });
    $form->setTitle(TextFormat::BOLD . 'MineShop [Sell]');
    $form->addLabel('Hold item in your hand! \n \n Set the ore price to 0 if you dont sell the ore!');
    foreach($economies as $itemNameId){
      $item = StringToItemParser::getInstance()->parse($itemNameId);
      if($item === null) continue;
      $form->addInput('Price ' . $item->getName(), 'amount price', '0');
    }
    $player->sendForm($form);
  }
}
