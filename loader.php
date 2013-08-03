<?php
/*
Plugin Name: BuddyPress Wall
Plugin URI: 
Description: Turn your Buddypress Activity Component to a Facebook-style Wall.
Profiles with Facebook-style walls
Version: 0.8.1
Requires at least:  WP 3.4, BuddyPress 1.5
Tested up to: BuddyPress 1.7, 1.8
License: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
Author: Meg@Info
Author URI: http://profiles.wordpress.org/megainfo 
Network: true
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/*************************************************************************************************************
 --- BuddyPress Wall 0.8.1 ---
 *************************************************************************************************************/

// Define a constant that can be checked to see if the component is installed or not.
define( 'BP_WALL_IS_INSTALLED', 1 );

// Define a constant that will hold the current version number of the component
// This can be useful if you need to run update scripts or do compatibility checks in the future
define( 'BP_WALL_VERSION', '0.8.1' );

// Define a constant that we can use to construct file paths throughout the component
define( 'BP_WALL_PLUGIN_DIR', dirname( __FILE__ ) );

define ( 'BP_WALL_DB_VERSION', '1.0' );

/* Only load the component if BuddyPress is loaded and initialized. */
function bp_wall_init() {
	require( dirname( __FILE__ ) . '/includes/bp-wall-loader.php' );
}
add_action( 'bp_include', 'bp_wall_init' );

/* Put setup procedures to be run when the plugin is activated in the following function */
function bp_wall_activate() {
	global $bp;

	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( !is_plugin_active( 'buddypress/bp-loader.php' ) ) {
		//deactivate_plugins( basename( __FILE__ ) ); // Deactivate this plugin
		die( _e( 'You cannot enable BuddyPress Wall <strong>BuddyPress</strong> is not active. Please install and activate BuddyPress before trying to activate Buddypress Wall.' , 'bp-wall' ) );
	}	
}
register_activation_hook( __FILE__, 'bp_wall_activate' );

/* On deacativation, clean up anything your component has added. */
function bp_wall_deactivate() {
	/* You might want to delete any options or tables that your component created. */
}
register_deactivation_hook( __FILE__, 'bp_wall_deactivate' );


function bp_wall_template_filter_init() {
	add_action( 'bp_template_content', 'bp_wall_filter_template_content' );
	add_filter( 'bp_get_template_part', 'bp_wall_template_part_filter', 10, 3 );
 
}
add_action('bp_init', 'bp_wall_template_filter_init');
 
function bp_wall_template_part_filter( $templates, $slug, $name ) {
	
	if ( 'activity/index' == $slug  ) {
		//return bp_buffer_template_part( 'activity/index-wall' );
		$templates[0] = 'activity/index-wall.php';
	}
	elseif ( 'members/single/home' == $slug  ) {
		$templates[0] = 'members/single/home-wall.php';
		//return bp_buffer_template_part( 'members/single/home-wall' );
	}
	elseif ( 'groups/single/home' == $slug  ) {
		$templates[0] = 'groups/single/home-wall.php';
		//return bp_buffer_template_part( 'members/single/home-wall' );
	}

	return $templates;
	//return bp_get_template_part( 'members/single/plugins' );
  
}
 
function bp_wall_filter_template_content() {
   // bp_buffer_template_part( 'activity/index-wall' );
}

