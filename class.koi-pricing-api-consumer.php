<?php

use Automattic\WooCommerce\Client;
class KoiPricing_APIConsumer {
    private static $initiated = false;
    private static $woo_client;

    public static function init() {
		if ( ! self::$initiated ) {
            self::init_woocommerce_client();
		}
    }

    public static function init_woocommerce_client() {
        self::$initiated = true;
        $api_options = get_option( 'koi_pricing_plugin_options' );
        
        self::$woo_client = new Client(
            $api_options['production_domain'],            
            $api_options['consumer_key'],
            $api_options['consumer_secret']
        );

        // self::validate_api_key();
    }

    public static function validate_api_key() {
        $results = self::$woo_client->get('customers');
        error_log(print_r($results, true));
    }
}