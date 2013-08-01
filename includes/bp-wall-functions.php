<?php
/**
 * BP Wall Functions 
 *  
 * @package BP-Wall
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * Add new message before comments how many people liked an item
 *
 */
function bp_wall_add_likes_comments() {
	$actid = (int) bp_get_activity_id();
	
	if ( $actid === 0 )
		return false;
	
	$count = (int) bp_activity_get_meta( $actid, 'favorite_count' );
	
	if ( $count === 0 )
		return false;
	
	$subject = ($count == 1) ? 'person' : 'people';
	$verb = ($count > 1) ? 'like' : 'likes';
	
	$like_html = "<ul><li class=\"activity-like-count\">$count $subject $verb this.</li></ul>";
	
	echo $like_html;
}