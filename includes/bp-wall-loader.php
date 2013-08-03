<?php
/**
 * BP-WALL
 *
 * @package BP-WALL
 */
 
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


// textdomain loader
$textdomain_local = BP_WALL_PLUGIN_DIR . '/languages/buddypress-wall-' . get_locale() . '.mo';
if ( file_exists( $textdomain_local ) )
	load_textdomain( 'bp-wall', $textdomain_local );
else{
	$textdomain_global = trailingslashit( WP_LANG_DIR ) . 'buddypress-wall-' . get_locale() . '.mo';
	if( file_exists( $textdomain_global ) )
	load_textdomain( 'bp-wall', $textdomain_global );
}


/**
 * BP_Wall Class
 */
class BP_Wall {

	public $activities;

	public $activity_count = 0;

	public $filter_qs = false;
	
	public $likes_store = array();

	function __construct( $options = null ) {

		global $bp, $activity_template;
		
		$this->includes();

		add_action( 'wp_before_admin_bar_render', array($this, 'bp_wall_remove_unused_menu'), 98 );

		add_action( 'bp_setup_nav', array($this, 'bp_wall_remove_unused_subnav_items'), 98 );
		add_action( 'bp_setup_nav', array($this, 'bp_wall_update_subnav_items'), 99 );
		add_filter( 'bp_get_displayed_user_nav_activity', array($this, 'bp_wall_replace_activity_link') );

		// Add body class
		add_filter( 'body_class', array($this, 'add_body_class') );
		
		return $this;
	}

	
	function includes() {
		// Files to include
		include( BP_WALL_PLUGIN_DIR . '/includes/bp-wall-actions.php' );
		include( BP_WALL_PLUGIN_DIR . '/includes/bp-wall-filters.php' );
		include( BP_WALL_PLUGIN_DIR . '/includes/bp-wall-screens.php' );
		include( BP_WALL_PLUGIN_DIR . '/includes/bp-wall-template.php' );
		include( BP_WALL_PLUGIN_DIR . '/includes/bp-wall-functions.php' );
		include( BP_WALL_PLUGIN_DIR . '/includes/bp-wall-cssjs.php' );
		include( BP_WALL_PLUGIN_DIR . '/includes/bp-wall-ajax.php' );
		include( BP_WALL_PLUGIN_DIR . '/includes/bp-wall-widgets.php' );
		// As an follow of how you might do it manually, let's include the functions used
		// on the WordPress Dashboard conditionally:	
		if ( is_admin() || is_network_admin() ) {
			include( BP_WALL_PLUGIN_DIR . '/includes/bp-wall-admin.php' );
		}
		
	}

	
	/**
	 * Save the cache at wp shutdown
	 * 
	 */
	function shutdown() {
	}
	
	/**
	 * Get option of $name
	 * 
	 */
	function get_option($name) {
		if (isset($this->options[$name])) return $this->options[$name];
		return false;
	}
	
	/**
	 * Add active wall class to <body>
	 * 
	 */
	function add_body_class( $classes ) {
		$classes[] = 'bp-wall';
		return $classes;
	}
	
	/**
	 *  Rename Activity link
	 * 
	 */
	function bp_wall_replace_activity_link($v) {
		return str_replace('Activity', __("Wall", "bp-wall"), $v);
	}
	
	/** 
	 * Remove unused menu from wp admin bar and rename others
	 * 
	 */
	function bp_wall_remove_unused_menu() {
		global $bp, $wp_admin_bar;

		if ( !is_user_logged_in() ) 
			return;
		
		$wp_admin_bar->remove_menu( "my-account-activity-friends" );
		$wp_admin_bar->remove_menu( "my-account-activity-groups" );


		$activity_menu = $wp_admin_bar->get_node("my-account-activity");
		$activity_menu->title = __("Wall", "bp-wall");
		$wp_admin_bar->add_menu( $activity_menu );


		$activity_favorites_menu = $wp_admin_bar->get_node("my-account-activity-favorites");
		$activity_favorites_menu->title = __("My Likes", "bp-wall");
		$wp_admin_bar->add_menu( $activity_favorites_menu );

		$activity_mentions_menu = $wp_admin_bar->get_node("my-account-activity-mentions");
		$activity_mentions_menu->title = __("My mentions", "bp-wall");
		$wp_admin_bar->add_menu( $activity_mentions_menu );


		$activity_personal_menu = $wp_admin_bar->get_node("my-account-activity-personal");
		$activity_personal_menu->title = __("News Feed", "bp-wall");
		$wp_admin_bar->add_menu( $activity_personal_menu );

		//$wp_admin_bar->remove_menu( "my-account-activity-favorites" );
		//$wp_admin_bar->remove_menu( "my-account-activity-mentions" );

	}

	/**
	 * Remove unused activity subnav from profile page
	 * 
	 */ 
	function bp_wall_remove_unused_subnav_items() {
		global $bp;

		bp_core_remove_subnav_item( $bp->activity->slug, 'friends' );
		bp_core_remove_subnav_item( $bp->activity->slug, 'mentions' );
		bp_core_remove_subnav_item( $bp->activity->slug, 'groups' );
		if ( !bp_is_home() )
			bp_core_remove_subnav_item( $bp->activity->slug, 'favorites' );
	}
	
