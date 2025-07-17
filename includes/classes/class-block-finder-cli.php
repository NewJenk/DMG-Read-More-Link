<?php

namespace DMG\RML;

// Only load this file when WP-CLI is running to be safe.
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {

    return;

}

/**
 * Finds posts containing the 'dmg-rml' block.
 */
class BlockFinderCli extends \WP_CLI_Command {

    /**
     * The constructor is where we register the command.
     * This runs when the Registry creates the object.
     */
    public function __construct() {

        \WP_CLI::add_command( 'dmg-read-more', $this );

    }

    /**
     * Finds all posts with the block and outputs their IDs.
     *
     * ## OPTIONS
     *
     * [--date-before=<date>]
     * : Find posts published before this date (format: Y-m-d).
     *
     * [--date-after=<date>]
     * : Find posts published after this date (format: Y-m-d).
     *
     * ## EXAMPLES
     *
     * # Search for blocks in posts from the last 30 days (default).
     * wp dmg-read-more search
     *
     * # Search for blocks in posts published after 2025-01-15.
     * wp dmg-read-more search --date-after=2025-01-15
     *
     * # Search for blocks in posts published between 2025-01-01 and 2025-01-31.
     * wp dmg-read-more search --date-after=2025-01-01 --date-before=2025-01-31
     */
    public function search( $args, $assoc_args ) {

        $text_domain = 'dmg-rml';

        // Extract date arguments from the associative array.
        $date_before = \WP_CLI\Utils\get_flag_value( $assoc_args, 'date-before' );
        $date_after  = \WP_CLI\Utils\get_flag_value( $assoc_args, 'date-after' );

        $date_query  = [];
        $log_message = '';

        // Validate dates and build the date_query array.
        if ( $date_after ) {

            if ( false === strtotime( $date_after ) ) {

                \WP_CLI::error( __( 'Invalid --date-after format. Please use Y-m-d.', $text_domain ) );

            }

            $date_query['after'] = $date_after;

        }

        if ( $date_before ) {

            if ( false === strtotime( $date_before ) ) {

                \WP_CLI::error( __( 'Invalid --date-before format. Please use Y-m-d.', $text_domain ) );

            }

            $date_query['before'] = $date_before;

        }

        if ( ! empty( $date_query ) ) {

            $date_query['inclusive'] = true;

        }

        // Build the translatable log message based on which dates were provided.
        if ( $date_after && $date_before ) {

            // Case 1: Both dates are provided.
            $log_message = sprintf(
                /* translators: 1: A date (e.g. 2025-01-15), 2: A date (e.g. 2025-02-15) */
                __( 'Searching for posts published after %1$s and before %2$s...', $text_domain ),
                $date_after,
                $date_before
            );

        } elseif ( $date_after ) {

            // Case 2: Only the 'after' date is provided.
            $log_message = sprintf(
                /* translators: %s: A date (e.g. 2025-01-15) */
                __( 'Searching for posts published after %s...', $text_domain ),
                $date_after
            );

        } elseif ( $date_before ) {

            // Case 3: Only the 'before' date is provided.
            $log_message = sprintf(
                /* translators: %s: A date (e.g. 2025-01-15) */
                __( 'Searching for posts published before %s...', $text_domain ),
                $date_before
            );

        } else {

            // Case 4: No dates are provided (the default).
            $date_query = [
                'after'     => '30 days ago',
                'inclusive' => true,
            ];
            $log_message = __( 'Searching for posts published in the last 30 days...', $text_domain );

        }

        $paged       = 1;
        $batch_size  = 1000;
        $total_found = 0;

        \WP_CLI::log( $log_message );

        do {
            $query_args = [
                'post_type'      => [ 'post', 'page' ],
                'posts_per_page' => $batch_size,
                'paged'          => $paged,
                'fields'         => 'ids',
                'tax_query'      => [
                    [
                        'taxonomy' => 'dmg_rml_block_marker',
                        'field'    => 'slug',
                        'terms'    => 'has-read-more-block',
                    ],
                ],
                'date_query' => $date_query,
            ];

            $query = new \WP_Query( $query_args );

            if ( ! $query->have_posts() ) {

                break;

            }

            foreach ( $query->posts as $post_id ) {

                \WP_CLI::line( $post_id );
                $total_found++;

            }

            // Free up memory.
            $this->stop_the_insanity();
            $paged++;

        } while ( true );

        // Use _n() to handle singular/plural forms correctly.
        $success_message = sprintf(
            _n(
                'Finished! Found a total of %d post.',
                'Finished! Found a total of %d posts.',
                $total_found,
                $text_domain
            ),
            $total_found
        );

        \WP_CLI::success( $success_message );

    }

    /**
     * Clears the object cache to prevent memory exhaustion during long loops.
     *
     * @link https://github.com/Automattic/VIP-Coding-Standards/issues/151
     * @link https://docs.wpvip.com/vip-cli/wp-cli-with-vip-cli/write-custom-wp-cli-commands/
     */
    private function stop_the_insanity() {

        global $wpdb, $wp_object_cache;

        $wpdb->queries = [];

        if ( is_object( $wp_object_cache ) ) {

            $wp_object_cache->cache = [];

        }

    }

}
