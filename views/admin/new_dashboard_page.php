<ul id="dashboard-cms">
	<?php if(current_user_can('edit_pages')) { ?><li class="left-gray"><a href="post-new.php?post_type=page"><div id="icon-edit-pages" class="icon32">&nbsp;</div><?php echo __('Add Site Page','fe-fiction'); ?></a></li><?php } ?>
	<?php if(current_user_can('edit_pages')) { ?><li class="right-gray"><a href="edit.php?post_type=page"><div id="icon-edit-pages" class="icon32">&nbsp;</div><?php echo __('Edit Site Pages','fe-fiction'); ?></a></li><?php }

	if(!post_type_exists( CUSTOM_POST_TYPE )) { ?>

		<?php if(current_user_can('edit_posts')) { ?><li class="left-gray"><a href="post-new.php"><div id="icon-edit" class="icon32">&nbsp;</div><?php echo __('New Blog Post','fe-fiction'); ?></a></li><?php } ?>
		<?php if(current_user_can('edit_posts')) { ?><li class="right-gray"><a href="edit.php"><div id="icon-edit" class="icon32">&nbsp;</div><?php echo __('Edit Blog Posts','fe-fiction'); ?></a></li><?php } ?>

	<?php } else { ?>

		<?php if(current_user_can('manage_options')) { ?><li class="left-gray"><a href="post-new.php"><div id="icon-edit" class="icon32">&nbsp;</div><?php echo __('New Blog Post','fe-fiction'); ?></a></li><?php } ?>
		<?php if(current_user_can('manage_options')) { ?><li class="right-gray"><a href="edit.php"><div id="icon-edit" class="icon32">&nbsp;</div><?php echo __('Edit Blog Posts','fe-fiction'); ?></a></li><?php } ?>

		<?php if(current_user_can('edit_posts')) { ?><li class="left-gray"><a href="post-new.php?post_type=fiction"><div id="icon-edit" class="icon32">&nbsp;</div><?php echo __('New Fan Fiction Story','fe-fiction'); ?></a></li><?php } ?>
		<?php if(current_user_can('edit_posts')) { ?><li class="right-gray"><a href="edit.php?post_type=fiction"><div id="icon-edit" class="icon32">&nbsp;</div><?php echo __('Manage Fan Fiction Stories','fe-fiction'); ?></a></li><?php } ?>

	<?php } ?>

	<?php if(current_user_can('switch_themes')) { ?><li class="left-gray"><a href="themes.php"><div id="icon-themes" class="icon32">&nbsp;</div><?php echo __('Manage Themes','fe-fiction'); ?></a></li><?php } ?>
	<?php if(current_user_can('edit_themes') && current_theme_supports('widgets')) { ?><li class="left-gray"><a href="widgets.php"><div id="icon-themes" class="icon32">&nbsp;</div><?php echo __('Manage Widgets','fe-fiction'); ?></a></li><?php } ?>
	<?php if(current_user_can('edit_themes') && current_theme_supports('menus')) { ?><li class="right-gray"><a href="nav-menus.php"><div id="icon-themes" class="icon32">&nbsp;</div><?php echo __('Manage Menus','fe-fiction'); ?></a></li><?php } ?>
	<?php if(current_user_can('edit_themes') && current_theme_supports('custom-header')) { ?><li class="right-gray"><a href="themes.php?page=custom-header"><div id="icon-themes" class="icon32">&nbsp;</div><?php echo __('Theme Header','fe-fiction'); ?></a></li><?php } ?>
	<?php if(current_user_can('edit_themes') && current_theme_supports('custom-background')) { ?><li class="right-gray"><a href="themes.php?page=custom-background"><div id="icon-themes" class="icon32">&nbsp;</div><?php echo __('Theme Background','fe-fiction'); ?></a></li><?php } ?>

	<?php if(current_user_can('add_users')) { ?><li class="left-gray"><a href="user-new.php"><div id="icon-users" class="icon32">&nbsp;</div><?php echo __('Add User','fe-fiction'); ?></a></li><?php } ?>
	<?php if(current_user_can('edit_users')) { ?><li class="right-gray"><a href="users.php"><div id="icon-users" class="icon32">&nbsp;</div><?php echo __('Manage Users','fe-fiction'); ?></a></li><?php } ?>

	<?php if(current_user_can('read')) { ?><li class="gray"><a href="profile.php"><div id="icon-users" class="icon32">&nbsp;</div><?php echo __('My Profile','fe-fiction'); ?></a></li><?php } ?>

	<?php if(current_user_can('edit_posts')) { ?><li><a href="edit-comments.php"><div id="icon-edit-comments" class="icon32">&nbsp;</div><?php echo __('Manage Comments','fe-fiction'); ?></a></li><?php } ?>
	<?php if(current_user_can('manage_options')) { ?><li><a href="options-general.php"><div id="icon-options-general" class="icon32">&nbsp;</div><?php echo __('Settings','fe-fiction'); ?></a></li><?php } ?>
</ul>
	
<br class="clear" />