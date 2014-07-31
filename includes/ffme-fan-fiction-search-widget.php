<?php
/**
* FanficMeFanFictionSearchSidebarWidget Class
*/
class FanficMeFanFictionSearchSidebarWidget extends WP_Widget
{
	/** constructor */
	function FanficMeFanFictionSearchSidebarWidget() {
		parent::WP_Widget(false, $name = __('WP FFWA Story Search','fe-fiction'));
	}

	/** @see WP_Widget::widget */
	function widget($args, $instance)
	{
		extract( $args );
		$title = apply_filters('widget_title', $instance['title']);

		echo $before_widget;

		if ( $title )
			echo $before_title . $title . $after_title;
		?>
		<form id="fiction_search_frmw" name="fiction_search_frmw" method="get" action="/<?php echo FeFiction_Get_Page_Slug_Name(); ?>/">
		<label for="keywordw"><?php echo __('Keyword','fe-fiction'); ?>:<br /><input type="text" name="s" id="keywordw" value="<?php echo isset($_REQUEST['s']) ? stripslashes($_REQUEST['s']) : ''; ?>" /></label><br /><br />
		<?php
		
		$search_cat_args['echo'] = 1;
		$search_cat_args['show_option_all'] = __('Fandom','fe-fiction');
		$search_cat_args['name'] = 'story_category';
		$search_cat_args['id'] = 'story_categoryw';
		$search_cat_args['taxonomy'] = 'story_category';
		$search_cat_args['hierarchical'] = 0;
		$search_cat_args['selected'] = $_REQUEST['story_category'];
		$search_cat_args['exclude'] = '31';
		wp_dropdown_categories( $search_cat_args );
		echo '<br />';
		global $wpdb;
		$author = $wpdb->get_results('
								SELECT DISTINCT
								  post_author
								FROM
								  `'.$wpdb->prefix.'posts`'
							,ARRAY_A);
		$authorlist = array();
		for($i=0;$i<count($author);$i++){
			$authorlist[] = $author[$i]['post_author'];
			}
			
		$search_user_args = array(
			'show_option_all'		 => __('Authors','fe-fiction'),
			'show_option_none'		=> '',
			'hide_if_only_one_author' => 0,
			'orderby'				 => 'display_name',
			'order'				   => 'ASC',
			'include'				 => $authorlist,
			'exclude'				 => '1',
			'multi'				   => 0,
			'show'					=> 'display_name',
			'echo'					=> 1,
			'selected'				=> $_REQUEST['story_author'],
			'include_selected'		=> 1,
			'name'					=> 'story_author',
			'id'					  => 'story_authorw',
			'class'				   => 'postform',
			'blog_id'				 => $GLOBALS['blog_id'],
			'who'					 => ''
		);

		wp_dropdown_users( $search_user_args );
		echo '<br />';

		$search_cat_args['echo'] = 1;
		$search_cat_args['show_option_all'] = __('Genres','fe-fiction');
		$search_cat_args['name'] = 'genre';
		$search_cat_args['id'] = 'genrew';
		$search_cat_args['taxonomy'] = 'genre';
		$search_cat_args['hierarchical'] = 0;
		$search_cat_args['selected'] = $_REQUEST['genre'];
		wp_dropdown_categories( $search_cat_args );
		echo '<br />';

		$search_cat_args['echo'] = 1;
		$search_cat_args['show_option_all'] = __('Ratings','fe-fiction');
		$search_cat_args['name'] = 'rating';
		$search_cat_args['id'] = 'ratingw';
		$search_cat_args['taxonomy'] = 'rating';
		$search_cat_args['hierarchical'] = 0;
		$search_cat_args['selected'] = $_REQUEST['rating'];
		wp_dropdown_categories( $search_cat_args );
		echo '<br />';

		$search_cat_args['echo'] = 1;
		$search_cat_args['show_option_all'] = __('Characters','fe-fiction');
		$search_cat_args['name'] = 'pairings';
		$search_cat_args['id'] = 'pairingsw';
		$search_cat_args['taxonomy'] = 'pairings';
		$search_cat_args['hierarchical'] = 0;
		$search_cat_args['hide_if_empty'] = 1;
		$search_cat_args['selected'] = $_REQUEST['pairings'];
		wp_dropdown_categories( $search_cat_args );
		echo '<br />';

		?>
		<input type="submit" name="submit" value="<?php echo __('Search','fe-fiction'); ?>" />
		</form>
		<?php
		echo $after_widget;
	}

	/** @see WP_Widget::update */
	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;

		$instance['title'] = strip_tags($new_instance['title']);

		return $instance;
	}

	/** @see WP_Widget::form */
	function form($instance)
	{
		$title = esc_attr($instance['title']);
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><strong><?php _e('Title:','fe-fiction'); ?></strong></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
	<?php 
	}

} // class FanficMeFanFictionSearchSidebarWidget
