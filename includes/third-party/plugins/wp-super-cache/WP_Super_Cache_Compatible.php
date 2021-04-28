<?php

defined( 'ABSPATH' ) or die();

class WP_Super_Cache_Compatible extends RapidLoad_ThirdParty {

    function __construct(){

        $this->plugin = 'wp-super-cache/wp-cache.php';
        $this->catgeory = 'cache';
        $this->name = 'wp-super-cache';

        parent::__construct();
    }

    public function init_hooks(){

        add_action( 'uucss/cached', [$this, 'handle'], 10, 2 );
        add_action( 'uucss/cache_cleared', [$this, 'handle'], 10, 2 );

    }

    public function handle($args){

        if ( function_exists( 'wpsc_delete_url_cache' ) ) {

            $url = null;

            if ( isset( $args['url'] ) ) {
                $url = $this->transform_url( $args['url'] );
            }

            if ( $url ) {

                wpsc_delete_url_cache($url);
                self::log([
                    'url' => $url,
                    'log' => 'wp-super-cache post url page cache cleared',
                    'type' => 'purging'
                ]);

            }

        }

    }

    public function is_mu_plugin()
    {
        return false;
    }
}