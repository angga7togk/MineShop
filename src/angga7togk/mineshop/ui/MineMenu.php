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
  public function __construct(private MineShop $plugin) {}

  public function open(Player $player, MenuMode $mode = MenuMode::NORMAL, int $page = 1): void
  {
    $schema = $this->plugin->getGUISchema();
    $shops = $this->plugin->getShopManager()->getShop();

    $menu = InvMenu::create($schema['type'] === 'CHEST' ? InvMenu::TYPE_CHEST : InvMenu::TYPE_DOUBLE_CHEST);
    $maxSlotForItemSell = $schema['max_slot'];

    $menuName = match ($mode) {
      MenuMode::DELETING => TextFormat::BOLD . TextFormat::RED . 'Delete Mode',
      MenuMode::EDITING => TextFormat::BOLD . TextFormat::GREEN . 'Edit Mode',
      default => TextFormat::colorize($schema['name']),
    };
    $menu->setName($menuName);

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
                $menu->getInventory()->setItem($i, MenuButton::getItemButton($i_shop, $shops[$i_shop], $mode));
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
    $menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction) use ($page, $maxPage, $mode): void {
      $p = $transaction->getPlayer();
      $item = $transaction->getItemClicked();
      if ($item->getNamedTag()->getTag('mineshop') === null) {
        return;
      }

      $type = $item->getNamedTag()->getString('mineshop');
      switch ($type) {
        case 'selling':
          $p->removeCurrentWindow();
          $transaction->then(function (Player $player) use ($item, $mode): void {
            $itemIndex = $item->getNamedTag()->getInt('mineshop_index');
            if ($mode === MenuMode::DELETING) {
              $this->askDeleteMenu($player, $itemIndex);
            } else if ($mode === MenuMode::EDITING) {
              $this->sellMenu($player, $itemIndex);
            } else {
              $this->askBuyMenu($player, $itemIndex);
            }
          });
          break;
        case 'previous':
          if ($page > 1) $this->open($p, $mode, $page - 1);
          break;
        case 'next':
          if ($page < $maxPage) $this->open($p, $mode, $page + 1);
          break;
      }
    }));

    $menu->send($player);
  }

  private function askDeleteMenu(Player $player, int $index): void
  {
    $form = new ModalForm(function (Player $player, ?bool $data) use ($index): void {
      if ($data === null) return;
      if ($data) {
        $this->plugin->getShopManager()->unsellItem($index);
        $player->sendMessage(MineShop::$PREFIX . TextFormat::RED . 'Successfully removed item from shop.');
      }
    });

    $form->setTitle(TextFormat::BOLD . 'MineShop [Deleting]');
    $form->setContent('Are you sure you want to remove this item from shop?');
    $form->setButton1('Yes');
    $form->setButton2('No');
    $player->sendForm($form);
  }

  private function askBuyMenu(Player $player, int $index): void
  {
    $shop = $this->plugin->getShopManager()->getShop()[$index];
    $playerOres = $this->plugin->getEconomyManager()->getOres($player);

    $form = new ModalForm(function (Player $player, bool $data) use ($shop, $playerOres) {
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

  /**
   * input index if you want to edit item
   */
  public function sellMenu(Player $player, ?int $index = null): void
  {
    $economies = array_keys($this->plugin->getEconomies());
    $shops = $this->plugin->getShopManager()->getShop();

    $form = new CustomForm(function (Player $player, ?array $data) use ($economies, $index) {
      if ($data === null) return;

      $item = $index === null ? $player->getInventory()->getItemInHand() : $this->plugin->getShopManager()->getShop()[$index]->getItem();
      if ($item->isNull()) {
        $player->sendMessage(MineShop::$PREFIX . TextFormat::RED . 'Please hold item in your hand!');
        return;
      }

      /** @var OreEconomy[] $prices */
      $prices = [];
      for ($i = 0; $i < count($economies); $i++) {

        if (!isset(($data[$i + 1]))) continue;

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

      $this->plugin->getShopManager()->sellItem($item, $prices, $index);
      if ($index !== null) {
        $player->sendMessage(MineShop::$PREFIX . TextFormat::GREEN . 'Successfully editing ' . $item->getName() . '.');
      } else {
        $player->sendMessage(MineShop::$PREFIX . TextFormat::GREEN . 'Successfully selling ' . $item->getName() . '.');
      }
    });
    $form->setTitle(TextFormat::BOLD . ($index === null ? 'MineShop [Selling]' : 'MineShop [Editing]'));
    $form->addLabel('Set the ore price to 0 if you dont sell the ore!');
    $_i = 0;
    foreach ($economies as $itemTypeId) {
      $item = $this->plugin->getEconomies()[$itemTypeId];

      $price = $index !== null && isset($shops[$index]) && isset($shops[$index]->getPrices()[$_i]) ?
        strval($shops[$index]->getPrices()[$_i]->getAmount()) :
        '0';

      $form->addInput('Price ' . $item->getName(), 'amount price', $price);
      $_i++;
    }
    $player->sendForm($form);
  }
}
