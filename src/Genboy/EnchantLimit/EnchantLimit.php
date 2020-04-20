<?php
declare(strict_types=1);

namespace Genboy\EnchantLimit;

use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;


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
        $this->configSetup();
        $this->hasEnchantPlugin();
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{

        if(!isset($args[0])){
			return false;
		}

        $playerName = strtolower($sender->getName());
		$action = strtolower($args[0]);
		$o = "";

        if( $sender instanceof Player && $sender->isOp() ){
            switch($action){

                // info
                case "help":
                case "info":
                    $o = TextFormat::LIGHT_PURPLE . "EnchantLimit default is". TextFormat::GREEN . " ". $this->config['settings']['limit'] . TextFormat::AQUA . " - commands: set the server default enchantment limit: /el default <number(int)> , choose display position limit warning: /el display <title|tip|msg|pop>";
                    $sender->sendMessage( $o );
                    return true;

                // default limit
                case "default":
                    if( isset( $args[1] )  ){
                        if( is_numeric( $args[1] ) ){

                            $this->config['settings']['limit'] = $args[1];
                            $this->helper->saveDataSet( "config", $this->config );
                            $o = TextFormat::YELLOW . "Set EnchantLimit default to". TextFormat::GREEN . " " . $args[1];

                            $inv = $sender->getInventory();
                            $arm = $sender->getArmorInventory();

                            $this->checkInventory( $sender, $inv );
                            $this->checkInventory( $sender, $arm );

                        }
                    }else{
                        $o = TextFormat::AQUA . "use: /el default <number (int)>";
                    }
                    $sender->sendMessage( $o );
                    return true;

                // limit warning display position
                case "display":
                    if( isset( $args[1] )  ){
                        if( $args[1] == 'title' || $args[1] == 'tip' || $args[1] == 'msg' || $args[1] == 'pop' ){
                            $this->config['settings']['display'] = $args[1];
                            $this->helper->saveDataSet( "config", $this->config );
                            $o = TextFormat::YELLOW . "EnchantLimit warning display position set to". TextFormat::GREEN . " ". $args[1];
                        }else{
                            $o = TextFormat::AQUA . "use: /el display <title|tip|msg|pop>";
                        }
                    }else{
                        $o = TextFormat::AQUA . "use: /el display <title|tip|msg|pop>";
                    }
                    $sender->sendMessage( $o );
                    return true;

                default:
                    return false;
            }
        }else{
            $o = TextFormat::RED . "Command not allowed!";
            $sender->sendMessage( $o );
            return false;
        }


	}

	public function onDisable() : void{

		$this->getLogger()->info("Bye");

	}

    // hasEnchantPlugin
    public function hasEnchantPlugin() : void{
        /* Filter out / choose  plugin (not really needed ([yet]) */
        $pluginNames = [ 'PiggyCustomEnchants', 'VanillaEnchantment' ];
        foreach( $pluginNames as $nm ){
            if( $this->helper->isPluginLoaded( $nm ) ){
                $this->getLogger()->info( "test: Plugin ". $nm ." available."  );
                $this->getLogger()->info( "test: Innitiating Limit Control for ". $nm ."."  );
                $this->usedplugin = $nm;
                break;
            }
        }
    }

    // configSetup
    public function configSetup(): void{
        $config = $this->helper->getDataSet( "config" ); // latest json type config file in datafolde
        if( isset( $config["settings"] ) && is_array( $config["settings"] ) ){
            $this->config = $config;
            if( !isset( $this->config['settings']['display'] ) ){
                $this->config['settings']['display'] = 'title'; // msg | title | tip | (popup)
            }
            $o = "test: Configuration ready!";
        }else{
            $this->config = [ 'settings' => [
                'limit' => 8,
                'display' => 'title',
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
            $slot = $key;
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
                    $this->areaMessage( TextFormat::RED . 'Enchant Limit Reached!' , $player );
                }
            }

            $info = $item->getLore();
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

    /** AreaMessage
    * @param string $msg
    */
	public function areaMessage( $msg , $player ){
        if($this->config['settings']['display'] == 'msg'){
            $player->sendMessage($msg);
        }else if( $this->config['settings']['display'] == 'title'){
            $player->addTitle($msg);
            // $player->addTitle("Title", "Subtitle", $fadeIn = 20, $duration = 60, $fadeOut = 20);
        }else if($this->config['settings']['display'] == 'tip'){
			$player->sendTip($msg);
		}else{
			$player->sendPopup($msg);
		}
        return true;
	}

}
