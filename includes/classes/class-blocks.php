<?php

namespace DMG\RML;

class Blocks {

    public function __construct() {

        // Register Gutenberg blocks.
        add_action( 'init', array($this, 'blocks_init'), 20 );

    }




    /**
     * Registers the block using the metadata loaded from the `block.json` file.
     * Behind the scenes, it registers also all assets so they can be enqueued
     * through the block editor in the corresponding context.
     *
     * @see https://developer.wordpress.org/reference/functions/register_block_type/
     */
    function blocks_init() {

        register_block_type( DMG_RML_PLUGIN_PATH . '/build/blocks/read-more-link', 
            array(
                'render_callback' => array($this, 'read_more_link_render_content_callback')
            )
        );

    }




    /**
     * Renders the content for the block-a block.
     *
     * @param array $attributes The attributes of the block.
     * @param string $content The content of the block.
     * @param WP_Block $block The block object.
     * 
     * @return string The rendered content.
     * 
     * @since 1.0.0
     */
    function read_more_link_render_content_callback( $attributes, $content, $block ) {

        $post_id = $attributes['postID'] ?? null;

        // If no post is selected, render nothing on the front-end.
        if ( ! $post_id ) {
            return '';
        }

        $post_url   = get_permalink( $post_id );
        $post_title = get_the_title( $post_id );

        // If the post has been deleted or is invalid, render nothing.
        if ( ! $post_url || ! $post_title ) {
            return '';
        }

        // Translators: %s is a placeholder for the post title.
        $read_more_text = sprintf(
            __( 'Read More: %s', 'dmg-rml' ),
            esc_html( $post_title )
        );

        $wrapper_attributes = get_block_wrapper_attributes([
            'class' => 'dmg-read-more'
        ]);

        $the_content = sprintf(
            '<p %1$s><a class="dmg-read-more__link" href="%2$s">%3$s</a></p>',
            $wrapper_attributes,
            esc_url( $post_url ),
            $read_more_text
        );

        return $the_content;

    }




}