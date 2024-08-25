<?php
// Enqueue Backend Scripts and Styles
add_action( 'admin_enqueue_scripts', function( $hook_suffix ) {
    // Only load on specific admin pages
    if ( in_array( $hook_suffix, array( 'post.php', 'post-new.php', 'edit.php' ), true ) ) {
        // Enqueue jQuery UI and Tooltip
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-tooltip' );
        wp_enqueue_script( 'jquery-ui-dialog' );
        wp_enqueue_style( 'wp-jquery-ui-dialog' );

        // Enqueue backend JavaScript and CSS with versioning for cache busting
        wp_enqueue_script( 'bb-draft-backend-js', BB_DRAFT_UTILITY_PLUGIN_URL . 'assets/js/bb-draft-backend.js', array( 'jquery', 'jquery-ui-dialog', 'jquery-ui-tooltip' ), BB_DRAFT_UTILITY_VERSION, true );
        wp_enqueue_style( 'bb-draft-backend-css', BB_DRAFT_UTILITY_PLUGIN_URL . 'assets/css/bb-draft-backend.css', array( 'wp-jquery-ui-dialog' ), BB_DRAFT_UTILITY_VERSION );

        // Localize script with data about the scheduling filter
        wp_localize_script( 'bb-draft-backend-js', 'bbDraftUtility', array(
            'enableScheduling' => bb_draft_utility_enable_scheduling(),
            'showSavedInfo'    => bb_draft_utility_show_saved_info(),
            'builderName'      => bb_draft_utility_branding(),
            'postId'           => get_the_ID(),
            'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
            'nonce'            => wp_create_nonce( 'bb_draft_utility_nonce' )
        ));
    }
});