<?php
/**
* FanficMeSidebarWidget Class
*/
class FanficMeSidebarWidget extends WP_Widget
{
	/** constructor */
	function FanficMeSidebarWidget() {
		parent::WP_Widget(false, $name = __('WP FFWA Most Recent Submissions','fe-fiction'));
	}

	/** @see WP_Widget::widget */
	function widget($args, $instance)
	{
		extract( $args );
		$title = apply_filters('widget_title', $instance['title']);
		?>
		<?php echo $before_widget; ?>
		<?php
		if ( $title )
			echo $before_title . $title . $after_title;

			/**
			$user = get_user_by('id',get_current_user_id());
			?>
			<strong>
			<?php
			if ( $instance['show_login_logout_link'] )
			{
				if(is_user_logged_in() ) {
					printf(__('Welcome, %s.','fe-fiction'), ($instance['show_profile_name'] == '1' ? $user->display_name : $user->user_login) );
				}
				else
				{
					_e('Welcome!','fe-fiction');
 				}

				echo '<br />';
				wp_loginout();
			}
			else
			{
				if(is_user_logged_in() ) {
					printf(__('Welcome, %s.','fe-fiction'), ($instance['show_profile_name'] == '1' ? $user->nickname : $user->user_login) );
				}
				else
				{
					_e('Welcome!','fe-fiction');
				}
			}
			?>
			</strong>
			<?php
			**/
			?>
			<?php
			//if(is_user_logged_in() ) {
			?>

			<?php wp_loginout(); ?>
			<br /><!-- <br /> -->
			<a title="<?php _e('Dashboard','fe-fiction'); ?>" href="<?php echo admin_url(); ?>"><?php _e('Dashboard','fe-fiction'); ?></a>

			<br /><a title="<?php _e('My profile','fe-fiction'); ?>" href="<?php echo admin_url('profile.php'); ?>"><?php _e('My Profile','fe-fiction'); ?></a>

			<br /><a title="<?php _e('My Fiction','fe-fiction'); ?>" href="<?php echo admin_url('edit.php?post_type=fiction'); ?>"><?php _e('My Fiction','fe-fiction'); ?></a>

			<br /><a title="<?php _e('Post a Story','fe-fiction'); ?>" href="<?php echo admin_url('post-new.php?post_type=fiction'); ?>"><?php _e('Post a Story','fe-fiction'); ?></a>
			<?php
			//}
			?>

			<?php echo $after_widget; ?>
		<?php
	}

	/** @see WP_Widget::update */
	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;

		$instance['title'] = strip_tags($new_instance['title']);

		/**
		$instance['show_login_logout_link'] = strip_tags($new_instance['show_login_logout_link']);

		$instance['show_profile_name'] = strip_tags($new_instance['show_profile_name']);
		**/

		return $instance;
	}

	/** @see WP_Widget::form */
	function form($instance)
	{
		$title = esc_attr($instance['title']);
		$show_login_logout_link = esc_attr($instance['show_login_logout_link']);
		$show_profile_name = esc_attr($instance['show_profile_name']);
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><strong><?php _e('Title:','fe-fiction'); ?></strong></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />

			<!--
			<br /><br />
			<input id="<?php echo $this->get_field_id('show_login_logout_link'); ?>" name="<?php echo $this->get_field_name('show_login_logout_link'); ?>" type="checkbox" value="1" <?php echo $show_login_logout_link == '1' ? 'checked="checked"' : ''; ?> />
			<label for="<?php echo $this->get_field_id('show_login_logout_link'); ?>"><?php _e('Show Login/Logout Link','fe-fiction'); ?></label>

			<br />
			<input id="<?php echo $this->get_field_id('show_profile_name'); ?>" name="<?php echo $this->get_field_name('show_profile_name'); ?>" type="checkbox" value="1" <?php echo $show_profile_name == '1' ? 'checked="checked"' : ''; ?> />
			<label for="<?php echo $this->get_field_id('show_profile_name'); ?>"><?php _e('Show User\'s Nickname','fe-fiction'); ?></label>
			-->
		</p>
	<?php 
	}

} // class FanficMeSidebarWidget
?>