<?php
/**
 * Buddypress Wall actions
 *
 * @package BP-Wall
 */
 
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * Replace the default buddypress Actions by a new actions
 * 
 */ 
function bp_wall_actions(){

	//legacy
	if ( !bp_wall_is_bp_default() ){
		
		remove_action( 'wp_ajax_activity_widget_filter', 'bp_legacy_theme_activity_template_loader' );
		remove_action( 'wp_ajax_activity_get_older_updates', 'bp_legacy_theme_activity_template_loader' );

		add_action( 'wp_ajax_activity_widget_filter', 'bp_wall_ltheme_activity_template_loader' );
		add_action( 'wp_ajax_activity_get_older_updates', 'bp_wall_ltheme_activity_template_loader' );

		remove_action( 'wp_ajax_activity_mark_fav', 'bp_legacy_theme_mark_activity_favorite' );
		remove_action( 'wp_ajax_activity_mark_unfav', 'bp_legacy_theme_unmark_activity_favorite' );

		remove_action( 'wp_ajax_post_update', 'bp_legacy_theme_post_update' );
		add_action( "wp_ajax_post_update", "bp_wall_ltheme_post_update" );

	} else {

		remove_action( 'wp_ajax_activity_widget_filter', 'bp_dtheme_activity_template_loader' );
		remove_action( 'wp_ajax_activity_get_older_updates', 'bp_dtheme_activity_template_loader' );

		add_action( 'wp_ajax_activity_widget_filter', 'bp_wall_dtheme_activity_template_loader' );
		add_action( 'wp_ajax_activity_get_older_updates', 'bp_wall_dtheme_activity_template_loader' );
		
		remove_action( 'wp_ajax_activity_mark_fav', 'bp_dtheme_mark_activity_favorite' );
		remove_action( 'wp_ajax_activity_mark_unfav', 'bp_dtheme_unmark_activity_favorite' );

		remove_action( 'wp_ajax_post_update', 'bp_dtheme_post_update' );
		add_action( "wp_ajax_post_update", "bp_wall_dtheme_post_update" );

	}

	add_action( 'wp_ajax_activity_mark_fav', 'bp_wall_mark_activity_like' );	
	add_action( 'wp_ajax_activity_mark_unfav', 'bp_wall_unmark_activity_like' );
	add_action( "bp_before_activity_comment", "bp_wall_add_like_action" );

}
add_action( "init","bp_wall_actions", 5 );


/**
 * load the activity loop template when activity is requested via AJAX
 * for default or child theme.
 * 
 */ 
function bp_wall_dtheme_activity_template_loader() {
	global $bp;

	$scope = '';
	if ( !empty( $_POST['scope'] ) )
		$scope = $_POST['scope'];

	// We need to calculate and return the feed URL for each scope
	switch ( $scope ) {
		case 'friends':
			$feed_url = $bp->loggedin_user->domain . bp_get_activity_slug() . '/friends/feed/';
			break;
		case 'groups':
			$feed_url = $bp->loggedin_user->domain . bp_get_activity_slug() . '/groups/feed/';
			break;
		case 'favorites':
			$feed_url = $bp->loggedin_user->domain . bp_get_activity_slug() . '/favorites/feed/';
			break;
		case 'mentions':
			$feed_url = $bp->loggedin_user->domain . bp_get_activity_slug() . '/mentions/feed/';
			bp_activity_clear_new_mentions( $bp->loggedin_user->id );
			break;
		default:
			$feed_url = home_url( bp_get_activity_root_slug() . '/feed/' );
			break;
	}

	/* Buffer the loop in the template to a var for JS to spit out. */
	ob_start();
	bp_wall_load_sub_template( array( 'activity/activity-wall-loop.php' ) );
	$result['contents'] = ob_get_contents();
	$result['feed_url'] = apply_filters( 'bp_dtheme_activity_feed_url', $feed_url, $scope );
	ob_end_clean();

	echo json_encode( $result );
}


/**
 * Load the activity loop template when activity is requested via AJAX
 * for legacy theme.
 * 
 */
function bp_wall_ltheme_activity_template_loader() {
	global $bp;

	$scope = '';
	if ( !empty( $_POST['scope'] ) )
		$scope = $_POST['scope'];

	// We need to calculate and return the feed URL for each scope
	switch ( $scope ) {
		case 'friends':
			$feed_url = $bp->loggedin_user->domain . bp_get_activity_slug() . '/friends/feed/';
			break;
		case 'groups':
			$feed_url = $bp->loggedin_user->domain . bp_get_activity_slug() . '/groups/feed/';
			break;
		case 'favorites':
			$feed_url = $bp->loggedin_user->domain . bp_get_activity_slug() . '/favorites/feed/';
			break;
		case 'mentions':
			$feed_url = $bp->loggedin_user->domain . bp_get_activity_slug() . '/mentions/feed/';
			bp_activity_clear_new_mentions( $bp->loggedin_user->id );
			break;
		default:
			$feed_url = home_url( bp_get_activity_root_slug() . '/feed/' );
			break;
	}

	/* Buffer the loop in the template to a var for JS to spit out. */
	ob_start();
	bp_get_template_part( 'activity/activity-wall-loop' );
	$result['contents'] = ob_get_contents();
	$result['feed_url'] = apply_filters( 'bp_dtheme_activity_feed_url', $feed_url, $scope );
	ob_end_clean();

	echo json_encode( $result );
}

