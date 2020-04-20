<?php
// src/genboy/EnchantLimit/EnchantListener.php
declare(strict_types = 1);

namespace Genboy\EnchantLimit;

use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\inventory\InventoryOpenEvent;

class EnchantListener implements Listener {

    public $plugin;

    public function __construct(EnchantLimit $owner) {

        $this->plugin = $owner;

    }

    public function onJoin(PlayerJoinEvent $event){
            $player = $event->getPlayer();
            $inv = $player->getInventory();
            $arm = $player->getArmorInventory();

            $this->plugin->checkInventory( $player, $inv );
            $this->plugin->checkInventory( $player, $arm );
    }

    public function onInventoryOpen(InventoryOpenEvent $event) {

        $player = $event->getPlayer();

        if( $player instanceof Player){

            $inv = $player->getInventory();
            $arm = $player->getArmorInventory();

            $this->plugin->checkInventory( $player, $inv );
            $this->plugin->checkInventory( $player, $arm );

        }

    }

    public function onHold(PlayerItemHeldEvent $event): void {

        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();

        $inv = $player->getInventory();
        $arm = $player->getArmorInventory();
        $this->plugin->checkInventory( $player, $inv );
        $this->plugin->checkInventory( $player, $arm );

    }

}
