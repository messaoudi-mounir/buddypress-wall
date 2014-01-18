<?php
/**
 * BP Wall Admin functions
 *
 * @package BP-Wall
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Loads Buddypress Wall plugin admin area
 *
 */
class BPWall_Admin {

	var $setting_page = '';

	function __construct() {
		$this->setup_actions();

	}

	function setup_actions(){
		add_action( bp_core_admin_hook(), array( &$this, 'admin_menu' ) );
		//Welcome page redirect
		add_action( 'admin_init', array( &$this, 'do_activation_redirect' ), 1 );
	}

	function admin_menu() {
		$welcome_page = add_dashboard_page(
				__( 'Welcome to Buddypress Wall',  'bp-wall' ),
				__( 'Welcome to BP Activity Privacy',  'bp-wall' ),
				'manage_options',
				'bp-wall-about',
				array( $this, 'about_screen' )
		);

	    remove_submenu_page( 'index.php', 'bp-wall-about' );

	}

	/**
	 * Modifies the links in plugins table
	 * 
	 */
	public function modify_plugin_action_links( $links, $file ) {

		// Return normal links if not BuddyPress
		if ( plugin_basename( BP_WALL_PLUGIN_FILE_LOADER ) != $file )
			return $links;

		// Add a few links to the existing links array
		return array_merge( $links, array(
			'about'    => '<a href="' . add_query_arg( array( 'page' => 'bp-wall-about'      ), bp_get_admin_url( 'index.php'          ) ) . '">' . esc_html__( 'About',    'bp-wall' ) . '</a>'
		) );
	}

	function admin_submit() {
	}

	function admin_page() {  
	}

  	public function about_screen() {
		$display_version = BP_WALL_VERSION;
		$settings_url = add_query_arg( array( 'page' => 'bp-wall'), bp_get_admin_url( '' ) );
		?>
		<style type="text/css">
			/* Changelog / Update screen */

			.about-wrap .feature-section img {
				border: none;
				margin: 0 1.94% 10px 0;
				-webkit-border-radius: 3px;
				border-radius: 3px;
			}

			.about-wrap .feature-section.three-col img {
				margin: 0.5em 0 0.5em 5px;
				max-width: 100%;
				float: none;
			}

			.ie8 .about-wrap .feature-section.three-col img {
				margin-left: 0;
			}

			.about-wrap .feature-section.images-stagger-right img {
				float: right;
				margin: 0 5px 12px 2em;
			}

			.about-wrap .feature-section.images-stagger-left img {
				float: left;
				margin: 0 2em 12px 5px;
			}

			.about-wrap .feature-section img.image-100 {
				margin: 0 0 2em 0;
				width: 100%;
			}

			.about-wrap .feature-section img.image-66 {
				width: 65%;
			}

			.about-wrap .feature-section img.image-50 {
				max-width: 50%;
			}

			.about-wrap .feature-section img.image-30 {
				max-width: 31.2381%;
			}

			.ie8 .about-wrap .feature-section img {
				border-width: 1px;
				border-style: solid;
			}	

			.about-wrap .images-stagger-right img.image-30:nth-child(2) {
				margin-left: 1em;
			}

			.about-wrap .feature-section img {
			    background: none repeat scroll 0% 0% #FFF;
			    border: 1px solid #CCC;
			    box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.3);
			}

			.bpap-admin-badge {
				position: absolute;
				top: 0px;
				right: 0px;
				padding-top: 190px;
				height: 25px;
				width: 173px;
				color: #555;
				font-weight: bold;
				font-size: 11px;
				text-align: center;
				margin: 0px -5px;
				background: url('<?php echo BP_WALL_PLUGIN_URL; ?>includes/images/badge.png') no-repeat scroll 0% 0% transparent;
			}
		</style>
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to BuddyPress Wall %s', 'bp-wall' ), $display_version ); ?></h1>
			<div class="about-text"><?php printf( __( 'Thank you for upgrading to the latest version of BP Wall! <br \> BP Wall %s is ready to turn your site to a Facebook style wall posting.', 'bp-wall' ), $display_version ); ?></div>
			<div class="bpap-admin-badge" style=""><?php printf( __( 'Version %s', 'bp-wall' ), $display_version ); ?></div>

			<h2 class="nav-tab-wrapper">
				<a class="nav-tab nav-tab-active" href="<?php echo esc_url(  bp_get_admin_url( add_query_arg( array( 'page' => 'bp-wall-about' ), 'index.php' ) ) ); ?>">
					<?php _e( 'About', 'bp-wall' ); ?>
				</a>
			</h2>

			<div class="changelog">
				<h3><?php _e( 'Another way to show & use Buddypress Activity Stream' , 'bp-wall' ); ?></h3>

