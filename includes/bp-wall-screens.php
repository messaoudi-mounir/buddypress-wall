<?php

/**
 * Catches any visits to the "Activity > Following" tab on a users profile.
 *
 * @uses bp_core_load_template() Loads a template file.
 */
/*
function bp_follow_screen_activity_following() {
	bp_update_is_item_admin( is_super_admin(), 'activity' );
	do_action( 'bp_follow_screen_activity_following' );
	bp_core_load_template( apply_filters( 'bp_follow_screen_activity_following', 'members/single/home' ) );
}

if ( class_exists( 'BP_Theme_Compat' ) ) {
    //mod:bp1.7
    class BP_Wall_Theme_Compat {
     
        public function __construct() { 
     
            add_action( 'bp_setup_theme_compat', array( $this, 'is_bp_plugin' ) );
        }
     
        public function is_bp_plugin() {
           
                // first we reset the post
                add_action( 'bp_template_include_reset_dummy_post_data', array( $this, 'directory_dummy_post' ) );
                // then we filter ‘the_content’ thanks to bp_replace_the_content
                add_filter( 'bp_replace_the_content', array( $this, 'directory_content'    ) );


        }


        public function directory_dummy_post() {

        }


        public function directory_content() {
            bp_buffer_template_part( 'members/single/activity' );
        }
    }
     
    new BP_Wall_Theme_Compat ();


    function bp_wall_add_template_stack( $templates ) {
        // if we're on a page of our plugin and the theme is not BP Default, then we
        // add our path to the template path array
        if ( !bp_wall_is_bp_default() ) {
            $templates[] = BP_WALL_PLUGIN_DIR . '/includes/templates/';
        }

        return $templates;
    }
     
    add_filter( 'bp_get_template_stack', 'bp_wall_add_template_stack', 10, 1 );
}

*/





/**
 * bp_follow_load_template_filter()
 *
 * You can define a custom load template filter for your component. This will allow
 * you to store and load template files from your plugin directory.
 *
 * This will also allow users to override these templates in their active theme and
 * replace the ones that are stored in the plugin directory.
 *
 * If you're not interested in using template files, then you don't need this function.
 *
 * This will become clearer in the function bp_follow_screen_one() when you want to load
 * a template file.
 */

function bp_wall_load_template_filter( $found_template, $templates ) {
	global $bp, $bp_deactivated;

	if ( !bp_wall_is_bp_default() || 
           !bp_is_current_component( 'activity' ) &&
           ( !bp_is_group_home() || !bp_is_active( 'activity' ) ) ) {
        return $found_template; 
    }


	$templates_dir = "/templates/bp-default/";
	
    
	//if( bp_is_user_profile() && )
	//Only filter the template location when we're on the follow component pages.

	//if (bp_wall_is_bp_default()) {
	if ( $templates[0] == "members/single/home.php") {
		$found_template = dirname( __FILE__ ) . $templates_dir . 'members/single/home-wall.php';
		return $found_template;
	}elseif ( $templates[0] == "activity/index.php") {
		$found_template = dirname( __FILE__ ) . $templates_dir . 'activity/index-wall.php';
		return $found_template;
	} elseif ( $templates[0] == "groups/single/home.php" )	{
		$found_template = dirname( __FILE__ ) . $templates_dir . 'groups/single/home-wall.php';
		return $found_template;
	}
	//}

    //Only filter the template location when we're on the bp-plugin component pages.
	//mod:bp1.7

	foreach ( (array) $templates as $template ) {
		
		if ( file_exists( STYLESHEETPATH . '/' . $template ) )
			$filtered_templates[] = STYLESHEETPATH . '/' . $template;
		else
			$filtered_templates[] = dirname( __FILE__ ) . $templates_dir . $template;
	}

	$found_template = $filtered_templates[0];
	
	return apply_filters( 'bp_wall_load_template_filter', $found_template );
}
add_filter( 'bp_located_template', 'bp_wall_load_template_filter', 10, 2 );



/**
* http://buddypress.trac.wordpress.org/ticket/2198
*/
function bp_wall_load_sub_template( $template = false, $require_once = true ) {
	if( empty( $template ) )
        return false;

    if( bp_wall_is_bp_default() ) {
    	if ( $located_template = apply_filters( 'bp_located_template', locate_template( $template , false ), $template ) )	
			load_template( apply_filters( 'bp_load_template', $located_template ), $require_once );
    
    } else {
        bp_get_template_part( $template );

    }
}

//mod:bp1.7
function bp_wall_is_bp_default() {
    // if active theme is BP Default or a child theme, then we return true
    // as i was afraid a BuddyPress theme that is not relying on BP Default might
    // be active, i added a BuddyPress version check.
    // I imagine that once version 1.7 will be released, this case will disappear.

    if(current_theme_supports('buddypress') || in_array( 'bp-default', array( get_stylesheet(), get_template() ) )  || ( defined( 'BP_VERSION' ) && version_compare( BP_VERSION, '1.7', '<' ) ))
   		return true;
   else {
	    // check to see if the 'buddypress' tag is in the theme 
	    // some bp themes are not yet compatible to bp 1.7 but the plugin is updated

   		// get current theme
	    $theme = wp_get_theme();
	    // get current theme's tags
	    $theme_tags = ! empty( $theme->tags ) ? $theme->tags : array();

	    // or if stylesheet is 'bp-default'
	    $backpat = in_array( 'buddypress', $theme_tags );    
	    if($backpat)
	    	return true;
   		else 
   			return false;
   }
}



if ( class_exists( 'BP_Theme_Compat' ) ) {
    //mod:bp1.7
    class BP_Wall_Theme_Compat {
     
        /**
         * Setup the bp plugin component theme compatibility
         */
        public function __construct() { 
            /* this is where we hook bp_setup_theme_compat !! */
            add_action( 'bp_setup_theme_compat', array( $this, 'is_bp_plugin' ) );
        }
     
        /**
         * Are we looking at something that needs theme compatability?
         */
        public function is_bp_plugin() {
           
                // first we reset the post
                add_action( 'bp_template_include_reset_dummy_post_data', array( $this, 'directory_dummy_post' ) );
                // then we filter ‘the_content’ thanks to bp_replace_the_content
                add_filter( 'bp_replace_the_content', array( $this, 'directory_content'    ) );


        }

        /**
         * Update the global $post with directory data
         */
        public function directory_dummy_post() {

        }
        /**
         * Filter the_content with bp-plugin index template part
         */
        public function directory_content() {
          // bp_buffer_template_part( 'members/single/follow' );
        }
    }
     
    new BP_Wall_Theme_Compat();

    function bp_wall_add_template_stack( $templates ) {
       // if ( ( bp_is_user_activity() || bp_is_activity_component() || bp_is_group_home() ) && !bp_wall_is_bp_default() ) {
        if ( ( bp_is_member() || bp_is_activity_component() || bp_is_group() ) && !bp_wall_is_bp_default() )
            $templates[] = BP_WALL_PLUGIN_DIR . '/includes/templates/bp-legacy/buddypress';
       // }

        return $templates;
    }
     
    add_filter( 'bp_get_template_stack', 'bp_wall_add_template_stack', 10, 1 );
}
