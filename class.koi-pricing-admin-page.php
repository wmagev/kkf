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
        // add_action( 'admin_footer', array( 'KoiPricing_Admin_Page', 'add_wicked_folder_pane' ) );
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
    public static function get_inventory_collection($page_size = 10) {
        $args = array(
            'posts_per_page' => $page_size,
            'post_type'   => KoiPricing::INV_POST_TYPE,
            'fields' => 'ids'
        );
        
           
        return get_posts( $args );
    }
    public static function koi_admin_page_contents() {
        $inventories = self::get_inventory_collection();
		?>
			<h1>
				<?php esc_html_e( 'KOI Pricing', 'koi-pricing' ); ?>
            </h1>            
            <div class='koi-pricing-table-filter'>
                <h2>Koi Filter</h2>
                <div class="input-wrapper first">
                    <label for="auction-start">Auction Start: </label>     
                    <div class="input-box">                   
                        <input name="auction-start" />
                        <span class="focus-border"></span>                        
                    </div>
                
                    <label for="auction-start">Auction End: </label>
                    <div class="input-box">
                        <input name="auction-end" />
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
                    
                    <label for="auction-start">Status: </label>     
                    <div class="input-box">                   
                        <input name="auction-start" />
                        <span class="focus-border"></span>                        
                    </div>
                
                    <label for="auction-start">Variety: </label>
                    <div class="input-box">
                        <input name="auction-end" />
                        <span class="focus-border"></span>
                    </div>
                    <label for="auction-start">Breeder: </label>
                    <div class="input-box">
                        <input name="born-in" />
                        <span class="focus-border"></span>
                    </div>                    
                </div>
                <div class="input-wrapper">
                    <label for="auction-start">Product Category: </label>
                    <div class="input-box">
                        <input name="born-in" />
                        <span class="focus-border"></span>
                    </div>
                    <label for="auction-start">Price: </label>
                    <div class="input-box">
                        <input name="from-pond" />
                        <span class="focus-border"></span>
                    </div>                    
                </div>
                <div class="input-wrapper">
                    <label for="auction-start">Auction Group: </label>
                    <div class="input-box">
                        <input name="to-pond" />
                        <span class="focus-border"></span>
                    </div>
                    <label for="auction-start">Photo Group: </label>
                    <div class="input-box">
                        <input name="to-pond" />
                        <span class="focus-border"></span>
                    </div>
                </div>
                
                <div style="text-align: right;">
                    <button class="button" />
                        Submit
                    </button>
                </div>
            </div>
            <hr/>
            <div class='koi-pricing-table-wrapper'>
                <table class='koi-pricing-table'>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>KOI Info</th>
                            <th>Pricing</th>
                            <th>Auction Info</th>
                            <th>Content</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($inventories as $post_id) :                    
                        echo do_shortcode('[koi-table-row inventory="'.$post_id.'"]');
                    endforeach; ?>
                    </tbody>
                </table>
            </div>
		<?php
    }
    
    public static function add_wicked_folder_pane() {

        $screen 						= get_current_screen();
        $post_type 						= KoiPricing::INV_POST_TYPE;
        $active_folder_id 				= isset( $_GET['wicked_' . $post_type . '_folder_filter'] ) ? ( string ) $_GET['wicked_' . $post_type . '_folder_filter'] : false;
        $active_folder 					= false;
        $state 							= Wicked_Folders_Admin::get_instance()->get_screen_state( $screen->id );
        $taxonomy 						= Wicked_Folders::get_tax_name( $post_type );
        $folders 						= Wicked_Folders::get_folders( $post_type, $taxonomy );
        $active_folder_type 			= isset( $_GET['folder_type'] ) ? $_GET['folder_type'] : false;
        $lazy_folders 					= array();
        $lang 							= Wicked_Folders::get_language();
        $classes 						= array();
        $enable_horizontal_scrolling 	= Wicked_Folders::is_horizontal_scrolling_enabled();
        $show_item_counts 				= get_option( 'wicked_folders_show_item_counts', true );

        if ( $enable_horizontal_scrolling ) $classes[] = 'wicked-scroll-horizontal';

        // Items can also be filtered using the folders column which uses
        // the folder taxonomy name as the parameter with the folder term
        // slug as the value
        $slug = isset( $_GET[ $taxonomy ] ) ? $_GET[ $taxonomy ] : false;

        // The attachments page uses the 'taxonomy' parameter
        $slug = isset( $_GET['taxonomy'] ) && isset( $_GET['term'] ) && $taxonomy == $_GET['taxonomy'] ? $_GET['term'] : $slug;

        if ( $slug ) {
            $term = get_term_by( 'slug', $slug, $taxonomy );
            if ( $term ) $active_folder_id = ( string ) $term->term_id;
        }

        if ( false === $active_folder_id ) {
            $active_folder_id = $state->folder;
        }

        if ( false === $active_folder_type ) {
            $active_folder_type = $state->folder_type;
        }

        // Make sure the folder exists
        if ( ! Wicked_Folders::get_folder( $active_folder_id, $post_type ) && 'Wicked_Folders_Term_Folder' == $active_folder_type ) {
            $active_folder_id = '0';
        }

        // For other folder types, check folders array to make sure folder exists
        if ( 'Wicked_Folders_Term_Folder' != $active_folder_type ) {
            $folder_exists = false;
            foreach ( $folders as $folder ) {
                if ( $folder->id == $active_folder_id ) $folder_exists = true;
            }
            // The active dyanmic folder may by the child of a lazy folder
            // in which case it hasn't been loaded yet...try to get it
            if ( ! $folder_exists ) {
                $folder 	= Wicked_Folders::dynamic_folders_enabled_for( $post_type ) ? Wicked_Folders::get_dynamic_folder( $active_folder_type, $active_folder_id, $post_type ) : false;
                $fetched 	= $folder ? $folder->fetch() : false;

                // Fetch should return false if the folder doesn't exist;
                // check that dynamic folder exists
                if ( $fetched ) {
                    // Add the folder's ancestors to the expanded list to
                    // ensure that the parent's get loaded
                    $ancestor_ids = $folder->get_ancestor_ids();
                    $state->expanded_folders = array_merge( $state->expanded_folders, $ancestor_ids );
                } else {
                    $active_folder_id = '0';
                }
            }
        }

        // Always expand the active folder
        $state->expanded_folders[] = $active_folder_id;

        $state->folder 		= $active_folder_id;
        $state->folder_type = $active_folder_type;

        // Load lazy dynamic folders that are expanded
        $lazy_folders = Wicked_Folders_Admin::get_instance()->get_expanded_lazy_dynamic_folders( $folders, $state->expanded_folders );

        // Add our lazy folders to the collection
        $folders = array_merge( $folders, $lazy_folders );

        // Disable laziness for expanded folders since we've already loaded
        // their children
        foreach ( $folders as &$folder ) {
            if ( in_array( $folder->id, $state->expanded_folders ) ) {
                $folder->lazy = false;
            }
        }

        // Remove any expanded folders that haven't been loaded. This will
        // prevent a child folder in a lazy folder from starting out as
        // expanded (which can happen if the child folder was previously
        // expanded)
        $folder_ids = array();

        foreach ( $folders as $_folder ) $folder_ids[] = $_folder->id;

        $state->expanded_folders = array_intersect( $state->expanded_folders, $folder_ids );

        // Save any changes we've made to the state
        $state->save();

        include( dirname( __FILE__ ) . '/admin-templates/object-folder-pane.php' );
    }

}