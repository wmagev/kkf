<?php
class KoiPricing {
    private static $initiated = false;

    public static function init() {
		if ( ! self::$initiated ) {
            self::init_hooks();
            self::init_post_type();
		}
    }
    private static function init_hooks() {
        self::$initiated = true;
    }

    private static function init_post_type() {
        
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
            'taxonomies'            => array( 'category', 'post_tag' ),
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
    }
}