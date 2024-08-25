<?php
// Enqueue Frontend Scripts and Styles
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
        $draft_saved_by = get_post_meta( $post->ID, '_fl_builder_draft_saved_by', true );
        $draft_saved_at = get_post_meta( $post->ID, '_fl_builder_draft_saved_at', true );

        // Get the user info from the user ID
        $user_info = $draft_saved_by ? get_userdata( $draft_saved_by ) : null;
        $saved_by_name = $user_info ? $user_info->user_login : '';

        // Check if there are unpublished changes (i.e., draft exists and differs from live data)
        if ( '' !== $draft && $draft != $live ) {
            // Prepare localized data
            $localized_data = array(
                'hasDraft'       => true,
                'postId'         => $post->ID,
                'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
                'nonce'          => wp_create_nonce( 'bb_draft_utility_nonce' ),
                'scheduledTime'  => $scheduled_time ? date( 'M j, Y H:i', strtotime( $scheduled_time ) ) : '', // Format the scheduled time
                'draftSavedBy'   => $saved_by_name,
                'draftSavedAt'   => $draft_saved_at ? date( 'M j, Y H:i', strtotime( $draft_saved_at ) ) : ''
            );

            // Enqueue necessary scripts and styles for the modal
            wp_enqueue_script( 'jquery-ui-dialog' );
            wp_enqueue_style( 'wp-jquery-ui-dialog' );
            wp_enqueue_script( 'bb-draft-frontend-js', BB_DRAFT_UTILITY_PLUGIN_URL . 'assets/js/bb-draft-frontend.js', array( 'jquery', 'jquery-ui-dialog' ), BB_DRAFT_UTILITY_VERSION, true );
            wp_enqueue_style( 'bb-draft-frontend-css', BB_DRAFT_UTILITY_PLUGIN_URL . 'assets/css/bb-draft-frontend.css', array(), BB_DRAFT_UTILITY_VERSION );

            // Localize the data to pass it to the frontend
            wp_localize_script( 'bb-draft-frontend-js', 'bbDraftUtility', $localized_data );
        }
    }
}
add_action( 'wp_enqueue_scripts', 'bb_draft_maybe_load_scripts' );