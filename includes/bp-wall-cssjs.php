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
 */
function bp_wall_add_js(){
    if(!is_user_logged_in())
        return ;//we do not want to include the js

    wp_enqueue_script( 'bp-wall-js', plugins_url( 'js/bp-wall.js' ,  __FILE__ ), array('jquery'), false, true );
    wp_enqueue_script( 'bp-wall-comment-autogrow-js', plugins_url( 'js/jquery.autogrow-textarea.js' ,  __FILE__ ), array('jquery') );

	// Add words that we need to use in JS to the end of the page so they can be translated and still used.
	$params = array(
		'my_favs'           => __( 'My Likes', 'bp-wall' ),
		'mark_as_fav'	    => __( 'like', 'bp-wall' ),
		'remove_fav'	    => __( 'Unlike', 'bp-wall' ),
	);

	wp_localize_script( 'dtheme-ajax-js', 'BP_DTheme', $params );

}
add_action("wp_enqueue_scripts","bp_wall_add_js");
	

/**
 * Enqueue stylesheet files
 * @return [type] [description]
 */
function bp_wall_add_css() {
	global $bp;

    if(!is_user_logged_in())
        return ;//we do not want to include the js
	
    wp_enqueue_style( 'bp-activity-privacy-css', plugins_url( 'css/bp-wall.css' ,  __FILE__ ),  false, true ); 
}
add_action( 'bp_actions', 'bp_wall_add_css', 1 );