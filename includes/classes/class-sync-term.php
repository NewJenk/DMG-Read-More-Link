<?php

namespace DMG\RML;

class SyncTerm {

    public function __construct() {

        add_action( 'save_post', array($this, 'sync_block_term'), 10, 2 );

    }




    /**
     * Updates post meta based on the presence of the block.
     *
     * @param int     $post_id The ID of the post being saved.
     * @param \WP_Post $post    The post object.
     */
    public function sync_block_term( $post_id, $post ) {

        // Replace with your actual block name
        $block_name = 'dmg-rml/read-more-link'; 
        $taxonomy   = 'dmg_rml_block_marker';
        $term       = 'has-read-more-block'; // The specific term we are adding/removing

        // Check if the post content has the block
        if ( has_block( $block_name, $post->post_content ) ) {
    
            // Block exists, so add the term (this also creates the term if it doesn't exist)
            wp_set_object_terms( $post_id, $term, $taxonomy, true );
    
        } else {

            // Block does not exist, so remove the term
            wp_remove_object_terms( $post_id, $term, $taxonomy );

        }

    }




}
