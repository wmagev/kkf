<?php
class KoiPricing_Admin {
    private static $initiated = false;
    private static $inventory_meta_data = [
        '_inventory_sku' => 'SKU',
        '_inventory_send_to_production_date' => 'Send To Production Date',
        '_inventory_production_post_id' => 'Production Post ID',
        '_inventory_menu_order' => 'Menu Order',
        '_inventory_price' => 'Price',
        '_inventory_purchase_price' => 'Purchase Price',
        '_inventory_stock_qty' => 'Stock Qty',
        '_inventory_unit' => 'Unit',
        '_inventory_born_in' => 'Born In',
        '_inventory_size' => 'Size',
        '_inventory_gender' => 'Gender',
        '_inventory_from_pond' => 'From Pond',
        '_inventory_to_pond' => 'To Pond',
        '_inventory_primary_photo' => 'Primary Photo',
        '_inventory_additional_photo(s)' => 'Additional Photo(s)',
        '_inventory_estimated_price' => 'Estimated Price',
        '_inventory_start_price' => 'Start Price',
        '_inventory_reserve_price' => 'Reserve Price'
    ];

    public static function init() {
		if ( ! self::$initiated ) {
            self::init_hooks();
		}
    }
    private static function init_hooks() {
        self::$initiated = true;
        // add_filter( 'manage_inventory_posts_columns', array( 'KoiPricing_Admin', 'set_custom_edit_inventory_columns' ) );
        add_action( 'add_meta_boxes', array( 'KoiPricing_Admin', 'inventory_add_custom_box') );        
        add_action( 'admin_enqueue_scripts', array( 'KoiPricing_Admin', 'enqueue_admin_script' ));
        add_action( 'save_post', array( 'KoiPricing_Admin', 'inventory_save_meta_data' ) );
    }
 
    public static function set_custom_edit_inventory_columns($columns) {        
        foreach( self::$inventory_meta_data as $meta_key => $meta_label ) {
            $columns[$meta_key] = __( $meta_label, $meta_key );
        }        
    
        return $columns;
    }
    public static function inventory_add_custom_box() {
        add_meta_box(
            'inventory_box_id',                 // Unique ID
            'Inventory Meta Data',      // Box title
            array( 'KoiPricing_Admin', 'inventory_meta_box_html' ),  // Content callback, must be of type callable
            'inventory'                            // Post type
        );    
    }

    public static function inventory_meta_box_html( $post ) {
        ?>
        <div class="_invetory_meta_container">
        <?php       
        foreach (self::$inventory_meta_data as $meta_key => $meta_label) :            
            $meta_value = get_post_meta( $post->ID, $meta_key, true );
        ?>
            <div class="<?= $meta_key ?>_field _inventory_custom_meta">
                <label for="<?= $meta_key ?>">
                    <?= $meta_label ?> : 
                </label>
                <input 
                    name="<?= $meta_key ?>"
                    id="<?= $meta_key ?>"
                    value="<?= $meta_value ?>"
                />
            </div>       
        <?php
        endforeach;
        ?>
        </div>
        <?php
    }

    public static function inventory_save_meta_data( $post_id ) {
        foreach( self::$inventory_meta_data as $meta_key => $meta_label) {
            if ( array_key_exists( $meta_key, $_POST ) ) {
                error_log($meta_key.' is submitted with value ->'.$_POST[$meta_key]);
                update_post_meta(
                    $post_id,
                    $meta_key,
                    $_POST[$meta_key]
                );
            }
        }
    }

    public static function enqueue_admin_script( $hook ) {
        if ( 'post.php' != $hook ) {
            return;
        }
        wp_enqueue_script( 'koi_admin_script', plugin_dir_url( __FILE__ ) . 'assets/js/admin-script.js', array(), '1.0' );
        wp_register_style( 'koi_admin_css', plugin_dir_url( __FILE__ ) . 'assets/css/admin-style.css', false, '1.0.0' );
        wp_enqueue_style( 'koi_admin_css' );
    }
}