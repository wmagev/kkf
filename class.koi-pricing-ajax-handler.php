<?php
class KoiPricing_AjaxHandler {
    private static $initiated = false;
    public static function init() {
		if ( ! self::$initiated ) {
            self::init_hooks();
		}
    }
    private static function init_hooks() {
        self::$initiated = true;
        add_action( "wp_ajax_filter_koi_handler", array( "KoiPricing_AjaxHandler", "filter_koi_handler" ) );
        add_action( "wp_ajax_assign_taxonomy_term", array( "KoiPricing_AjaxHandler", "assign_taxonomy_term" ) );
        add_action( "wp_ajax_filter_by_term", array( "KoiPricing_AjaxHandler", "filter_by_term" ) );
        add_action( "wp_ajax_change_current_page", array( "KoiPricing_AjaxHandler", "change_current_page" ) );
        add_action( "wp_ajax_refresh_filter", array( "KoiPricing_AjaxHandler", "refresh_filter_handler" ) );
        add_action( "wp_ajax_get_auction_items", array( "KoiPricing_AjaxHandler", "get_auction_items_handler" ) );
        add_action( "wp_ajax_auction_reorder", array( "KoiPricing_AjaxHandler", "auction_reorder_handler" ) );
    }

    public static function get_html_by_inventories($inventories) {
        $html = "";
        foreach($inventories as $post_id) {
            $html .= do_shortcode('[koi-table-row inventory="'.$post_id.'"]');
        }
        return $html;
    }
    public static function get_pagination_html() {
        return KoiShortcodes::render_pagination();
    }

    public static function get_active_filter_html() {
        return KoiShortcodes::render_active_filter();
    }

    public static function refresh_filter_handler() {
        KoiPricing::reset_session();
        $inventories = KoiPricing::get_inventory_collection();
        $html = self::get_html_by_inventories($inventories);
        $paginationHtml = self::get_pagination_html();
        $activeFilterHtml = self::get_active_filter_html();

        wp_send_json([
            'data' => $html,
            'pagination' => $paginationHtml,
            'active_filter' => $activeFilterHtml,
            'success' => true
        ], 200);
        wp_die();
    }

    public static function change_current_page() {
        $pageNum = $_POST['offset'];
        $offset = ($pageNum - 1) * 10;
        
        $inventories = KoiPricing::get_inventory_collection($offset);
        $html = self::get_html_by_inventories($inventories);

        wp_send_json([
            'data' => $html,
            'success' => true
        ], 200);
        wp_die();
    }    

    public static function filter_by_term() {
        $term_id = $_POST['term_id'];
        $taxonomy = $_POST['taxonomy'];
        KoiPricing::set_session_term($term_id, $taxonomy);

        $inventories = KoiPricing::get_inventory_collection();

        $html = self::get_html_by_inventories($inventories);
        $paginationHtml = self::get_pagination_html();
        $activeFilterHtml = self::get_active_filter_html();

        wp_send_json([
            'data' => $html,
            'pagination' => $paginationHtml,
            'active_filter' => $activeFilterHtml,
            'success' => true
        ], 200);
        wp_die();
    }

