<?php
/*
Plugin Name: Beaver Builder Drafts Utility
Description: Provides utilities for Beaver Builder drafts, including scheduling and draft notices.
Version: 1.0
Author: PYLE/DIGITAL
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

define( 'BB_DRAFT_UTILITY_VERSION', '1.0' );

// Include the Draft Notices functionality
include_once plugin_dir_path( __FILE__ ) . 'includes/draft-notices.php';

// Include the Scheduling functionality
include_once plugin_dir_path( __FILE__ ) . 'includes/scheduler.php';

// Enqueue Scripts and Styles
add_action( 'admin_enqueue_scripts', function( $hook_suffix ) {
    // Only load on specific admin pages, such as post editing pages
    if ( in_array( $hook_suffix, array( 'post.php', 'post-new.php', 'edit.php' ), true ) ) {
        // Enqueue jQuery UI and Tooltip
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-tooltip' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_style( 'wp-jquery-ui-dialog' );

        // Enqueue the plugin's JavaScript and CSS with versioning for cache busting
        wp_enqueue_script( 'bb-draft-utility-js', plugin_dir_url( __FILE__ ) . 'assets/js/bb-draft-utility.js', array( 'jquery', 'jquery-ui-dialog', 'jquery-ui-tooltip' ), BB_DRAFT_UTILITY_VERSION, true );
        wp_enqueue_style( 'bb-draft-utility-css', plugin_dir_url( __FILE__ ) . 'assets/css/bb-draft-utility.css', array( 'wp-jquery-ui-dialog' ), BB_DRAFT_UTILITY_VERSION );
    }
});