<?php if(is_user_logged_in()){
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#fiction_search_block').hide('blind');
            $('#fic_age_verification_submit_btn').button().click(function(){
                $('#fic_age_verification_frm').trigger('submit');
            });
        });
    </script>
<?php } ?>
<h3><?php printf(__('This story has been set to a rating of <strong>%s</strong>.  Age verification is required to proceed.','fe-fiction'),$story_rating_display_value); ?></h3>
<div id="fic_age_verification" class="ui-widget ui-widget-content ui-corner-all">
	<form id="fic_age_verification_frm" name="fic_age_verification_frm" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<?php wp_nonce_field('Y','age_verify_submission'); ?>

		<?php include(FIC_PLUGIN_ABS_PATH_DIR.'/views/admin/fe-fiction-user_profile_age.php'); ?>

		<br />
		<div class="fe-age-verification-button-div"><span id="fic_age_verification_submit_btn" name="fic_age_verification_submit"><?php echo __('Submit','fe-fiction'); ?></span></div>
	</form>
</div>