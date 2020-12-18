<?php
class KoiPricing {
    private static $initiated = false;
    const INV_POST_TYPE = 'inventory';
    const TERM_START_DATE = '__term_start_date';
    const TERM_END_DATE = '__term_end_date';
    const TERM_PHOTO_DATE = '__term_photo_date';
    public static $post_statuses = [
        'to_review' => 'To Review',
        'reviewed' => 'Reviewed',
        'deployed' => 'Deployed',
        'live' => 'Live',
        'sold' => 'Sold',
        'unsold' => 'Unsold',
        'cancelled' => 'Cancelled',
    ];

    public static function init() {
        if ( ! self::$initiated ) {            
            self::init_hooks();
		}
    }
    private static function init_hooks() {
        self::$initiated = true;
        add_action('product_taxonomies_added', array( 'KoiPricing', 'init_post_type' ) );        
        add_action('inventory_post_type_added', array( 'KoiPricing', 'init_taxonomy' ) );
        add_action('inventory_post_type_added', array( 'KoiPricing', 'init_taxonomy_meta_data' ) );
        add_action('inventory_post_type_added', array( 'KoiPricing', 'init_post_statuses' ) );
    }

    public static function init_post_statuses() {
        foreach(self::$post_statuses as $key => $label) {
            register_post_status( $key, array(
                'label'                     => _x( $label, 'post' ),
                'public'                    => true,
                'show_in_admin_all_list'    => false,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( $label.' <span class="count">(%s)</span>', $label.' <span class="count">(%s)</span>' )
            ) );
        }
    }
    

    public static function init_post_type() {        
        $labels = array(
            'name'                  => _x( 'Inventories', 'Post Type General Name', 'inventory' ),
            'singular_name'         => _x( 'Inventory', 'Post Type Singular Name', 'inventory' ),
            'menu_name'             => __( 'Inventory', 'inventory' ),
            'name_admin_bar'        => __( 'Inventory', 'inventory' ),
            'archives'              => __( 'Item Archives', 'inventory' ),
            'attributes'            => __( 'Item Attributes', 'inventory' ),
            'parent_item_colon'     => __( 'Parent Item:', 'inventory' ),
            'all_items'             => __( 'All Inventories', 'inventory' ),
            'add_new_item'          => __( 'Add New Item', 'inventory' ),
            'add_new'               => __( 'Add New', 'inventory' ),
            'new_item'              => __( 'New Item', 'inventory' ),
            'edit_item'             => __( 'Edit Item', 'inventory' ),
            'update_item'           => __( 'Update Item', 'inventory' ),
            'view_item'             => __( 'View Item', 'inventory' ),
            'view_items'            => __( 'View Items', 'inventory' ),
            'search_items'          => __( 'Search Item', 'inventory' ),
            'not_found'             => __( 'Not found', 'inventory' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'inventory' ),
            'featured_image'        => __( 'Featured Image', 'inventory' ),
            'set_featured_image'    => __( 'Set featured image', 'inventory' ),
            'remove_featured_image' => __( 'Remove featured image', 'inventory' ),
            'use_featured_image'    => __( 'Use as featured image', 'inventory' ),
            'insert_into_item'      => __( 'Insert into item', 'inventory' ),
            'uploaded_to_this_item' => __( 'Uploaded to this item', 'inventory' ),
            'items_list'            => __( 'Items list', 'inventory' ),
            'items_list_navigation' => __( 'Items list navigation', 'inventory' ),
            'filter_items_list'     => __( 'Filter items list', 'inventory' ),
        );
        $args = array(
            'label'                 => __( 'Inventory', 'inventory' ),
            'description'           => __( 'Inventory Description', 'inventory' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'editor', 'thumbnail', 'comments', 'custom-fields' ),
            'taxonomies'            => array( 'post_tag', 'breeder', 'variety', 'price_type', 'product_cat' ),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'page',
        );
        register_post_type( 'inventory', $args );

        do_action( 'inventory_post_type_added' ); //Register taxnomoies after post_type added
        
    }
    public static function init_taxonomy() {
        $labels = array(
            'name' => _x( 'Auction Groups', 'taxonomy general name' ),
            'singular_name' => _x( 'Auction Group', 'taxonomy singular name' ),
            'search_items' =>  __( 'Search Auction Groups' ),
            'all_items' => __( 'All Auction Groups' ),
            'parent_item' => __( 'Parent Auction Group' ),
            'parent_item_colon' => __( 'Parent Auction Group:' ),
            'edit_item' => __( 'Edit Auction Group' ), 
            'update_item' => __( 'Update Auction Group' ),
            'add_new_item' => __( 'Add New Auction Group' ),
            'new_item_name' => __( 'New Auction Group Name' ),
            'menu_name' => __( 'Auction Groups' ),
          );    
         
        // Now register the taxonomy
        register_taxonomy('auction_groups', 
            array('inventory'), 
            array(
                'hierarchical' => true,
                'labels' => $labels,
                'show_ui' => true,
                'show_in_rest' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => array( 'slug' => 'auction_group' ),
            )
        );

        

        $labels = array(
            'name' => _x( 'Photo Groups', 'taxonomy general name' ),
            'singular_name' => _x( 'Photo Group', 'taxonomy singular name' ),
            'search_items' =>  __( 'Search Photo Groups' ),
            'popular_items' => __( 'Popular Photo Groups' ),
            'all_items' => __( 'All Photo Groups' ),
            'parent_item' => null,
            'parent_item_colon' => null,
            'edit_item' => __( 'Edit Photo Group' ), 
            'update_item' => __( 'Update Photo Group' ),
            'add_new_item' => __( 'Add New Photo Group' ),
            'new_item_name' => __( 'New Photo Group Name' ),
            'separate_items_with_commas' => __( 'Separate photo groups with commas' ),
            'add_or_remove_items' => __( 'Add or remove photo groups' ),
            'choose_from_most_used' => __( 'Choose from the most used photo groups' ),
            'menu_name' => __( 'Photo Groups' ),
          ); 
         
        // Now register the non-hierarchical taxonomy like tag
         
        register_taxonomy('photo_groups',
            'inventory',
            array(
                'hierarchical' => false,
                'labels' => $labels,
                'show_ui' => true,
                'show_in_rest' => true,
                'show_admin_column' => true,
                'update_count_callback' => '_update_post_term_count',
                'query_var' => true,
                'rewrite' => array( 'slug' => 'photo_group' ),
            )
        );
    }

