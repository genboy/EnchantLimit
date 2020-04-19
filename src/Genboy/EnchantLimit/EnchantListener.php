<?php
/** src/genboy/EnchantLimit/EnchantListener.php
 *
 * global listener
 *
 */
declare(strict_types = 1);

namespace Genboy\EnchantLimit;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;

use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\inventory\Inventory;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\inventory\InventoryOpenEvent;

//use pocketmine\event\inventory\InventoryCloseEvent;
//use pocketmine\event\player\PlayerInteractEvent;
//use pocketmine\event\entity\EntityEffectAddEvent;
//use pocketmine\event\entity\EntityInventoryChangeEvent;
//use pocketmine\event\inventory\InventoryTransactionEvent;
//use pocketmine\event\entity\EntityArmorChangeEvent;
//use pocketmine\event\entity\EntityInventoryChangeEvent;

class EnchantListener implements Listener {

    public $plugin;

    public function __construct(EnchantLimit $owner) {

        $this->plugin = $owner;
        $this->plugin->getLogger()->info( "test: EnchantLimit Listener Loaded"  );

    }

    public function onJoin(PlayerJoinEvent $event){
            $player = $event->getPlayer();
            $inv = $player->getInventory();
            $arm = $player->getArmorInventory();

            //$player->sendMessage('Start inventory');
            $this->plugin->checkInventory( $player, $inv );
            $this->plugin->checkInventory( $player, $arm );
    }

    public function onInventoryOpen(InventoryOpenEvent $event) {

        $player = $event->getPlayer();
        if( $player instanceof Player){
            $inv = $player->getInventory();
            $arm = $player->getArmorInventory();

            //$player->sendMessage('Open inventory');

            $this->plugin->checkInventory( $player, $inv );
            $this->plugin->checkInventory( $player, $arm );

        }

    }


    public function onHold(PlayerItemHeldEvent $event): void {

        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();

        //$player->sendMessage('Hold item');

        $inv = $player->getInventory();
        $arm = $player->getArmorInventory();
        $this->plugin->checkInventory( $player, $inv );
        $this->plugin->checkInventory( $player, $arm );

    }

    // https://forums.pmmp.io/threads/inventorytransactionevent.4025/
    // https://forums.pmmp.io/threads/help-inventorytransactionevent.9153/
    // https://forums.pmmp.io/threads/transactions.1738/

}



