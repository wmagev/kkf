<?php
/*
Plugin Name: Custom Koi Pricing   
Version: 1.2
Author: Mike Lovell   
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'KOI_PRICING__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// register_activation_hook( __FILE__, array( 'KoiPricing', 'plugin_activation' ) );
// register_deactivation_hook( __FILE__, array( 'KoiPricing', 'plugin_deactivation' ) );

require_once( KOI_PRICING__PLUGIN_DIR . 'class.koi-pricing.php' );

add_action( 'init', array( 'KoiPricing', 'init' ) );

if ( is_admin() ) {
	require_once( KOI_PRICING__PLUGIN_DIR . 'class.koi-pricing-admin.php' );
	add_action( 'init', array( 'KoiPricing_Admin', 'init' ) );
}