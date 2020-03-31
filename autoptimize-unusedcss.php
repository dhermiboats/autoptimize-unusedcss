<?php
/*
Plugin Name: Autoptimize UnusedCSS Power-Up
Plugin URI:  unusedcss.io
Description: Removes Unused CSS from your website pages.
Version:     1.0.0
Author:      Shakeeb Sadikeen
Author URI:  http://shakee93.me/
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'UUCSS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'UUCSS_PLUGIN_FILE', __FILE__ );

require('vendor/autoload.php');
require('includes/UnusedCSS_Utils.php');
require('includes/UnusedCSS_Api.php');
require('includes/UnusedCSS_Store.php');
require('includes/UnusedCSS.php');
require('includes/Autoptimize/UnusedCSS_Autoptimize.php');
require('includes/Autoptimize/UnusedCSS_Autoptimize_Admin.php');

$ao_uucss = new UnusedCSS_Autoptimize();

if (is_admin()) {
    new UnusedCSS_Autoptimize_Admin($ao_uucss);
}

