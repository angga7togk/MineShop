<?php

namespace angga7togk\mineshop\ui;

use angga7togk\mineshop\MineShop;
use angga7togk\mineshop\model\OreEconomy;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\ModalForm;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class MineMenu
{
  public function __construct(private readonly MineShop $plugin) {}

  public function open(Player $player, bool $unsellMode = false, int $page = 1): void
  {
    $schema = $this->plugin->getGUISchema();
    $shops = $this->plugin->getShopManager()->getShop();

    $menu = InvMenu::create($schema['type'] === 'CHEST' ? InvMenu::TYPE_CHEST : InvMenu::TYPE_DOUBLE_CHEST);
    $menu->setName($unsellMode ? TextFormat::BOLD . TextFormat::RED . 'Unsell Mode' : TextFormat::colorize($schema['name']));
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
                $menu->getInventory()->setItem($i, MenuButton::getItemButton($i_shop, $shops[$i_shop], $unsellMode));
                $i_shop++;
              }
              break;
            case 'info':
              $menu->getInventory()->setItem($i, MenuButton::getInfoButton($player));
              break;
            case 'previous':
              $menu->getInventory()->setItem($i, MenuButton::getPreviousButton($page, $maxPage));
              break;
            case 'next':
              $menu->getInventory()->setItem($i, MenuButton::getNextButton($page, $maxPage));
              break;
          }
          break;
      }
      $i++;
    }

    // Menu Listener
    $menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction) use ($page, $maxPage, $unsellMode): void {
      $p = $transaction->getPlayer();
      $item = $transaction->getItemClicked();
      if ($item->getNamedTag()->getTag('mineshop') === null) {
        return;
      }

      $type = $item->getNamedTag()->getString('mineshop');
      switch ($type) {
        case 'selling':
          $p->removeCurrentWindow();
          $transaction->then(function (Player $player) use ($item, $unsellMode): void {
            $itemIndex = $item->getNamedTag()->getInt('mineshop_index');
            if($unsellMode){
              $this->askUnsellMenu($player, $itemIndex);
            }else{
              $this->askBuyMenu($player, $itemIndex);
            }
          });
          break;
        case 'previous':
          if ($page > 1) $this->open($p, $unsellMode, $page - 1);
          break;
        case 'next':
          if ($page < $maxPage) $this->open($p, $unsellMode, $page + 1);
          break;
      }
    }));

    $menu->send($player);
  }

  private function askUnsellMenu(Player $player, int $index): void{
    $form = new ModalForm(function (Player $player, ?bool $data) use ($index): void {
      if($data === null) return;
      if ($data) {
        $this->plugin->getShopManager()->unsellItem($index);
        $player->sendMessage(MineShop::$PREFIX . TextFormat::RED . 'Successfully removed item from shop.');
      }
    });

    $form->setTitle(TextFormat::BOLD . 'MineShop [Unsell]');
    $form->setContent('Are you sure you want to remove this item from shop?');
    $form->setButton1('Yes');
    $form->setButton2('No');
    $player->sendForm($form);
  }

  private function askBuyMenu(Player $player, int $index): void
  {
    $shop = $this->plugin->getShopManager()->getShop()[$index];
    $playerOres = $this->plugin->getEconomyManager()->getOres($player);

    $form = new ModalForm(function (Player $player, ?bool $data) use ($shop, $playerOres) {
      if($data === null) return;
      if ($data) {
        /** @var OreEconomy[] $reduceTask */
        $reduceTask = [];

        foreach ($shop->getPrices() as $oreEco) {
          $itemPrice = $oreEco->getItem();

          if (!isset($playerOres[$itemPrice->getTypeId()])) {
            $player->sendMessage(MineShop::$PREFIX . TextFormat::RED . 'You dont have enough ' . $itemPrice->getName() . ' to buy this item.');
            return;
          }

          $playerOre = $playerOres[$itemPrice->getTypeId()]->getAmount();
          if ($playerOre < $oreEco->getAmount()) {
            $player->sendMessage(MineShop::$PREFIX . TextFormat::RED . 'You dont have enough ' . $itemPrice->getName() . ' to buy this item.');
            return;
          }

          $reduceTask[] = $oreEco;
        }


        if (count($reduceTask) < 1) {
          $player->sendMessage(MineShop::$PREFIX . TextFormat::RED . 'You dont have enough ores to buy this item.');
          return;
        }

        foreach ($reduceTask as $oreEco) {
          $this->plugin->getEconomyManager()->reduceOre($player, $oreEco);
        }

        $player->getInventory()->addItem($shop->getItem());
        $player->sendMessage(MineShop::$PREFIX . TextFormat::GREEN . 'Successfully bought ' . $shop->getItem()->getName() . '.');
      }
    });
    $form->setTitle(TextFormat::BOLD . 'MineShop [Buy]');
    $form->setContent('Are you sure you want to buy this item?');
    $form->setButton1('Yes');
    $form->setButton2('No');
    $player->sendForm($form);
  }

  public function sellMenu(Player $player): void
  {
    $economies = array_keys($this->plugin->getEconomies());

    $form = new CustomForm(function (Player $player, ?array $data) use ($economies) {
      if ($data === null) return;

      $item = $player->getInventory()->getItemInHand();
      if ($item->isNull()) {
        $player->sendMessage(MineShop::$PREFIX . TextFormat::RED . 'Please hold item in your hand!');
        return;
      }

      /** @var OreEconomy[] $prices */
      $prices = [];
      for ($i = 0; $i < count($economies); $i++) {
        $itemTypeId = $economies[$i];
        if (!is_numeric($data[$i + 1])) {
          $player->sendMessage(MineShop::$PREFIX . TextFormat::RED . 'The price of ore must be a number!');
          return;
        }
        $itemPrice = (int) $data[$i + 1];

        if ($itemPrice > 0) {
          $prices[] = new OreEconomy($this->plugin->getEconomies()[$itemTypeId], $itemPrice);
        }
      }
      if (count($prices) < 1) {
        $player->sendMessage(MineShop::$PREFIX . TextFormat::RED . 'Set one of the ore prices!');
        return;
      }

      $this->plugin->getShopManager()->sellItem($item, $prices);
      $player->sendMessage(MineShop::$PREFIX . TextFormat::GREEN . 'Successfully selling ' . $item->getName() . '.');
    });
    $form->setTitle(TextFormat::BOLD . 'MineShop [Sell]');
    $form->addLabel('Set the ore price to 0 if you dont sell the ore!');
    foreach ($economies as $itemTypeId) {
      $item = $this->plugin->getEconomies()[$itemTypeId];
      $form->addInput('Price ' . $item->getName(), 'amount price', '0');
    }
    $player->sendForm($form);
  }
}
