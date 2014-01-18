<?php

/**
 * BuddyPress - Users Activity Wall
 *
 * @package BuddyPress Wall
 * @subpackage Templates
 */

?>

<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
	<ul>

		<?php bp_get_options_nav(); ?>
		
		<!-- bp-wall-start -->
		<!-- 
		<li id="activity-filter-select" class="last">
			
			<label for="activity-filter-by"><?php _e( 'Show:', 'buddypress' ); ?></label>
			<select id="activity-filter-by">
				<option value="-1"><?php _e( 'Everything', 'buddypress' ); ?></option>
				<option value="activity_update"><?php _e( 'Updates', 'buddypress' ); ?></option>

				<?php
				if ( !bp_is_current_action( 'groups' ) ) :
					if ( bp_is_active( 'blogs' ) ) : ?>

						<option value="new_blog_post"><?php _e( 'Posts', 'buddypress' ); ?></option>
						<option value="new_blog_comment"><?php _e( 'Comments', 'buddypress' ); ?></option>

					<?php
					endif;

					if ( bp_is_active( 'friends' ) ) : ?>

						<option value="friendship_accepted,friendship_created"><?php _e( 'Friendships', 'buddypress' ); ?></option>

					<?php endif;

				endif;

				if ( bp_is_active( 'forums' ) ) : ?>

					<option value="new_forum_topic"><?php _e( 'Forum Topics', 'buddypress' ); ?></option>
					<option value="new_forum_post"><?php _e( 'Forum Replies', 'buddypress' ); ?></option>

				<?php endif;

				if ( bp_is_active( 'groups' ) ) : ?>

					<option value="created_group"><?php _e( 'New Groups', 'buddypress' ); ?></option>
					<option value="joined_group"><?php _e( 'Group Memberships', 'buddypress' ); ?></option>

				<?php endif;

				do_action( 'bp_member_activity_filter_options' ); ?>

			</select>

		</li>
		-->
		<!-- bp-wall-end -->
	</ul>
</div><!-- .item-list-tabs -->

<?php do_action( 'bp_before_member_activity_post_form' ); ?>
<!-- bp-wall-start -->
<?php if ( ( !bp_current_action() || bp_is_current_action( 'just-me' ) ) || 
          is_user_logged_in() && bp_is_my_profile() && ( !bp_current_action() || bp_is_current_action( 'just-me' ) ) ) : ?>
          
	<?php bp_wall_load_sub_template( array('activity/post-wall-form.php'), true ) ?> 

<?php endif; ?>
<!-- bp-wall-end -->
<?php do_action( 'bp_after_member_activity_post_form' ); ?>
<?php do_action( 'bp_before_member_activity_content' ); ?>

<div class="activity" role="main">
	<!-- bp-wall-start -->
	<?php bp_wall_load_sub_template( array( 'activity/activity-wall-loop.php' ) ); ?>
	<!-- bp-wall-end -->
</div><!-- .activity -->

<?php do_action( 'bp_after_member_activity_content' ); ?>
