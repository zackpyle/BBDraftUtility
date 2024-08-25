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
        $draft_saved_by = get_post_meta( $post->ID, '_fl_builder_draft_saved_by', true );
        $draft_saved_at = get_post_meta( $post->ID, '_fl_builder_draft_saved_at', true );

        // Get user info if available
        $user_info = $draft_saved_by ? get_userdata( $draft_saved_by ) : null;
        $saved_by_name = $user_info ? $user_info->user_login : '';
        $formatted_saved_at = $draft_saved_at ? date( 'M j, Y, H:i', strtotime( $draft_saved_at ) ) : '';

        // If there are unpublished changes, display the post state
        if ( '' !== $draft && $draft != $live ) {
            // Always display the draft information link
            $post_states['bb_draft'] = '<a class="fl-saved-draft" data-post-id="' . esc_attr( $post->ID ) . '" 
                                        data-scheduled-time="' . esc_attr( $scheduled_time ) . '" 
                                        data-draft-saved-by="' . esc_attr( $saved_by_name ) . '" 
                                        data-draft-saved-at="' . esc_attr( $formatted_saved_at ) . '">Saved Draft';

            // Only display the calendar icon if scheduling is enabled and there is a scheduled time
            if ( $enable_scheduling && $scheduled_time ) {
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


// Display admin notices for saved draft
add_action( 'admin_notices', function() {
    global $post;

    if ( ! isset( $post->ID ) ) {
        return;
    }

    if ( get_post_meta( $post->ID, '_fl_builder_enabled', true ) ) {
        $draft = get_post_meta( $post->ID, '_fl_builder_draft', true );
        $live  = get_post_meta( $post->ID, '_fl_builder_data', true );
		$scheduled_time = get_post_meta( $post->ID, '_fl_builder_schedule', true );
		$saved_by = get_post_meta( $post->ID, '_fl_builder_draft_saved_by', true );
        $saved_at = get_post_meta( $post->ID, '_fl_builder_draft_saved_at', true );

        if ( '' !== $draft && $draft != $live ) {
            $message = sprintf(
                __( 'Notice: There is an unpublished %s Saved Draft', 'fl-builder' ),
                FLBuilderModel::get_branding()
            );

			// Append "Saved by" and "Saved at" information
            if ( $saved_by && $saved_at ) {
                // Get user information
                $user_info = get_userdata( $saved_by );
                $saved_by_name = $user_info ? $user_info->user_login : __( 'Unknown User', 'fl-builder' );
                $formatted_saved_at = date( 'M j, Y H:i', strtotime( $saved_at ) );

                $message .= sprintf( '. Saved by %s on %s.', esc_html( $saved_by_name ), esc_html( $formatted_saved_at ) );
            }
			
			// If there is a scheduled time, append it to the message
            if ( $scheduled_time ) {
                $formatted_time = date( 'M j, Y H:i', strtotime( $scheduled_time ) );
                $message .= sprintf( ' It is scheduled to be published on %s.', esc_html( $formatted_time ) );
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

// Record who saved the Saved Draft
add_action( 'fl_builder_after_save_draft', function( $post_id ) {
    // Save the current user ID and timestamp when the draft is saved
    $user_id = get_current_user_id();
    $timestamp = current_time( 'mysql' );

    update_post_meta( $post_id, '_fl_builder_draft_saved_by', $user_id );
    update_post_meta( $post_id, '_fl_builder_draft_saved_at', $timestamp );

    // Get the user info from the user ID
    $user_info = get_userdata( $user_id );
    $saved_by_name = $user_info ? $user_info->display_name : 'Unknown';

    // Log the draft save event to Simple History
    if ( function_exists( 'bb_draft_utility_log' ) ) {
        bb_draft_utility_log( sprintf( 'Draft for Post ID %d was saved by %s on %s.', $post_id, $saved_by_name, $timestamp ), 'info' );
    }
});