	/**
	 * Rename subnav items in activity tab
	 * 
	 */
	function bp_wall_update_subnav_items() {

		global $bp;

		$domain = (!empty($bp->displayed_user->id)) ? $bp->displayed_user->domain : $bp->loggedin_user->domain;
		
		$profile_link = $domain . $bp->activity->slug . '/';

		// RENAME PERSONAL TAB
		bp_core_new_subnav_item( array( 
			'name' => __("News Feed", "bp-wall"), 
			'slug' => 'just-me', 
			'parent_url' => $profile_link, 
			'parent_slug' => $bp->activity->slug, 
			'screen_function' => 
			'bp_activity_screen_my_activity', 
			"position" => 10 
		) );
		
		// RENAME FAVORITES TAB
		bp_core_new_subnav_item( array( 
			'name' => __("My Likes", "bp-wall"), 
			'slug' => 'favorites', 
			'parent_url' => $profile_link, 
			'parent_slug' => $bp->activity->slug, 
			'screen_function' => 'bp_activity_screen_favorites',
			'position' => 20
		) );

		if ( bp_is_my_profile() ) {
			bp_core_new_subnav_item( array( 
	        'name' => __("My Mentions", "bp-wall"), 
	        'slug' => 'mentions',
	        'parent_url' => $profile_link,
	        'parent_slug' => $bp->activity->slug,
	        'screen_function' => 'bp_activity_screen_mentions',
	        'position' => 40
	    	) );		
		}


	}
	
	/**
	 * Check if a member with id $user_id  is my friend 
	 * 
	 */
	function is_myfriend( $user_id ) {
		global $bp;
		$bp_loggedin_user_id = bp_loggedin_user_id();

		return friends_check_friendship( $bp_loggedin_user_id, $user_id );
	}
	
	/**
	 * GET WALL ACTIVITES
	 */
	function get_wall_activities( $page=0, $per_page=20 ){
		global $bp, $wpdb;
		$min = ( $page>0 ) ? ($page-1) * $per_page : 0;
		$max = ( $page+1 ) * $per_page;

		$per_page = bp_get_activity_per_page();
		/*
		if ( isset( $bp->loggedin_user ) && isset( $bp->loggedin_user->id ) && 
			$bp->displayed_user->id == $bp->loggedin_user->id ) {
		*/	
		if ( bp_is_my_profile() ) {
			$is_my_profile = true;
		}
		else {
			$is_my_profile = false;
		}

		$user_id = $bp->displayed_user->id;

		$filter = addslashes($bp->displayed_user->fullname);
		$friend_ids = friends_get_friend_user_ids($user_id);

		if (!empty($friend_ids)) 
			$friend_id_list = implode( ",", ( $friend_ids ) );

		$table = $wpdb->prefix."bp_activity";

		// Group Display code
		$groups = BP_Groups_Member::get_group_ids( $user_id ) ;

		$valid_groups=array();
		if (!empty($groups)) {
			foreach ($groups['groups'] as $id) {
				$group = new BP_Groups_Group( $id);
				if ("public" == $group->status) {
					$valid_groups[]=$id;
				}
			}
		}

		$valid_group_list = implode(",",$valid_groups);
		if ( $is_my_profile && !empty( $friend_id_list ) ) {
			$group_modifier =  "OR ( component='groups' AND user_id IN ( $user_id,$friend_id_list ) ) ";
		}
		else {
			$group_modifier = "OR ( user_id = $user_id AND component='groups' ) ";
		}
		
		if (!empty($friend_id_list)) {
			$friends_modifier = $is_my_profile 
				? "OR ( user_id IN ($friend_id_list) AND type!='activity_comment' ) " 
				: "OR ( (component = 'activity' || component = 'groups') AND user_id IN ($friend_id_list) AND type!='activity_comment' AND type!='joined_group' AND type!='left_group' AND type!='created_group' AND type!='deleted_group') ";
		}
		else {
			$friends_modifier = "";
		}
		
		$mentions_filter = like_escape( $bp->displayed_user->userdata->user_login );
		$mentions_modifier = "OR ( component = 'activity' AND ACTION LIKE '%@$mentions_filter%' ) ";

		$query = " SELECT id FROM $table 
				  WHERE (	component = 'activity' AND user_id = $user_id AND type!='activity_comment' ) 
						$friends_modifier 
						$group_modifier
						$mentions_modifier 
			   ORDER BY date_recorded 
			 DESC LIMIT $min, $max";

		$activities  = $wpdb->get_results( $query, ARRAY_A );

		if ( empty($activities ) ) return null;

		$tmp = array();

		foreach ( $activities as $a ) {
			$tmp[] = $a["id"];
		}
		$activity_list = implode( ",", $tmp );

		return $activity_list;

	}
	
	/**
	 * Retrive likes for current activity
	 */
	function has_likes( $activity_id = null ) {
		if ( $activity_id === null ) $activity_id = bp_get_activity_id();
		return bp_activity_get_meta( $activity_id, 'favorite_count' );

	}
}

/**
 * Load the core of the plugin
 */
function bp_wall_load_core() {
	global $bp, $bp_wall;
	$bp_wall = new BP_Wall;
	do_action('bp_wall_load_core');

}
add_action( 'bp_loaded', 'bp_wall_load_core', 5 );