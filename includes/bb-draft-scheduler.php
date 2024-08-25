<?php
// Scheduling: Handles scheduling Beaver Builder draft changes


function bb_draft_utility_should_enable_scheduling() {
    // Allow scheduling by default, but this can be overridden using the filter
    return apply_filters( 'bb_draft_utility_enable_scheduling', true );
}

/**
 * Log a message to Simple History if available, and always log errors to the PHP error log.
 *
 * @param string $message The message to log.
 * @param string $type    The type of log: 'success' or 'error'.
 */
function bb_draft_utility_log( $message, $type = 'info' ) {
    // Always log errors to the PHP error log
    if ( 'error' === $type ) {
        error_log( $message );
    }

    // Log to Simple History if it's available
    if ( has_filter( 'simple_history_log' ) ) {
        apply_filters( 'simple_history_log', ucfirst( $type ) . ": $message" );
    }
}


// Handle AJAX request for scheduling draft changes
add_action( 'wp_ajax_fl_schedule_changes', function() {
    check_ajax_referer( 'bb_draft_utility_nonce', 'nonce' );

    $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
    $scheduled_time = isset( $_POST['scheduled_time'] ) ? sanitize_text_field( $_POST['scheduled_time'] ) : '';

    if ( ! $post_id || ! $scheduled_time ) {
        bb_draft_utility_log( "Failed to schedule: Invalid data. Post ID: $post_id, Scheduled Time: $scheduled_time", 'error' );
        wp_send_json_error( 'Invalid data' );
    }

    // Convert the scheduled time to a Unix timestamp based on server time
    $timestamp = strtotime( $scheduled_time );
    $current_time = strtotime( current_time( 'Y-m-d H:i:s' ) );

    if ( ! $timestamp || $timestamp <= $current_time ) {
        bb_draft_utility_log( "Failed to schedule draft: Invalid or past date/time. Post ID: $post_id, Scheduled Time: $scheduled_time", 'error' );
        wp_send_json_error( 'Invalid or past date/time.' );
    }

    // Clear any existing scheduled events for this hook and post ID
    wp_clear_scheduled_hook( 'publish_bb_draft_changes', array( $post_id ) );

    // Attempt to schedule the event
    $event_scheduled = wp_schedule_single_event( $timestamp, 'publish_bb_draft_changes', array( $post_id ) );

    if ( $event_scheduled ) {
        // Store the scheduled time in post meta for reference
        update_post_meta( $post_id, '_fl_builder_schedule', $scheduled_time );
        bb_draft_utility_log( "Scheduled draft to for Post ID: $post_id. Scheduled Time: $scheduled_time.", 'success' );
        wp_send_json_success( 'Changes scheduled successfully.' );
    } else {
        bb_draft_utility_log( "Failed to schedule for Post ID: $post_id, Scheduled Time: $scheduled_time (timestamp: $timestamp)", 'error' );
        wp_send_json_error( 'Failed to schedule event.' );
    }
});


// Handle AJAX request to remove scheduled publishing
add_action( 'wp_ajax_fl_remove_schedule', function() {
    check_ajax_referer( 'bb_draft_utility_nonce', 'nonce' );

    $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;

    if ( ! $post_id ) {
        bb_draft_utility_log( "Failed to remove schedule: Invalid post ID.", 'error' );
        wp_send_json_error( 'Invalid post ID.' );
    }

    // Remove the scheduled time and clear any scheduled cron event
    delete_post_meta( $post_id, '_fl_builder_schedule' );
    wp_clear_scheduled_hook( 'publish_bb_draft_changes', array( $post_id ) );

    bb_draft_utility_log( "Removed scheduled publishing for Post ID: $post_id.", 'success' );

    wp_send_json_success( 'Scheduled publishing removed.' );
});


// Handle AJAX request to delete unpublished draft
add_action( 'wp_ajax_fl_delete_draft', function() {
    check_ajax_referer( 'bb_draft_utility_nonce', 'nonce' );

    $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;

    if ( ! $post_id ) {
        bb_draft_utility_log( "Failed to delete draft: Invalid post ID.", 'error' );
        wp_send_json_error( 'Invalid post ID.' );
    }

    // Remove the scheduled time, clear any scheduled cron event, and delete the draft data
    delete_post_meta( $post_id, '_fl_builder_schedule' );
    wp_clear_scheduled_hook( 'publish_bb_draft_changes', array( $post_id ) );

    // Delete draft data
    $deleted_draft = delete_post_meta( $post_id, '_fl_builder_draft' );
    $deleted_draft_settings = delete_post_meta( $post_id, '_fl_builder_draft_settings' );
	delete_post_meta( $post_id, '_fl_builder_draft_saved_by' );
    delete_post_meta( $post_id, '_fl_builder_draft_saved_at' );

    // Log what was actually deleted
    if ( $deleted_draft ) {
        //bb_draft_utility_log( "Successfully deleted '_fl_builder_draft' for Post ID: $post_id.", 'success' );
    } else {
        bb_draft_utility_log( "Failed to delete '_fl_builder_draft' for Post ID: $post_id.", 'error' );
    }

    if ( $deleted_draft_settings ) {
        //bb_draft_utility_log( "Successfully deleted '_fl_builder_draft_settings' for Post ID: $post_id.", 'success' );
    } else {
        bb_draft_utility_log( "Failed to delete '_fl_builder_draft_settings' for Post ID: $post_id.", 'error' );
    }

    // Only send success response if both meta fields were deleted
    if ( $deleted_draft && $deleted_draft_settings ) {
		bb_draft_utility_log( "Deleted saved draft for Post ID: $post_id.", 'success' );
        wp_send_json_success( 'Saved draft deleted.' );
    } else {
        wp_send_json_error( 'Failed to delete the draft completely.' );
    }
});


// Cron event to publish draft changes
add_action( 'publish_bb_draft_changes', function( $post_id ) {
    // bb_draft_utility_log( "Cron event triggered for publishing draft changes. Post ID: $post_id", 'info' );

    $draft_data = get_post_meta( $post_id, '_fl_builder_draft', true );
    $draft_settings = get_post_meta( $post_id, '_fl_builder_draft_settings', true );

    if ( ! empty( $draft_data ) && ! empty( $draft_settings ) ) {
        update_post_meta( $post_id, '_fl_builder_data', $draft_data );
        update_post_meta( $post_id, '_fl_builder_data_settings', $draft_settings );

        // Log successful publication of the draft
        bb_draft_utility_log( "Published draft changes for Post ID: $post_id.", 'success' );

        // Delete the draft data as we already published it
        delete_post_meta( $post_id, '_fl_builder_draft' );
        delete_post_meta( $post_id, '_fl_builder_draft_settings' );
		delete_post_meta( $post_id, '_fl_builder_draft_saved_by' );
    	delete_post_meta( $post_id, '_fl_builder_draft_saved_at' );
    } else {
        // Log if there was an issue with the draft data
        bb_draft_utility_log( "Failed to publish draft changes for Post ID: $post_id. Missing draft data or settings.", 'error' );
    }

    // Clean up the scheduled time meta field
    delete_post_meta( $post_id, '_fl_builder_schedule' );
});