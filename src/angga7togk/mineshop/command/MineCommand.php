<?php

namespace angga7togk\mineshop\command;

use angga7togk\mineshop\MineShop;
use angga7togk\mineshop\ui\MineMenu;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class MineCommand extends Command
{

  public function __construct(private MineShop $plugin)
  {
    parent::__construct('mineshop', 'Open the mine shop menu', '/mineshop', ['mshop']);
    $this->setPermission('mineshop.command');
  }

  public function execute(CommandSender $sender, string $commandLabel, array $args)
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
          if ($sender->hasPermission('mineshop.command.sell') || $sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            (new MineMenu($this->plugin))->sellMenu($sender);
            return;
          }
          break;
        case 'unsell':
        case 'remove':
        case 'delete':
          if ($sender->hasPermission('mineshop.command.unsell') || $sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            (new MineMenu($this->plugin))->open($sender, true);
            return;
          }
          break;
        default:
          if ($sender->hasPermission('mineshop.command.sell') || $sender->hasPermission('mineshop.command.unsell') || $sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $sender->sendMessage(MineShop::$PREFIX . TextFormat::RED . 'Usage: /mineshop <sell|unsell>');
          }
          break;
      }
    } else {
      (new MineMenu($this->plugin))->open($sender);
    }
  }
}
