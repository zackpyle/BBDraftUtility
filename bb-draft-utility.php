<?php
/*
Plugin Name: Beaver Builder Drafts Utility
Description: Provides utilities for Beaver Builder drafts, including scheduling and draft notices.
Version: 1.3
Author: PYLE/DIGITAL
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

define( 'BB_DRAFT_UTILITY_VERSION', '1.3' );
define( 'BB_DRAFT_UTILITY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BB_DRAFT_UTILITY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );


// Include our files
include_once BB_DRAFT_UTILITY_PLUGIN_DIR . 'includes/bb-draft-notices.php';
include_once BB_DRAFT_UTILITY_PLUGIN_DIR . 'includes/bb-draft-scheduler.php';
include_once BB_DRAFT_UTILITY_PLUGIN_DIR . 'includes/GithubUpdater.php';
include_once BB_DRAFT_UTILITY_PLUGIN_DIR . 'includes/bb-draft-enqueue-frontend.php';
include_once BB_DRAFT_UTILITY_PLUGIN_DIR . 'includes/bb-draft-enqueue-backend.php';


// Init Github updater
function init_updater() {
	$updater = new BBDraftUtility\GithubUpdater( __FILE__ );
	$updater->set_username( 'zackpyle' );
	$updater->set_repository( 'BBDraftUtility' );
	$updater->set_settings( array(
	    'requires'        => '5.1',
	    'tested'          => '6.6.1',
	    'rating'          => '100.0',
	    'num_ratings'     => '10',
	    'downloaded'      => '100',
	    'added'           => '2024-08-22',
	) );
	$updater->initialize();
}
init_updater();


/**
 * Hooks
 *
 * These functions provide hooks for customizing the plugin's behavior:
 * - 'bb_draft_utility_enable_scheduling' filter allows enabling/disabling the scheduling feature.
 * - 'bb_draft_utility_branding' filter allows overriding the default Beaver Builder branding.
 * - 'bb_draft_utility_show_saved_info' filter determines whether to show the "Draft saved by" and "on" information.
*/
function bb_draft_utility_enable_scheduling() {
    return apply_filters( 'bb_draft_utility_enable_scheduling', true );
}
function bb_draft_utility_branding() {
    $default_branding = FLBuilderModel::get_branding();
    return apply_filters('bb_draft_utility_branding', $default_branding);
}
function bb_draft_utility_show_saved_info() {
    return apply_filters( 'bb_draft_utility_show_saved_info', true );
}
