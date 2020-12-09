<?php
class KoiShortcodes {
    private static $initiated = false;
    public static function init() {
		if ( ! self::$initiated ) {
            self::init_hooks();
		}
    }

    private static function init_hooks() {
        self::$initiated = true;
        add_shortcode( 'koi-table-row', array( 'KoiShortcodes', 'render_inventory_row' ) );
    }

    public static function get_term_info($post, $taxonomy) {
        $terms = get_the_terms($post, $taxonomy);
        $names = '';

        foreach($terms as $index => $term) {
            if(!$index)
                $names .= $term->name;            
            else 
                $names .= ', '.$term->name;
        }
        if($taxonomy === 'auction_groups')
            error_log(print_r($terms, true));
        return $names;
    }
    public static function get_auction_date($inventory, $date_type) {
        $auction_term = get_the_terms( $inventory, 'auction_groups' )[0];        
        $auction_date = KoiPricing_Admin::get_term_meta_value( $auction_term->term_id, $date_type );

        return $auction_date;
    }

    public static function render_inventory_row( $atts ) {
        $attr = shortcode_atts( array(
              'inventory' => 'something'
          ), $atts );
        $id = $attr['inventory'];
        $inventory = get_post($attr['inventory']);

        $breeders = self::get_term_info($inventory, 'breeder');
        $varieties = self::get_term_info($inventory, 'variety');
        $auction_group = self::get_term_info($inventory, 'auction_groups');

        $auction_start_date = self::get_auction_date($inventory, KoiPricing::TERM_START_DATE);
        $auction_end_date = self::get_auction_date($inventory, KoiPricing::TERM_END_DATE);
        $thumbnail = get_the_post_thumbnail($inventory, array( 150, 150 ) );
        return '
            <tr>
                <td class="name">
                    <div class="thumbnail">
                        '.$thumbnail.'
                    </div>
                    '.$inventory->post_title.'
                </td>
                <td class="info">
                    <table class="child-table">
                        <tr>
                            <td>Born:</td>
                            <td>'.get_post_meta($id, '_inventory_born_in', true).'</td>
                        </tr>
                        <tr>
                            <td>Breeder:</td>
                            <td>'.$breeders.'</td>
                        </tr>
                        <tr>
                            <td>Variety:</td>
                            <td>'.$varieties.'</td>
                        </tr>
                        <tr>
                            <td>Sex:</td>
                            <td>'.get_post_meta($id, '_inventory_gender', true).'</td>
                        </tr>
                        <tr>
                            <td>Size (cm):</td>
                            <td>'.get_post_meta($id, '_inventory_size', true).'</td>
                        </tr>
                    </table>
                </td>
                <td class="pricing">
                    <table class="child-table">
                        <tr>
                            <td>Estimated Value:</td>
                            <td>'.get_post_meta($id, '_inventory_estimated_price', true).'</td>
                        </tr>
                        <tr>
                            <td>Start Price:</td>
                            <td>'.get_post_meta($id, '_inventory_start_price', true).'</td>
                        </tr>
                        <tr>
                            <td>Reserve Price:</td>
                            <td>'.get_post_meta($id, '_inventory_reserve_price', true).'</td>
                        </tr>
                        <tr>
                            <td>Buy It Now:</td>
                            <td></td>
                        </tr>
                    </table>
                </td>
                <td class="auction-info">
                    <table class="child-table">
                        <tr>
                            <td>Auction Group:</td>
                            <td>'.$auction_group.'</td>
                        </tr>
                        <tr>
                            <td>Order:</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Allow Proxy:</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Bid Increment:</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Auction Start:</td>
                            <td>'.$auction_start_date.'</td>
                        </tr>
                        <tr>
                            <td>Auction End:</td>
                            <td>'.$auction_end_date.'</td>
                        </tr>                        
                    </table>
                </td>
                <td class="content">'.$inventory->post_content.'</td>
            </tr>
        ';
    }
}


