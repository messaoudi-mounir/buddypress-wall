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
	 * Get the wall activites
	 */
	function get_wall_activities( $page=0, $per_page= 20 ){
		global $bp, $wpdb;

		$page = ( $page>0 ) ? ($page-1) * $per_page : 0;
		//+1 to make sure having total more than 20 ( force printing Load more button)
		$per_page = $per_page + 1;
	
		$user_id = $bp->displayed_user->id;
		$filter = $bp->displayed_user->domain;

		$table_activity = $bp->activity->table_name; 
		$table_activity_meta = $bp->activity->table_name_meta;

		$select_sql = "SELECT DISTINCT $table_activity.id";
		$from_sql = " FROM $table_activity LEFT JOIN $table_activity_meta ON $table_activity.id = $table_activity_meta.activity_id";

		$where_conditions = array();
		$where_conditions['activity_sql'] = "( $table_activity.user_id = $user_id AND $table_activity.type!='activity_comment' AND $table_activity.type!='friends' )";
		$where_conditions['friends_sql'] = "( $table_activity_meta.meta_value LIKE '%$filter%' AND $table_activity.type!='activity_comment' )";
		$where_conditions['groups_sql'] = "( $table_activity.user_id = $user_id AND $table_activity.component = 'groups' )";
		$where_conditions['friendships_sql'] = "( $table_activity.user_id = $user_id AND $table_activity.component = 'friends' )";
		$where_conditions['mentions_sql'] = "( $table_activity.content LIKE '%$filter%' AND $table_activity.type!='activity_comment' )";

		$where_sql = 'WHERE ' . join( ' OR ', $where_conditions );

		$pag_sql = $wpdb->prepare( "LIMIT %d, %d", absint( $page ), $per_page );
		
		$activities = $wpdb->get_results( apply_filters( 'bp_wall_activity_get_user_join_filter', "{$select_sql} {$from_sql} {$where_sql} ORDER BY date_recorded DESC {$pag_sql}", $select_sql, $from_sql, $where_sql, $pag_sql ) , ARRAY_A );
		
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