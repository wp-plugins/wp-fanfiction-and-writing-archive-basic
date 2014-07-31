<h3><?php echo __('Fan Fiction Options','fe-fiction'); ?></h3>
<table class="form-table">
	<tbody>
	<tr>
		<th scope="row"><?php echo __('Fiction Story Scoring','fe-fiction'); ?></th>
		<td><input name="<?php echo FIC_OPTION_FICTION_STORY_SCORING; ?>" type="checkbox" id="<?php echo FIC_OPTION_FICTION_STORY_SCORING; ?>" value="1" <?php if(get_user_meta(get_current_user_id(),FIC_OPTION_FICTION_STORY_SCORING,true) == '1') { ?>checked="checked"<?php } ?>> <label for="<?php echo FIC_OPTION_FICTION_STORY_SCORING; ?>"><?php echo __('Enable Fiction Story Scoring (star ratings)','fe-fiction'); ?></label></td>
	</tr>
	</tbody>
</table>
<br />
<?php
include_once(FIC_PLUGIN_ABS_PATH_DIR.'/views/admin/fe-fiction-user_profile_age.php');
?>

