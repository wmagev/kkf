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
                error_log($post->post_status);
                if(array_key_exists($post->post_status, KoiPricing::$post_statuses)){
                    $complete = " selected='selected'";
                    $label = KoiPricing::$post_statuses[$post->post_status];
                    error_log($label." is selected!");
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

    public static function enqueue_admin_script( $hook ) {       
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script( 'koi_admin_script', plugin_dir_url( __FILE__ ) . 'assets/js/admin-script.js', array(), '1.0' );
        wp_register_style( 'koi_admin_css', plugin_dir_url( __FILE__ ) . 'assets/css/admin-style.css', false, '1.0.0' );
        wp_enqueue_style( 'koi_admin_css' );
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
            <input type="text" name="<?= KoiPricing::TERM_START_DATE ?>" id="term-start-date" value="" class="term-start-date-field" />
        </div>
        <?php wp_nonce_field( basename( __FILE__ ), 'term_end_date_nonce' ); ?>
        <div class="form-field term-end-date-wrap">
            <label for="term-end-date"><?php _e( 'End Date', 'end_date' ); ?></label>
            <input type="text" name="<?= KoiPricing::TERM_END_DATE ?>" id="term-end-date" value="" class="term-end-date-field" />
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
                <input type="text" name="<?= KoiPricing::TERM_START_DATE ?>" id="term-start-date" value="<?php echo esc_attr( $start_date ); ?>" class="term-start-date-field"  />
            </td>
        </tr>
        <tr class="form-field term-end-date-wrap">
            <th scope="row"><label for="term-end-date"><?php _e( 'End Date', 'end_date' ); ?></label></th>
            <td>
                <?php wp_nonce_field( basename( __FILE__ ), 'term_end_date_nonce' ); ?>
                <input type="text" name="<?= KoiPricing::TERM_END_DATE ?>" id="term-end-date" value="<?php echo esc_attr( $end_date ); ?>" class="term-end-date-field"  />
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
    
}