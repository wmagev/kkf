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

        $request = [];
        foreach($filter_keys as $key) {
            $request[$key] = $_POST[$key];
        }
        $args = [];
        $meta_query = [];
        foreach($request as $key => $value) {
            if($value && array_key_exists($key, $meta_keys)) {
                $meta_query[] = [ 
                    'key' => $meta_keys[$key],
                    'value' => $value
                ];
            }
        }        
        KoiPricing::set_filter_session($meta_query);
        $inventories = KoiPricing::get_inventory_collection();

        $html = self::get_html_by_inventories($inventories);

        wp_send_json([
            'data' => $html,
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
}