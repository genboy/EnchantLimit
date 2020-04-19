<?php declare(strict_types = 1);
/** src/genboy/EnchantLimit/Helper.php
 *
 */
namespace Genboy\EnchantLimit;

use pocketmine\math\Vector3;

class Helper {

    private $plugin;

    public function __construct( EnchantLimit $plugin){

        $this->plugin = $plugin;
    }


    // getServerInfo
    public function getServerInfo() : ARRAY {
        $s = [];
        $s['ver']   = $this->plugin->getServer()->getVersion();
        $s['api']   = $this->plugin->getServer()->getApiVersion();
        return $s;
    }

    // isPluginLoaded
    public function isPluginLoaded(string $pluginName){

        return ($findplugin = $this->plugin->getServer()->getPluginManager()->getPlugin($pluginName)) !== null and $findplugin->isEnabled();

    }

    // get Dataset
    public function getDataSet( $name , $type = 'json' ) : ARRAY {
        if( file_exists($this->plugin->getDataFolder() . $name . ".". $type)){
            switch( $type ){
                case 'yml':
                case 'yaml':
                    $data = yaml_parse_file($this->plugin->getDataFolder() . $name . ".yml"); // the old defaults
                break;
                case 'json':
                default:
                    $data = json_decode( file_get_contents( $this->plugin->getDataFolder() . $name . ".json" ), true );
                break;
            }
        }
        if( isset( $data ) && is_array( $data ) ){
            return $data;
        }
        return [];
    }

    // save Dataset
    public function saveDataSet( $name, $data, $type = 'json') : ARRAY {
        switch( $type ){
            case 'yml':
            case 'yaml':
                 $src = new FileConfig($this->plugin->getDataFolder(). $name . ".yml", FileConfig::YAML, $data);
                 $src->save();
            break;
            case 'json':
            default:
		        file_put_contents( $this->plugin->getDataFolder() . $name . ".json", json_encode( $data ) );
            break;
        }
        return $this->getDataSet( $name , $type );
    }

}