    public static function init_taxonomy_meta_data() {
        // Auction Group
        register_meta( 'term', self::TERM_START_DATE, array( 'KoiPricing_Admin', 'sanitize_term_meta_text' ) );
        register_meta( 'term', self::TERM_END_DATE, array( 'KoiPricing_Admin', 'sanitize_term_meta_text' ) );

        // Photo Group
        register_meta( 'term', self::TERM_PHOTO_DATE, array( 'KoiPricing_Admin', 'sanitize_term_meta_text' ) );
    }

    public static function plugin_activation() {

    }

    public static function get_tax_query() {
        $term_info = self::get_current_session_term();
        $tax_query = self::get_current_session_tax_filter();
        if($term_info) {
            $field = ($term_info['taxonomy'] == 'photo_groups') ? 'slug' : 'term_id';
            $tax_query[] = [
                'taxonomy' => $term_info['taxonomy'],
                'field' => $field,
                'terms' => $term_info['term_id']
            ];
        }
        if($tax_query) {
            $tax_query['relation'] = 'OR';
            return [                
                $tax_query
            ];
        }
        return false;
    }

    public static function get_max_num_page() {
        $tax_query = self::get_tax_query();
        $meta_query = self::get_current_session_filter();

        $args = array(
            'post_type' => KoiPricing::INV_POST_TYPE,
            'posts_per_page' => -1
        );
        
        if($tax_query) {
            $args['tax_query'] = $tax_query;
        }

        if ($meta_query) {
            $args['meta_query'] = $meta_query;
        }    
        
        $posts = new WP_Query($args);

        return ceil($posts->post_count/10);
    }

