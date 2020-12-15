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
        add_shortcode( 'koi-table-filter-form', array( 'KoiShortcodes', 'render_koi_filter_form' ) );
        add_shortcode( 'koi-thumbnail-lightbox', array( 'KoiShortcodes', 'render_lightbox' ) );
        add_shortcode( 'koi-table-pagination', array( 'KoiShortcodes', 'render_pagination' ) );
        add_shortcode( 'active-filter-info', array( 'KoiShortcodes', 'render_active_filter' ) );        
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
            
        return $names;
    }

    public static function get_auction_date($inventory, $date_type) {
        $auction_term = get_the_terms( $inventory, 'auction_groups' )[0];        
        $auction_date = KoiPricing_Admin::get_term_meta_value( $auction_term->term_id, $date_type );

        return $auction_date;
    }
    

    public static function render_active_filter() {
        $html = 'Active Filters: ';

        $term_info = KoiPricing::get_current_session_term();
        if($term_info) {
            $html .= '<span class="filter-field">Term: </span><span class="filter-value" >'.KoiPricing::get_term_name_by_id($term_info['term_id']).'</span>';
            $html .= '<span class="filter-field">Taxonomy: </span><span class="filter-value" >'.KoiPricing::get_taxonomy_name_by_slug($term_info['taxonomy']).'</span>';
        }

        $html .= '<span id="refresh-filter" class="refresh-filter"><i class="fa fa-remove"></i></span>';
        return $html;
    }

    public static function render_koi_filter_form() {
        return '<div class="koi-pricing-table-filter">
            <h2>Koi Filter</h2>
            <div class="input-wrapper first">
                <label for="auction-start">Auction Start: </label>     
                <div class="input-box">                   
                    <input name="auction-start" class="datepicker" />
                    <span class="focus-border"></span>                        
                </div>
            
                <label for="auction-start">Auction End: </label>
                <div class="input-box">
                    <input name="auction-end" class="datepicker" />
                    <span class="focus-border"></span>
                </div>
                <label for="auction-start">Born In: </label>
                <div class="input-box">
                    <input name="born-in" />
                    <span class="focus-border"></span>
                </div>
            </div>
            <div class="input-wrapper">
                <label for="auction-start">From Pond: </label>
                <div class="input-box">
                    <input name="from-pond" />
                    <span class="focus-border"></span>
                </div>
                <label for="auction-start">To Pond: </label>
                <div class="input-box">
                    <input name="to-pond" />
                    <span class="focus-border"></span>
                </div>
            </div>
            <div class="input-wrapper">                
                <label for="koi-status">Status: </label>     
                <div class="input-box">                   
                    <input name="koi-status" />
                    <span class="focus-border"></span>                        
                </div>
            
                <label for="variety">Variety: </label>
                <div class="input-box">
                    <input name="variety" />
                    <span class="focus-border"></span>
                </div>
                <label for="Breeder">Breeder: </label>
                <div class="input-box">
                    <input name="breeder" />
                    <span class="focus-border"></span>
                </div>                    
            </div>
            <div class="input-wrapper">
                <label for="product-category">Product Category: </label>
                <div class="input-box">
                    <input name="product-category" />
                    <span class="focus-border"></span>
                </div>
                <label for="koi-price">Price: </label>
                <div class="input-box">
                    <input name="koi-price" />
                    <span class="focus-border"></span>
                </div>                    
            </div>
            <div class="input-wrapper">
                <label for="auction-group">Auction Group: </label>
                <div class="input-box">
                    <input name="auction-group" />
                    <span class="focus-border"></span>
                </div>
                <label for="photo-group">Photo Group: </label>
                <div class="input-box">
                    <input name="photo-group" class="datepicker" />
                    <span class="focus-border"></span>
                </div>
            </div>            
            <div style="text-align: right;">
                <button class="button filter-submit" />
                    Filter
                </button>
            </div>
        </div>';
    }

    public static function render_pagination() {
        $count = KoiPricing::get_max_num_page();
        $result = '
            <div id="koi-pricing-pagination" class="pagination">
                <a href="#">&laquo;</a>
        ';
        for($i = 1;$i <= $count ;$i ++) {
            $result .= '<a class="koi-pricing-pagelinks" data-offset="'.$i.'">'.$i.'</a>';
        }
        $result .= '
                <a href="#">&raquo;</a>
            </div>
        ';
        return $result;
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
        $photo_group = self::get_term_info($inventory, 'photo_groups');

        $auction_start_date = self::get_auction_date($inventory, KoiPricing::TERM_START_DATE);
        $auction_end_date = self::get_auction_date($inventory, KoiPricing::TERM_END_DATE);
        // $thumbnails = get_the_post_thumbnail($inventory, array( 150, 150 ) );
        $thumbnails = self::get_inventory_thumbnails($inventory->ID);

        error_log(print_r($inventory, true));
        return '
            <tr>
                <td class="drag-n-drop"><i draggable="true" class="fas fa-arrows-alt draggable-icon" id="'.$inventory->ID.'"></i></td>
                <td class="name">
                    <div class="thumbnail">
                        '.$thumbnails.'
                    </div>
                    <div class="inventory-title">
                    '.$inventory->post_title.'
                    </div>
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
                        <tr>
                            <td>Menu Order:</td>
                            <td>'.$inventory->menu_order.'</td>
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
                        <tr>
                            <td>Photo Group:</td>
                            <td>'.$photo_group.'</td>
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
                <td class="content"></td>
            </tr>
        ';
    }
    public static function render_lightbox() {
        return '
            <div id="lightbox-modal" class="lightbox-modal">
                <span class="close cursor" onclick="closeModal()">&times;</span>
                <div class="modal-content">
                    <div id="koi-slides-container">
                        <div class="koi-slides">
                        </div>
                    </div>
                    <!-- Next/previous controls -->
                    <a class="lightbox-prev prev" >&#10094;</a>
                    <a class="lightbox-next next" >&#10095;</a>
                
                    <!-- Caption text -->
                    <div class="caption-container">
                        <p id="caption"></p>
                    </div>
                
                    <!-- Thumbnail image controls -->
                    <div id="lightbox-thumbnail-container">
                    </div>
                </div>
            </div>
        ';
    }

    public static function get_inventory_thumbnails($post_id) {
        $images = get_children( array (
            'post_parent' => $post_id,
            'post_type' => 'attachment',
            'post_mime_type' => 'image'
        ));

        if ( empty($images) ) {
            // no attachments here
        } else {
            $html = '';
            $index = 1;
            foreach ( $images as $attachment_id => $attachment ) {
                
                 // Begin custom code
                $full_size_image = wp_get_attachment_image_src( $attachment_id, 'full' );
                $thumbnail       = wp_get_attachment_image_src( $attachment_id, 'medium' );
                $image_title     = get_post_field( 'post_excerpt', $attachment_id );
                $attachment_meta = get_post_custom($attachment_id);
                // now you have the custom fields, and can do stuff with them 
                $attributes = array(
                    'title'                   => $image_title,
                    'data-src'                => $full_size_image[0],
                    'data-large_image'        => $full_size_image[0],
                    'data-large_image_width'  => $full_size_image[1],
                    'data-large_image_height' => $full_size_image[2],
                );
                $size = ($index === 1) ? [ 150, 150 ] : [ 50, 50 ];
                $html .= '<div data-index="'.$index.'" data-thumb="' . esc_url( $thumbnail[0] ) . '" class="koi-thumbnails">';
                $html .= wp_get_attachment_image( $attachment_id, $size, false, $attributes );
                $html .= '</div>';
                // End custom code
                $index ++;
            }
            return $html;
        }        
    }
}