    public static function filter_koi_handler() {
        $filter_keys = [
            'auction-start', 
            'auction-end',
            'born-in',
            'from-pond',
            'to-pond',
            'koi-status',
            'variety',
            'breeder',
            'product-category',
            'koi-price',
            'auction-group',
            'photo-group'
        ];

        $tax_keys = ['variety', 'breeder', 'product-category', 'auction-start', 'auction-end', 'auction-group', 'photo-group'];
        $meta_keys = [
            'born-in' => '_inventory_born_in',
            'from-pond' => '_inventory_from_pond',
            'to-pond' => '_inventory_to_pond',
            'koi-price' => '_inventory_price'
        ];
        $tax_keys = [
            'variety' => 'variety',
            'breeder' => 'breeder',
            'product-category' => 'product_cat'
        ];

        $request = [];
        foreach($filter_keys as $key) {
            $request[$key] = $_POST[$key];
        }
        
        
        $term_query = [];
        if($request['auction-start'] != '') {
            $term_query[] = [
                'key'       => KoiPricing::TERM_START_DATE,
                'value'     => $request['auction-start'],
                'compare'   => '>='
            ];
        }
        if($request['auction-end'] != '') {
            $term_query[] = [
                'key'       => KoiPricing::TERM_END_DATE,
                'value'     => $request['auction-end'],
                'compare'   => '<='
            ];
        }
        if(count($term_query)) {
            $auction_groups = KoiPricing::get_taxonomy_terms('auction_groups', $term_query);
        }

        $term_query = [];
        if($request['photo-group']) {
            $term_query[] = [
                'key'       => KoiPricing::TERM_PHOTO_DATE,
                'value'     => $request['photo-group'],
                'compare'   => '='
            ];
        }
        if(count($term_query)) {
            $photo_groups = KoiPricing::get_taxonomy_terms('photo_groups', $term_query);
        }

        $args = [];
        $meta_query = [];
        $tax_query = [];
        foreach($request as $key => $value) {
            if($value && array_key_exists($key, $meta_keys)) {
                $meta_query[] = [ 
                    'key' => $meta_keys[$key],
                    'value' => $value
                ];
            }

            if($value && array_key_exists($key, $tax_keys)) {
                $tax_query[] = [
                    'taxonomy' => $tax_keys[$key],
                    'field' => 'term_id',
                    'terms' => $value
                ];
            }
        }

        if(count($auction_groups)) {
            KoiPricing::unset_session_term();
            foreach($auction_groups as $term) {
                $tax_query[] = [
                    'taxonomy' => 'auction_groups',
                    'field' => 'term_id',
                    'terms' => $term->term_id
                ];
            }
        }

        if(count($photo_groups)) {
            KoiPricing::unset_session_term();
            foreach($photo_groups as $term) {
                $tax_query[] = [
                    'taxonomy' => 'photo_groups',
                    'field' => 'slug',
                    'terms' => $term->slug
                ];
            }
        }

        error_log(print_r($tax_query, true));

        KoiPricing::set_filter_session($meta_query);
        KoiPricing::set_tax_filter_session($tax_query);
        KoiPricing::set_filter_request_session($request);

        $inventories = KoiPricing::get_inventory_collection();

        $html = self::get_html_by_inventories($inventories);

        $paginationHtml = self::get_pagination_html();
        $activeFilterHtml = self::get_active_filter_html();

        wp_send_json([
            'data' => $html,
            'pagination' => $paginationHtml,
            'active_filter' => $activeFilterHtml,
            'success' => true
        ], 200);
        wp_die();
    }

    public static function assign_taxonomy_term() {
        $post_id = $_POST['post_id'];
        $term_id = $_POST['term_id'];
        $taxonomy = $_POST['taxonomy'];

        $result = wp_set_post_terms( $post_id, array($term_id), $taxonomy );
        
        wp_send_json([            
            'success' => is_array($result)
        ], 200);
        wp_die();
    }

    public static function get_auction_items_handler() {
        $term_id = $_POST['term_id'];

        $args = array(
            'posts_per_page' => '-1',
            'post_type'   => KoiPricing::INV_POST_TYPE,
            'orderby' => 'meta_value_num',
            'meta_key' => '_inventory_menu_order',
            'order' => 'ASC',
            'tax_query' => array(
                array(
                    'taxonomy' => 'auction_groups',
                    'field' => 'term_id',
                    'terms' => $term_id
                )
            )
        );
        
        $inventories = get_posts( $args );
        $attachments = [];
        foreach($inventories as $inventory) {
            $img = KoiPricing::get_inventory_thumbnail_src($inventory->ID);
            if($img) {
                $attachments[] = [
                    'id'      => $inventory->ID,
                    'caption' => $inventory->post_title,
                    'src'     => $img[0]
                ];
            }
            else {
                $attachments[] = [
                    'id'      => $inventory->ID,
                    'caption' => $inventory->post_title,
                    'src'     => '/wp-content/uploads/woocommerce-placeholder.png'
                ];
            }
        }

        wp_send_json([
            'attachments' => json_encode($attachments),
            'success' => true
        ], 200);
        wp_die();
    }

    public static function auction_reorder_handler() {
        $itemIds = $_POST['items'];
        foreach($itemIds as $key => $itemId) {
            update_post_meta(
                $itemId,
                '_inventory_menu_order',
                $key + 1
            );
        }
        wp_send_json([
            'success' => true
        ], 200);
        wp_die();
    }
}