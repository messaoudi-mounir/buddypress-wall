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
		if ( is_super_admin() && ( is_admin() || is_network_admin() ) ) {
			include( BP_WALL_PLUGIN_DIR . '/includes/bp-wall-admin.php' );
			$this->admin = new BPWall_Admin;
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
	
		$activity_slug = bp_get_activity_slug();

		$wp_admin_bar->remove_menu( "my-account-" . $activity_slug . "-friends" );
		$wp_admin_bar->remove_menu( "my-account-" . $activity_slug . "-groups" );

		$activity_menu = $wp_admin_bar->get_node( "my-account-" . $activity_slug );
		$activity_menu->title = __( "Wall", "bp-wall" );
		$wp_admin_bar->add_menu( $activity_menu );

      
		$activity_personal_menu = $wp_admin_bar->get_node( "my-account-" . $activity_slug . "-personal" );
		$wp_admin_bar->remove_node( "my-account-" . $activity_slug . "-personal" );
		$activity_personal_menu->title = __("Timeline", "bp-wall");
		$wp_admin_bar->add_menu( $activity_personal_menu );
		
		$args = array(
			'parent'    => 'my-account-' . $activity_slug,
			'id'    => 'my-account-' . $activity_slug . '-newsfeed',
			'title' => __( 'News Feed', 'bp-wall' ),
			'href'  => trailingslashit( bp_loggedin_user_domain() .  $activity_slug . '/' . 'news-feed' )
		);
		$wp_admin_bar->add_node( $args );	

		$activity_mentions_menu = $wp_admin_bar->get_node( "my-account-" . $activity_slug . "-mentions" );
		$wp_admin_bar->remove_node( "my-account-" . $activity_slug . "-mentions" );
		$activity_mentions_menu->title = __( "My mentions", "bp-wall" );
		$wp_admin_bar->add_menu( $activity_mentions_menu );

		$activity_favorites_menu = $wp_admin_bar->get_node( "my-account-" . $activity_slug . "-favorites" );
		$wp_admin_bar->remove_node( "my-account-" . $activity_slug . "-favorites" );
		$activity_favorites_menu->title = __( "My Likes", "bp-wall" );
		$wp_admin_bar->add_menu( $activity_favorites_menu );

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
		//if ( !bp_is_home() )  //depreaced
		if ( !bp_is_my_profile() )
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
			'name' => __("Timeline", "bp-wall"), 
			'slug' => 'just-me',
			'parent_url' => $profile_link, 
			'parent_slug' => $bp->activity->slug, 
			'screen_function' => 'bp_activity_screen_my_activity', 
			"position" => 10 
		) );

		// if is my profile 
		if ( bp_is_my_profile() ) {

			// add "News Feed" tab
			bp_core_new_subnav_item( array( 
				'name' => __("News Feed", "bp-wall"), 
				'slug' => 'news-feed', 
				'parent_url' => $profile_link, 
				'parent_slug' => $bp->activity->slug, 
				'screen_function' => 'bp_wall_activity_screen_newsfeed_activity', 
				"position" => 20 
			) );

			// rename favorites tab to My Likes
			bp_core_new_subnav_item( array( 
				'name' => __("My Likes", "bp-wall"), 
				'slug' => 'favorites', 
				'parent_url' => $profile_link, 
				'parent_slug' => $bp->activity->slug, 
				'screen_function' => 'bp_activity_screen_favorites',
				'position' => 30
			) );

			bp_core_new_subnav_item( array( 
	        'name' => __("My Mentions", "bp-wall"), 
	        'slug' => 'mentions',
	        'parent_url' => $profile_link,
	        'parent_slug' => $bp->activity->slug,
	        'screen_function' => 'bp_activity_screen_mentions',
	        'position' => 40
	    	) );	

		} else {
			/*	
			// rename favorites tab to Likes
			bp_core_new_subnav_item( array( 
				'name' => __("Likes", "bp-wall"), 
				'slug' => 'favorites', 
				'parent_url' => $profile_link, 
				'parent_slug' => $bp->activity->slug, 
				'screen_function' => 'bp_activity_screen_favorites',
				'position' => 30
			) );*/
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
	 * Get the timeline activites (my-activity + mentions)
	 */
	function get_timeline_activities( $page = 0, $per_page = 20 ){
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
		//optimization 
		/*
		$where_conditions['activity_sql'] = "( $table_activity.user_id = $user_id AND $table_activity.type!='activity_comment' AND $table_activity.type!='friends' )";
		$where_conditions['friends_sql'] = "( $table_activity_meta.meta_value LIKE '%$filter%' AND $table_activity.type!='activity_comment' )";
		$where_conditions['groups_sql'] = "( $table_activity.user_id = $user_id AND $table_activity.component = 'groups' )";
		$where_conditions['friendships_sql'] = "( $table_activity.user_id = $user_id AND $table_activity.component = 'friends' )";
		$where_conditions['mentions_sql'] = "( $table_activity.content LIKE '%$filter%' AND $table_activity.type!='activity_comment' )";
		 */
		
		$where_conditions['activity_sql'] = "( $table_activity.user_id = $user_id AND $table_activity.type!='activity_comment' )";

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
	 * Get the news feed activites ( friends + groups + mentions )
	 */
	function get_newsfeed_activities( $page = 0, $per_page = 20 ){
		global $bp, $wpdb;

		$page = ( $page>0 ) ? ($page-1) * $per_page : 0;
		//+1 to make sure having total more than 20 ( force printing Load more button)
		$per_page = $per_page + 1;
	
		$user_id = $bp->displayed_user->id;
		$filter = $bp->displayed_user->domain;

		$friends_ids = friends_get_friend_user_ids( $user_id );
		if ( empty( $friends_ids ) )
			$friends_ids = null;
		else 
			$friends_ids = implode( ',', wp_parse_id_list( $friends_ids ) );

		$groups_ids = groups_get_user_groups( $user_id );
		if ( empty( $groups_ids['groups'] ) )
			$groups_ids = null;
		else
			$groups_ids = implode( ',', wp_parse_id_list( $groups_ids['groups'] ) );

 		$table_activity = $bp->activity->table_name; 
		$table_activity_meta = $bp->activity->table_name_meta;

		$select_sql = "SELECT DISTINCT $table_activity.id";
		$from_sql = " FROM $table_activity LEFT JOIN $table_activity_meta ON $table_activity.id = $table_activity_meta.activity_id";

		$where_conditions = array();
		if( isset( $friends_ids ) )
			$where_conditions['friends_sql'] = "( $table_activity.user_id IN ( $friends_ids ) AND $table_activity.type!='activity_comment' )";
		//$where_conditions['friends_sql'] = "( $table_activity_meta.meta_value LIKE '%$filter%' AND $table_activity.type!='activity_comment' )";
		if( isset( $groups_ids ) )
			$where_conditions['groups_sql'] = "( $table_activity.user_id != $user_id AND $table_activity.item_id IN ( $groups_ids ) AND $table_activity.component = 'groups' )";
		
		//$where_conditions['friendships_sql'] = "( $table_activity.user_id = $user_id AND $table_activity.component = 'friends' )";
		//$where_conditions['mentions_sql'] = "( $table_activity.content LIKE '%$filter%' AND $table_activity.type!='activity_comment' )";

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