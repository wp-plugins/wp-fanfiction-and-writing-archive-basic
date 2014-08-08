<?php if(isset($_POST['Submit'])){
    ?><script>location.reload();</script><?php
}?>
<div class="wrap">
	<div id="icon-ms-admin" class="icon32"><br></div>
	<h2><?php echo __('Fanfic.me Options','fe-fiction'); ?></h2> 

	<?php if($options_updated['success']) { ?><div id="message" class="updated below-h2"><p><?php echo __('Fanfic.me options have been updated','fe-fiction'); ?></p></div><?php } ?>
	<?php if(isset($options_updated['page_created_error']) && $options_updated['page_created_error'] != '') { ?><div id="error" class="error below-h2"><p><?php echo __('There were complications with page creation.  Please see message below.','fe-fiction'); ?></p></div><?php } ?>

	<form method="post" action="admin.php?page=writing-options">
	<?php
		settings_fields('fe-fiction-options');
	?>

	<table class="form-table fe-admin-form-table">
        <tr>
            <td></td>
            <td>
                <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                    <input type="hidden" name="cmd" value="_donations">
                    <input type="hidden" name="business" value="info@fandomentertainment.com">
                    <input type="hidden" name="lc" value="US">
                    <input type="hidden" name="item_name" value="Fandom Entertainment">
                    <input type="hidden" name="no_note" value="0">
                    <input type="hidden" name="currency_code" value="USD">
                    <input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHostedGuest">
                    <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                    <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
                </form>
                <div>WP Fanfiction and Writing Archive was designed by an English
                    teacher/fanfiction site publisher. <br>She worked with developers to
                    create a plugin that would host online writing communities. <br>Read more
                    < http://writing-archive.com/consulting-services/about/>
                    <br><br>
                    Please consider a donation: info@fandomentertainment.com and rate us
                    in the WordPress Plugins directory.</div></td>
        </tr>
        <tr>
		<th class="fe-admin-form-th">
		<p><strong><?php echo __('Create Fan Fiction Page','fe-fiction'); ?></strong></p></th>
		<td  class="fe-admin-form-td" >
			<?php if(isset($options_updated['page_created'])&& $options_updated['page_created']) { ?><div id="message" class="updated below-h2"><p><?php echo __('Page has been created for you','fe-fiction'); ?></p></div><?php } ?>
			<?php if(isset($options_updated['page_deleted']) && $options_updated['page_deleted']) { ?><div id="message" class="updated below-h2"><p><?php echo __('Page has been deleted!  You may safely create a new page.','fe-fiction'); ?></p></div><?php } ?>

			<?php if(isset($options_updated['page_created']) && !$options_updated['page_created'] && $options_updated['page_created_error'] != '') { ?><div id="error" class="error below-h2"><p><?php echo $options_updated['page_created_error']; ?></p></div><?php } ?>

			<?php if($current_fe_fiction_page != '0') { ?><div id="message" class="updated below-h2"><p><?php echo __('page already exists.','fe-fiction'); ?></p><p><a href="/wp-admin/post.php?post=<?php echo $current_fe_fiction_page; ?>&action=edit"><?php echo __('Manage the page here','fe-fiction'); ?></a></p><p><?php echo "<a title='" . esc_attr(__('Delete the existing page','fe-fiction')) . "' href='javascript:FeFiction_Confirm_Page_Delete();'>" . __('Delete the existing page.','fe-fiction') . "</a>"; ?></p></div><?php } ?>

			<?php if($current_fe_fiction_page == '0') { ?>
			<input name="FeFiction_Create_FeFiction_Page" type="checkbox" id="FeFiction_Create_FeFiction_Page" tabindex="1" value="1" /><label><?php echo __('Yes, Create the page for me','fe-fiction'); ?></label><br />
			<?php } ?>
			<label><?php echo __('The page title (anything other than "Fiction")','fe-fiction'); ?>:</label> <input name="fe_fiction_page_title" type="text" id="fe_fiction_page_title" class="fe_fiction_page_input" tabindex="1" size="50" value="<?php echo $current_fe_fiction_page_title; ?>" /><br />
			<em><?php echo __('(this will create a page for you that will be used to display your fiction list.  This is a single page that will be used for listing all fiction and fictin search results)','fe-fiction'); ?></em><br /><br />
			<em><?php echo __('<strong>Note:</strong> you don\'t need to have us do this!  You can create the page yourself.  just add [wp-fanfiction-writing-archive] where you want the fiction listing or story to display :)','fe-fiction'); ?></em>
		</td>
	</tr>
	<tr>
		<td colspan="2"><hr size="1" color="#dddddd" /></td>
	</tr>
	<tr>
		<th class="fe-admin-form-th">
		<p><strong><?php echo __('Dashboard','fe-fiction'); ?></strong></p></th>
		<td class="fe-admin-form-td"><?php if(isset($options_updated['custom_dashboard_enabled']) && $options_updated['custom_dashboard_enabled']) { ?><div id="message" class="updated below-h2"><p><?php echo __('Override Dashboard option enabled','fe-fiction'); ?></p></div><?php } ?>
			<input name="custom_dashboard" type="checkbox" id="custom_dashboard" tabindex="1" value="1" <?php if(get_option(FIC_OPTION_CUSTOM_DASHBOARD) == '1') { ?>checked="checked"<?php } ?> /><label><?php echo __('Override Dashboard','fe-fiction'); ?></label><br />
			<em><?php echo __('(this will change the standard WP dashboard and replace it with a nice options dashboard)','fe-fiction'); ?></em>
			<br /><strong>sample admin dashboard:</strong><br />
			<img src="<?php echo $plugin_view_path; ?>/images/db-ss-admin.png" />
			<br /><br />
			<strong>sample user (author) dashboard:</strong><br />
			<img src="<?php echo $plugin_view_path; ?>/images/db-ss-author.png" />
		</td>
	</tr>
	<tr>
		<td colspan="2"><hr size="1" color="#dddddd" /></td>
	</tr>
	<tr>
	  <th class="fe-admin-form-th"><strong><?php echo __('Enhanced Admin Interface','fe-fiction'); ?></strong></th>
	  <td class="fe-admin-form-td"><?php if(isset($options_updated['hide_admin_menus_enabled']) && $options_updated['hide_admin_menus_enabled']) { ?><div id="message" class="updated below-h2"><p><?php echo __('Admin menus are now hidden (except for admins)','fe-fiction'); ?></p></div><?php } ?>
		<input name="hide_admin_menus" type="checkbox" id="hide_admin_menus" tabindex="1" value="1" <?php if(get_option(FIC_OPTION_HIDE_ADMIN_MENUS) == '1') { ?>checked="checked"<?php } ?> /><label><?php echo __('Click to enable','fe-fiction'); ?></label>
		<br />
		<em><?php echo __('(Set up the admin interface so that Authors can only access their profile, read and manage comments, and manage their fiction)','fe-fiction'); ?></em></td>
	  </tr>
	<tr>
		<td colspan="2"><hr size="1" color="#dddddd" /></td>
	</tr>
	<tr>
	  <th class="fe-admin-form-th"><strong><?php echo __('Default Role','fe-fiction'); ?></strong></th>
	  <td class="fe-admin-form-td"><?php if(isset($options_updated['default_role_set']) && $options_updated['default_role_set']) { ?><div id="message" class="updated below-h2"><p><?php echo __('Default role has been set to Author','fe-fiction'); ?></p></div><?php } ?>
		<input name="enable_fe_fiction_default_role" type="checkbox" id="enable_fe_fiction_default_role" tabindex="1" value="1" <?php if(get_option(FIC_OPTION_ENABLE_DEFAULT_ROLE) != '') { ?>checked="checked"<?php } ?> /><label><?php echo __('Change the default role to Author','fe-fiction'); ?></label>
		<br />
		<em><?php echo __('(Allow Fanfic.me Fan Fiction to set the default role for new users to the Author level.  The Author level allows for users to submit and publish their own stories.  It also allows them to manage the comments for their stories.  You can always not enable this and continue to manage roles yourself.)','fe-fiction'); ?></em>
		<br /><br />
		<em><?php echo __('<strong>Note:</strong> If you disable this feature after having it enabled, make sure to go to the <a href="'.(is_multisite() ? 'ms-options.php' : 'options-general.php').'">settings</a> page and ensure the default level is what you desire. Typically this is set to "subscriber"'); ?></em></td>
	  </tr>
	<tr>
		<td colspan="2"><hr size="1" color="#dddddd" /></td>
	</tr>
	<tr>
	  <th class="fe-admin-form-th"><strong><?php echo __('Fan Fiction Page Stylesheet','fe-fiction'); ?></strong></th>
	  <td class="fe-admin-form-td"><?php if(isset($options_updated['fiction_page_stylesheet_enabled']) && $options_updated['fiction_page_stylesheet_enabled']) { ?><div id="message" class="updated below-h2"><p><?php echo __('Stylesheet Updated','fe-fiction'); ?></p></div><?php } ?>
		<label>
			<?php echo __('Customize the style defintions for the fiction pages','fe-fiction'); ?>
			<br /><em><?php echo __('(If you delete the entire contents here, we will reset it to the default stylesheet)','fe-fiction'); ?></em>
			<br /></label><textarea name="fiction_page_stylesheet" id="fiction_page_stylesheet" cols="80" rows="20" ><?php echo $fiction_page_stylesheet; ?></textarea>
		
	  </td>
	</tr>

	<tr>
		<td colspan="2"><hr size="1" color="#dddddd" /></td>
	</tr>
	<tr>
		<td colspan="2"><hr size="1" color="#dddddd" /></td>
	</tr>
        <tr>
            <th class="fe-admin-form-th"><strong><?php echo __('Fiction Position','fe-fiction'); ?></strong></th>
            <td>
                <input name="fe_fiction_position" type="text" id="fe_fiction_position" tabindex="1" class="fe_fiction_page_input" size="50" value="<?php echo $current_fe_fiction_position; ?>" /><br />
            </td>
        </tr>
        <tr>
            <td colspan="2"><hr size="1" color="#dddddd" /></td>
        </tr>
        <tr>
            <th class="fe-admin-form-th"><strong><?php echo __('Posting Page','fe-fiction'); ?></strong></th>
            <td>
                <label><?php echo __('Posting page slug','fe-fiction'); ?>:</label>
                <input name="fe_fiction_posting_page_id" type="text" id="fe_fiction_posting_page_id" tabindex="1" class="fe_fiction_page_input" size="50" value="<?php echo $current_fe_fiction_posting_page_id; ?>" /><br />
                <!--
        <em><?php echo __('(this will create a page for you that will be used to display your fiction list.  This is a single page that will be used for listing all fiction and fictin search results)','fe-fiction'); ?></em><br /><br />
        <em><?php echo __('<strong>Note:</strong> you don\'t need to have us do this!  You can create the page yourself.  just add [fe-fiction] where you want the fiction listing or story to display :)','fe-fiction'); ?></em> -->
            </td>
        </tr>


	</table>

	<p class="submit"><input type="submit" class="button-primary" name="Submit" value="<?php echo __('Save Changes','fe-fiction'); ?>"></p>

  </form>

</div>
