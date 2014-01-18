=== BuddyPress Wall ===
Contributors: megainfo
Tags: buddypress, activity, wall, facebook, facebook style, facebook comment system, wall mode, like, unlike
Requires at least: WordPress 3.4, BuddyPress 1.5
Tested up to: WordPress 3.8 / BuddyPress 1.9
Stable tag: 0.9


== Description ==

BuddyPress Wall (BP-Wall) turn your Buddypress Activity Component to an activity stream similar to a Facebook “Wall”. 

When you install BP-Wall, the members can post status, updates and reply on each other’s walls.

BP-Wall turn the Favorite/Unfavorite module of Buddypress to a facebook “I Like/Unlike” system.

BP-Wall change add a new comment system similar to Facebook.


== Installation ==

Download and upload the plugin to your plugins folder. 

Then Activate the plugin.

== Screenshots ==
1. **New comment system (Like Facebook)** - A member can write a new comment just by typing a comment and pressing Enter to send or Esc to cancel.
2. **Write in the Wall of you friend** - Member can write in the wall of his friends.
3. **Activity Member wall** - The Activity tab is now Wall Tab, all activities personal, friends and groups are printed are orderd in one screen : News Feed.
4. **I-Like/Unlike system** - Member can  “Like/Unlike” a post.


== Frequently Asked Questions ==

Buddypress Wall use <a href="http://codex.buddypress.org/themes/theme-compatibility-1-7/a-quick-look-at-1-7-theme-compatibility/">BuddyPress theme compatibility</a>.

Using Child Theme of the BP Default ?

If you use a child theme for buddypress default theme, you must costumise the plugin template for your theme.

By copying /wp-content/plugins/buddypress-wall/includes/templates/bp-default/ to your WordPress theme you can override the template that comes with the plugin. 

The files template will be placed in the buddypress activity,groups and members directory.

9 template should be customised :

activity/activity-wall-loop.php
activity/entry-wall.php
activity/index-wall.php
activity/post-wall-form.php


groups/single/activity-wall.php
groups/single/home-wall.php

members/single/activity-wall.php
members/single/home-wall.php


For example, to update the activity/activity-wall-loop.php template, 
open the template file in your favorite editor or IDE and open the template 
activity/activity-loop.php (without -wall-) and replacing the template activity-wall-loop.php 
by code from activity-loop.php and keeping only the bp-wall php code (marked between <!-- bp-wall-start --> and <!--bp-wall-end-->) in the activity-wall-loop.php.

for example in activity-wall-loop.php, the
code 
<?php bp_wall_load_sub_template( array( 'activity/activity-wall-loop.php' ) ); ?>

is used to load activity-wall-loop.php instead this code
<?php locate_template( array( 'activity/activity-loop.php' ), true ); ?>
used in the default buddypress template.

so you must replace the template content and keep the bp-wall script.

#### Where to find support? ####

Please post on the Wordpress support forum(http://wordpress.org/support/plugin/buddypress-wall).

For bug reports or to add patches or translation files, visit the [Buddypress Wall Github page](https://github.com/dzmounir/buddypress-wall).


== Changelog ==

= 0.9 =
* Fix bug, Activity feed has numbers after username.
* Translation ready
* Add Event keyboard (Shift+Enter) for new line comment.

= 0.8.2 =
* Fix bug, personal Activity is Missing Load More Button.
* Remove activity filter select from wall Page template

= 0.8.1 =
* Fix error message on the sitewide activity screen.

= 0.8 =
* Initial release.
