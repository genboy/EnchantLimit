<?php
/*
Ok great the small plugin is an enchant limit plugin,
it makes it so players can only add lets say the
[x] configs - max enchants is 8, they can only add 8 enchants on their items vanilla and custom enchants,
[ ] display - Enchants [0/8] on the lore of the items which is showing how many enchant slots they have used out of their available slots.
This could need to be compatible with PiggyCE and VanillaEnchants. Just ask if you need more information about this.

https://piggydocs.aericio.net/PiggyCustomEnchants.html

- [x] events to check inventory/armor enchants
- [x] way to change item lore (static?)
- [x] way to change remove enchantments (beyond limit)
*/

declare(strict_types=1);

namespace Genboy\EnchantLimit;

use Genboy\EnchantLimit\Helper;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use DaPigGuy\PiggyCustomEnchants\CustomEnchants\CustomEnchants;

use pocketmine\inventory\Inventory;
use pocketmine\inventory\ArmorInventory;
use pocketmine\event\inventory\InventoryEvent;

use pocketmine\Player;
use pocketmine\entity\Entity;


class EnchantLimit extends PluginBase {

    /** @var helper */
	public $helper; // helper class

	/** @var array[] */
	public $config = [];    // list of config settings

	/** @var string */
	public $usedplugin = '';    // used enchanment plugin


	public function onEnable() : void{

        $this->getServer()->getPluginManager()->registerEvents(new EnchantListener($this), $this);

        $this->helper = new Helper($this);

        $this->configSetup(); // might add translations

        $this->hasEnchantPlugin();

	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{

        if(!isset($args[0])){
			return false;
		}

        $playerName = strtolower($sender->getName());

		$action = strtolower($args[0]);
		$o = "";

        if($sender->isOp()){

            switch($action){

                case "set":
                    if( isset( $args[1] )  ){
                        if( is_numeric( $args[1] ) ){
                            $this->config['settings']['limit'] = $args[1];
                            $this->helper->saveDataSet( "config", $this->config );
                            $o = "Set EnchantLimit to ". $args[1];
                        }
                    }else{
                        $o = "use: /enchantLimit set <number (int)> ";
                    }
                    return true;
                default:
                    return false;
            }
        }else{
            $o = "Command not allowed!";
        }
        $sender->sendMessage( $o );

	}

	public function onDisable() : void{

		$this->getLogger()->info("Bye");

	}

    // hasEnchantPlugin
    public function hasEnchantPlugin() : void{

        /* Filter out / choose  plugin */
        $pluginNames = [ 'PiggyCustomEnchants', 'VanillaEnchantment' ];
        foreach( $pluginNames as $nm ){
            if( $this->helper->isPluginLoaded( $nm ) ){
                $this->getLogger()->info( "test: Plugin ". $nm ." available."  );
                $this->getLogger()->info( "test: Innitiating Limit Control for ". $nm ."."  );
                $this->usedplugin = $nm;
                break;
            }
        }
        // https://github.com/DaPigGuy/PiggyCustomEnchants/blob/044df614f676d140d399ebca5503679a4bfebc65/src/DaPigGuy/PiggyCustomEnchants/utils/Utils.php#L167

    }

    // configSetup
    public function configSetup(): void{

        $config = $this->helper->getDataSet( "config" ); // latest json type config file in datafolde
        if( isset( $config["settings"] ) && is_array( $config["settings"] ) ){

            $this->config = $config;
            $o = "test: Configuration ready!";

        }else{

            $this->config = [ 'settings' => [
                'limit' => 8,
                'worlds' => []
            ] ];
            $o = "test: Default configuration loaded!";
        }

        $this->helper->saveDataSet( "config", $this->config );
        $this->getLogger()->info( $o );

    }


    // check inventory items enchantments / add lore
    public function checkInventory( $player, $inv  ) : void{
        $contents = $inv->getContents();
        foreach($inv->getContents() as $key => $item) {
            $slot = $key; //array_search($item, $contents); // https://forums.pmmp.io/threads/get-item-slot.2186/
            $this->checkItem( $player, $item, $inv, $slot );
        }
    }


    // check item enchantments
    public function checkItem( $player, $item, $inv, $slot ) : void{

        if ($item->hasEnchantments()) {

            $enchanted = 0;
            $limit = $this->config['settings']['limit'];
            $level = 0 ;

            foreach($item->getEnchantments() as $enchantm) {

                if( $enchanted < $limit){
                    $enchanted++;
                }else{
                    $id = $enchantm->getId();
                    $item->removeEnchantment( $id );
                    $player->sendMessage('Enchant Limit Reached!');

                }


            }

            $info = $item->getLore(); //$item->setCustomName('test');
            foreach( $info as $i => $line ){
                if (strpos($line, "Enchant Limit") !== false) {
                    unset($info[$i]);
                }
            }
            $info[] = "Enchant Limit [" . $enchanted . "/" . $limit . "]";
            $item->setLore($info);
            $inv->setItem($slot, $item);

        }

    }

}
