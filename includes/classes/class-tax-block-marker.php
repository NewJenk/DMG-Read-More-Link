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

        add_action( 'pre_get_posts', array( $this, 'show_all_post_types_in_list' ) );

		add_action( 'admin_menu', array( $this, 'add_posts_link_to_settings' ));

		add_filter( 'admin_head', array( $this, 'admin_menu_highlight' ) );

		add_filter( 'post_type_labels_post', array( $this, 'change_post_labels' ) );

		add_action( 'all_admin_notices', array( $this, 'add_post_list_description' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 99 );

        // Custom column.
        add_filter( 'manage_edit-post_columns', array( $this, 'add_post_type_column' ) );
        add_action( 'manage_posts_custom_column', array( $this, 'render_post_type_column' ), 10, 2 );
        add_action( 'manage_pages_custom_column', array( $this, 'render_post_type_column' ), 10, 2 );

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

		$post_types_array = apply_filters( 'dmg_rml_block_marker_post_types', array( 'post' ) );

		// Register "DMG_RML Marker" taxonomy.
		register_taxonomy( 'dmg_rml_block_marker', $post_types_array, $args );

	}




    /**
     * Includes posts and pages in the query for our custom screen.
     *
     * @param \WP_Query $query The main WordPress query object.
     */
    public function show_all_post_types_in_list( $query ) {

        // We must only run this for the main query on the correct admin screen.
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }

        // Check if we are on our specific filtered view.
        $current_taxonomy = filter_input( INPUT_GET, 'taxonomy', FILTER_SANITIZE_STRING );
        $current_term     = filter_input( INPUT_GET, 'term', FILTER_SANITIZE_STRING );

        if ( 'dmg_rml_block_marker' === $current_taxonomy && 'has-read-more-block' === $current_term ) {

            $query->set( 'post_type', array( 'post', 'page' ) );

        }

    }




	public function add_posts_link_to_settings() {
		add_submenu_page(
			'edit.php',
			__( 'Posts with DMG Read More Link Block', 'simple-read-more-link' ),
			__( 'DMG Read More Link Posts', 'simple-read-more-link' ),
			'manage_options',
			// Use the destination URL as the menu slug.
			'edit.php?taxonomy=dmg_rml_block_marker&term=has-read-more-block'
		);
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
            
            // Force the "Posts" menu to be the active parent.
            $parent_file = 'edit.php';
            
            // Set the submenu file.
            $submenu_file = 'edit.php?taxonomy=dmg_rml_block_marker&term=has-read-more-block';
        }

    }




    /**
     * Changes the main title by filtering the 'post' post type's labels.
     */
    public function change_post_labels( $labels ) {

        global $pagenow;

        // Check that we are on the correct admin page before changing anything.
        if ( is_admin() && 'edit.php' === $pagenow ) {
            $current_taxonomy = filter_input( INPUT_GET, 'taxonomy', FILTER_SANITIZE_STRING );
            $current_term     = filter_input( INPUT_GET, 'term', FILTER_SANITIZE_STRING );

            if ( 'dmg_rml_block_marker' === $current_taxonomy && 'has-read-more-block' === $current_term ) {
                $labels->name = __( 'DMG Read More Link Posts', 'simple-read-more-link' );
            }
        }

        // Always return the labels object.
        return $labels;

    }




    /**
     * Adds a description below the title.
     */
    public function add_post_list_description() {

		global $pagenow;

        // Check that we are on the correct admin page before changing anything.
        if ( is_admin() && 'edit.php' === $pagenow ) {
            $current_taxonomy = filter_input( INPUT_GET, 'taxonomy', FILTER_SANITIZE_STRING );
            $current_term     = filter_input( INPUT_GET, 'term', FILTER_SANITIZE_STRING );

            if ( 'dmg_rml_block_marker' === $current_taxonomy && 'has-read-more-block' === $current_term ) {
				echo '<div class="notice notice-info"><p>';
				echo esc_html__( 'This is a list of all posts that contain the DMG Read More Link block.', 'simple-read-more-link' );
				echo '</p></div>';
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

        // This conditional is used as this is only needed on the LMS options screen.
        if ( get_current_screen()->taxonomy == 'dmg_rml_block_marker' ) {

            wp_enqueue_style(
                'dmg-rml-posts-list',
                DMG_RML_PLUGIN_URL .  'build/admin/posts-list.css',
                array(),
                '1.0.0'
            );

        }

    }




    /**
     * Adds a 'Post Type' column to the posts list table header.
     */
    public function add_post_type_column( $columns ) {
    
        global $pagenow;
    
        if ( is_admin() && 'edit.php' === $pagenow ) {
    
            $current_taxonomy = filter_input( INPUT_GET, 'taxonomy', FILTER_SANITIZE_STRING );
            $current_term     = filter_input( INPUT_GET, 'term', FILTER_SANITIZE_STRING );

            if ( 'dmg_rml_block_marker' === $current_taxonomy && 'has-read-more-block' === $current_term ) {

                // Create a new columns array to insert the Post Type column after the title.
                $new_columns = [];
    
                foreach ( $columns as $key => $title ) {

                    $new_columns[ $key ] = $title;

                    if ( 'title' === $key ) {

                        $new_columns['post_type'] = __( 'Post Type', 'dmg-rml' );

                    }

                }

                return $new_columns;

            }

        }

        return $columns;

    }

    /**
     * Renders the content for the custom 'Post Type' column.
     */
    public function render_post_type_column( $column_name, $post_id ) {
        // This function only needs to check the column name, as the column
        // will only be added on the correct screen.
        if ( 'post_type' === $column_name ) {

            $post_type_object = get_post_type_object( get_post_type( $post_id ) );

            if ( $post_type_object ) {
    
                echo esc_html( $post_type_object->labels->singular_name );

            }

        }

    }




}
