<?php

/**
 * Class UnusedCSS
 */
class UnusedCSS_Autoptimize extends UnusedCSS {


    protected $options = [];

    /**
     * UnusedCSS constructor. 
     */
    public function __construct()
    {
        $this->provider = 'autoptimize';

        add_action( 'autoptimize_action_cachepurged', [$this, 'clear_cache'] );

        parent::__construct();

    }


    public function enabled() {

        if (!UnusedCSS_Autoptimize_Admin::enabled()) {
            return false;
        }

        if(is_multisite()) {

            UnusedCSS_Utils::add_admin_notice("UnusedCSS not supported for multisite");

            return false;
        }

        if(!function_exists('autoptimize') || autoptimizeOptionWrapper::get_option( 'autoptimize_css' ) == "") {

            UnusedCSS_Utils::add_admin_notice("Autoptimize UnusedCSS Plugin only works when autoptimize is installed and css optimization is enabled");
            
            return false;
        }

        return true;
    }

    public function get_css(){


        add_action('autoptimize_filter_cache_getname', function($ao_css){
            $this->css[] = $ao_css;
        });
           
        
    }


    public function replace_css(){

        if (!$this->cache_source_dir_exists()) {
            return;
        }

        add_action('autoptimize_html_after_minify', function($html) {

//            foreach($this->purged_files as $file){
//                $html = str_replace($file->file, $this->cache_file_location($file->file, WP_CONTENT_URL . "/cache/uucss"), $html);
//            }

            $hash = $this->encode($this->url);

            foreach ($this->css as  $css) {

                $_css = str_replace('/autoptimize/css', "/uucss/$this->provider/$hash", $css);
                $html = str_replace($css, $_css, $html);

            }

            return $html;            
        });

        
    }

}
