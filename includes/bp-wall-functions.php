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
	$activity_id = (int) bp_get_activity_id();
	
	if ( !isset( $activity_id ) || $activity_id == 0 )
		return false;
	
	$count = (int)bp_activity_get_meta( $activity_id, 'favorite_count' );
	
	if ( $count == 0 )
		return false;

	$like_html = false;

	if ( $count == 1 )
		$like_html = sprintf( __( '<ul><li class="activity-like-count">%s person like this.</li></ul>', 'bp-wall' ), number_format_i18n( $count ) );
	elseif ( $count > 1 ) {
		$like_html = sprintf( __( '<ul><li class="activity-like-count">%s people likes this.</li></ul>', 'bp-wall' ), number_format_i18n( $count ) );
	}

	echo $like_html;
}