/**
 * rocesses Activity updates received via a POST request
 * on default or child theme.
 * 
 */ 
function bp_wall_dtheme_post_update() {
	global $bp;

	// Check the nonce
	check_admin_referer( 'post_update', '_wpnonce_post_update' );

	if ( !is_user_logged_in() ) {
		echo '-1';
		return false;
	}
	
	if ( empty( $_POST['content'] ) ) {
		echo '-1<div id="message" class="error"><p>' . __( 'Please enter some content to post.', 'buddypress' ) . '</p></div>';
		return false;
	}

	if ( empty( $_POST['object'] ) && function_exists( 'bp_activity_post_update' ) ) {

		if(!bp_is_home()&&bp_is_member())
		$content="@". bp_get_displayed_user_username()." ".$_POST['content'];
		else
		$content=$_POST['content'];
		$activity_id = bp_activity_post_update( array( 'content' => $content ) );
	} elseif ( $_POST['object'] == 'groups' ) {
		if ( !empty( $_POST['item_id'] ) && function_exists( 'groups_post_update' ) )
		$activity_id = groups_post_update( array( 'content' => $_POST['content'], 'group_id' => $_POST['item_id'] ) );
	} else
	$activity_id = apply_filters( 'bp_activity_custom_update', $_POST['object'], $_POST['item_id'], $_POST['content'] );

	if ( !$activity_id ) {
		echo '-1<div id="message" class="error"><p>' . __( 'There was a problem posting your update, please try again.', 'buddypress' ) . '</p></div>';
		return false;
	}

	if ( bp_has_activities ( 'include=' . $activity_id ) ) : ?>
	<?php while ( bp_activities() ) : bp_the_activity(); ?>
	<?php bp_wall_load_sub_template( array( 'activity/entry-wall.php' ) ) ?>
	<?php endwhile; ?>
	<?php endif;
}

/**
 * Processes Activity updates received via a POST request
 * on legacy theme.
 *
 */
function bp_wall_ltheme_post_update() {
	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	// Check the nonce
	check_admin_referer( 'post_update', '_wpnonce_post_update' );

	if ( ! is_user_logged_in() )
		exit( '-1' );

	if ( empty( $_POST['content'] ) )
		exit( '-1<div id="message" class="error"><p>' . __( 'Please enter some content to post.', 'buddypress' ) . '</p></div>' );

	$activity_id = 0;
	if ( empty( $_POST['object'] ) && bp_is_active( 'activity' ) ) {
		$activity_id = bp_activity_post_update( array( 'content' => $_POST['content'] ) );

	} elseif ( $_POST['object'] == 'groups' ) {
		if ( ! empty( $_POST['item_id'] ) && bp_is_active( 'groups' ) )
			$activity_id = groups_post_update( array( 'content' => $_POST['content'], 'group_id' => $_POST['item_id'] ) );

	} else {
		$activity_id = apply_filters( 'bp_activity_custom_update', $_POST['object'], $_POST['item_id'], $_POST['content'] );
	}

	if ( empty( $activity_id ) )
		exit( '-1<div id="message" class="error"><p>' . __( 'There was a problem posting your update, please try again.', 'buddypress' ) . '</p></div>' );

	if ( bp_has_activities ( 'include=' . $activity_id ) ) {
		while ( bp_activities() ) {
			bp_the_activity();
			bp_get_template_part( 'activity/entry-wall' );
		}
	}

	exit;
}

/**
 * Add how many people liked an item
 *
 */
function bp_wall_add_like_action() {
	global $bp_wall;
	
	if ( isset($_POST['action']) && $_POST['action'] == 'new_activity_comment' )
		return false;
	
	$actid = (int) bp_get_activity_id();
	
	if ( $actid === 0 )
		return false;
	
	if ( isset( $bp_wall->likes_store[$actid] ) )
		return false;
	
	$count = (int) bp_activity_get_meta( $actid, 'favorite_count' );
	
	$bp_wall->likes_store[$actid] = 1;
	
	if ( $count === 0 )
		return false;
	
	$subject = ($count == 1) ? __( 'person', 'bp-wall' ) : __( 'people', 'bp-wall' );
	$verb = ($count > 1) ? __( 'like', 'bp-wall' ): __( 'likes', 'bp-wall' );
	$like_html = "<li class=\"activity-like-count\">$count $subject $verb this.</li>";
	
	echo $like_html;
}

/**
 * Mark an activity as a Like via a POST request.
 *
 * @return string HTML
 */
function bp_wall_mark_activity_like() {
	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	if ( bp_activity_add_user_favorite( $_POST['id'] ) )
		_e( 'Unlike', 'bp-wall' );
	else
		_e( 'Like', 'bp-wall' );

	exit;
}

/**
 * Un-Like an activity via a POST request.
 *
 * @return string HTML
 */
function bp_wall_unmark_activity_like() {
	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	if ( bp_activity_remove_user_favorite( $_POST['id'] ) )
		_e( 'Like', 'bp-wall' );
	else
		_e( 'Unlike', 'bp-wall' );

	exit;
}