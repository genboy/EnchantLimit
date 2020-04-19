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


class EnchantLimit extends PluginBase { //  implements Listener

    /** @var obj */
	public $helper; // helper class

	/** @var array[] */
	public $config = [];    // list of config settings

	/** @var string */
	public $usedplugin = '';    // used enchanment plugin

	/** @var string */
	public $piggyCE = false;    // used enchanment plugin

	public function onEnable() : void{

        $this->getServer()->getPluginManager()->registerEvents(new EnchantListener($this), $this);

        $this->helper = new Helper($this);

        $this->configSetup(); // might add translations

        $this->hasEnchantPlugin();

	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{

		switch($command->getName()){
            // add base commands like set default limit, add/remove active worlds
			case "enchantlimit":
				$sender->sendMessage("Example command output, plugin in progress");
                // change config limit
				return true;
			default:
				return false;
		}

	}

	public function onDisable() : void{

		$this->getLogger()->info("Bye");

	}

    /** hasEnchantPlugin
     * @func Main isPluginLoaded()a
     */
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

        if( $this->usedplugin == 'PiggyCustomEnchants'){

            $this->piggyCE = $this->getServer()->getPluginManager()->getPlugin("PiggyCustomEnchants");

        }

        // https://github.com/DaPigGuy/PiggyCustomEnchants/blob/044df614f676d140d399ebca5503679a4bfebc65/src/DaPigGuy/PiggyCustomEnchants/utils/Utils.php#L167

    }

    /** configSetup
	 * @class Helper
	 * @func Helper getSource
	 * @var $plugin->options
     */
    public function configSetup(): void{

        $config = $this->helper->getDataSet( "config" ); // latest json type config file in datafolde
        if( isset( $config["settings"] ) && is_array( $config["settings"] ) ){

            $this->config = $config;
            $o = "test: Configuration ready!";

        }else{

            $this->config = [ 'settings' => [
                'limit' => 8,
                'worlds' => []
            ] ];   // {"settings":{"limit":8,"worlds":[]}};
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
            //$repli = clone $item;
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

    // https://forums.pmmp.io/threads/enchants.2433/
    public function removeEnchantment(int $id, int $level = -1){
        if(!$this->hasEnchantments()){
            return;
        }
        $tag = $this->getNamedTag();
        foreach($tag->ench as $k => $entry){
            if($entry["id"] === $id){
                if($level === -1 or $entry["lvl"] === $level){
                    unset($tag->ench[$k]);
                    break;
                }
            }
        }
        $this->setNamedTag($tag);
    }


    // set item lore info
    public function setItemEnchantInfo( $item ) : void{
    }

}
