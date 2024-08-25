<?php
/*
Plugin Name: Beaver Builder Drafts Utility
Description: Provides utilities for Beaver Builder drafts, including scheduling and draft notices.
Version: 1.2.1
Author: PYLE/DIGITAL
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

define( 'BB_DRAFT_UTILITY_VERSION', '1.2.1' );
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
	    'downloaded'      => '10',
	    'added'           => '2024-08-22',
	) );
	$updater->initialize();
}
init_updater();