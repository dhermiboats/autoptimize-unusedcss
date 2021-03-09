<?php

defined( 'ABSPATH' ) || exit;

abstract class RapidLoad_ThirdParty
{
    use UnusedCSS_Utils;

    public $plugin = null;
    public $catgeory = null;
    public $name = null;
    public $is_mu_plugin = false;

    public function __construct(){

        if($this->is_exists()){
            $this->register_plugin();
            $this->init_hooks();
        }
    }

    abstract public function init_hooks();

    public function is_exists(){
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        if(function_exists('is_plugin_active') && is_plugin_active($this->plugin) || $this->is_mu_plugin){
            return true;
        }
        return false;
    }

    abstract public function handle($args);

    public function register_plugin(){

        add_filter('uucss/third-party/plugins', function ($plugins){
            $plugins[] = [
                'category' => $this->catgeory,
                'plugin' => $this->name
            ];
            return $plugins;
        }, 10, 1 );

    }

    public static function initialize(){

        $third_party_plugins_dir = plugin_dir_path(UUCSS_PLUGIN_FILE) . '/includes/third-party/plugins';

        $class_iterator = new RecursiveTreeIterator(new RecursiveDirectoryIterator($third_party_plugins_dir, RecursiveDirectoryIterator::SKIP_DOTS));

        foreach($class_iterator as $class_path) {

            if(substr_compare($class_path, '.php', -strlen('.php')) === 0){

                $class = str_replace('.php', '', basename($class_path));

                if(class_exists($class)){
                    new $class;
                }
            }
        }

    }

}