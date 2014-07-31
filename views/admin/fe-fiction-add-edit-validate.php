<script src="<?php echo plugins_url();?>/<?php echo FIC_PLUGIN_DIR ?>/views/jquery.validate.min.js" type="text/javascript"></script>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#title-prompt-text').text('');

	// validate signup form on keyup and submit
	var validator = jQuery("#post").validate({
		rules: {
			title: "required",
			<?php echo FIC_POST_CUSTOM_FIELDS_PREFIX.'author_notes'; ?>: {
				required: true,
				minlength: 1,
				maxlength: 100
			}
		},
		errorContainer: fefictionerrorcontainer,
		errorLabelContainer: $("ol", fefictionerrorcontainer),
		wrapper: 'li'
		//meta: "validate"
	});
});
</script>
<div id="fefictionerrorcontainer">
	<ol>
		<li><label for="title" class="error"><?php echo __('Title is required','fe-fiction'); ?></label></li>
		<li><label for="<?php echo FIC_POST_CUSTOM_FIELDS_PREFIX.'author_notes'; ?>" class="error"><?php echo __('Author Notes is required and cannot exceed 150 characters','fe-fiction'); ?></label></li>
	</ol>
</div>