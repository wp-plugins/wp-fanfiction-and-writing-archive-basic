<?php
if(count($cur_user_story_list))
{
	$user_story_select = '<form id="fe-super-fiction-story-selection-frm" action="/wp-admin/post.php" method="get"><input type="hidden" name="post" value="'.$post->ID.'" /><input type="hidden" name="action" value="edit" />';
	$user_story_select .= '<strong>'.__('Creating a new chapter for a book?  Select the story / book here.','fe-fiction').'</strong>';
	$user_story_select .= '<br /><br /><select id="fe-super-fiction-story-selection" name="fe-super-fiction-story-selection">';
	$user_story_select .= '<option value="0">'.__('Select a Story','fe-fiction').'</option>';
	$user_story_select .= '<option value="0">'.__('No story','fe-fiction').'</option>';
	$user_story_select .= '<option value="0">- - - - - - - - - - - - - - - - -</option>';

	foreach($cur_user_story_list as $cur_user_story)
	{
		$selected = '';
		if(isset($_REQUEST['fe-super-fiction-story-selection']))
		{
			$selected = selected($_REQUEST['fe-super-fiction-story-selection'],$cur_user_story->ID,false);
		}
		else
		{
			$selected = selected($parent_story_selected,$cur_user_story->ID,false);
		}

		$user_story_select .= '<option value="'.$cur_user_story->ID.'" '.$selected.'>'.$cur_user_story->post_title.'</option>';

		if(isset($_GET['fe-super-fiction-story-selection']) && $_GET['fe-super-fiction-story-selection'] == $cur_user_story->ID)
		{
			$book_title = $cur_user_story->post_title;
			$parent_story_id = $cur_user_story->ID;
			$post_genres = get_the_terms($cur_user_story->ID,'genre');
			$post_categories = get_the_terms($cur_user_story->ID,'story_category');
			$post_ratings = get_the_terms($cur_user_story->ID,'rating');
			$post_pairings = get_the_terms($cur_user_story->ID,'pairings');
		?>
			<script type="text/javascript">
			jQuery(document).ready(function($) {
				$('#titlewrap').html('<div class="fe-admin-titlewrap"><?php echo __("Book Title: ","fe-fiction"); ?><span class="fe-admin-title-span"><?php echo wp_specialchars($book_title); ?></span></div><div class="fe-admin-title-span">(<?php echo __("Enter chapter number and chapter name below.","fe-fiction"); ?>)</div><input type="hidden" name="post_title" value="<?php echo wp_specialchars($book_title); ?>" />');
				$('#<?php echo FIC_POST_CUSTOM_FIELDS_PREFIX.'parent_story'; ?>').val('<?php echo $parent_story_id; ?>');
				$('#<?php echo FIC_POST_CUSTOM_FIELDS_PREFIX.'author_notes'; ?>').val('<?php echo addslashes(get_post_meta( $parent_story_id, FIC_POST_CUSTOM_FIELDS_PREFIX.'author_notes', true )); ?>');
				$('#<?php echo FIC_POST_CUSTOM_FIELDS_PREFIX.'summary'; ?>').val('<?php echo addslashes(get_post_meta( $parent_story_id, FIC_POST_CUSTOM_FIELDS_PREFIX.'summary', true )); ?>');
				<?php
				if(count($post_genres))
				{
					for($a=0;$a<count($post_genres);$a++)
					{
				?>
				$('#in-genre-<?php echo $post_genres[$a]->term_id; ?>').attr('checked',true);
				<?php
					}
				}
				if(count($post_categories))
				{
					for($a=0;$a<count($post_categories);$a++)
					{
				?>
				$('#in-story_category-<?php echo $post_categories[$a]->term_id; ?>').attr('checked',true);
				<?php
					}
				}
				if(count($post_ratings))
				{
					for($a=0;$a<count($post_ratings);$a++)
					{
				?>
				$('#in-rating-<?php echo $post_ratings[$a]->term_id; ?>').attr('checked',true);
				<?php
					}
				}
				if(count($post_pairings))
				{
					for($a=0;$a<count($post_pairings);$a++)
					{
				?>
				$('#in-pairings-<?php echo $post_pairings[$a]->term_id; ?>').attr('checked',true);
				<?php
					}
				}
				?>
			});
			</script>
		<?php
		}
		else
		{
		}
	}

	$user_story_select .= '</select> <!-- <input type="submit" name="story_from_existing_submit" value="'.__('Continue','fe-fiction').'"> --><br /><br />';
	$user_story_select .= '</form>';

	//$user_story_select_html = '<div class="metabox-holder"><div id="fe-super-fic-story_select_div" class="postbox ">';
	//$user_story_select_html .= '	<div class="handlediv" title="'.__('Click to toggle','fe-fiction').'"><br></div><h3 class="hndle"><span>'.__('New Chapter For A book?','fe-fiction').':</span></h3>';
	$user_story_select_html = '	<div class="inside fe-admin-inside">';
	$user_story_select_html .= '		'.$user_story_select;
	$user_story_select_html .= '	</div>';
	//$user_story_select_html .= '</div></div>';
}
else
{
	$user_story_select_html .= '';
}
if($parent_story_selected == 0)
{
?>
<script type=""text/javascript">
	jQuery(document).ready(function($) {
		$('#<?php echo FIC_POST_CUSTOM_FIELDS_PREFIX.'parent_story'; ?>').val('<?php echo ($post->ID); ?>');
	});
</script>
<?php
}
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$('.wrap').prepend('<?php echo str_replace("'","\'",$user_story_select_html); ?>');
	$('#fe-super-fiction-story-selection').live('change', function(e){
		if(e.target.value > 0 && confirm('<?php _e('Are you sure you want to create a new chapter for: ','fe-fiction'); ?>' + jQuery('#fe-super-fiction-story-selection option:selected').text() + '<?php _e('? \n\nNote: you will lose all unsaved data!!!','fe-fiction'); ?>' ) )
		{
			$('#fe-super-fiction-story-selection-frm').submit();
		}
	});
	$('.postarea').prepend('<h2><?php echo __("Content Body","fe-fiction"); ?></h2>');
});
</script>

<?php
if(isset($_SESSION['fiction_required_fields_missing']['story_category']) && $_SESSION['fiction_required_fields_missing']['story_category'])
{
	$update_post_status = array('ID'=>$_REQUEST['post'], 'post_status'=>'draft');
	wp_update_post( $update_post_status );
	unset($_SESSION['fiction_required_fields_missing']['story_category']);
?>
<script type="text/javascript">
alert('You must select a fandom for your story');
location.replace('post.php?post=<?php echo $_REQUEST['post']; ?>&action=edit');
</script>
<?php
}
?>