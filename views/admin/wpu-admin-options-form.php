		<?php if($updated_wpu_options) { ?>
		<div class="updated"><p><strong><?php echo __('Options saved.','fe-fiction'); ?></strong></p></div>
		<?php } ?>
		<div class="wrap">
			<h2><?php _e('Author Bio Directory Options') ?></h2>
				
			<form name="wpu_admin_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
				<input type="hidden" name="wpu_hidden" value="Y">
				<table>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td></td>
					</tr>
					<tr>
						<td><?php _e("Page ID: " ); ?>&nbsp;</td>
						<td colspan="2"><input type="text" name="wpu_page_id" value="<?php echo $pageid; ?>" size="3">&nbsp; ID of the page on which you want to display the user directory.</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td></td>
					</tr>
					<tr>
						<td><?php _e("Users Per Page: " ); ?>&nbsp;</td>
						<td colspan="2"><input type="text" name="wpu_users_per" value="<?php echo $usersperpage; ?>" size="3">&nbsp; How many users you want to display at once.</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td></td>
					</tr>
					<tr>
						<td><?php _e("Noindex User Listings: " ); ?>&nbsp;</td>
						<td colspan="2"><input name="wpu_noindex_users" type="checkbox" value="yes" <?php checked('yes', get_option('wpu_noindex_users')); ?> />&nbsp; Insert robots noindex meta tag on user listings to prevent search engine indexing.</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="3">
							<h3>Select Which Users to Display</h3>
							<p><small><strong>Note:</strong> If no options are selected, all users will be displayed.</small></p>
						</td>
					</tr>
					<tr>
						<td colspan="3"><input name="wpu_roles_admin" type="checkbox" value="yes" <?php checked('yes', get_option('wpu_roles_admin')); ?> />&nbsp; <?php _e("Administrator" ); ?></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="3"><input name="wpu_roles_fic_site_owner" type="checkbox" value="yes" <?php checked('yes', get_option('wpu_roles_fic_site_owner')); ?> />&nbsp; <?php _e("Site Owner" ); ?></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="3"><input name="wpu_roles_editor" type="checkbox" value="yes" <?php checked('yes', get_option('wpu_roles_editor')); ?> />&nbsp; <?php _e("Editors" ); ?></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="3"><input name="wpu_roles_author" type="checkbox" value="yes" <?php checked('yes', get_option('wpu_roles_author')); ?> />&nbsp; <?php _e("Authors" ); ?></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="3"><input name="wpu_roles_contributor" type="checkbox" value="yes" <?php checked('yes', get_option('wpu_roles_contributor')); ?> />&nbsp; <?php _e("Contribtors" ); ?></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="3"><input name="wpu_roles_subscriber" type="checkbox" value="yes" <?php checked('yes', get_option('wpu_roles_subscriber')); ?> />&nbsp; <?php _e("Subscribers" ); ?></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="3"><h3>Profile Options</h3></td>
					</tr>
					<tr>
						<td colspan="3"><input name="wpu_image_list" type="checkbox" value="yes" <?php checked('yes', get_option('wpu_image_list')); ?> />&nbsp; <?php _e("Display user images on directory page." ); ?></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="3"><input name="wpu_description_list" type="checkbox" value="yes" <?php checked('yes', get_option('wpu_description_list')); ?> />&nbsp; <?php _e("Display user descriptions on directory page." ); ?></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="3"><input name="wpu_image_profile" type="checkbox" value="yes" <?php checked('yes', get_option('wpu_image_profile')); ?> />&nbsp; <?php _e("Display user images on profile page." ); ?></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="3"><input name="wpu_description_profile" type="checkbox" value="yes" <?php checked('yes', get_option('wpu_description_profile')); ?> />&nbsp; <?php _e("Display user descriptions on profile page." ); ?></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="3"><input type="text" name="wpu_description_limit" value="<?php echo $desc_limit; ?>" size="3">&nbsp; <?php _e("Number of characters to display of user description on the directory page." ); ?></td>
					</tr>
					<tr>
						<td colspan="3">
							<p><small><strong>Note:</strong> If no limit is specified, entire user description will be displayed.</small></p>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="3"><h3>Avatar Options</h3></td>
					</tr>
					<tr>
						<td><?php _e("Avatar Type: " ); ?></td>
						<td colspan="2"><input id="wpu_avatars_gravatars" type="radio" name="wpu_avatars" value="gravatars" <?php checked('gravatars', get_option('wpu_avatars')); ?> /> Gravatars</td>
					</tr>
					<tr>
						<td></td>
						<td colspan="2"><input id="wpu_avatars_userphoto" type="radio" name="wpu_avatars" value="userphoto" <?php checked('userphoto', get_option('wpu_avatars')); ?> /> User Photo</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="3"><strong>Gravatar Options:</strong></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td></td>
					</tr>
					<tr>
						<td><?php _e("Gravatar Type: " ); ?>&nbsp;</td>
						<td><input type="text" name="wpu_gravatar_type" value="<?php echo $gravatar_type; ?>" size="15">&nbsp; Gravatar type - ex. mystery, blank, gravatar_default, identicon, wavatar, monsterid</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td></td>
					</tr>
					<tr>
						<td><?php _e("Gravatar Size: " ); ?>&nbsp;</td>
						<td><input type="text" name="wpu_gravatar_size" value="<?php echo $gravatar_size; ?>" size="2"> px &nbsp; Size of gravatar in the user listings.</td>
					</tr>
					<tr>
						<td colspan="3"><p class="submit"><input type="submit" name="Submit" value="<?php _e('Update Options') ?>" /></p></td>
					</tr>
				</table>
			</form>
		</div>