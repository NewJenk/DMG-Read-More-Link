<?php
/**
 * The DMG_RML Marker hidden taxonomy.
 *
 * @version 1.0.0
 * @since 1.0.0
 *
 * @package GB\LMS 
 *
 */

namespace DMG\RML;

class TaxBlockMarker {




    public function __construct() {

		// DMG_RML Marker taxonomy.
		add_action( 'init', array($this, 'register'), 0 );

		add_action( 'admin_menu', array( $this, 'add_posts_link_to_settings' ));

		add_filter( 'admin_head', array( $this, 'admin_menu_highlight' ) );

		add_action( 'admin_notices', array( $this, 'add_post_list_description' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 99 );

    }




	/**
	* Register the DMG_RML Marker taxonomy.
	*
	* @version 1.0.0
	* @since 1.0.0
	*/
	function register() {

		// Labels for "DMG_RML Marker" taxonomy
		$labels_group = array(
			'name'              => _x( 'DMG_RML Marker', 'taxonomy general name', 'dmg-rml' ),
			'singular_name'     => _x( 'DMG_RML Marker', 'taxonomy singular name', 'dmg-rml' ),
			'search_items'      => __( 'Search DMG_RML Marker', 'dmg-rml' ),
			'all_items'         => __( 'All DMG_RML Marker', 'dmg-rml' ),
			'parent_item'       => __( 'Parent DMG_RML Marker', 'dmg-rml' ),
			'parent_item_colon' => __( 'Parent DMG_RML Marker:', 'dmg-rml' ),
			'edit_item'         => __( 'Edit DMG_RML Marker', 'dmg-rml' ),
			'update_item'       => __( 'Update DMG_RML Marker', 'dmg-rml' ),
			'add_new_item'      => __( 'Create DMG_RML Marker', 'dmg-rml' ),
			'new_item_name'     => __( 'New DMG_RML Marker Name', 'dmg-rml' ),
			'menu_name'         => __( 'Manage DMG_RML Marker', 'dmg-rml' ),
			'back_to_items'		=> __( '&larr; Go to DMG_RML Marker', 'dmg-rml' ),
			'not_found'			=> __( 'No DMG_RML Marker found.', 'dmg-rml' ),
			'no_terms'			=> __( 'No DMG_RML Marker', 'dmg-rml' ),
		);

		// Args for "DMG_RML Marker" taxonomy
		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels_group,
			'show_ui'           => false,
			'show_admin_column' => false,
			'show_in_rest'      => false,
            'show_in_nav_menus' => false,
            'show_tagcloud'     => false,
            'show_in_quick_edit'=> false,
			'query_var'         => false,
			'public'            => false,
			'capabilities' => array(
				'edit_terms'   => 'do_not_allow',
				'delete_terms' => 'do_not_allow',
				'assign_terms' => 'do_not_allow',
			)
		);

		$post_types_array = array( 'post', 'page' );

		// Register "DMG_RML Marker" taxonomy.
		register_taxonomy( 'dmg_rml_block_marker', $post_types_array, $args );

	}




	public function add_posts_link_to_settings() {

		add_submenu_page(
			'options-general.php',
			__( 'Posts with DMG Read More Link Block', 'dmg-rml' ),
			__( 'DMG Read More Link Posts and Pages', 'dmg-rml' ),
			'manage_options',
			// Use the destination URL as the menu slug.
			'edit.php?taxonomy=dmg_rml_block_marker&term=has-read-more-block'
		);

		// add_submenu_page(
		// 	'options-general.php',
		// 	__( 'Posts with DMG Read More Link Block', 'dmg-rml' ),
		// 	__( 'DMG Read More Link Posts and Pageshuhu', 'dmg-rml' ),
		// 	'manage_options',
		// 	// Use the destination URL as the menu slug.
		// 	'edit.php?taxonomy=dmg_rml_block_marker&term=has-read-more-block&post_type=page'
		// );

	}




    /**
     * Highlights the correct menu item.
     */
    public function admin_menu_highlight() {

        global $parent_file, $submenu_file;

        $current_taxonomy = filter_input( INPUT_GET, 'taxonomy', FILTER_SANITIZE_STRING );
        $current_term     = filter_input( INPUT_GET, 'term', FILTER_SANITIZE_STRING );

        // Check if we are on the specific filtered post list page.
        if ( 'dmg_rml_block_marker' === $current_taxonomy && 'has-read-more-block' === $current_term ) {
            
            // Force the "Settings" menu to be the active parent.
            $parent_file = 'options-general.php';
            
            $submenu_file = 'edit.php?taxonomy=dmg_rml_block_marker&term=has-read-more-block';

            // For the 'Pages' screen, WordPress will override the parent. We fix this with JS (see below).
            add_action( 'admin_footer-edit.php', array( $this, 'force_menu_highlight_script' ) );

        }

    }



