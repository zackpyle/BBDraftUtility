<?php
// Draft Notices: Adds notices to indicate if a page has an unpublished Beaver Builder draft

// Add a post state for pages with Beaver Builder drafts
add_filter( 'display_post_states', function( $post_states, $post ) {

	// Check if scheduling is enabled
    $enable_scheduling = apply_filters( 'bb_draft_utility_enable_scheduling', true );
	
    if ( get_post_meta( $post->ID, '_fl_builder_enabled', true ) ) {
        $draft = get_post_meta( $post->ID, '_fl_builder_draft', true );
        $live  = get_post_meta( $post->ID, '_fl_builder_data', true );
        $scheduled_time = get_post_meta( $post->ID, '_fl_builder_schedule', true );

        // If there are unpublished changes, display the post state
        if ( '' !== $draft && $draft != $live ) {
            if ( $enable_scheduling ) {
                // Display the clickable link and calendar icon if scheduling is enabled
                $post_states['bb_draft'] = '<a class="fl_schedule" data-post-id="' . esc_attr( $post->ID ) . '" href="#">Unpublished Changes';
                if ( $scheduled_time ) {
                    $formatted_time = date( 'M j, Y H:i', strtotime( $scheduled_time ) );
                    // Store the scheduled time in the dashicon's data attribute
                    $post_states['bb_draft'] .= ' <span class="dashicons dashicons-calendar-alt" title="Scheduled for ' . esc_attr( $formatted_time ) . '" data-scheduled-time="' . esc_attr( $scheduled_time ) . '"></span>';
                }
                $post_states['bb_draft'] .= '</a>'; // Close the anchor tag
            } else {
                // Display plain text if scheduling is disabled
                $post_states['bb_draft'] = 'Unpublished Changes';
            }
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
		$scheduled_time = get_post_meta( $post->ID, '_fl_builder_schedule', true );

        if ( '' !== $draft && $draft != $live ) {
            $message = sprintf(
                __( 'Unpublished Changes: There is an unpublished %s draft', 'fl-builder' ),
                FLBuilderModel::get_branding()
            );
			// If there is a scheduled time, append it to the message
            if ( $scheduled_time ) {
                $formatted_time = date( 'M j, Y H:i', strtotime( $scheduled_time ) );
                $message .= sprintf( '. It is scheduled to be published on %s.', esc_html( $formatted_time ) );
            }
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

function bb_draft_maybe_load_scripts() {
    // Check if Beaver Builder is available and active on this page
    if ( ! class_exists( 'FLBuilderModel' ) || ! FLBuilderModel::is_builder_active() ) {
        return;
    }

    global $post;

    // Ensure post ID is available and Beaver Builder is active
    if ( $post ) {
        $draft = get_post_meta( $post->ID, '_fl_builder_draft', true );
        $live  = get_post_meta( $post->ID, '_fl_builder_data', true );
        $scheduled_time = get_post_meta( $post->ID, '_fl_builder_schedule', true );

        // Check if there are unpublished changes (i.e., draft exists and differs from live data)
        if ( '' !== $draft && $draft != $live ) {
            // Prepare localized data
            $localized_data = array(
                'hasDraft'      => true,  // Indicate that there is a draft
                'postId'        => $post->ID,
                'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
                'nonce'         => wp_create_nonce( 'fl-schedule-changes' ),
                'scheduledTime' => $scheduled_time ? date( 'M j, Y H:i', strtotime( $scheduled_time ) ) : '', // Format the scheduled time
            );

            // Enqueue necessary scripts and styles for the modal
            wp_enqueue_script( 'jquery-ui-dialog' );
            wp_enqueue_style( 'wp-jquery-ui-dialog' );
            wp_enqueue_script( 'bb-draft-modal', BB_DRAFT_UTILITY_PLUGIN_URL . 'assets/js/bb-draft-modal.js', array( 'jquery', 'jquery-ui-dialog' ), BB_DRAFT_UTILITY_VERSION, true );
            wp_enqueue_style( 'bb-draft-modal-css', BB_DRAFT_UTILITY_PLUGIN_URL . 'assets/css/bb-draft-modal.css', array(), BB_DRAFT_UTILITY_VERSION );

            // Localize the data to pass it to the frontend
            wp_localize_script( 'bb-draft-modal', 'bbDraftUtility', $localized_data );
        }
    }
}
add_action( 'wp_enqueue_scripts', 'bb_draft_maybe_load_scripts' );