<ul id="dashboard-cms">
	<?php if(current_user_can('edit_pages')) { ?><li class="gray"><a href="post-new.php?post_type=page" class="small"><?php echo __('Add Site Page','fe-fiction'); ?></a></li><?php } ?>
	<?php if(current_user_can('edit_pages')) { ?><li class="gray"><a href="edit.php?post_type=page" class="small"><?php echo __('Edit Site Pages','fe-fiction'); ?></a></li><?php }

	if(!post_type_exists( CUSTOM_POST_TYPE )) { ?>

		<?php if(current_user_can('edit_posts')) { ?><li class="gray"><a href="post-new.php" class="small"><?php echo __('New Blog Post','fe-fiction'); ?></a></li><?php } ?>
		<?php if(current_user_can('edit_posts')) { ?><li class="gray"><a href="edit.php" class="small"><?php echo __('Edit Blog Posts','fe-fiction'); ?></a></li><?php } ?>

	<?php } else { ?>

		<?php if(current_user_can('manage_options')) { ?><li class="gray"><a href="post-new.php" class="small"><?php echo __('New Blog Post','fe-fiction'); ?></a></li><?php } ?>
		<?php if(current_user_can('manage_options')) { ?><li class="gray"><a href="edit.php" class="small"><?php echo __('Edit Blog Posts','fe-fiction'); ?></a></li><?php } ?>

		<?php if(current_user_can('edit_posts')) { ?><li class="gray"><a href="post-new.php?post_type=fiction" class="small"><?php echo __('New Fan Fiction Story','fe-fiction'); ?></a></li><?php } ?>
		<?php if(current_user_can('edit_posts')) { ?><li class="gray"><a href="edit.php?post_type=fiction" class="small"><?php echo __('Manage Fan Fiction Stories','fe-fiction'); ?></a></li><?php } ?>

	<?php } ?>

	<?php if(current_user_can('switch_themes')) { ?><li class="gray"><a href="themes.php" class="small"><?php echo __('Manage Themes','fe-fiction'); ?></a></li><?php } ?>
	<?php if(current_user_can('edit_themes') && current_theme_supports('widgets')) { ?><li class="gray"><a href="widgets.php" class="small"><?php echo __('Manage Widgets','fe-fiction'); ?></a></li><?php } ?>
	<?php if(current_user_can('edit_themes') && current_theme_supports('menus')) { ?><li class="gray"><a href="nav-menus.php" class="small"><?php echo __('Manage Menus','fe-fiction'); ?></a></li><?php } ?>
	<?php if(current_user_can('edit_themes') && current_theme_supports('custom-header')) { ?><li class="gray"><a href="theme.php?page=custom-header" class="small"><?php echo __('Theme Header','fe-fiction'); ?></a></li><?php } ?>
	<?php if(current_user_can('edit_themes') && current_theme_supports('custom-background')) { ?><li class="gray"><a href="theme.php?page=custom-background" class="small"><?php echo __('Theme Background','fe-fiction'); ?></a></li><?php } ?>

	<?php if(current_user_can('add_users')) { ?><li class="gray"><a href="user-new.php" class="small"><?php echo __('Add User','fe-fiction'); ?></a></li><?php } ?>
	<?php if(current_user_can('edit_users')) { ?><li class="gray"><a href="users.php" class="small"><?php echo __('Manage Users','fe-fiction'); ?></a></li><?php } ?>

	<?php if(current_user_can('read')) { ?><li class="gray"><a href="profile.php" class="small"><?php echo __('My Profile','fe-fiction'); ?></a></li><?php } ?>

	<?php if(current_user_can('edit_posts')) { ?><li class="gray"><a href="edit-comments.php" class="small"><?php echo __('Manage Comments','fe-fiction'); ?></a></li><?php } ?>
	<?php if(current_user_can('manage_options')) { ?><li class="gray"><a href="options-general.php" class="small"><?php echo __('Settings','fe-fiction'); ?></a></li><?php } ?>
</ul>
	
<br class="clear" />