    /**
     * Gotta use JS for this (would have liked to achive a PHP solution but the Pages admin menu is particularly stubborn).
     */
    public function force_menu_highlight_script() {

        // Only run this script on the 'Pages' tab of our custom view.
        $current_post_type = filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_STRING );

        if ( 'page' !== $current_post_type ) {

            return;

        }

        ?>
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                // Define the classes to swap
                const openClasses = ['wp-has-current-submenu', 'wp-menu-open'];
                const closedClass = 'wp-not-current-submenu';

                // --- Fix the 'Pages' Menu ---
                // Select both the <li> and its direct <a> child
                const pagesMenuElements = document.querySelectorAll('#menu-pages, #menu-pages > a.menu-top');
                pagesMenuElements.forEach(function(element) {
                    element.classList.remove(...openClasses);
                    element.classList.add(closedClass);
                });

                // --- Fix the 'Settings' Menu ---
                // Select both the <li> and its direct <a> child
                const settingsMenuElements = document.querySelectorAll('#menu-settings, #menu-settings > a.menu-top');
                settingsMenuElements.forEach(function(element) {
                    element.classList.remove(closedClass);
                    element.classList.add(...openClasses);
                });
            });
        </script>
        <?php

    }




    /**
     * Adds a description and a tabbed navigation to switch between post types.
     */
    public function add_post_list_description() {

        global $pagenow;

        // Check that we are on the correct admin page before changing anything.
        if ( is_admin() && 'edit.php' === $pagenow ) {

            $current_taxonomy = filter_input( INPUT_GET, 'taxonomy', FILTER_SANITIZE_STRING );
            $current_term     = filter_input( INPUT_GET, 'term', FILTER_SANITIZE_STRING );

            if ( 'dmg_rml_block_marker' === $current_taxonomy && 'has-read-more-block' === $current_term ) {
    
                // Display the main description for the screen.
                echo '<div class="notice notice-info"><p>';
                    echo esc_html__( 'This is a list of all items that contain the DMG Read More Link block. Use the tabs to switch between Posts and Pages.', 'dmg-rml' );
                echo '</p></div>';

                // --- Tab Implementation ---

                // Determine the currently active tab from the URL, defaulting to 'post'.
                $current_post_type = filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_STRING ) ?? 'post';
            
                // Base URL for our links.
                $base_url = admin_url( 'edit.php' );

                // The query arguments that are common to both tabs.
                $common_args = array(
                    'taxonomy' => 'dmg_rml_block_marker',
                    'term'     => 'has-read-more-block',
                );

                // Build the specific URLs for each tab.
                $posts_url = add_query_arg( array_merge( $common_args, array( 'post_type' => 'post' ) ), $base_url );
                $pages_url = add_query_arg( array_merge( $common_args, array( 'post_type' => 'page' ) ), $base_url );

                ?>
                <h2 class="nav-tab-wrapper" style="margin-bottom: 15px;">
                    <a href="<?php echo esc_url( $posts_url ); ?>" class="nav-tab <?php echo 'post' === $current_post_type ? 'nav-tab-active' : ''; ?>">
                        <?php esc_html_e( 'Posts', 'dmg-rml' ); ?>
                    </a>
                    <a href="<?php echo esc_url( $pages_url ); ?>" class="nav-tab <?php echo 'page' === $current_post_type ? 'nav-tab-active' : ''; ?>">
                        <?php esc_html_e( 'Pages', 'dmg-rml' ); ?>
                    </a>
                </h2>
                <?php
            }

        }

    }




    /**
     * Enqueue scripts/styles (admin)
     * 
     * @version 1.0.0
     * @since 1.0.0
     */
    function admin_scripts() {

        $screen           = get_current_screen();
        $current_taxonomy = filter_input( INPUT_GET, 'taxonomy', FILTER_SANITIZE_STRING );

        // This conditional checks the screen ID and taxonomy parameter.
        if ( $screen && 'edit' === $screen->base && 'dmg_rml_block_marker' === $current_taxonomy ) {

            wp_enqueue_style(
                'dmg-rml-posts-list',
                DMG_RML_PLUGIN_URL .  'build/admin/posts-list.css',
                array(),
                '1.0.0'
            );

        }

    }




}