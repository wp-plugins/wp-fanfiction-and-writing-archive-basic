<?php
$site_user_limit = get_option(FIC_OPTION_SITE_USER_LIMIT,"");
?>
<div class="wrap">
	<div id="icon-ms-admin" class="icon32"><br></div>
	<h2><?php echo __('Site User Limits','fe-fiction'); ?></h2> 

	<?php echo __('Use this setting to limit the number of users that this site is permitted to have.  This does not include Administrators or the site owner','fe-fiction'); ?>

	<?php if($options_updated['success']) { ?><div id="message" class="updated below-h2"><p><?php echo __('site options have been updated','fe-fiction'); ?></p></div><?php } ?>

	<form method="post" action="admin.php?page=fanficme_site_user_limits">
	<?php
		settings_fields('fanficme_site_user_limits');
	?>

	<table class="form-table fe-admin-form-table">
	<tr>
		<th class="fe-admin-form-th">
		<p><strong><?php echo __('Set User Limit','fe-fiction'); ?></strong></p></th>
		<td class="fe-admin-form-td">
			<select name="user_limit" id="user_limit">
				<option value="" <?php echo $site_user_limit == '0' ? 'selected="selected"' : ''; ?>><?php echo __('Unlimited','fe-fiction'); ?></option>
				<option value="1" <?php echo $site_user_limit == '1' ? 'selected="selected"' : ''; ?>>1</option>
				<option value="5" <?php echo $site_user_limit == '5' ? 'selected="selected"' : ''; ?>>5</option>
				<option value="10" <?php echo $site_user_limit == '10' ? 'selected="selected"' : ''; ?>>10</option>
				<option value="25" <?php echo $site_user_limit == '25' ? 'selected="selected"' : ''; ?>>25</option>
				<option value="50" <?php echo $site_user_limit == '50' ? 'selected="selected"' : ''; ?>>50</option>
				<option value="75" <?php echo $site_user_limit == '75' ? 'selected="selected"' : ''; ?>>75</option>
				<option value="100" <?php echo $site_user_limit == '100' ? 'selected="selected"' : ''; ?>>100</option>
				<option value="150" <?php echo $site_user_limit == '150' ? 'selected="selected"' : ''; ?>>150</option>
				<option value="250" <?php echo $site_user_limit == '250' ? 'selected="selected"' : ''; ?>>250</option>
				<option value="500" <?php echo $site_user_limit == '500' ? 'selected="selected"' : ''; ?>>500</option>
			</select>
		</td>
	</tr>
	<tr>
		<td colspan="2"><hr size="1" color="#dddddd" /></td>
	</tr>
	</table>

	<p class="submit"><input type="submit" class="button-primary" name="Submit" value="<?php echo __('Save Changes','fe-fiction'); ?>"></p>

  </form>

</div>