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
        add_action( 'add_meta_boxes', array( 'KoiPricing_Admin', 'inventory_add_custom_box' ) );        
        add_action( 'admin_enqueue_scripts', array( 'KoiPricing_Admin', 'enqueue_admin_script' ) );
        add_action( 'save_post', array( 'KoiPricing_Admin', 'inventory_save_meta_data' ) );
        add_action( 'admin_footer-post.php', array( 'KoiPricing_Admin', 'inventory_append_post_status_list' ) );
        add_filter( 'display_post_states', array( 'KoiPricing_Admin', 'inventory_display_state' ) );

        add_action( 'admin_menu', array( 'KoiPricing_Admin', 'init_settings_page' ) );
        add_action( 'admin_init', array( 'KoiPricing_Admin', 'init_register_settings' ) );
        // Taxonomy Meta Data Hooks
        // -- Auction Group Taxonomy Meta
        add_action( 'auction_groups_add_form_fields', array( 'KoiPricing_Admin', 'add_form_field_auction_group_meta' ) );
        add_action( 'auction_groups_edit_form_fields', array( 'KoiPricing_Admin', 'edit_form_field_auction_group_meta' ) );

        add_action( 'edit_auction_groups',   array( 'KoiPricing_Admin', 'save_auction_group_meta' ) );
        add_action( 'create_auction_groups', array( 'KoiPricing_Admin', 'save_auction_group_meta' ) );

        add_filter( 'manage_edit-auction_groups_columns', array( 'KoiPricing_Admin', 'edit_auction_group_meta_columns'), 10, 3 );
        add_filter( 'manage_auction_groups_custom_column', array( 'KoiPricing_Admin', 'manage_tax_meta_custom_column'), 10, 3 );

        // -- Photo Group Taxonomy Meta
        add_action( 'photo_groups_add_form_fields', array( 'KoiPricing_Admin', 'add_form_field_photo_group_meta' ) );
        add_action( 'photo_groups_edit_form_fields', array( 'KoiPricing_Admin', 'edit_form_field_photo_group_meta' ) );

        add_action( 'edit_photo_groups',   array( 'KoiPricing_Admin', 'save_photo_group_meta' ) );
        add_action( 'create_photo_groups', array( 'KoiPricing_Admin', 'save_photo_group_meta' ) );

        add_filter( 'manage_edit-photo_groups_columns', array( 'KoiPricing_Admin', 'edit_photo_group_meta_columns'), 10, 3 );
        add_filter( 'manage_photo_groups_custom_column', array( 'KoiPricing_Admin', 'manage_tax_meta_custom_column'), 10, 3 );
    }

    public static function enqueue_admin_script( $hook ) {       
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script( 'koi_admin_script', plugin_dir_url( __FILE__ ) . 'assets/js/admin-script.js', array(), '1.0' );
        wp_localize_script( 'koi_admin_script', 'admin_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));

        wp_enqueue_script( 'koi_admin_reorder_script', plugin_dir_url( __FILE__ ) . 'assets/js/admin-auction-reorder.js', array(), '1.0' );
        wp_localize_script( 'koi_admin_reorder_script', 'admin_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));

        wp_enqueue_script( 'koi_admin_lightbox_script', plugin_dir_url( __FILE__ ) . 'assets/js/admin-lightbox.js', array(), '1.0' );

        wp_register_style( 'koi_admin_lightbox_style', plugin_dir_url( __FILE__ ) . 'assets/css/admin-lightbox.css', false, '1.0.0' );
        wp_register_style( 'koi_admin_auction_reorder', plugin_dir_url( __FILE__ ) . 'assets/css/admin-auction-reorder.css', false, '1.0.0' );
        wp_register_style( 'koi_admin_style', plugin_dir_url( __FILE__ ) . 'assets/css/admin-style.css', false, '1.0.0' );
        wp_enqueue_style( 'koi_admin_style' );
        wp_enqueue_style( 'koi_admin_auction_reorder' );
        wp_enqueue_style( 'koi_admin_lightbox_style' );
    }

    public static function init_settings_page() {
        add_options_page(
            'Koi Pricing',
            'Koi Pricing',
            'manage_options',
            'koi-pricing-setting',
            array( 'KoiPricing_Admin', 'koi_pricing_render_plugin_settings_page' )
        );
    }

    public static function init_register_settings() {
        register_setting( 'koi_pricing_plugin_options', 'koi_pricing_plugin_options',  array( 'KoiPricing_Admin', 'koi_pricing_plugin_options_validate') );
        add_settings_section( 'api_settings', 'API Settings', array( 'KoiPricing_Admin', 'koi_plugin_section_text'), 'koi_pricing_plugin' );
    
        add_settings_field( 'koi_plugin_setting_production_domain', 'Production Domain',  array( 'KoiPricing_Admin', 'koi_plugin_setting_production_domain' ), 'koi_pricing_plugin', 'api_settings' );
        add_settings_field( 'koi_plugin_setting_consumer_key', 'Consumer Key',  array( 'KoiPricing_Admin', 'koi_plugin_setting_consumer_key' ), 'koi_pricing_plugin', 'api_settings' );
        add_settings_field( 'koi_plugin_setting_consumer_secret', 'Consumer Secret',  array( 'KoiPricing_Admin', 'koi_plugin_setting_consumer_secret' ), 'koi_pricing_plugin', 'api_settings' );
        
    }
    public static function koi_pricing_plugin_options_validate( $input ) {
        // $newinput['consumer_key'] = trim( $input['consumer_key'] );
        // if ( ! preg_match( '/^[a-z0-9]{32}$/i', $newinput['consumer_key'] ) ) {
        //     $newinput['consumer_key'] = '';
        // }

        // $newinput['consumer_secret'] = trim( $input['consumer_secret'] );
        // if ( ! preg_match( '/^[a-z0-9]{32}$/i', $newinput['consumer_secret'] ) ) {
        //     $newinput['consumer_secret'] = '';
        // }
        // return $newinput;
        return $input;
    }

    public static function koi_plugin_section_text() {
        echo '<p>Here you can set all the options for using the API</p>';
    }

    public static function koi_plugin_setting_production_domain() {
        $options = get_option( 'koi_pricing_plugin_options' );
        echo "<input id='koi_plugin_setting_production_domain' class='koi-pricing-api-key-field' name='koi_pricing_plugin_options[production_domain]' type='text' value='".esc_attr( $options['production_domain'] )."' />";
    }
    
    public static function koi_plugin_setting_consumer_key() {
        $options = get_option( 'koi_pricing_plugin_options' );
        echo "<input id='koi_plugin_setting_consumer_key' class='koi-pricing-api-key-field' name='koi_pricing_plugin_options[consumer_key]' type='text' value='".esc_attr( $options['consumer_key'] )."' />";
    }

    public static function koi_plugin_setting_consumer_secret() {
        $options = get_option( 'koi_pricing_plugin_options' );
        echo "<input id='koi_plugin_setting_consumer_secret' class='koi-pricing-api-key-field' name='koi_pricing_plugin_options[consumer_secret]' type='text' value='".esc_attr( $options['consumer_secret'] )."' />";
    }

    public static function koi_pricing_render_plugin_settings_page() {
        ?>
        <h2>Koi Pricing Live Site API setting</h2>
        <form action="options.php" method="post">
            <?php 
                settings_fields( 'koi_pricing_plugin_options' );
                do_settings_sections( 'koi_pricing_plugin' ); 
            ?>
            <input name="submit" class="button button-primary koi-pricing-setting" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
        </form>
        <?php
    }

    public static function inventory_display_state( $states ) {
        global $post;
        $arg = get_query_var( 'post_status' );
        
        if(!array_key_exists($arg, KoiPricing::$post_statuses)){
            if(isset(KoiPricing::$post_statuses[$post->post_status]))
                return array(KoiPricing::$post_statuses[$post->post_status]);
        }
        return $states;
    }

    public static function inventory_append_post_status_list(){        
        global $post;
        
        $label = '';

        if($post->post_type == KoiPricing::INV_POST_TYPE){
            echo '
                <script>
                jQuery(document).ready(function($){
            ';
            foreach(KoiPricing::$post_statuses as $status_key => $status_label) {
                $complete = '';                
                if(array_key_exists($post->post_status, KoiPricing::$post_statuses)){
                    $complete = " selected='selected'";
                    $label = KoiPricing::$post_statuses[$post->post_status];
                
                }
                echo '
                    $("select#post_status").append("<option value=\"'.$status_key.'\" '.$complete.'>'.$status_label.'</option>");
                ';
            }
            if($label != '')
                echo '$(".misc-pub-section #post-status-display").text("'.$label.'");';
            echo '
                });
                </script>
            ';
        }
    }
 
    public static function set_custom_edit_inventory_columns($columns) {        
        foreach( self::$inventory_meta_data as $meta_key => $meta_label ) {
            $columns[$meta_key] = __( $meta_label, $meta_key );
        }        
    
        return $columns;
    }
    public static function inventory_add_custom_box() {
        add_meta_box(
            'inventory_box_id',         // Unique ID
            'Inventory Meta Data',      // Box title
            array( 'KoiPricing_Admin', 'inventory_meta_box_html' ),     // Content callback, must be of type callable
            'inventory'                 // Post type
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
                update_post_meta(
                    $post_id,
                    $meta_key,
                    $_POST[$meta_key]
                );
            }
        }
    }
    
    public static function sanitize_term_meta_text ( $value ) {
        return sanitize_text_field ($value);
    }

    public static function get_term_meta_value( $term_id, $meta_key ) {
        $value = get_term_meta( $term_id, $meta_key, true );
        $value = self::sanitize_term_meta_text( $value );
        return $value;
    }

    public static function add_form_field_auction_group_meta() { ?>
        <?php wp_nonce_field( basename( __FILE__ ), 'term_start_date_nonce' ); ?>
        <div class="form-field term-start-date-wrap">
            <label for="term-start-date"><?php _e( 'Start Date', 'start_date' ); ?></label>
            <input type="text" name="<?= KoiPricing::TERM_START_DATE ?>" id="term-start-date" value="" class="term-start-date-field datepicker" />
        </div>
        <?php wp_nonce_field( basename( __FILE__ ), 'term_end_date_nonce' ); ?>
        <div class="form-field term-end-date-wrap">
            <label for="term-end-date"><?php _e( 'End Date', 'end_date' ); ?></label>
            <input type="text" name="<?= KoiPricing::TERM_END_DATE ?>" id="term-end-date" value="" class="term-end-date-field datepicker" />
        </div>
    <?php }

    public static function add_form_field_photo_group_meta() { ?>
        <?php wp_nonce_field( basename( __FILE__ ), 'term_photo_date_nonce' ); ?>
        <div class="form-field term-photo-date-wrap">
            <label for="term-photo-date"><?php _e( 'Photo Date', 'photo_date' ); ?></label>
            <input type="text" name="<?= KoiPricing::TERM_PHOTO_DATE ?>" id="term-photo-date" value="" class="term-photo-date-field datepicker" />
        </div>
    <?php }
    

    public static function edit_form_field_auction_group_meta( $term ) {

        $start_date = self::get_term_meta_value( $term->term_id, KoiPricing::TERM_START_DATE );
        $end_date = self::get_term_meta_value( $term->term_id, KoiPricing::TERM_END_DATE );

        if ( ! $start_date )
            $start_date = "";
        if ( ! $end_date )
            $end_date = "";
        ?>

        <tr class="form-field term-start-date-wrap">
            <th scope="row"><label for="term-start-date"><?php _e( 'Start Date', 'start_date' ); ?></label></th>
            <td>
                <?php wp_nonce_field( basename( __FILE__ ), 'term_start_date_nonce' ); ?>
                <input type="text" name="<?= KoiPricing::TERM_START_DATE ?>" id="term-start-date" value="<?php echo esc_attr( $start_date ); ?>" class="term-start-date-field datepicker"  />
            </td>
        </tr>
        <tr class="form-field term-end-date-wrap">
            <th scope="row"><label for="term-end-date"><?php _e( 'End Date', 'end_date' ); ?></label></th>
            <td>
                <?php wp_nonce_field( basename( __FILE__ ), 'term_end_date_nonce' ); ?>
                <input type="text" name="<?= KoiPricing::TERM_END_DATE ?>" id="term-end-date" value="<?php echo esc_attr( $end_date ); ?>" class="term-end-date-field datepicker"  />
            </td>
        </tr>
    <?php }

    public static function edit_form_field_photo_group_meta( $term ) {

        $photo_date = self::get_term_meta_value( $term->term_id, KoiPricing::TERM_PHOTO_DATE );

        if ( ! $photo_date )
            $photo_date = "";
        ?>

        <tr class="form-field term-photo-date-wrap">
            <th scope="row"><label for="term-photo-date"><?php _e( 'Photo Date', 'photo_date' ); ?></label></th>
            <td>
                <?php wp_nonce_field( basename( __FILE__ ), 'term_photo_date_nonce' ); ?>
                <input type="text" name="<?= KoiPricing::TERM_PHOTO_DATE ?>" id="term-photo-date" value="<?php echo esc_attr( $photo_date ); ?>" class="term-photo-date-field datepicker"  />
            </td>
        </tr>
    <?php }


    public static function save_auction_group_meta( $term_id ) {
        foreach (
            [
                KoiPricing::TERM_START_DATE,
                KoiPricing::TERM_END_DATE
            ] as $meta_key
        ) 
        {
            self::update_tax_meta($term_id, $meta_key);
        }
    }

    public static function save_photo_group_meta( $term_id ) {
        self::update_tax_meta($term_id, KoiPricing::TERM_PHOTO_DATE);
    }    

    public static function edit_auction_group_meta_columns( $columns ) {

        $columns[KoiPricing::TERM_START_DATE] = __( 'Start Date', 'start_date' );
        $columns[KoiPricing::TERM_END_DATE] = __( 'End Date', 'end_date' );

        return $columns;
    }

    public static function edit_photo_group_meta_columns( $columns ) {

        $columns[KoiPricing::TERM_PHOTO_DATE] = __( 'Photo Date', 'photo_date' );        

        return $columns;
    }

    public static function manage_tax_meta_custom_column( $out, $column, $term_id ) {

        if ( in_array($column, [KoiPricing::TERM_START_DATE, KoiPricing::TERM_END_DATE, KoiPricing::TERM_PHOTO_DATE] )) {
            $value  = self::get_term_meta_value( $term_id, $column );

            if ( ! $value )
                $value = '';    
            $out = sprintf( '<span class="term-meta-text-block" style="" >%s</div>', esc_attr( $value ) );
        }
        return $out;
    }

    public static function update_tax_meta($term_id, $meta_key) {
        $old_value  = self::get_term_meta_value( $term_id, $meta_key );
        $new_value = isset( $_POST[$meta_key] ) ? self::sanitize_term_meta_text ( $_POST[$meta_key] ) : '';

        if ( $old_value && '' === $new_value )
            delete_term_meta( $term_id, $meta_key );

        else if ( $old_value !== $new_value )
            update_term_meta( $term_id, $meta_key, $new_value );
    }

    public static function get_meta_lable_by_key($meta_key) {
        return self::$inventory_meta_data[$meta_key];
    }
    
}