<?php
// Draft Notices: Adds notices to indicate if a page has an unpublished Beaver Builder draft

// Add a post state for pages with Beaver Builder drafts
add_filter( 'display_post_states', function( $post_states, $post ) {
    if ( get_post_meta( $post->ID, '_fl_builder_enabled', true ) ) {
        $draft = get_post_meta( $post->ID, '_fl_builder_draft', true );
        $live  = get_post_meta( $post->ID, '_fl_builder_data', true );
        $scheduled_time = get_post_meta( $post->ID, '_fl_builder_schedule', true );

        // If there are unpublished changes, display the post state
        if ( '' !== $draft && $draft != $live ) {
            $post_states['bb_draft'] = '<a class="fl_schedule" data-post-id="' . esc_attr( $post->ID ) . '" href="#">Unpublished Changes';
            if ( $scheduled_time ) {
                $formatted_time = date( 'M j, Y H:i', strtotime( $scheduled_time ) );
                // Store the scheduled time in the dashicon's data attribute
                $post_states['bb_draft'] .= ' <span class="dashicons dashicons-calendar-alt" title="Scheduled for ' . esc_attr( $formatted_time ) . '" data-scheduled-time="' . esc_attr( $scheduled_time ) . '"></span>';
            }
            $post_states['bb_draft'] .= '</a>'; // Close the anchor tag
        }
    }
    return $post_states;
}, 1000, 2 );

// Change the green dot to yellow in the admin bar too
add_action( 'admin_bar_menu', function( $wp_admin_bar ) {
    global $post;

    if ( ! isset( $post->ID ) ) {
        return;
    }

    // Check if Beaver Builder is enabled for this post
    if ( get_post_meta( $post->ID, '_fl_builder_enabled', true ) ) {
        $draft = get_post_meta( $post->ID, '_fl_builder_draft', true );
        $live  = get_post_meta( $post->ID, '_fl_builder_data', true );

        // Check if the draft exists and differs from the live version
        if ( '' !== $draft && $draft != $live ) {
            // Get the existing admin bar node for the Page Builder link
            $node = $wp_admin_bar->get_node( 'fl-builder-frontend-edit-link' );

            if ( $node ) {
                // Forcefully replace the dot color with yellow
                $new_title = str_replace( 'color:#6bc373;', 'color:#f1c40f;', $node->title );
                $wp_admin_bar->add_node(array(
                    'id'    => $node->id,
                    'title' => $new_title,
                ));
            }
        }
    }
}, 99999 );


// Display admin notices for unpublished changes
add_action( 'admin_notices', function() {
    global $post;

    if ( ! isset( $post->ID ) ) {
        return;
    }

    if ( get_post_meta( $post->ID, '_fl_builder_enabled', true ) ) {
        $draft = get_post_meta( $post->ID, '_fl_builder_draft', true );
        $live  = get_post_meta( $post->ID, '_fl_builder_data', true );

        if ( '' !== $draft && $draft != $live ) {
            $message = 'Unpublished Changes: There is an unpublished draft for this page using Beaver Builder';
            $type    = 'warning';

            echo '<div class="notice notice-' . esc_attr( $type ) . ' is-dismissible">';
            echo wpautop( esc_html( $message ) );
            echo '</div>';

            ?>
            <script>
                (function(wp) {
                    wp.data.dispatch( 'core/notices' ).createNotice(
                        '<?php echo esc_js( $type ); ?>',
                        '<?php echo esc_js( $message ); ?>',
                        {
                            isDismissible: true,
                        }
                    );
                })(window.wp);
            </script>
            <?php
        }
    }
});
