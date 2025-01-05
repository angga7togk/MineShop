<?php

namespace angga7togk\mineshop\command;

use angga7togk\mineshop\MineShop;
use angga7togk\mineshop\ui\MenuMode;
use angga7togk\mineshop\ui\MineMenu;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use pocketmine\utils\TextFormat;

class MineCommand extends Command implements PluginOwned
{
  use PluginOwnedTrait;

  public function __construct(MineShop $plugin)
  {
    parent::__construct('mineshop', 'Open the mine shop menu', '/mineshop', ['mshop']);
    $this->setPermission('mineshop.command');

    $this->owningPlugin = $plugin;
  }

  public function execute(CommandSender $sender, string $commandLabel, array $args): void
  {
    if (!$sender instanceof Player) {
      $sender->sendMessage(MineShop::$PREFIX . TextFormat::RED . 'Please use this command in-game!');
      return;
    }

    if (isset($args[0])) {
      switch (strtolower($args[0])) {
        case 'sell':
        case 'set':
        case 'add':
          if ($sender->getInventory()->getItemInHand()->isNull()) {
            $sender->sendMessage(MineShop::$PREFIX . TextFormat::RED . 'Please hold item in your hand!');
            return;
          }
          if ($sender->hasPermission('mineshop.command.add') || $sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            (new MineMenu($this->owningPlugin))->sellMenu($sender);
            return;
          }
          break;
        case 'unsell':
        case 'remove':
        case 'delete':
          if ($sender->hasPermission('mineshop.command.delete') || $sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            (new MineMenu($this->owningPlugin))->open($sender, MenuMode::DELETING);
            return;
          }
          break;
        case 'edit':
          if ($sender->hasPermission('mineshop.command.edit') || $sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            (new MineMenu($this->owningPlugin))->open($sender, MenuMode::EDITING);
            return;
          }
          break;
        default:
          if ($sender->hasPermission('mineshop.command.add') || $sender->hasPermission('mineshop.command.delete') || $sender->hasPermission('mineshop.command.edit') || $sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $sender->sendMessage(MineShop::$PREFIX . TextFormat::RED . 'Usage: /mineshop <set|edit|delete>');
          }
          break;
      }
    } else {
      (new MineMenu($this->owningPlugin))->open($sender);
    }
  }
}
