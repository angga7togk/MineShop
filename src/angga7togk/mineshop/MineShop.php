<?php

namespace angga7togk\mineshop;

use angga7togk\mineshop\command\MineCommand;
use angga7togk\mineshop\manager\EconomyManager;
use angga7togk\mineshop\manager\ShopManager;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;

class MineShop extends PluginBase
{
  use SingletonTrait;

  private array $economies = [];
  private array $guiSchema;

  private EconomyManager $economyManager;
  private ShopManager $shopManager;

  public static string $PREFIX;

  public function onLoad(): void
  {
    self::setInstance($this);
  }

  public function onEnable(): void
  {
    $this->saveDefaultConfig();
    $cfg = $this->getConfig();
    self::$PREFIX = TextFormat::colorize($cfg->get('prefix')) . " " . TextFormat::RESET;

    // Register GUI Schema
    $this->saveResource("gui/{$cfg->get('gui-schema', 'default')}.json");
    $this->guiSchema = (new Config($this->getDataFolder() . "gui/{$cfg->get('gui-schema', 'default')}.json", Config::JSON))->getAll();


    // Register Economie Ores
    foreach ($cfg->get('economies', []) as $itemName) {
      $itemEconomy = StringToItemParser::getInstance()->parse($itemName);
      $this->economies[$itemEconomy->getTypeId()] = $itemEconomy;
    }

    $this->economyManager = new EconomyManager($this);
    $this->shopManager = new ShopManager($this);

    // Register InvMenu
    if (!InvMenuHandler::isRegistered()) {
      InvMenuHandler::register($this);
    }

    $this->getServer()->getCommandMap()->register('mineshop', new MineCommand($this));
  }

  /** @return array<int, Item>
   *  example: array<itemTypeId, Item>
   * */
  public function getEconomies(): array
  {
    return $this->economies;
  }

  public function getGUISchema(): array
  {
    return $this->guiSchema;
  }

  public function getShopManager(): ShopManager
  {
    return $this->shopManager;
  }

  public function getEconomyManager(): EconomyManager
  {
    return $this->economyManager;
  }
}
