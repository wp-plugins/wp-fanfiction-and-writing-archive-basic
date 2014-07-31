<?php if(isset($_GET['user_id'])){
     $user_id = $_GET['user_id'];

}else{

    $user_id = get_current_user_id();
}
$user_last = get_user_meta( $user_id, 'fic_profile_age', 'true' ); 
if(!isset($user_last)||$user_last==''){
	$user_last = 17;}?>
<div class="ui-widget ui-widget-content ui-corner-all fe-admin-profile-age">
	<div class="ui-widget ui-widget-content ui-corner-all fe-admin-profile-age-2">
		<h3><?php echo __('Age Verification','fe-fiction'); ?></h3>
		<span><strong><?php printf(__('I am %s years of age as of today, %s','fe-fiction'),'<input name="fic_profile_age" type="text" id="fic_profile_age" value="'.$user_last.'" size="3" maxlength="3" class="fe-admin-age-input" />',mysql2date(get_option('date_format'), date('Y-m-d H:i:s'))); ?></strong></span>

		<p><?php printf(__('Enter your current age into the field provide above.  Stories with a rating of R or NC-17 may contain material not suitable for children.  %s requires that all individuals wishing to read these stories confirm they are of at least 17 years of age.  %s uses the MPAA rating labeling system for all stories.','fe-fiction'),get_bloginfo('name'),get_bloginfo('name')); ?></p>
		
		<p><strong><?php printf(__('%s will also make a best attempt to filter profane words in stories that are not rated R or NC-17 unless the individual confirms they are of at least 17 years of age.','fe-fiction'),get_bloginfo('name')); ?></strong></p>
	</div>

	<div class="ui-widget ui-widget-content ui-corner-all fe-admin-rating-div">
		<a name="fic_rating_labels"></a>
		<strong><?php printf(__('%1$s uses the following rating scale for stories.','fe-fiction'),get_bloginfo('name')); ?></strong>
		<br /><br />
		<img src="<?php echo plugins_url();?>/wp-fanfiction-writing-archive-basic/views/images/g_rating.gif" />
		<br />
		<img src="<?php echo plugins_url();?>/wp-fanfiction-writing-archive-basic/views/images/pg_rating.gif" />
		<br />
		<img src="<?php echo plugins_url();?>/wp-fanfiction-writing-archive-basic/views/images/pg-13_rating.gif" />
		<br />
		<img src="<?php echo plugins_url();?>/wp-fanfiction-writing-archive-basic/views/images/r_rating.gif" />
		<br />
		<img src="<?php echo plugins_url();?>/wp-fanfiction-writing-archive-basic/views/images/nc-17_rating.gif" />
	</div>
</div>