				<div class="feature-section">
					<p><?php _e( 'BP Wall is Buddypress plugin to personlize the activity component and try togive a feel of stream you see on most of the social networks(like Facebook home page, or your orkut home page etc).', 'bp-wall') ?></p>
					<!--
					<p><?php _e( 'The plugin simplify the Activity Streams by using the Timeline and NewsFeed pages, a Facebook style activity commenting system and .', 'bp-wall' );?></p>
				 	<p><?php _e( 'BP Wall is Buddypress plugin to personlize the Activity Component stream allow members to use activity as wire, it\'s allows members to post when they are visiting the Timeline of there friends.') ?></p>
					<p><?php _e( 'Each Member have his own Timeline Activity page who show Personal acitivity. News Feed page merge the friends and groups activites in one activity streams.', 'bp-wall' ); ?></p>
					<p><?php _e( 'Favorite posts renamed "My Likes".', 'bp-wall' ); ?></p>
					<p><?php _e( 'The plugin add a new Facebook Style Activity Commenting system.', 'bp-wall' );?></p>
 					 -->
 				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'How it\'s Work ?' , 'bp-wall' ); ?></h3>

				<div class="feature-section images-stagger-right">
					<img alt="" src="<?php echo BP_WALL_PLUGIN_URL;?>/screenshot-1.png" class="image-50" />
					<p><?php _e( 'Once installed and activated, BuddyPress Wall (BP-Wall) turn your Buddypress Activity Component to an activity stream similar to a Facebook "Wall".', 'bp-wall' ); ?></p>
					<ul>
						<li><?php _e( 'The members can post status, updates and reply on each others friends walls.', 'bp-wall' ); ?></li>
						<li><?php _e( 'Each Member have his own Timeline Activity page who aggregate all of his activities across a site.', 'bp-wall' ); ?></li>
 						<li><?php _e( 'The News Feed page merge friends and group\'s activites in one activity stream.', 'bp-wall' ); ?></li>
						<li><?php _e( 'BP-Wall turn the Favorite/Unfavorite module of Buddypress to a facebook "I Like/Unlike" system.', 'bp-wall' ); ?></li>
						<li><?php _e( 'BP-Wall add a new comment system similar to Facebook.', 'bp-wall' ); ?></li>
					</ul>
				</div>
			</div>


			<div class="changelog">
				<h3><?php _e( 'Member Activity Timeline', 'bp-wall' ); ?></h3>

				<div class="feature-section images-stagger-right">
					<img alt="" src="<?php echo BP_WALL_PLUGIN_URL;?>/screenshot-2.png" class="image-50" />
					<p><?php _e( 'The Timeline subnav show only the personal activity of the current/displayed member :', 'bp-wall' ); ?></p>
					<ul>
						<li><?php _e( 'Status update activities,', 'bp-wall' ); ?></li>
						<li><?php _e( 'Activities/Updates in groups,', 'bp-wall' ); ?></li>
						<li><?php _e( 'All others activities posted/lunched by the current/displayed member.', 'bp-wall' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Member Activity News feed', 'bp-wall' ); ?></h3>
				<div class="feature-section images-stagger-right">
					<img alt="" src="<?php echo BP_WALL_PLUGIN_URL;?>/screenshot-3.png" class="image-50" />
					<p><?php _e( 'The  News feed subanv contain all the activity of :', 'bp-wall' ); ?></p>
					<ul>
						<li><?php _e( 'My Friends', 'bp-wall' ); ?></li>
						<li><?php _e( 'My Groups', 'bp-wall' ); ?></li>
						<li><?php _e( 'Friends Posts in my Wall', 'bp-wall' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'From "Favorite/Unfavorite" to "Like/Unlike"', 'bp-wall' ); ?></h3>

				<div class="feature-section images-stagger-right">
					<img alt="" src="<?php echo BP_WALL_PLUGIN_URL;?>/screenshot-4.png" class="image-50" />
					<p><?php _e( 'BP-Wall turn the Favorite/Unfavorite module of Buddypress to a facebook "Like/Unlike" system.', 'bp-wall' ); ?></p>
					<p><?php _e( 'Buddypress members can find all "Liked" posts in My Likes subnav.', 'bp-wall' ); ?></p>
				</div>
			</div>			

			<div class="changelog">
				<h3><?php _e( 'Facebook Style Activity Commenting System', 'bp-wall' ); ?></h3>

				<div class="feature-section images-stagger-right">
					<img alt="" src="<?php echo BP_WALL_PLUGIN_URL;?>/screenshot-5.png" class="image-50" />
					<p><?php _e( 'BP Wall make the BuddyPress activity stream commenting system look/work like facebook activity commenting.', 'bp-wall' ); ?></p>
					<ul>
						<li><?php _e( 'To insert new line press Shift + Enter.', 'bp-wall' ); ?></li>
						<li><?php _e( 'To send the comment press Enter.', 'bp-wall' ); ?></li>
						<li><?php _e( 'To cancel the comment press Esc.', 'bp-wall' ); ?></li>
					</ul>
				</div>
				
			</div>

		</div>
	<?php
  	}

	/**
	 * Welcome screen redirect
	 */
	function do_activation_redirect() {
		// Bail if no activation redirect
		if ( ! get_transient( '_bp_wall_activation_redirect' ) )
			return;

		// Delete the redirect transient
		delete_transient( '_bp_wall_activation_redirect' );

		// Bail if activating from network, or bulk
		if ( isset( $_GET['activate-multi'] ) )
			return;

		$query_args = array( 'page' => 'bp-wall-about' );

		// Redirect to Buddypress Activity privacy about page
		wp_safe_redirect( add_query_arg( $query_args, bp_get_admin_url( 'index.php' ) ) );
	}  	

  	function enqueue_scripts() {
  	}

  	function enqueue_styles() {

  	}
}  