    public static function get_inventory_collection($offset = 0, $page_size = 10) {
        $tax_query = self::get_tax_query();
        $meta_query = self::get_current_session_filter();
        $args = array(
            'posts_per_page' => $page_size,
            'offset' => $offset,
            'post_type'   => KoiPricing::INV_POST_TYPE,
            'fields' => 'ids'            
        );

        if($tax_query) {
            $args['tax_query'] = $tax_query;
        }

        if ($meta_query) {
            $args['meta_query'] = $meta_query;
        }

        return get_posts( $args );
    }
    public static function get_taxonomy_terms($taxonomy, $meta_query = []) {
        $args = [
            'taxonomy' => $taxonomy,
            'hide_empty' => false
        ];

        if(count($meta_query)) {
            $args['meta_query'] = $meta_query;
        }

        return get_terms($args);
    }

    public static function set_session_term($term_id, $taxonomy) {
        update_user_meta(get_current_user_id(), '_cur_term_id', $term_id);
        update_user_meta(get_current_user_id(), '_cur_taxonomy', $taxonomy);
    }

    public static function set_filter_session($meta_query) {        
        update_user_meta(get_current_user_id(), '_cur_meta_query', json_encode($meta_query));
    }
    public static function set_tax_filter_session($tax_query) {        
        update_user_meta(get_current_user_id(), '_cur_tax_query', json_encode($tax_query));
    }

    public static function set_filter_request_session($request) {        
        update_user_meta(get_current_user_id(), '_cur_request', json_encode($request));
    }
    
    public static function get_current_session_filter_request() {
        $request = get_user_meta(get_current_user_id(), '_cur_request', true);
        if( $request ) {
            return json_decode($request, true);
        }
        return false;
    }

    public static function get_current_session_term() {
        $term_id = get_user_meta(get_current_user_id(), '_cur_term_id', true);
        $taxonomy = get_user_meta(get_current_user_id(), '_cur_taxonomy', true);

        if( $term_id && $taxonomy ) {
            return [
                'term_id' => $term_id,
                'taxonomy' => $taxonomy
            ];
        }
        return false;
    }

    public static function get_current_session_tax_filter() {
        $tax_query = get_user_meta(get_current_user_id(), '_cur_tax_query', true);
        if( $tax_query ) {
            return json_decode($tax_query, true);
        }
        return false;
    }

    public static function get_current_session_filter() {
        $meta_query = get_user_meta(get_current_user_id(), '_cur_meta_query', true);
        if( $meta_query ) {
            return json_decode($meta_query, true);
        }
        return false;
    }

    public static function unset_session_term() {
        delete_user_meta(get_current_user_id(), '_cur_term_id');
        delete_user_meta(get_current_user_id(), '_cur_taxonomy');
    }
    
    public static function reset_session() {
        delete_user_meta(get_current_user_id(), '_cur_term_id');
        delete_user_meta(get_current_user_id(), '_cur_taxonomy');
        delete_user_meta(get_current_user_id(), '_cur_meta_query');
        delete_user_meta(get_current_user_id(), '_cur_tax_query');
        delete_user_meta(get_current_user_id(), '_cur_request');
    }

    public static function get_term_name_by_id($term_id) {
        return get_term( $term_id )->name;
    }

    public static function get_term_name_by_slug($slug) {
        return get_term_by('slug', $slug, 'photo_groups')->name;
    }

    public static function get_taxonomy_name_by_slug($slug) {
        $taxonomies = [
            'auction_groups' => 'Auction Group',
            'photo_groups' => 'Photo Group',
            'breeder' => 'Breeder',
            'variety' => 'Variety',
            'product_cat' => 'Product Category'
        ];
        return $taxonomies[$slug];
    }

    public static function get_inventory_thumbnail_src($post_id) {
        $images = get_children( array (
            'post_parent' => $post_id,
            'post_type' => 'attachment',
            'post_mime_type' => 'image'
        ));

        if ( empty($images) ) {
            return false;
        } else {
            foreach ( $images as $attachment_id => $attachment ) {
                return wp_get_attachment_image_src( $attachment_id, 'medium' );
            }
        }
    }    
}