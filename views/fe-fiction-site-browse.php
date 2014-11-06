<?php
//include(FIC_PLUGIN_ABS_PATH_DIR.'/views/fe-fiction-search-block.php');
if (function_exists("FeFiction_Pagination")) {
	FeFiction_Pagination($wp_query->max_num_pages,$cur_query_string['paged'],7,'search',$cur_query_string['story_category']);
	echo '<br />';
}
if ( have_posts() ) {
	if($num_stories > 0 || isset($cur_query_string['paged']))
	{
	?>
	<div class="ui-widget ui-widget-content ui-corner-all fe-browse-site">
		<h3><?php
		if(is_search() || (isset($cur_query_string['submit']) && $cur_query_string['submit'] == __('Search','fe-fiction')))
		{
			echo sprintf(__('<strong>%s</strong> stories found matching your criteria','fe-fiction'),$num_stories);
		}
		else
		{
			echo sprintf(__('<strong>%s</strong> stories found matching your criteria','fe-fiction'),$num_stories);
		}
		?></h3>
	</div>
	<?php
	}

	while ( have_posts() ) {
		the_post();
		global $post;
		global $current_user;
		get_currentuserinfo();
		$weptilecurrent = $current_user->ID;
		$age_wep = get_user_meta( $weptilecurrent, 'fic_profile_age', $single );
		if($age_wep>17){
		$filter_bad_words = true;
		}else{
		$filter_bad_words = true;
		}
        $story_rating = array();
		$story_rating = wp_get_object_terms(FeFiction_the_ID(false), 'rating');
		$story_rating_slug = $story_rating[0]->slug;
		$story_rating_display_value = $story_rating[0]->name;

		if($GLOBALS['FIC_USERS_AGE'] >= FIC_RATINGS_MIN_AGE)
		{
			$filter_bad_words = false;
		}

		if($num_stories > 1 || isset($cur_query_string['paged']))
		{
			include(FIC_PLUGIN_ABS_PATH_DIR.'/views/fe-fiction-site-browse-multiple.php');
		}
		else
		{
			if( !preg_match("@".get_option('siteurl')."/".$wp->request."/?$@u",FeFiction_the_permalink(post_permalink(),false)) )
			{
				//wp_redirect(FeFiction_the_permalink(post_permalink(),false));
				?>
				<script type="text/javascript">
				location.replace('<?php FeFiction_the_permalink(post_permalink()); ?>');
				</script>
				<?php
			}
			else
			{
				if(in_array(strtolower($story_rating_slug),$GLOBALS['FIC_RATINGS_REQUIRING_AGE_VERIFICATION']) && $GLOBALS['FIC_USERS_AGE'] < FIC_RATINGS_MIN_AGE)
				{
					include(FIC_PLUGIN_ABS_PATH_DIR.'/views/fe-fiction-site-profile_age_form.php');
				}
			}
		}
	}

}
else
{

/* If there are no posts to display, such as an empty archive page */
if ( ! have_posts() ) : ?>
	<h2><?php
	if(is_search() || (isset($cur_query_string['submit']) && $cur_query_string['submit'] == __('Search','fe-fiction')))
	{
		echo __('Story Search Results','fe-fiction');
	}
	else
	{
		echo __('Browse Stories','fe-fiction');
	}
	?></h2>
	<div id="post-0" class="post error404 not-found">
		<h3 class="entry-title"><?php _e( 'Not Found', 'fe-fiction' ); ?></h3>
		<div class="entry-content">
			<p><?php echo __( 'We apologize, but no stories were found.','fe-fiction'); ?></p>
		</div><!-- .entry-content -->
	</div><!-- #post-0 -->
<?php endif; ?>

<?php
}

if (function_exists("FeFiction_Pagination")) {
	FeFiction_Pagination($wp_query->max_num_pages,$cur_query_string['paged'],2,'search');
}

