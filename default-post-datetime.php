<?php
/*
Plugin Name: Default Post Date and Time
Version: 1.2
Plugin URI: http://toolstack.com/default-post-datetime
Author: Greg Ross
Author URI: http://toolstack.com/
Description: Set the default date and time when you create a new post.

Compatible with WordPress 3+.

Read the accompanying readme.txt file for instructions and documentation.

Copyright (c) 2012-15 by Greg Ross

This software is released under the GPL v2.0, see license.txt for details
*/

// Set our version for use later as a define.
define( 'DPDT_VER', '1.2' );

// Our admin page is pretty basic, really just an about box at this time.
function default_post_datetime_admin_page()
	{
	//***** Start HTML
	?>
<div class="wrap">
	<fieldset style="border:1px solid #cecece;padding:15px; margin-top:25px" >
		<legend><span style="font-size: 24px; font-weight: 700;">&nbsp;<?php _e('User Settings');?>&nbsp;</span></legend>
		<p><?php echo sprintf(__('User settings can be found in %syour profile page%s, under the Default Post Date and Time heading.'), '<a href="' . get_edit_profile_url(get_current_user_id()) . '#DefaultPostDateTime">', '</a>' );?></p>
	</fieldset>

	<fieldset style="border:1px solid #cecece;padding:15px; margin-top:25px" >
		<legend><span style="font-size: 24px; font-weight: 700;">About</span></legend>
		<h2><?php echo __('Default Post Date and Time Version') . ' ' . DPDT_VER;?></h2>
		<p><?php echo __('by');?> Greg Ross</p>
		<p>&nbsp;</p>
		<p><?php printf(__('Licenced under the %sGPL Version 2%s'), '<a href="http://www.gnu.org/licenses/gpl-2.0.html" target=_blank>', '</a>');?></p>
		<p><?php printf(__('To find out more, please visit the %sWordPress Plugin Directory page%s or the plugin home page on %sToolStack.com%s'), '<a href="http://wordpress.org/plugins/default-post-datetime/" target=_blank>', '</a>', '<a href="http://toolstack.com/default-post-datetime" target=_blank>', '</a>');?></p>
		<p>&nbsp;</p>
		<p><?php printf(__("Don't forget to %srate and review%s it too!"), '<a href="http://wordpress.org/support/view/plugin-reviews/default-post-datetime" target=_blank>', '</a>');?></p>
</fieldset>
</div>
	<?php
	//***** End HTML
	}
	
function default_post_datetime_admin()
{
	add_options_page( 'Default Post Date and Time', 'Default Post Date and Time', 'manage_options', basename( __FILE__ ), 'default_post_datetime_admin_page');
}

// Add the profile fields 
function default_post_datetime_load_profile( $user )
	{
	include_once( "default-post-datetime-options.php" );
	default_post_datetime_user_profile_fields( $user );
	}

function default_post_datetime_save_profile( $user )
	{
	include_once( "default-post-datetime-options.php" );
	default_post_datetime_save_user_profile_fields( $user );
	}

function default_post_datetime_hook( $data , $postarr )
	{
	GLOBAL $wpdb;
	
	if( empty( $postarr['post_date'] ) || '0000-00-00 00:00:00' == $postarr['post_date'] ) 
		{
		$cuid = get_current_user_id();
		$options = get_the_author_meta( 'default_post_datetime', $cuid );

		if( !array_key_exists( 'uselastpost', $options ) ) { $options['uselastpost'] = 'off'; }

		if( !is_array( $options['disable'] ) ) { $options['disable'] = array();	} 
		
		if( array_key_exists( $data['post_type'], $options['disable'] ) ) 
			{
			if( $options['disable'][$data['post_type']] == 'on' )
				{
				return $data;
				}
			}
			
		// If both date and time are left blank, just fall back to the WordPress default behaviour.
		if( !( $options['date'] == '' && $options['time'] == '' && $options['uselastpost'] != 'on' ) ) 
			{
			// Use the current start time by default.
			$starttime = time();

			// Check to see if we're been told to use the existing scheduled posts to base our date calculation on.
			if( $options['uselastpost'] == 'on' )
				{
				// Find the post with the farthest time stamp in the future.
				$result = $wpdb->get_var( "SELECT ID FROM " . $wpdb->posts . " WHERE post_status = 'future' AND post_type = '" . $data['post_type']. "' ORDER BY post_date DESC LIMIT 1" );

				// If we found one, set the start time.
				if( !empty( $result ) )
					{
					$starttime = get_the_time('U', $result ); // Get the timestamp for the last post
					}
					
				// If the date is blank, use the future  date.
				if( $options['date'] == '' ) { $options['date'] = date('Y-m-d', $starttime); }
				
				// If time is left blank, use the future time.
				if( $options['time'] == '' ) { $options['time'] = date('G:i:s', $starttime); }
				}

			// If the date is blank, use today's date.
			if( $options['date'] == '' ) { $options['date'] = date('Y-m-d'); }
			
			// If time is left blank, use the current time.
			if( $options['time'] == '' ) { $options['time'] = date('G:i:s'); }
				
			// Calculate the new date/time.
			$newtime = strtotime( $options['date'] . ' ' . $options['time'], $starttime );
			
			$data['post_date'] = date( 'Y-m-d G:i:s', $newtime );
			$data['post_date_gmt'] = get_gmt_from_date( $data['post_date'] );
			}
		}
		
	return $data;
	}

// Add a WordPress plugin page and rating links to the meta information to the plugin list.
function default_post_datetime_add_meta_links($links, $file) 
	{
	if( $file == plugin_basename(__FILE__) ) 
		{
		$plugin_url = 'http://wordpress.org/plugins/default-post-datetime/';
		
		$links[] = '<a href="'. $plugin_url .'" target="_blank" title="'. __('Click here to visit the plugin on WordPress.org') .'">'. __('Visit WordPress.org page') .'</a>';
		
		$rate_url = 'http://wordpress.org/support/view/plugin-reviews/default-post-datetime?rate=5#postform';
		$links[] = '<a href="'. $rate_url .'" target="_blank" title="'. __('Click here to rate and review this plugin on WordPress.org') .'">'. __('Rate this plugin') .'</a>';
		}
	
	return $links;
	}

// Add a WordPress plugin page and rating links to the meta information to the plugin list.
add_filter('plugin_row_meta', 'default_post_datetime_add_meta_links', 10, 2);
	
// Add the hook to the insert post code.
add_filter( 'wp_insert_post_data', 'default_post_datetime_hook', '99', 2 );	

// Add the admin menu item.
add_action( 'admin_menu', 'default_post_datetime_admin', 1 );

// Handle the user profile items
add_action( 'show_user_profile', 'default_post_datetime_load_profile' );
add_action( 'edit_user_profile', 'default_post_datetime_load_profile' );
add_action( 'personal_options_update', 'default_post_datetime_save_profile' );
add_action( 'edit_user_profile_update', 'default_post_datetime_save_profile' );

?>