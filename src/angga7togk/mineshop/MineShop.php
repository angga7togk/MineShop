<?php

namespace angga7togk\mineshop;

use angga7togk\mineshop\command\MineCommand;
use angga7togk\mineshop\economy\MineEconomy;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\item\Item;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\world\format\io\GlobalItemDataHandlers;

class MineShop extends PluginBase
{
  private array $guiSchema;
  private Config $shop;
  private MineEconomy $economy;

  private static MineShop $instance;
  public static string $PREFIX;

  public function onLoad(): void {
    self::$instance = $this;
  }

  public function onEnable(): void
  {
    $this->saveDefaultConfig();
    $this->saveResource('shop.yml');
    $this->shop = new Config($this->getDataFolder() . 'shop.yml', Config::YAML, []);
    self::$PREFIX = $this->getConfig()->get('prefix');

    // Register GUI Schema
    $this->saveResource("gui/{$this->getConfig()->get('gui-schema', 'default')}.json");
    $this->guiSchema = (new Config($this->getDataFolder() . "gui/{$this->shop->get('gui-schema', 'default')}.json", Config::JSON))->getAll();

    $this->economy = new MineEconomy($this);

    // Register InvMenu
    if (!InvMenuHandler::isRegistered()) {
      InvMenuHandler::register($this);
    }

    $this->getServer()->getCommandMap()->register('mineshop', new MineCommand($this));
  }


  /**
   * @return array<int, array{
   *     item: Item,
   *     prices: array<string, int>
   * }>
   */
  public function getShop(): array
  {
    $shops = $this->shop->getAll();
    $formattedShops = [];

    foreach ($shops as $shop) {
      $formattedShops[] = [
        'item' => Item::legacyJsonDeserialize($shop['item']),
        'prices' => $shop['prices'],
      ];
    }

    return $formattedShops;
  }



  /** @param array<string, int> $price */
  public function sellItem(Item $item, array $prices): void
  {
    $itemArray = $item->jsonSerialize();
    $shops = $this->shop->getAll();
    $shops[] = ['item' => $itemArray, 'prices' => $prices];
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

  /** @return string[] */
  public function getEconomies(): array
  {
    return $this->getConfig()->get('economies', []);
  }

  public function getGUISchema(): array
  {
    return $this->guiSchema;
  }

  public function getEconomy(): MineEconomy
  {
    return $this->economy;
  }

  public static function getInstance(): MineShop
  {
    return self::$instance;
  }
}
