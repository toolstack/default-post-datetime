<?php
/*
Copyright (c) 2013 by Greg Ross

This software is released under the GPL v2.0, see license.txt for details
*/

/*
 	This function returns either on or off depending on the state of an HTML checkbox 
    input field returned from a post command.
*/
function default_post_datetime_get_checked_state( $value )
	{
	if( $value == 'on' ) 
		{
		return 'on';
		}
	else
		{
		return 'off';
		}
	}

/*
 	This function is called to save the user profile settings for Just Writing.
*/
function default_post_datetime_save_user_profile_fields( $user_id )
	{
	if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
	
	update_user_meta( $user_id, 'default_post_datetime', $_POST['default_post_datetime'] );
	}

/*
 	This function is called to draw the user settings page for Just Writing.
*/
function default_post_datetime_user_profile_fields( $user ) 
	{ 
	// If the user cannot edit posts or pages, then we don't want to display the options as they won't be using them.
	if ( !current_user_can( 'edit_posts', $user ) || !current_user_can( 'edit_pages', $user ) ) { return; }

	// Check to see if this is the first time we've run for this user and no config
	// has been written yet, so let's do that now.
	if( get_the_author_meta( 'default_post_datetime', $user->ID ) == "" )
		{
		if ( current_user_can( 'edit_user', $user->ID ) ) 
			{
			update_user_meta( $user->ID, 'default_post_datetime', array( 'date' => '', 'time' => '', 'uselastpost' => '' ) );
			}
		}
	
	$options = get_the_author_meta( 'default_post_datetime', $user->ID );

	if( !array_key_exists('disable', $options) ) { $options['disable'] = array(); }
	if( !is_array($options['disable']) ) { $options['disable'] = array(); }
	
	wp_register_script( 'strtotime_js', plugins_url( '', __FILE__ )  . '/strtotime.js' );
	wp_enqueue_script( 'strtotime_js' );
	
	?>
	<h3 id=DefaultPostDateTime><?php _e('Default Post Date and Time', 'default-post-datetime');?></h3>

	<script>
	function DefaultPostDateTimeValidate() 
		{
		dpdt_date = jQuery('#default_post_datetime_date').val();
		dpdt_time = jQuery('#default_post_datetime_time').val();
		
		if( !strtotime( dpdt_date ) && dpdt_date != '' ) 
			{ 
			jQuery('#default-post-datetime-date-valid').hide();
			jQuery('#default-post-datetime-date-invalid').show();
			}
		else
			{ 
			jQuery('#default-post-datetime-date-invalid').hide();
			jQuery('#default-post-datetime-date-valid').show();
			}
	
		if( !strtotime( "2009-05-04 " + dpdt_time ) ) 
			{ 
			jQuery('#default-post-datetime-time-valid').hide();
			jQuery('#default-post-datetime-time-invalid').show();
			}
		else
			{ 
			jQuery('#default-post-datetime-time-invalid').hide();
			jQuery('#default-post-datetime-time-valid').show();
			}
		}
	</script>
	
	<table class="form-table">
		<tr>
			<th></th>
			<td>
			<span class="description"><?php echo __("The following date and time will be used for the defaults when you create a new post.", 'default-post-datetime');?></span><br><br>
			<span class="description"><?php echo sprintf( __("The date can use the standard %sPHP strtotime()%s format, for example you can set the date to 'next Tuesday' and a new post will have a default date of the following Tuesday set.  You may leave the fields blank to use the default WordPress behaviour.", 'default-post-datetime'), "<a href='http://php.net/manual/en/datetime.formats.php'>", "</a>");?></span><br><br>
			<span class="description"><?php echo __("You should validate your settings with the button supplied.", 'default-post-datetime');?></span>
			</td>
		</tr>
	</table>
	<table class="form-table" id='default_post_datetime_options_table'>	
		<tr>
			<th>
			<?php echo __("Date", 'default-post-datetime');?>: 
			</th>
			<td>
			<input type="text" id="default_post_datetime_date" name="default_post_datetime[date]" size='40' value='<?php echo $options['date']?>'><div id="default-post-datetime-date-valid" class="dashicons dashicons-yes" style="font-size:26pt; color: lightgreen; display: none;"></div><div id="default-post-datetime-date-invalid" class="dashicons dashicons-no" style="font-size:26pt; color: red; display: none;"></div>
			</td>
		</tr>

		<tr>
			<th>
			<?php echo __("Time", 'default-post-datetime');?>: 
			</th>
			<td>
			<input type="text" id="default_post_datetime_time" name="default_post_datetime[time]" size='10' value='<?php echo $options['time']?>'><div id="default-post-datetime-time-valid" class="dashicons dashicons-yes" style="font-size:26pt; color: lightgreen; display: none;"></div><div id="default-post-datetime-time-invalid" class="dashicons dashicons-no" style="font-size:26pt; color: red; display: none;"></div>
			</td>
		</tr>

		<tr>
			<th></th>
			<td>
			<input type='button' id='date_check' name='date_check' class='button' value='Validate' onClick='DefaultPostDateTimeValidate()'>
			</td>
		</tr>

		<tr>
			<th>
			<?php echo __("Use latest scheduled post as the starting time", 'default-post-datetime');?>: 
			</th>
			<td>
			<input type="checkbox" id="default_post_datetime_uselastpost" name="default_post_datetime[uselastpost]"<?php if( !array_key_exists( 'uselastpost', $options ) ) { $options['uselastpost'] = ''; } if( default_post_datetime_get_checked_state( $options['uselastpost'] ) == 'on' ) { echo ' CHECKED'; } ?>>
			</td>
		</tr>

		<tr>
			<th>
			<?php echo __("Disable for post type", 'default-post-datetime');?>: 
			</th>
			<td>
			<?php
				$post_types = get_post_types( '', 'objects' ); 

				foreach ( $post_types as $post_type ) {
					echo '			<input type="checkbox" id="default_post_datetime_disable_' . $post_type->name . '" name="default_post_datetime[disable][' . $post_type->name . ']"';
					if( array_key_exists( $post_type->name, $options['disable'] ) ) {
						if( default_post_datetime_get_checked_state( $options['disable'][$post_type->name] ) == 'on' ) { echo ' CHECKED'; }
					}
					echo ">" . $post_type->label . "<br>\n";
				}


			?>
			</td>
		</tr>
		
		
	</table>
<?php 
	}
?>