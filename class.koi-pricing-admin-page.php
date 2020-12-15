<?php
class KoiPricing_Admin_Page {
    private static $initiated = false;
    public static function init() {
		if ( ! self::$initiated ) {
            self::init_hooks();
		}
    }
    private static function init_hooks() {
        self::$initiated = true;
        add_action( 'admin_menu', array( 'KoiPricing_Admin_Page', 'init_admin_menu' ) );        
    }
    public static function init_admin_menu() {
        add_menu_page( 
            __( 'KOI Pricing', 'koi-pricing-page-title' ), 
            __( 'KOI Pricing', 'koi-pricing-menu-title' ),
            'manage_options', 
            'koi-pricing',
            array( 'KoiPricing_Admin_Page', 'koi_admin_page_contents' ),
            '',
            2
        );
    }    
    
    public static function koi_admin_page_contents() {
        $inventories = KoiPricing::get_inventory_collection();
        $auction_groups = KoiPricing::get_taxonomy_terms('auction_groups');
        $photo_groups = KoiPricing::get_taxonomy_terms('photo_groups');
		?>
			<h1 class="koi-pricing-title">
				<?php esc_html_e( 'KOI Pricing', 'koi-pricing' ); ?>
            </h1>
            <div class="koi-pricing-notification" style="display:none">
                <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
                Successfully added the post into selected auction group.
            </div>
            <div id="current-filter-info" class="current-filter-info">
                <?php echo do_shortcode( '[active-filter-info]' ); ?>
            </div>
            <div class="koi-container">                
                <div class="koi-sidebar">
                    <button class="accordion folder-icon"><i class="fas fa-folder"></i>Auction Groups</button>
                    <div class="panel">
                        <ul>
                            <?php foreach($auction_groups as $term): ?>
                                <li class="taxonomy-term-list-item" id="<?= $term->term_id ?>" data-term-id="<?= $term->term_id ?>" data-taxonomy="auction_groups"><i class="fas fa-list-alt"></i><?= $term->name ?><span class="term-count" ><?= $term->count ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <button class="accordion folder-icon"><i class="fas fa-folder"></i>Photo Groups</button>
                    <div class="panel">
                        <ul>
                            <?php foreach($photo_groups as $term): ?>
                                <li class="taxonomy-term-list-item" id="<?= $term->name ?>" data-term-id="<?= $term->term_id ?>" data-taxonomy="photo_groups"><i class="fas fa-list-alt"></i><?= $term->name ?><span class="term-count" ><?= $term->count ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <div class="koi-main">
                    <?php echo do_shortcode( '[koi-table-filter-form]' ); ?>
                    <hr/>
                    <?php echo do_shortcode('[koi-table-pagination]'); ?>
                    <div class='koi-pricing-table-wrapper'>
                        <table class='koi-pricing-table'>
                            <thead>
                                <tr>
                                    <th><i class="fas fa-arrows-alt"></i></th>
                                    <th>Name</th>
                                    <th>KOI Info</th>
                                    <th>Pricing</th>
                                    <th>Auction Info</th>
                                    <th>Content</th>                                    
                                </tr>
                            </thead>
                            <tbody id="koi-pricing-table-body">
                            <?php foreach($inventories as $post_id) :                    
                                echo do_shortcode('[koi-table-row inventory="'.$post_id.'"]');
                            endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>                
            </div>
            <?php echo do_shortcode( '[koi-thumbnail-lightbox]' ); ?>
		<?php
    }
}