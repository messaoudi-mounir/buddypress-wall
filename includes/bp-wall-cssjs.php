<?php
/**
 * BP Wall Css and js enqueue  
 *
 * @package BP-Wall
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Enqueue the javascript files
 * 
 */
function bp_wall_add_js(){

    if( !is_user_logged_in() )
        return ;//we do not want to include the js

    wp_enqueue_script( 'bp-wall-autosize-js', plugins_url( 'js/jquery.autosize.js' ,  __FILE__ ), array('jquery') );
    wp_enqueue_script( 'bp-wall-js', plugins_url( 'js/bp-wall.js' ,  __FILE__ ), array('bp-wall-autosize-js'), false, true );

    global $wp_scripts;

	// Add words that we need to use in JS to the end of the page so they can be translated and still used.
	$params = array(
		'my_favs'           => __( 'My Likes', 'bp-wall' ),
		'mark_as_fav'	    => __( 'Like this post', 'bp-wall' ),
		'remove_fav'	    => __( 'Unlike this post', 'bp-wall' ),
	);
	wp_localize_script( 'bp-wall-js', 'BPWALL_DTheme', $params );

}
add_action( "wp_enqueue_scripts","bp_wall_add_js", 10 );
	

/**
 * Enqueue stylesheet files
 * 
 */
function bp_wall_add_css() {
	global $bp;

    if( !is_user_logged_in() )
        return ;//we do not want to include the js
	
	if ( !bp_wall_is_bp_default() )
		wp_enqueue_style( 'bp-wall-css', plugins_url( 'css/bp-wall.css' ,  __FILE__ ), array('bp-legacy-css') ); 
    else
    	wp_enqueue_style( 'bp-wall-css', plugins_url( 'css/bp-wall.css' ,  __FILE__ ) ); 
    	
}
add_action( 'wp_enqueue_scripts', 'bp_wall_add_css', 20 );