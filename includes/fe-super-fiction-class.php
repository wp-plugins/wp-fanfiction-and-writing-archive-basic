<?php
if (!@defined('FE_SuperFiction')) {
	@define('FE_SuperFiction', true);
	include_once(FIC_PLUGIN_ABS_PATH_DIR . '/includes/fe-super-fiction-config.php');

	function initialize() {
		
		global $wpdb;

		FeFiction_I18N();

		//if(get_option('users_can_register'))
		//{
		$site_users = new WP_User_Query(array('role' => 'administrator'));
		if ($site_users->total_users > 0) {
			for ($a = 0; $a < count($site_users->results); $a++) {
				$exclude_ids[] = $site_users->results[$a]->ID;
			}
		}

		$site_users = new WP_User_Query(array('role' => 'fic_site_owner'));
		if ($site_users->total_users > 0) {
			for ($a = 0; $a < count($site_users->results); $a++) {
				$exclude_ids[] = $site_users->results[$a]->ID;
			}
		}

		$site_users_count = $wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->users} WHERE ID NOT IN (" . implode(',', $exclude_ids) . ")");
		if (get_option(FIC_OPTION_SITE_USER_LIMIT, 0) > 0 && $site_users_count >= get_option(FIC_OPTION_SITE_USER_LIMIT)) {
			update_option('users_can_register', 0);
		} else {
			update_option('users_can_register', 1);
		}

		//}

		# ------------------------------------------------------------------
		# -Activation and DeActivation hooks
		# -ensure that permalink and rewrite rules are set on a re-activation after upgrade
		# ------------------------------------------------------------------
		//register_activation_hook(__FILE__, 'FeFiction_Activate');

		/** deprecated.  no longer needed?
		register_activation_hook(__FILE__, 'FeFiction_Set_Rewrite_Rules');
		register_deactivation_hook(__FILE__, 'FeFiction_DeActivate');
		 **/

		# ------------------------------------------------------------------
		# -Prevent users other than admins / site owners from editing posts and pages
		# ------------------------------------------------------------------
		//add_action('pre_get_posts', 'FeFiction_Init_Limit_Access');

		FeFiction_Init_Remove_AdminBar();

		# ------------------------------------------------------------------
		# -Create or update the custom post type, taxonomies, custom fields
		# -Change user experience based on options set by admin
		# ------------------------------------------------------------------
		add_action('init', 'FeFiction_Init_PostType_and_Taxonomies', 0);
		add_action('admin_menu', 'FeFiction_Init_Create_Custom_Fields', 2);
		add_action('admin_menu', 'FeFiction_Admin_Menu');
		add_action('init', 'FeFiction_Init_Options', 0);
		# -Add our custom columns to the edit page in the admin
		add_filter('manage_edit-' . CUSTOM_POST_TYPE . '_columns', 'FeFiction_Add_New_Custom_Columns');
		add_action('manage_' . CUSTOM_POST_TYPE . '_posts_custom_column', 'FeFiction_Manage_New_Custom_Columns', 10, 2);

		add_filter('bulk_actions-edit-' . CUSTOM_POST_TYPE, 'FeFiction_Bulk_Actions_Options');

		# ------------------------------------------------------------------
		# Change the default 'Enter title here' text
		# ------------------------------------------------------------------
		add_filter('enter_title_here', 'FeFiction_Change_Default_Title');

		# ------------------------------------------------------------------
		# Rewrite Rules & Query Vars
		# ------------------------------------------------------------------
		add_filter('page_rewrite_rules', 'FeFiction_Set_Rewrite_Rules');
		add_filter('rewrite_rules_array', 'FeFiction_Set_Rewrite_Rules');
		add_filter('query_vars', 'FeFiction_Set_Query_Vars');
		add_filter('wp_loaded', 'FeFiction_FlushRules');

		# ------------------------------------------------------------------
		# Change the Permalink that is displayed on the fiction editor
		# ------------------------------------------------------------------
		add_filter('get_sample_permalink_html', 'FeFiction_Override_Get_Sample_Permalink_HTML', 10, 4);
		add_filter('post_updated_messages', 'FeFiction_Override_Post_Updated_Messages');

		# ------------------------------------------------------------------
		# Make sure data for our custom fields is saved
		# ------------------------------------------------------------------

		add_filter('post_updated_messages', 'codex_' . CUSTOM_POST_TYPE . '_updated_messages');

		add_action('save_post', 'FeFiction_Save_Custom_Fields');
		add_action('save_post', 'FeFiction_Save_Post_Paginate_Content');
		add_action('delete_post', 'FeFiction_Delete_Post');

		add_action('admin_print_footer_scripts', 'FeFiction_OverWrite_Post_Edit_Meta_Boxes', 20);

		# ------------------------------------------------------------------
		# Custom CSS for front-end experience on fiction pages
		# ------------------------------------------------------------------
		add_action('wp_head', 'FeFiction_Site_Display_CSS');

		# ------------------------------------------------------------------
		# Establish the shortcode for whatever page wants to use it
		# ------------------------------------------------------------------
		add_action('wp_enqueue_scripts', 'FeFiction_Site_Display_Scripts');
		add_action('admin_enqueue_scripts', 'FeFiction_Site_Display_Scripts');

		if (!is_admin()) {
            add_shortcode('wp-fanfiction-writing-archive', 'FeFiction_Site_Display');
		}
		add_filter('wp_title', 'FeFiction_Site_Fiction_Page_Title');
		add_filter('the_content', 'FeFiction_The_Content_Bad_Word_Filter');

		add_filter('template_include', 'FeFiction_Single_Story_Template');

		# ----------------------------
		# Set the user's age
		# ----------------------------
		add_action('wp_loaded', 'FeFiction_Users_Age');
		add_action('wp_loaded', 'FeFiction_Users_Age_Submit_Processor');

		# ------------------------------------------------------------------
		# Make sure redirect after story comment is made goes back to the correct page
		# ------------------------------------------------------------------
		/** deprecated.  may not need any longer?
		add_filter('comment_post_redirect', 'FeFiction_Comment_Post_Redirect');
		add_filter('comment_form_defaults', 'FeFiction_Comment_Form_Defaults');
		 **/

		add_filter('query_string', 'FeFiction_Handle_QueryString');

		add_filter('post_row_actions', 'FeFiction_Override_Item_List_Links');

		add_filter('posts_where', 'FeFiction_Filter_Admin_Fiction_List_Where');

		add_filter('views_edit-' . CUSTOM_POST_TYPE, 'FeFiction_Filter_Admin_Fiction_List_Links');

		# ------------------------------------------------------------------
		# For adding new fiction stories, we need to fudge the post page
		# so that the user can select an existing story to copy from.
		# ------------------------------------------------------------------
		add_action('admin_print_footer_scripts', 'FeFiction_Copy_From_Existing_Stories', 20);

		/** make sure the user's meta options for the editor are set to one column **/
		add_action('init', 'FeFiction_Update_User_Meta_Options');

		add_action('media_buttons_context', 'FeFiction_Remove_Media_Buttons');

		/** CUSTOMIZE THE USER PROFILE **/
		add_action('show_user_profile', 'FeFiction_Profile_Fields');
		add_action('edit_user_profile', 'FeFiction_Profile_Fields');
		add_action('show_user_profile', 'FeFiction_Check_Can_View_Profile');
		add_action('edit_user_profile', 'FeFiction_Check_Can_Edit_Profile');
		add_action('delete_user', 'FeFiction_Check_Can_Delete_Profile');
		add_action('personal_options_update', 'FeFiction_Profile_Fields_Update');
		add_action('edit_user_profile_update', 'FeFiction_Profile_Fields_Update');
		add_filter('editable_roles', 'FeFiction_Filter_Roles_List');

		/** Wordpress Users **/
		/**
		add_action('wp_head', 'noindex_users');
		add_action('wp_head', 'wpu_styles');
		add_filter('the_content', 'wpu_get_users', 1);
		 **/

		/** URL SHORTENER WITH GOOGLE **/
		//add_filter('get_shortlink', 'googl_shortlink', 9, 2);
		/** END URL SHORTENER WITH GOOGLE **/

		add_action('wp_ajax_nopriv_fiction_fandoms_browse', 'FeFiction_Fandoms_Browse_Ajax_Actions');
		add_action('wp_ajax_fiction_fandoms_browse', 'FeFiction_Fandoms_Browse_Ajax_Actions');
		add_action('wp_ajax_nopriv_fiction_authors_get', 'FeFiction_Authors_Get_Ajax_Actions');
		add_action('wp_ajax_fiction_authors_get', 'FeFiction_Authors_Get_Ajax_Actions');

		add_action('wp_ajax_nopriv_fiction_fandoms_post_edit', 'FeFiction_Fandoms_Post_Edit_Ajax_Actions');
		add_action('wp_ajax_fiction_fandoms_post_edit', 'FeFiction_Fandoms_Post_Edit_Ajax_Actions');

		add_filter('page_link', 'FeFiction_Filter_Page_Link', 10);

		//FeFiction_Override_Views_files();
	}

/* tbc */
	function FeFiction_Init_Limit_accessx($query) {
		if (is_admin() && !current_user_can('manage_fic_options')) {
			$screen = get_current_screen();
			switch ($screen->base) {
			case 'post':
				wp_redirect('/');
				exit;
				break;
			case 'edit':
				if ($screen->post_type == 'post' || $screen->post_type == 'page') {
					wp_redirect('/');
					exit;
				}
				break;
			}
		}
	}

	function FeFiction_Init_Remove_AdminBar() {
		/** remove admin bar **/
		//if(!current_user_can('only_by_fanficme'))
		//{
/*
		remove_action('init', 'wp_admin_bar_init');
		remove_filter('init', 'wp_admin_bar_init');
		foreach (array('wp_footer', 'wp_admin_bar_render') as $filter)
			add_action($filter, 'wp_admin_bar_render', 1000);
		foreach (array('wp_footer', 'wp_admin_bar_render') as $filter)
			add_action($filter, 'wp_admin_bar_render', 1000);
		remove_action('wp_head', 'wp_admin_bar_render', 1000);
		remove_filter('wp_head', 'wp_admin_bar_render', 1000);
		remove_action('wp_footer', 'wp_admin_bar_render', 1000);
		remove_filter('wp_footer', 'wp_admin_bar_render', 1000);
		remove_action('admin_head', 'wp_admin_bar_render', 1000);
		remove_filter('admin_head', 'wp_admin_bar_render', 1000);
		remove_action('admin_footer', 'wp_admin_bar_render', 1000);
		remove_filter('admin_footer', 'wp_admin_bar_render', 1000);
		remove_action('wp_before_admin_bar_render', 'wp_admin_bar_me_separator', 10);
		remove_action('wp_before_admin_bar_render', 'wp_admin_bar_my_account_menu', 20);
		remove_action('wp_before_admin_bar_render', 'wp_admin_bar_my_blogs_menu', 30);
		remove_action('wp_before_admin_bar_render', 'wp_admin_bar_blog_separator', 40);
		remove_action('wp_before_admin_bar_render', 'wp_admin_bar_bloginfo_menu', 50);
		remove_action('wp_before_admin_bar_render', 'wp_admin_bar_edit_menu', 100);
		remove_action('wp_head', 'wp_admin_bar_css');
		remove_action('wp_head', 'wp_admin_bar_dev_css');
		remove_action('wp_head', 'wp_admin_bar_rtl_css');
		remove_action('wp_head', 'wp_admin_bar_rtl_dev_css');
		remove_action('admin_head', 'wp_admin_bar_css');
		remove_action('admin_head', 'wp_admin_bar_dev_css');
		remove_action('admin_head', 'wp_admin_bar_rtl_css');
		remove_action('admin_head', 'wp_admin_bar_rtl_dev_css');
		remove_action('wp_footer', 'wp_admin_bar_js');
		remove_action('wp_footer', 'wp_admin_bar_dev_js');
		remove_action('admin_footer', 'wp_admin_bar_js');
		remove_action('admin_footer', 'wp_admin_bar_dev_js');
		remove_action('wp_ajax_adminbar_render', 'wp_admin_bar_ajax_render');
		remove_action('personal_options', ' _admin_bar_preferences');
		remove_filter('personal_options', ' _admin_bar_preferences');
		remove_action('personal_options', ' _get_admin_bar_preferences');
		remove_filter('personal_options', ' _get_admin_bar_preferences');
		remove_filter('locale', 'wp_admin_bar_lang');
*/
//		add_filter('show_admin_bar', '__return_false');
		add_filter('show_admin_bar', '__return_true');
		//}
		/** remove admin bar **/
	}

	function FeFiction_Users_Age_Submit_Processor() {
		if (isset($_REQUEST['fic_profile_age']) && wp_verify_nonce($_REQUEST['age_verify_submission'], 'Y')) {
			$GLOBALS['FIC_USERS_AGE'] = _FeFiction_Set_Users_Age($_REQUEST['fic_profile_age']);
			header('Location:' . $_SERVER['REQUEST_URI']);
			exit;
		}
	}

	function FeFiction_Users_Age() {
		if (is_user_logged_in()) {
			$users_age = get_user_meta(get_current_user_id(), "fic_profile_age", true);
			if ($users_age == '' || $users_age < 0) {
				update_user_meta(get_current_user_id(), "fic_profile_age", '17');
				$users_age = '0';
			}
		} else {
			if (!isset($_COOKIE['fic_profile_age'])) {
				$users_age = '0';
			} else {
				$users_age = $_COOKIE['fic_profile_age'];
			}
		}
		$GLOBALS['FIC_USERS_AGE'] = $users_age;
		return $users_age;
	}

	function _FeFiction_Set_Users_Age($age) {
		if (is_user_logged_in()) {
			update_user_meta(get_current_user_id(), "fic_profile_age", $age);
			setcookie('fic_profile_age', '', time() - 3600);
		} else {
			setcookie('fic_profile_age', $age, 0);

		}
		$GLOBALS['FIC_USERS_AGE'] = $age;
	}

	function FeFiction_Authors_Get_Ajax_Actions() {
		global $cur_query_string;
		switch ($_REQUEST['type']) {
		case 'dropdown':
		default:
			$search_user_args = array('show_last_update' => 0, 'child_of' => 0, 'hierarchical' => 1, 'class' => 'postform', 'depth' => 0, 'tab_index' => 0, 'show_option_all' => __('Authors', 'fe-fiction'), 'show_option_none' => '', 'hide_if_only_one_author' => 0, 'orderby' => 'display_name',
					'order' => 'ASC', 'include' => '', 'exclude' => '1', 'multi' => 0, 'show' => 'display_name', 'echo' => 1, 'selected' => $cur_query_string['story_author'], 'include_selected' => 1, 'name' => 'story_author', 'id' => 'story_author', 'class' => 'postform', 'show_count' => 0,
					'blog_id' => $GLOBALS['blog_id'], 'who' => '', 'hide_empty' => 1, 'hide_if_empty' => 0);

			wp_dropdown_users($search_user_args);
			break;
		}
		die();
	}

	function FeFiction_Fandoms_Browse_Ajax_Actions() {
		global $wpdb;

		$slug = FeFiction_Get_Page_Slug_Name();

		if (isset($_REQUEST['cat_letter'])) {
			if ($_REQUEST['cat_letter'] == 'NUM') {
				$cat_ids = $wpdb->get_results("SELECT `" . $wpdb->prefix . "terms`.`term_id` FROM `" . $wpdb->prefix . "terms` WHERE NOT SUBSTRING(`" . $wpdb->prefix . "terms`.`name`, 1, 1) REGEXP '[[:alpha:]]'", ARRAY_A);
			} else {
				$cat_ids = $wpdb->get_results("SELECT `" . $wpdb->prefix . "terms`.`term_id` FROM `" . $wpdb->prefix . "terms` WHERE `" . $wpdb->prefix . "terms`.`name` LIKE '" . mysql_real_escape_string($_REQUEST['cat_letter']) . "%'", ARRAY_A);
			}

			for ($a = 0; $a < count($cat_ids); $a++) {
				$cat_filter[] = $cat_ids[$a]['term_id'];
			}

			$search_cat_args = array('title_li' => '', 'orderby' => 'name', 'order' => 'ASC', 'show_last_update' => 0, 'show_count' => 1, 'hide_empty' => FANDOMS_DEFAULT_SEARCH_FANDOMS_LIST_HIDE_EMPTY, 'child_of' => 0, 'exclude' => '', 'include' => implode(',', $cat_filter), 'echo' => 0,
					'hierarchical' => 0, 'depth' => 0, 'tab_index' => 0, 'taxonomy' => 'story_category', 'hide_if_empty' => FANDOMS_DEFAULT_SEARCH_FANDOMS_LIST_HIDE_EMPTY,);

			$fandoms = preg_replace('@title="(View all posts filed under (.+))"@', 'title=""', wp_list_categories($search_cat_args));

			$fandoms = preg_replace("@(\r|\n)+@", "", $fandoms);
			$fandoms = trim(preg_replace("@>(\s|\t)+<@", "><", $fandoms));

			$fandoms = preg_replace("@<li class=\"cat-item cat-item-(\d+)?\"><a href=\"http://([a-zA-z0-9.-]+)/story_category/([a-zA-z0-9-]+)/\" title=\"\">@",
					"<li class=\"cat-item cat-item-$1/\"><a href=\"" . (home_url() . "/" . $slug . "/story_category/") . "$3\" onclick=\"return false;\" id=\"fandom_search_list-$1\" title=\"\">", $fandoms);

			echo '<ul>' . $fandoms . '</ul>';
			die();
		}
	}

	function FeFiction_Fandoms_Post_Edit_Actions($cat_letter = false, $pid = false, $sel_fandoms = array()) {
		global $wpdb;

		//$slug = FeFiction_Get_Page_Slug_Name();

		if ($cat_letter && $pid) {

			$post_story_categories = wp_get_object_terms($pid, 'story_category', array('fields' => 'ids'));

			$tmp = $wpdb->get_results("SELECT `wpt`.`term_id`
				FROM
				  `" . $wpdb->prefix . "terms` `wpt`
				  INNER JOIN `" . $wpdb->prefix . "term_taxonomy` `wptt` ON (`wpt`.`term_id` = `wptt`.`term_id`)
				WHERE
				  `wptt`.`taxonomy` = 'story_category' AND
				  `wptt`.`parent` = 0", ARRAY_N);

			unset($parent_categories);
			for ($a = 0; $a < count($tmp); $a++) {
				$parent_categories[] = $tmp[$a][0];
			}
			unset($tmp);

			if ($cat_letter == 'NUM') {
				$cat_ids = $wpdb->get_results("SELECT `" . $wpdb->prefix . "terms`.`term_id` FROM `" . $wpdb->prefix . "terms` WHERE NOT SUBSTRING(`" . $wpdb->prefix . "terms`.`name`, 1, 1) REGEXP '[[:alpha:]]'", ARRAY_A);
			} else {
				$cat_ids = $wpdb->get_results("SELECT `" . $wpdb->prefix . "terms`.`term_id` FROM `" . $wpdb->prefix . "terms` WHERE `" . $wpdb->prefix . "terms`.`name` LIKE '" . mysql_real_escape_string($cat_letter) . "%'", ARRAY_A);
			}

			for ($a = 0; $a < count($cat_ids); $a++) {
				$cat_filter[] = $cat_ids[$a]['term_id'];
			}

			$parent_cat_ids = $wpdb
					->get_results(
							"SELECT DISTINCT `" . $wpdb->prefix . "terms`.`term_id`,`" . $wpdb->prefix . "terms`.`name`
				 FROM `" . $wpdb->prefix . "terms`,`" . $wpdb->prefix . "term_taxonomy`
				 WHERE `" . $wpdb->prefix . "terms`.`term_id`=`" . $wpdb->prefix . "term_taxonomy`.`parent`
				  AND
					`" . $wpdb->prefix . "term_taxonomy`.`taxonomy` = 'story_category'
				  AND
					`" . $wpdb->prefix . "term_taxonomy`.`term_id` IN (" . implode(",", $cat_filter) . ")", ARRAY_A);

			for ($cli = 0; $cli < count($parent_cat_ids); $cli++) {
				$cat_filter[] = $parent_cat_ids[$cli]['term_id'];
			}
			unset($cli);
			unset($parent_cat_ids);

			$search_cat_args = array('title_li' => '', 'orderby' => 'name', 'order' => 'ASC', 'show_last_update' => 0, 'show_count' => 0, 'hide_empty' => 0, 'child_of' => 0, 'exclude' => '', 'echo' => 0, 'hierarchical' => 1, 'depth' => 0, 'tab_index' => 0, 'taxonomy' => 'story_category',
					'hide_if_empty' => 0);
			return get_categories_checkboxes($search_cat_args, $cat_filter, $sel_fandoms);
		}
	}

function get_prev_post_by_author($link="&laquo; %link", $title="%title") {
        global $wpdb, $post;
        $prev = $wpdb->get_row($wpdb->prepare("SELECT ID, post_title FROM $wpdb->posts WHERE post_type='fiction' AND post_status='publish' AND post_author='".$post->post_author."' AND post_date < '".$post->post_date."' ORDER BY post_date DESC LIMIT 1;", ""));
        if($prev) {
                $title = preg_replace('/%title/',$prev->post_title, $title);
                echo preg_replace('/%link/', '<a href="'.get_permalink($prev->ID).'" rel="prev">Previous Post by This Author</a>', $link);
        }
}                               
function ffmain(){
	
		$current_fe_fiction_page_title = stripslashes(get_the_title(get_option(FIC_OPTION_PAGE_ID)));
		echo '<a href="'.esc_url( get_permalink( get_page_by_title( $current_fe_fiction_page_title ) ) ).'">FF Main Page</a>';
		
	}
function get_next_post_by_author($link="%link &raquo;", $title="%title") {
        global $wpdb, $post;
        $next = $wpdb->get_row($wpdb->prepare("SELECT ID, post_title FROM $wpdb->posts WHERE post_type='fiction' AND post_status='publish' AND post_author='".$post->post_author."' AND post_date > '".$post->post_date."' ORDER BY post_date ASC LIMIT 1;", ""));
        if($next) {
                $title = preg_replace('/%title/',$next->post_title, $title);
                echo preg_replace('/%link/', '<a href="'.get_permalink($next->ID).'" rel="next">Next Post by This Author</a>', $link);
        }
}
	function FeFiction_Fandoms_Post_Edit_Ajax_Actions() {
		global $wpdb;

		//$slug = FeFiction_Get_Page_Slug_Name();

		if (isset($_REQUEST['cat_letter']) && isset($_REQUEST['pid'])) {

			$post_story_categories = wp_get_object_terms($_REQUEST['pid'], 'story_category', array('fields' => 'ids'));

			$tmp = $wpdb->get_results("SELECT `wpt`.`term_id`
				FROM
				  `" . $wpdb->prefix . "terms` `wpt`
				  INNER JOIN `" . $wpdb->prefix . "term_taxonomy` `wptt` ON (`wpt`.`term_id` = `wptt`.`term_id`)
				WHERE
				  `wptt`.`taxonomy` = 'story_category' AND
				  `wptt`.`parent` = 0", ARRAY_N);

			unset($parent_categories);
			for ($a = 0; $a < count($tmp); $a++) {
				$parent_categories[] = $tmp[$a][0];
			}
			unset($tmp);

			if ($_REQUEST['cat_letter'] == 'NUM') {
				$cat_ids = $wpdb->get_results("SELECT `" . $wpdb->prefix . "terms`.`term_id` FROM `" . $wpdb->prefix . "terms` WHERE NOT SUBSTRING(`" . $wpdb->prefix . "terms`.`name`, 1, 1) REGEXP '[[:alpha:]]'", ARRAY_A);
			} else {
				$cat_ids = $wpdb->get_results("SELECT `" . $wpdb->prefix . "terms`.`term_id` FROM `" . $wpdb->prefix . "terms` WHERE `" . $wpdb->prefix . "terms`.`name` LIKE '" . mysql_real_escape_string($_REQUEST['cat_letter']) . "%'", ARRAY_A);
			}

			for ($a = 0; $a < count($cat_ids); $a++) {
				$cat_filter[] = $cat_ids[$a]['term_id'];
			}

			$parent_cat_ids = $wpdb
					->get_results(
							"SELECT DISTINCT `" . $wpdb->prefix . "terms`.`term_id`,`" . $wpdb->prefix . "terms`.`name`
				 FROM `" . $wpdb->prefix . "terms`,`" . $wpdb->prefix . "term_taxonomy`
				 WHERE `" . $wpdb->prefix . "terms`.`term_id`=`" . $wpdb->prefix . "term_taxonomy`.`parent`
				  AND
					`" . $wpdb->prefix . "term_taxonomy`.`taxonomy` = 'story_category'
				  AND
					`" . $wpdb->prefix . "term_taxonomy`.`term_id` IN (" . implode(",", $cat_filter) . ")", ARRAY_A);

			for ($cli = 0; $cli < count($parent_cat_ids); $cli++) {
				$cat_filter[] = $parent_cat_ids[$cli]['term_id'];
			}
			unset($cli);
			unset($parent_cat_ids);

			$search_cat_args = array('title_li' => '', 'orderby' => 'name', 'order' => 'ASC', 'show_last_update' => 0, 'show_count' => 0, 'hide_empty' => 0, 'child_of' => 0, 'exclude' => '', 'echo' => 0, 'hierarchical' => 1, 'depth' => 0, 'tab_index' => 0, 'taxonomy' => 'story_category',
					'hide_if_empty' => 0);
			echo '<div>' . get_categories_checkboxes($search_cat_args, $cat_filter, $_REQUEST['sel_fandoms']) . '</div>';
			die();
		}
	}

	function get_categories_checkboxes($args, $cat_ids = null, $selected_cats = null) {

		$args['include'] = implode(',', $cat_ids);
		$all_categories = get_categories($args);

		$o = '<ul class="fe-class-ul">';
		foreach ($all_categories as $key => $cat) {
			if ($cat->parent == "0")
				$o .= show_category($args, $cat, $selected_cats);
		}
		return $o . '</ul>';
	}
	function show_category($args, $cat_object, $selected_cats = null) {
		$checked = "";
		if (!is_null($selected_cats) && is_array($selected_cats)) {
			$checked = (in_array($cat_object->cat_ID, $selected_cats)) ? 'checked="checked"' : "";
		}

		$ou = '<li><label><input ' . $checked . ' type="checkbox" name="tax_input[' . $args['taxonomy'] . '][]" id="in-' . $args['taxonomy'] . '-' . $cat_object->cat_ID . '" value="' . $cat_object->cat_ID . '" onClick="storyCategory(this);" /> ' . $cat_object->cat_name . '</label>';

		$args['parent'] = $cat_object->cat_ID;
		$childs = get_categories($args);

		foreach ($childs as $key => $cat) {
			$ou .= '<ul class="fe-class-ul">' . show_category($args, $cat, $selected_cats) . '</ul>';
		}
		$ou .= '</li>';
		return $ou;
	}

	function googl_shortlink($url, $post_id) {
		global $post, $wp;

		if (!$post_id && $post)
			$post_id = $post->ID;

		if ($post->post_status != 'publish')
			return "";

		$shortlink = get_post_meta($post_id, '_googl_shortlink', true);

		if ($shortlink)
			return $shortlink;

		$slug = FeFiction_Get_Page_Slug_Name();

		$permalink = get_permalink($post_id);
		$permalink = str_replace('/' . CUSTOM_POST_TYPE . '/', '/fan_fiction_stories/', $permalink);

		$http = new WP_Http();
		$headers = array('Content-Type' => 'application/json');
		$result = $http->request('https://www.googleapis.com/urlshortener/v1/url', array('method' => 'POST', 'body' => '{"longUrl": "' . $permalink . '"}', 'headers' => $headers));

		if (!isset($result->errors['http_failure'])) {
            if(!is_wp_error($result)) $result = json_decode($result['body']);
			$shortlink = $result->id;
		} else {
			$shortlink = false;
		}
		if ($shortlink) {
			add_post_meta($post_id, '_googl_shortlink', $shortlink, true);
			return $shortlink;
		} else {
			return $url;
		}
	}

	# ------------------------------------------------------------------
	# FeFiction_Handle_QueryString
	#
	# since we are using a page to deliver fiction content, we need to make
	# sure that the page itself loads and that we store the originating
	# query string for use in our query for actual content.
	# ------------------------------------------------------------------
	function FeFiction_Handle_QueryString($query_string) {
		global $wp;

		$GLOBALS['FIC_CUR_QUERY_STRING'] = $query_string;

		$slug = FeFiction_Get_Page_Slug_Name();

		if (isset($wp->query_vars) && isset($wp->query_vars['pagename']) && $wp->query_vars['pagename'] == $slug) {
			if (!is_admin()) {
				return 'pagename=' . $slug;
			}
		}
		return $query_string;
	}

	function FeFiction_Override_Item_List_Links($actions) {
		global $wp;
		$slug = FeFiction_Get_Page_Slug_Name();

		if (isset($wp->query_vars) && $wp->query_vars['post_type'] == CUSTOM_POST_TYPE && is_admin()) {
			$actions['view'] = str_replace(home_url() . '/' . CUSTOM_POST_TYPE, home_url() . '/' . $slug . '/story', $actions['view']);
		}
		return $actions;
	}

	function FeFiction_Filter_Page_Link($url) {
		if (stristr($url, '/' . CUSTOM_POST_TYPE . '/')) {
			$fic_page_base_slug = FeFiction_Get_Page_Slug_Name();
			return str_replace('/' . CUSTOM_POST_TYPE . '/', '/fan_fiction_stories/', $url);
		}
		return $url;
	}

	function FeFiction_Filter_Admin_Fiction_List_Links($links) {
		$links['mine'] = str_replace('Mine ', 'All ', $links['mine']);
		unset($links['all']);
		unset($links['publish']);

		return $links;
	}

	function FeFiction_Filter_Admin_Fiction_List_Where($where) {
		global $wp, $wpdb;
		if (!current_user_can('manage_fic_options') && (stristr($_SERVER['REQUEST_URI'], '/wp-admin/edit.php') && stristr($_SERVER['REQUEST_URI'], 'post_type=' . CUSTOM_POST_TYPE))) {
			$where .= " AND {$wpdb->posts}.post_author='" . get_current_user_id() . "'";
		}

		return $where;
	}
    function remove_section($filename, $marker) {
        $markerdata = explode("\n", implode( '', file( $filename))); //parse each line of file into array

        $f = fopen($filename, 'w'); //open the file
        if ($markerdata) { //as long as there are lines in the file
            $state = true;
            foreach ($markerdata as $n => $markerline) { //for each line in the file
                if (strpos($markerline, '# BEGIN ' . $marker) !== false) { //if we're at the beginning of the section
                    $state = false;
                }
                if ($state == true) { //as long as we're not in the section keep writing
                    if ($n + 1 < count($markerdata)) //make sure to add newline to appropriate lines
                        fwrite($f, "{$markerline}\n");
                    else
                        fwrite($f, "{$markerline}");
                }
                if (strpos($markerline, '# END ' . $marker) !== false) { //see if we're at the end of the section
                    $state = true;
                }
            }
        }
        return true;
    }
	//This function is called on Plugin Activation
	function FeFiction_Activate() {
		global $wpdb;

        // Get path to main .htaccess for WordPress
        $htaccess = get_home_path().".htaccess";

        $lines = array();
        $lines[] = "php_flag output_buffering on";

        remove_section($htaccess, "Writing Archive");


		if (trim(get_option('permalink_structure')) == '')
			exit('<p class="fe-class-act-p">The Fanfic.me Fiction plugin currently only works if permalinks are configured and enabled (anything other than "Default". Please enable permalinks and try again.)</p>');

		$sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix
				. "fic_poststruct` (
			`id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'pid',
			`parent_id` int(10) unsigned NOT NULL COMMENT 'parent post id',
			`child_id` int(11) unsigned NOT NULL COMMENT 'post children ids',
			`type` varchar(15) NOT NULL,
			PRIMARY KEY (`id`),
			KEY `child_id` (`child_id`),
			KEY `parent_id` (`parent_id`),
			KEY `type` (`type`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Fanfic.me Fic Post Structure Table'";

		$wpdb->query($sql);

		$sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "story_favorites` (
					post_id bigint(20) NOT NULL,
					user_id bigint(20) NOT NULL,
					KEY user_id (user_id),
					KEY post_id (post_id)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Fanfic.me Story Favorites Table'";
		$wpdb->query($sql);

		$sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix
				. "fic_bad_words` (
				  `ID` int(11) NOT NULL auto_increment,
				  `word` varchar(255) collate utf8_unicode_ci NOT NULL,
				  `replacement` varchar(255) collate utf8_unicode_ci NOT NULL,
				  PRIMARY KEY  (`ID`),
				  UNIQUE KEY `word` (`word`),
				  KEY `replacement` (`replacement`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Fanfic.me bad Word Filter Table'";
		$wpdb->query($sql);

		$sql = str_replace('{db_prefix}', $wpdb->prefix, @implode(@file(FIC_PLUGIN_ABS_PATH_DIR . '/includes/bad_words.sql')));

		$wpdb->query($sql);

		FeFiction_Set_Fiction_Page_Stylesheet();

		/** create the site owner (sub admin) role **/
		$existing_role_check = get_role($GLOBALS['FIC_SUB_ADMIN_ROLE']['slug']);
		if ($existing_role_check === null) {
			add_role($GLOBALS['FIC_SUB_ADMIN_ROLE']['slug'], $GLOBALS['FIC_SUB_ADMIN_ROLE']['name'], $GLOBALS['FIC_SUB_ADMIN_ROLE']['capabilities']);
		} else {
			//remove_role($GLOBALS['FIC_SUB_ADMIN_ROLE']['slug']);
			//add_role($GLOBALS['FIC_SUB_ADMIN_ROLE']['slug'], $GLOBALS['FIC_SUB_ADMIN_ROLE']['name'], $GLOBALS['FIC_SUB_ADMIN_ROLE']['capabilities']);
		}

		// Set 'manage_ratings' Capabilities To Administrator
		$role = get_role('administrator');
		if (!$role->has_cap('publish_fiction')) {
			$role->add_cap('publish_fiction');
		}
		if (!$role->has_cap('edit_fiction')) {
			$role->add_cap('edit_fiction');
		}
		if (!$role->has_cap('delete_fiction')) {
			$role->add_cap('delete_fiction');
		}
		if (!$role->has_cap('delete_fictions')) {
			$role->add_cap('delete_fictions');
		}
		if (!$role->has_cap('delete_published_fiction')) {
			$role->add_cap('delete_published_fiction');
		}
		if (!$role->has_cap('delete_others_fiction')) {
			$role->add_cap('delete_others_fiction');
		}
		if (!$role->has_cap('delete_private_fiction')) {
			$role->add_cap('delete_private_fiction');
		}
		if (!$role->has_cap('edit_others_fiction')) {
			$role->add_cap('edit_others_fiction');
		}
		if (!$role->has_cap('edit_private_fiction')) {
			$role->add_cap('edit_private_fiction');
		}
		if (!$role->has_cap('edit_published_fiction')) {
			$role->add_cap('edit_published_fiction');
		}
		if (!$role->has_cap('edit_fictions')) {
			$role->add_cap('edit_fictions');
		}
		if (!$role->has_cap('read_private_fiction')) {
			$role->add_cap('read_private_fiction');
		}
		
		
		
		
		if (!$role->has_cap('manage_books')) {
			$role->add_cap('manage_books');
		}
		if (!$role->has_cap('edit_books')) {
			$role->add_cap('edit_books');
		}
		if (!$role->has_cap('delete_books')) {
			$role->add_cap('delete_books');
		}
		if (!$role->has_cap('manage_fic_options')) {
			$role->add_cap('manage_fic_options');
		}
		/** people can manage comments for their stories **/
		if (!$role->has_cap('manage_my_stories_comments')) {
			$role->add_cap('manage_my_stories_comments');
		}
		if (!$role->has_cap('only_by_fanficme')) {
			$role->add_cap('only_by_fanficme');
		}
		if (!$role->has_cap('manage_my_favorites')) {
			$role->add_cap('manage_my_favorites');
		}

		$role = get_role($GLOBALS['FIC_SUB_ADMIN_ROLE']['slug']);
		if (!$role->has_cap('manage_fic_options')) {
			$role->add_cap('manage_fic_options');
		}
		if (!$role->has_cap('only_by_fanficme')) {
			$role->add_cap('only_by_fanficme');
		}
		if (!$role->has_cap('manage_my_stories_comments')) {
			$role->add_cap('manage_my_stories_comments');
		}
		if (!$role->has_cap('manage_my_favorites')) {
			$role->add_cap('manage_my_favorites');
		}

		$role = get_role('contributor');
		/** people can manage comments for their stories **/
		if (!$role->has_cap('delete_fictions')) {
			$role->add_cap('delete_fictions');
		}
		if (!$role->has_cap('edit_posts')) {
			$role->add_cap('edit_posts');
		}
		if (!$role->has_cap('publish_fiction')) {
			$role->add_cap('publish_fiction');
		}
		if (!$role->has_cap('edit_fictions')) {
			$role->add_cap('edit_fictions');
		}
		if (!$role->has_cap('edit_private_fiction')) {
			$role->add_cap('edit_private_fiction');
		}
		if (!$role->has_cap('edit_others_fiction')) {
			$role->add_cap('edit_others_fiction');
		}
		
		if (!$role->has_cap('delete_fiction')) {
			$role->add_cap('delete_fiction');
		}
		if (!$role->has_cap('delete_published_fiction')) {
			$role->add_cap('delete_published_fiction');
		}
		if ($role->has_cap('manage_fic_options')) {
			$role->remove_cap('manage_fic_options');
		}
		
		if (!$role->has_cap('edit_others_fiction')) {
			$role->add_cap('edit_others_fiction');
		}
		if (!$role->has_cap('edit_private_fiction')) {
			$role->add_cap('edit_private_fiction');
		}
		if (!$role->has_cap('edit_published_fiction')) {
			$role->add_cap('edit_published_fiction');
		}
		if (!$role->has_cap('edit_fictions')) {
			$role->add_cap('edit_fictions');
		}
		if ($role->has_cap('manage_books')) {
			$role->remove_cap('manage_books');
		}
		if ($role->has_cap('edit_books')) {
			$role->remove_cap('edit_books');
		}
		if ($role->has_cap('delete_books')) {
			$role->remove_cap('delete_books');
		}
		
		if (!$role->has_cap('can_save_content')) {
			$role->add_cap('can_save_content');
		}
		if ($role->has_cap('only_by_fanficme')) {
			$role->remove_cap('only_by_fanficme');
		}
		if (!$role->has_cap('manage_my_stories_comments')) {
			$role->add_cap('manage_my_stories_comments');
		}
		if (!$role->has_cap('manage_my_favorites')) {
			$role->add_cap('manage_my_favorites');
		}

		$role = get_role('author');
		/** people can manage comments for their stories **/
		if (!$role->has_cap('delete_fictions')) {
			$role->add_cap('delete_fictions');
		}
		if (!$role->has_cap('publish_fiction')) {
			$role->add_cap('publish_fiction');
		}
		if (!$role->has_cap('edit_fictions')) {
			$role->add_cap('edit_fictions');
		}
		if (!$role->has_cap('edit_private_fiction')) {
			$role->add_cap('edit_private_fiction');
		}
		if (!$role->has_cap('edit_others_fiction')) {
			$role->add_cap('edit_others_fiction');
		}
		
		if (!$role->has_cap('delete_fiction')) {
			$role->add_cap('delete_fiction');
		}
		if (!$role->has_cap('delete_published_fiction')) {
			$role->add_cap('delete_published_fiction');
		}
		if ($role->has_cap('manage_fic_options')) {
			$role->remove_cap('manage_fic_options');
		}
		
		if (!$role->has_cap('edit_others_fiction')) {
			$role->add_cap('edit_others_fiction');
		}
		if (!$role->has_cap('edit_private_fiction')) {
			$role->add_cap('edit_private_fiction');
		}
		if (!$role->has_cap('edit_published_fiction')) {
			$role->add_cap('edit_published_fiction');
		}
		if (!$role->has_cap('edit_fictions')) {
			$role->add_cap('edit_fictions');
		}
		if ($role->has_cap('manage_books')) {
			$role->remove_cap('manage_books');
		}
		if ($role->has_cap('edit_books')) {
			$role->remove_cap('edit_books');
		}
		if ($role->has_cap('delete_books')) {
			$role->remove_cap('delete_books');
		}
		
		if (!$role->has_cap('can_save_content')) {
			$role->add_cap('can_save_content');
		}
		if ($role->has_cap('only_by_fanficme')) {
			$role->remove_cap('only_by_fanficme');
		}
		if (!$role->has_cap('manage_my_stories_comments')) {
			$role->add_cap('manage_my_stories_comments');
		}
		if (!$role->has_cap('manage_my_favorites')) {
			$role->add_cap('manage_my_favorites');
		}
		if (!$role->has_cap('edit_posts')) {
			$role->add_cap('edit_posts');
		}

		$role = get_role('editor');
		if (!$role->has_cap('publish_fiction')) {
			$role->add_cap('publish_fiction');
		}
		if (!$role->has_cap('edit_fiction')) {
			$role->add_cap('edit_fiction');
		}
		if (!$role->has_cap('delete_fiction')) {
			$role->add_cap('delete_fiction');
		}
		if (!$role->has_cap('delete_fictions')) {
			$role->add_cap('delete_fictions');
		}
		if (!$role->has_cap('delete_published_fiction')) {
			$role->add_cap('delete_published_fiction');
		}
		if (!$role->has_cap('delete_others_fiction')) {
			$role->add_cap('delete_others_fiction');
		}
		if (!$role->has_cap('delete_private_fiction')) {
			$role->add_cap('delete_private_fiction');
		}
		if (!$role->has_cap('edit_others_fiction')) {
			$role->add_cap('edit_others_fiction');
		}
		if (!$role->has_cap('edit_private_fiction')) {
			$role->add_cap('edit_private_fiction');
		}
		if (!$role->has_cap('edit_published_fiction')) {
			$role->add_cap('edit_published_fiction');
		}
		if (!$role->has_cap('edit_fictions')) {
			$role->add_cap('edit_fictions');
		}
		if (!$role->has_cap('read_private_fiction')) {
			$role->add_cap('read_private_fiction');
		}
		
		
		
		
		if (!$role->has_cap('manage_books')) {
			$role->add_cap('manage_books');
		}
		if (!$role->has_cap('edit_books')) {
			$role->add_cap('edit_books');
		}
		if (!$role->has_cap('delete_books')) {
			$role->add_cap('delete_books');
		}
		if (!$role->has_cap('manage_fic_options')) {
			$role->add_cap('manage_fic_options');
		}
		/** people can manage comments for their stories **/
		if (!$role->has_cap('manage_my_stories_comments')) {
			$role->add_cap('manage_my_stories_comments');
		}
		if (!$role->has_cap('only_by_fanficme')) {
			$role->add_cap('only_by_fanficme');
		}
		if (!$role->has_cap('manage_my_favorites')) {
			$role->add_cap('manage_my_favorites');
		}
		if (!$role->has_cap('edit_posts')) {
			$role->add_cap('edit_posts');
		}
		$role = get_role('subscriber');
		/** people can manage comments for their stories **/   
		
		if (!$role->has_cap('delete_fictions')) {
			$role->add_cap('delete_fictions');
		}
		if (!$role->has_cap('publish_fiction')) {
			$role->add_cap('publish_fiction');
		}
		if (!$role->has_cap('edit_fictions')) {
			$role->add_cap('edit_fictions');
		}
		if (!$role->has_cap('edit_private_fiction')) {
			$role->add_cap('edit_private_fiction');
		}
		if (!$role->has_cap('edit_others_fiction')) {
			$role->add_cap('edit_others_fiction');
		}
		
		if (!$role->has_cap('delete_fiction')) {
			$role->add_cap('delete_fiction');
		}
		if (!$role->has_cap('delete_published_fiction')) {
			$role->add_cap('delete_published_fiction');
		}
		
		if (!$role->has_cap('edit_others_fiction')) {
			$role->add_cap('edit_others_fiction');
		}
		if (!$role->has_cap('edit_private_fiction')) {
			$role->add_cap('edit_private_fiction');
		}
		if (!$role->has_cap('edit_published_fiction')) {
			$role->add_cap('edit_published_fiction');
		}
		if (!$role->has_cap('edit_fictions')) {
			$role->add_cap('edit_fictions');
		}
		if ($role->has_cap('manage_books')) {
			$role->remove_cap('manage_books');
		}
		if ($role->has_cap('edit_books')) {
			$role->remove_cap('edit_books');
		}
        if ($role->has_cap('delete_books')) {
            $role->remove_cap('delete_books');
        }

        if ($role->has_cap('edit_others_fiction')) {
            $role->remove_cap('edit_others_fiction');
        }

        if (!$role->has_cap('can_save_content')) {
			$role->add_cap('can_save_content');
		}
		if (!$role->has_cap('edit_posts')) {
			$role->add_cap('edit_posts');
		}
		/*if ($role->has_cap('only_by_fanficme')) {
			$role->remove_cap('only_by_fanficme');
		}*/
		if (!$role->has_cap('manage_my_stories_comments')) {
			$role->add_cap('manage_my_stories_comments');
		}
		if (!$role->has_cap('manage_my_favorites')) {
			$role->add_cap('manage_my_favorites');
		}

		fanfic_check_fiction_views_install();

	}
/*
	function FeFiction_Override_Views_files() {
		$dir = get_template_directory() . '/fiction-views/';
		$files = array('source' => array(FIC_PLUGIN_ABS_PATH_DIR . '/views/fe-fiction-site-browse-single.php', FIC_PLUGIN_ABS_PATH_DIR . '/views/fe-fiction-site-browse.php', FIC_PLUGIN_ABS_PATH_DIR . '/views/fe-fiction-site-browse-multiple.php'),
				'target' => array(get_template_directory() . '/fiction-views/fe-fiction-site-browse-single.php', get_template_directory() . '/fiction-views/fe-fiction-site-browse.php', get_template_directory() . '/fiction-views/fe-fiction-site-browse-multiple.php'));

		if (!file_exists($dir)) {
			mkdir($dir, 0777);
		}
		for ($a = 0; $a < count($files['source']); $a++) {
			if (!file_exists($files['target'][$a])) {
				copy($files['source'][$a], $files['target'][$a]);
			}
		}
	}
*/
	# ----------------------------------------------------------------------
	# FeFiction_Set_Fiction_Page_Stylesheet
	#
	# set the stylesheet to be used for the fiction browsing/reading
	# ----------------------------------------------------------------------
	function FeFiction_Set_Fiction_Page_Stylesheet($stylesheet = '', $return_css = false) {
		if ($stylesheet == 'DEFAULT' || $stylesheet == '') {
			ob_start();
			include(FIC_PLUGIN_ABS_PATH_DIR . '/views/css/style.css');
			include(FIC_PLUGIN_ABS_PATH_DIR . '/views/css/custom_fanfic.css');
			$stylesheet = ob_get_contents();
			ob_end_clean();
		} else {
			$existing_stylesheet = get_option(FIC_OPTION_FICTION_PAGE_STYLESHEET);
			if ($stylesheet == '' && $existing_stylesheet && strlen($existing_stylesheet) > 1) {
				$stylesheet = $existing_stylesheet;
			}
		}

		update_option(FIC_OPTION_FICTION_PAGE_STYLESHEET, $stylesheet);

		if ($return_css) {
			return $stylesheet;
		}
	}

	# ----------------------------------------------------------------------
	# FeFiction_Get_Fiction_Page_Stylesheet
	#
	# get the stylesheet to be used for the fiction browsing/reading
	# ----------------------------------------------------------------------
	function FeFiction_Get_Fiction_Page_Stylesheet() {
		$existing_stylesheet = get_option(FIC_OPTION_FICTION_PAGE_STYLESHEET);

		if (!$existing_stylesheet) {
			return FeFiction_Set_Fiction_Page_Stylesheet('DEFAULT', true);
		} else {
			return stripslashes(get_option(FIC_OPTION_FICTION_PAGE_STYLESHEET));
		}
	}

	//This function is called on Plugin De-Activation
	function FeFiction_DeActivate() {
		update_option(FIC_PLUGIN_OPTION_NAME, '0');
	}
	# ------------------------------------------------------------------
	# FeFiction_Set_Rewrite_Rules()
	# Setup the forum rewrite rules
	# ------------------------------------------------------------------
	function FeFiction_Set_Rewrite_Rules($rules) {
		global $wp_rewrite;

		$slug = FeFiction_Get_Page_Slug_Name();

		if ($wp_rewrite->using_index_permalinks()) {
			$slugmatch = 'index.php/' . $slug;
		} else {
			$slugmatch = $slug;
		}

		$fic_rules[$slugmatch . '/?$'] = 'index.php?pagename=' . $slug;

		$fic_rules[$slugmatch . '/browse/?$'] = 'index.php?pagename=' . $slug;

		$fic_rules[$slugmatch . '/fan_fiction_stories/([^/]+)/?$'] = 'index.php?pagename=' . $slug . '&story_title=$matches[1]';
		$fic_rules[$slugmatch . '/fan_fiction_stories/([^/]+)/page-([0-9]+)/?$'] = 'index.php?pagename=' . $slug . '&story_title=$matches[1]&page=$matches[2]';
		$fic_rules[$slugmatch . '/fan_fiction_stories/([^/]+)/comment-page-([0-9]{1,})/?$'] = 'index.php?pagename=' . $slug . '&story_title=$matches[1]&cpage=$matches[2]';

		$fic_rules[$slugmatch . '/genre/?$'] = 'index.php?pagename=' . $slug;
		$fic_rules[$slugmatch . '/rating/?$'] = 'index.php?pagename=' . $slug;
		$fic_rules[$slugmatch . '/pairings/?$'] = 'index.php?pagename=' . $slug;
		$fic_rules[$slugmatch . '/date/?$'] = 'index.php?pagename=' . $slug;

		$fic_rules[$slugmatch . '/genre/([^/]+)/?$'] = 'index.php?pagename=' . $slug . '&genre=$matches[1]';
		$fic_rules[$slugmatch . '/rating/([^/]+)/?$'] = 'index.php?pagename=' . $slug . '&rating=$matches[1]';
		$fic_rules[$slugmatch . '/pairings/([^/]+)/?$'] = 'index.php?pagename=' . $slug . '&pairings=$matches[1]';
		$fic_rules[$slugmatch . '/date/([^/]+)/?$'] = 'index.php?pagename=' . $slug . '&date=$matches[1]';

		$fic_rules[$slugmatch . '/genre/([^/]+)/page-([0-9]+)/?$'] = 'index.php?pagename=' . $slug . '&genre=$matches[1]&page=$matches[2]';
		$fic_rules[$slugmatch . '/rating/([^/]+)/page-([0-9]+)/?$'] = 'index.php?pagename=' . $slug . '&rating=$matches[1]&page=$matches[2]';
		$fic_rules[$slugmatch . '/pairings/([^/]+)/page-([0-9]+)/?$'] = 'index.php?pagename=' . $slug . '&pairings=$matches[1]&page=$matches[2]';
		$fic_rules[$slugmatch . '/date/([^/]+)/page-([0-9]+)/?$'] = 'index.php?pagename=' . $slug . '&date=$matches[1]&page=$matches[2]';


        $fic_rules['fanfiction/fandom/([^/]+)/?$'] = 'index.php?pagename=' . $slug . '&story_category=$matches[1]';
		$rules = array_merge($fic_rules, $rules);

		return $rules;
	}

	function FeFiction_FlushRules() {
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}

	# ------------------------------------------------------------------
	# FeFiction_Set_Query_Vars()
	# Setup the forum query variables
	# ------------------------------------------------------------------

	function FeFiction_Set_Query_Vars($vars) {
		# forums and topics
		$vars[] = 'story_title';
		$vars[] = 'genre';
		$vars[] = 'rating';
		$vars[] = 'pairings';
        $vars[] = 'story_category';
        $vars[] = 'fandom';
		$vars[] = 'cat_letter';
		$vars[] = 'cat_name';
		$vars[] = 'date';
		$vars[] = 'fe-super-fiction-story-selection';
		$vars[] = 'story_author';
		$vars[] = 'per_page';

		return $vars;
	}

	function FeFiction_Override_Get_Sample_Permalink_HTML($html, $postid, $new_title, $new_slug) {
		global $wpdb;

		$querystr = 'SELECT post_type FROM ' . $wpdb->posts . ' WHERE ID = ' . $postid;
		$post = $wpdb->get_results($querystr, OBJECT);
		list($permalink, $post_name) = get_sample_permalink($postid, $new_title, $new_slug);

		if (isset($post[0]->post_type) && $post[0]->post_type == CUSTOM_POST_TYPE) {
			$cur_page_slug = FeFiction_Get_Page_Slug_Name($postid);
			$fic_page_base_slug = FeFiction_Get_Page_Slug_Name();

			$permalink = home_url() . '/fan_fiction_stories/%postname%';

			if (function_exists('mb_strlen')) {
				if (mb_strlen($post_name) > 30) {
					$post_name_abridged = mb_substr($post_name, 0, 14) . '&hellip;' . mb_substr($post_name, -14);
				} else {
					$post_name_abridged = $post_name;
				}
			} else {
				if (strlen($post_name) > 30) {
					$post_name_abridged = substr($post_name, 0, 14) . '&hellip;' . substr($post_name, -14);
				} else {
					$post_name_abridged = $post_name;
				}
			}

			$post_name_html = '<span id="editable-post-name" title="' . $new_title . '">' . $post_name_abridged . '</span>';
			$display_link = str_replace(array('%pagename%', '%postname%'), $post_name_html, $permalink);
			$view_link = str_replace(array('%pagename%', '%postname%'), $post_name, $permalink);
			$return = '<strong>' . __('Permalink:') . "</strong>\n";
			$return .= '<span id="sample-permalink">' . $display_link . "</span>\n";
			$return .= '&lrm;'; // Fix bi-directional text display defect in RTL languages.
			$return .= '<span id="edit-slug-buttons"><a href="#post_name" class="edit-slug button hide-if-no-js" onclick="editPermalink(' . $postid . '); return false;">' . __('Edit') . "</a></span>\n";
			$return .= '<span id="editable-post-name-full">' . $post_name . "</span>\n";

			return $return;
		} else {
			return $html;
		}
	}

	function FeFiction_Override_Post_Updated_Messages($messages) {
		foreach ($messages as $doc_type => $message_array) {
			foreach ($message_array as $tid => $message) {
				$fic_page_base_slug = FeFiction_Get_Page_Slug_Name();
				$messages[$doc_type][$tid] = str_replace('/' . CUSTOM_POST_TYPE . '/', '/fan_fiction_stories/', $message);
			}
		}
		return $messages;
	}

	# ------------------------------------------------------------------
	# FeFiction_Get_Page_ID()
	#
	# gets the page id for the fiction page
	#
	# ------------------------------------------------------------------

	function FeFiction_Get_Page_ID() {
		return get_option(FIC_OPTION_PAGE_ID, '0');
	}

	# ------------------------------------------------------------------
	# FeFiction_Get_Page_Slug_Name()
	#
	# gets the the slug name based on the fic page id
	#
	# ------------------------------------------------------------------
	function FeFiction_Get_Page_Slug_Name($page_id = '') {
		if ($page_id == '') {
			$page_id = FeFiction_Get_Page_ID();
		}
		if ($page_id != '') {
			$permalink = get_permalink($page_id);
			$return = str_replace(home_url(), '', $permalink);
			if (stristr($return, '/' . CUSTOM_POST_TYPE . '/')) {
				$return = str_replace('/' . CUSTOM_POST_TYPE . '/', '/', $return);
			}
			$return = str_replace('/', '', $return);
			return $return;
		} else {
			return false;
		}
	}

	# ------------------------------------------------------------------
	# FeFiction_I18N()
	# Setup the forum localization
	# ------------------------------------------------------------------

	function FeFiction_I18N() {
		# i18n support
		load_plugin_textdomain('fe-fiction', FIC_PLUGIN_DIR . '/i18n', 'wp-fanfiction-and-writing-archive-basic/i18n');
		return;
	}

	function FeFiction_Init_PostType_and_Taxonomies() {
		if (!post_type_exists(CUSTOM_POST_TYPE)) {
			register_post_type(CUSTOM_POST_TYPE, $GLOBALS['FIC_CUSTOM_POST_TYPE_ARGS']);
		}

		foreach ($GLOBALS['FIC_TAXONOMIES'] as $taxonomy_name => $taxonomy_info) {
			register_taxonomy($taxonomy_name, $taxonomy_info['object_type'], $taxonomy_info['args']);
		}

		if (get_option(FIC_PLUGIN_OPTION_NAME) != '1') {
			FeFiction_Generate_Terms();
			update_option(FIC_PLUGIN_OPTION_NAME, '1');
		} else {
			add_option(FIC_PLUGIN_OPTION_NAME, '1', '', 'yes');
		}

		//flush_rewrite_rules(false);
	}

	function FeFiction_Update_User_Meta_Options() {
		update_user_meta(get_current_user_id(), 'meta-box-order_' . CUSTOM_POST_TYPE,
				unserialize('a:3:{s:4:"side";s:0:"";s:6:"normal";s:139:"fe-fic-custom-fields,story_categorydiv,ratingdiv,genrediv,pairingsdiv,commentstatusdiv,slugdiv,authordiv,commentsdiv,revisionsdiv,submitdiv";s:8:"advanced";s:0:"";}'));

		if (get_user_meta(get_current_user_id(), FIC_OPTION_FICTION_STORY_SCORING, true) == '') {
			update_user_meta(get_current_user_id(), FIC_OPTION_FICTION_STORY_SCORING, '1');
		}
	}

	function FeFiction_Init_Options() {

		//FeFiction_Set_Fiction_Page_Stylesheet();

		/** add settings link to the plugins page for our plugin * */
		add_filter('plugin_action_links', 'FeFiction_Manage_Plugins_Links', 10, 2);
		add_action('deactivate_plugin', 'FeFiction_Manage_Plugins_Deactivation');

		//add_filter('favorite_actions', 'no_favorites');
		add_action('admin_print_footer_scripts', 'FeFiction_No_Favorites');

		/** START OVERRIDE THE DASHBOARD WITH A NICE CLEAN OPTIONS PAGE * */
		if (get_option(FIC_OPTION_CUSTOM_DASHBOARD) == '1') {
			$user = wp_get_current_user();
			update_user_meta($user->ID, "screen_layout_dashboard", '1', true);

			add_action('admin_menu', 'FeFiction_Disable_Default_Dashboard_Widgets');
			add_action('admin_head', 'FeFiction_Admin_Register_Head_New_CMS_Dashboard');
			if (stristr('/wp-admin/index.php', $_SERVER['REQUEST_URI'])) {
				add_action('wp_dashboard_setup', 'new_cms_dashboard_widgets', 1);
			} else {
				add_action('admin_notices', 'new_cms_dashboard_widgets_pages', 10);
			}
		}
		/** END OVERRIDE THE DASHBOARD WITH A NICE CLEAN OPTIONS PAGE * */
		/** START REMOVE ADMIN MENUS * */
		if (get_option(FIC_OPTION_HIDE_ADMIN_MENUS) == '1' && !current_user_can('administrator') && !current_user_can($GLOBALS['FIC_SUB_ADMIN_ROLE']['slug'])) {
			add_action('admin_head', 'FeFiction_Remove_Admin_Menus');
		} else {
			if (!current_user_can('administrator') && !current_user_can($GLOBALS['FIC_SUB_ADMIN_ROLE']['slug'])) {
				add_action('admin_menu', 'FeFiction_Restrict_Admin_Menus');
			}
		}
		/** END REMOVE ADMIN MENUS * */
		/** START UPDATING DEFAULT ROLE FOR USERS * */
		if (get_option(FIC_OPTION_ENABLE_DEFAULT_ROLE) != '') {
			if (is_multisite()) {
				update_site_option('default_user_role', FIC_DEFAULT_ROLE);
			} else {
				update_option('default_role', FIC_DEFAULT_ROLE);
			}
		}
		/** END UPDATING DEFAULT ROLE FOR USERS * */

		/** START UPDATING FICTION STORY SCORING **/
		/** END UPDATING FICTION STORY SCORING **/
	}

	function FeFiction_Change_Default_Title($title) {
		$screen = get_current_screen();
		if ($screen->post_type == CUSTOM_POST_TYPE) {
			$title = __('Enter Book or Story Title', 'fe-fiction');
		}
		return $title;
	}

	function FeFiction_Add_New_Custom_Columns($columns) {
		$new_columns['cb'] = $columns['cb'];
		$new_columns['id'] = '<a href="?post_type=fiction&amp;orderby=id&amp;order=' . ($_REQUEST['order'] == 'asc' ? 'desc' : 'asc') . '"><span>' . __('ID', 'fe-fiction') . '</span><span class="sorting-indicator"></span></a>';
		$new_columns['title'] = __('Book / Story', 'fe-fiction'); /** $columns['title']; **/
		$new_columns['chapter'] = '<a href="?post_type=fiction&amp;orderby=chapter&amp;order=' . ($_REQUEST['order'] == 'asc' ? 'desc' : 'asc') . '"><span>' . __('Chapter', 'fe-fiction') . '</span><span class="sorting-indicator"></span></a>';
		$new_columns['author'] = $columns['author'];

		foreach ($GLOBALS['FIC_TAXONOMIES'] as $taxonomy => $taxonomy_data) {
			//$new_columns[$taxonomy] = $taxonomy_data['args']['labels']['name'];
			$new_columns[$taxonomy] = '<a href="?post_type=fiction&amp;orderby=' . $taxonomy . '&amp;order=' . ($_REQUEST['order'] == 'asc' ? 'desc' : 'asc') . '"><span>' . $taxonomy_data['args']['labels']['name'] . '</span><span class="sorting-indicator"></span></a>';
		}

		$new_columns['comments'] = $columns['comments'];
		$new_columns['date'] = $columns['date'];

		return $new_columns;
	}

	function FeFiction_Manage_New_Custom_Columns($column_name, $post_id) {
		if (array_key_exists($column_name, $GLOBALS['FIC_TAXONOMIES'])) {
			$values = get_the_terms($post_id, $column_name);
			if ($values) {
				foreach ($values as $term_id => $term_data) {
					$output[] = $term_data->name;
				}
				echo implode(", ", $output);
			} else {
				echo '<em>' . __('None', 'fe-fiction');
			}
		} elseif ($column_name == 'chapter') {
			$chapter_number = get_post_meta($post_id, FIC_POST_CUSTOM_FIELDS_PREFIX . 'chapter_number', true);
			$chapter_name = get_post_meta($post_id, FIC_POST_CUSTOM_FIELDS_PREFIX . 'chapter_title', true);
			if ($chapter_number == '' && $chapter_name == '') {
				echo '';
			} else {
				echo $chapter_number;
				if ($chapter_name != '') {
					echo ' - ' . $chapter_name;
				}
			}
		} elseif ($column_name == 'score') {
			echo the_ratings_results($post_id, 0, 0, 0, 25000);
		} elseif ($column_name == 'id') {
			echo $post_id;
		}
	}

	/** Bulk actions on the fiction story list page in the admin **/
	function FeFiction_Bulk_Actions_Options($actions) {
		return array();
	}

	function FeFiction_Comment_Post_Redirect($location) {
		$pagename = FeFiction_Get_Page_Slug_Name();
		$location = str_replace('/' . CUSTOM_POST_TYPE . '/', '/fan_fiction_stories/', $location);
		return $location;
	}

	function FeFiction_Comment_Form_Defaults($defaults) {
		global $wp;
		$slug = FeFiction_Get_Page_Slug_Name();
		$defaults['must_log_in'] = str_replace('%2F' . CUSTOM_POST_TYPE . '%2F', '%2F' . $slug . '%2Fstory%2F', $defaults['must_log_in']);
		$defaults['logged_in_as'] = str_replace('%2F' . CUSTOM_POST_TYPE . '%2F', '%2F' . $slug . '%2Fstory%2F', $defaults['logged_in_as']);
		return $defaults;
	}

	/**
	 ** Add Settings link to plugins
	 **/
	function FeFiction_Manage_Plugins_Links($links, $file) {
		if ($file == FIC_PLUGIN_DIR . '/fe-super-fiction-main.php') {
			$settings_link = '<a href="admin.php?page=writing-options">' . __("Settings", 'fe-fiction') . '</a>';
			if (!current_user_can('only_by_fanficme')) {
				unset($links['deactivate']);
			}
			array_unshift($links, $settings_link);
		}
		return $links;
	}

	function FeFiction_Manage_Plugins_Deactivation($file) {
		if ($file == FIC_PLUGIN_DIR . '/fe-super-fiction-main.php') {
			if (!current_user_can('only_by_fanficme')) {
				wp_redirect('/wp-admin/plugins.php');
				exit;
			}
		}
	}

	function FeFiction_No_Favorites($actions) {
		echo '<script>jQuery(\'#favorite-actions\').css(\'width\',\'0px\').hide();</script>';
		//var_dump($GLOBALS);
	}

	function fe_admin_options_js_scripts() {
		$current_fe_fiction_page = get_option(FIC_OPTION_PAGE_ID, '0');

		if ($current_fe_fiction_page != '0') {
			echo '<script>';
			echo 'function FeFiction_Confirm_Page_Delete() { ';
			echo 'var confirmAnswer = confirm("' . __('Are you sure you want to delete the page?', 'fe-fiction') . '");';
			echo 'if(confirmAnswer) { location.href="' . str_replace('&amp;', '&', get_delete_post_link($current_fe_fiction_page, true)) . '"; } else { }';
			echo '}';
			echo '</script>';
		}
	}

	function FeFiction_New_CMS_Dashboard_Widget_Function() {
		/** from fe-fiction-class * */
		$siteurl = get_option('siteurl');

		/** WordPress Administration Bootstrap */
		include_once(FIC_PLUGIN_ABS_PATH_DIR . '/views/admin/new_dashboard_page.php');
	}

	function FeFiction_New_CMS_Dashboard_Widget_Pages_Function() {
		/** from fe-fiction-class * */
		$siteurl = get_option('siteurl');

		/** WordPress Administration Bootstrap */
		include_once(FIC_PLUGIN_ABS_PATH_DIR . '/views/admin/new_dashboard_page_simple.php');
	}

	function new_cms_dashboard_widgets() {
    add_meta_box( 'new_cms_dashboard_widget', 'Your Options', 'FeFiction_New_CMS_Dashboard_Widget_Function', 'dashboard', 'normal', 'high' );
		//wp_add_dashboard_widget('new_cms_dashboard_widget', 'Your Options', 'FeFiction_New_CMS_Dashboard_Widget_Function');
	}

	function new_cms_dashboard_widgets_pages() {
		echo '<div class="fe-class-widgets-div"></div>';
		FeFiction_New_CMS_Dashboard_Widget_Pages_Function();
	}

	function FeFiction_Admin_Register_Head_New_CMS_Dashboard() {
		$siteurl = get_option('siteurl');
		$url = plugins_url(). '/' . FIC_PLUGIN_DIR . '/views/css/new_dashboard.css';
		echo "<link rel='stylesheet' type='text/css' href='$url' />\n";
	}

	/** START HIDE ADMIN MENU FOR NON-ADMIN USERS * */
	function FeFiction_Restrict_Admin_Menus() {
		// setup the global menu variable
		global $menu;
		// this is an array of the menu item names we wish to remove
/* tbc */
		$restricted = array(__('Links'), __('Tools'), __('Settings'), __('Posts'), __('Media'));
		end($menu);

		while (prev($menu)) {
			$value = explode(' ', $menu[key($menu)][0]);

			if (in_array($value[0] != NULL ? $value[0] : "", $restricted)) {
				unset($menu[key($menu)]);
			}
		}
	}

	function FeFiction_Remove_Admin_Menus() {
		global $submenu, $menu;
		foreach ($submenu as $url => $array) {
			unset($submenu[$url]);
		}
		foreach ($menu as $tid => $array) {
			unset($menu[$tid]);
		}

		add_action('admin_print_footer_scripts', 'FeFiction_Remove_Admin_Menus_Jscript');
	}

	function FeFiction_Remove_Admin_Menus_Jscript() {
		echo '<script>jQuery(\'#adminmenu\').css(\'width\',\'0px\').css(\'marginRight\',\'0px\').hide();jQuery(\'#wpbody\').css(\'marginLeft\',\'25px\');</script>';
	}

	function FeFiction_Init_Create_Custom_Fields() {
		if (!empty($GLOBALS['FIC_POST_CUSTOM_FIELDS'])) {
			foreach ($GLOBALS['FIC_POST_CUSTOM_FIELDS'] as $custom_field) {
				foreach ($custom_field['object_type'] as $object_type)
					add_meta_box('fe-fic-custom-fields', 'Story Details (Meta Information)', 'FeFiction_Display_Custom_Fields', $object_type, 'normal', 'high');
			}
		}
	}

	function FeFiction_Display_Custom_Fields() {
		include FIC_PLUGIN_ABS_PATH_DIR . '/views/admin/fe-fiction-display-custom-fields.php';
	}

	function codex_fiction_updated_messages($messages) {
		$messages['fiction'][11] = __('Fandom, Rating, and Genre are all required.', 'fe-fiction');
		return $messages;
	}
	function so_screen_layout_columns( $columns ) {
    $columns['dashboard'] = 2;
    return $columns;
}
add_filter( 'screen_layout_columns', 'so_screen_layout_columns' );

function so_screen_layout_dashboard() {
    return 1;
}
add_filter( 'get_user_option_screen_layout_dashboard', 'so_screen_layout_dashboard' );
	/**
	 * Save custom fields data
	 *
	 * @param int $post_id The post id of the post being edited
	 */
	function FeFiction_Save_Custom_Fields($post_id) {
		global $wpdb;

		/* Prevent autosave from deleting the custom fields */
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			return;

		$parent_story_id_field_exists = false;
		if (!empty($GLOBALS['FIC_POST_CUSTOM_FIELDS'])) {
			foreach ($GLOBALS['FIC_POST_CUSTOM_FIELDS'] as $custom_field) {

				if (isset($_POST[FIC_POST_CUSTOM_FIELDS_PREFIX . $custom_field['field_id']])) {
					update_post_meta($post_id, FIC_POST_CUSTOM_FIELDS_PREFIX . $custom_field['field_id'], preg_replace("@\r\n@", " ", $_POST[FIC_POST_CUSTOM_FIELDS_PREFIX . $custom_field['field_id']]));

					/** we only perform this when a story is being created **/
					if ($custom_field['field_id'] == 'parent_story') {
						$parent_story_id_field_exists = true;
					}
				} else {
					delete_post_meta($post_id, FIC_POST_CUSTOM_FIELDS_PREFIX . $custom_field['field_id']);
				}

				if ($custom_field['field_id'] == 'parent_story' && isset($_POST['post_status']) && $_POST['post_status'] == 'publish') {
					if ($parent_story_id_field_exists) {
                        $postfieldid = $_POST[FIC_POST_CUSTOM_FIELDS_PREFIX . $custom_field['field_id']];
						$children_sql = $wpdb->get_results("SELECT id,parent_id,child_id FROM " . $wpdb->prefix . "fic_poststruct WHERE parent_id=" . $postfieldid . " AND child_id=" . $post_id);

						if (!isset($_POST[FIC_POST_CUSTOM_FIELDS_PREFIX . $custom_field['field_id']]) || $_POST[FIC_POST_CUSTOM_FIELDS_PREFIX . $custom_field['field_id']] == '' || $_POST[FIC_POST_CUSTOM_FIELDS_PREFIX . $custom_field['field_id']] == '0') {
							$fiction_type = 'master';
							$parent_id = $post_id;
						} else {
							$fiction_type = 'chapter';
							$parent_id = $_POST[FIC_POST_CUSTOM_FIELDS_PREFIX . $custom_field['field_id']];

							/** save the parent categories with this story to ensure the parent categories flow down to chapters at all times **/
							$parent_story_categories = wp_get_object_terms($parent_id, 'story_category', array('fields' => 'ids'));
							wp_set_post_terms($post_id, $parent_story_categories, 'story_category', true);

						}

						/** if child does not exist, insert a new record */
						if (!count($children_sql)) {
							$insert_sql = $wpdb->query("INSERT INTO " . $wpdb->prefix . "fic_poststruct (parent_id,child_id,type) VALUES (" . $parent_id . "," . $post_id . ",'" . $fiction_type . "')");
						} else {
							$insert_sql = $wpdb->query("UPDATE " . $wpdb->prefix . "fic_poststruct SET parent_id=" . $parent_id . ",child_id=" . $post_id . ",type='" . $fiction_type . "' WHERE parent_id=" . $parent_id . " AND child_id=" . $post_id);
						}
					} else {
						//$insert_sql = $wpdb->query("INSERT INTO ".$wpdb->prefix."fic_poststruct (parent_id,child_id,type) VALUES(".$post_id.",".$post_id.",'master')");
					}
				}
			}
            if(isset($_POST['post_type'])&& isset($_POST['post_status'])&&isset($_POST['action'])){
                if ($_POST['post_type'] == CUSTOM_POST_TYPE && $_POST['post_status'] == 'publish' && $_POST['action'] == 'editpost') {
                    $error = false;
                    if (!isset($_POST['tax_input']['story_category']) || count($_POST['tax_input']['story_category']) < 1) {
                        if (isset($_POST['curr_selected_in-story_category']) && count($_POST['curr_selected_in-story_category'])) {
                            $_POST['tax_input']['story_category'] = $_POST['curr_selected_in-story_category'];
                        } else {
                            $error = true;
                        }
                    }
                    if (!isset($_POST['tax_input']['rating']) || count($_POST['tax_input']['rating']) < 1 || (count($_POST['tax_input']['rating']) == 1 && $_POST['tax_input']['rating'][0] == 0)) {
                        $error = true;
                    }
                    if (!isset($_POST['tax_input']['genre']) || count($_POST['tax_input']['genre']) < 1 || (count($_POST['tax_input']['genre']) == 1 && $_POST['tax_input']['genre'][0] == 0)) {
                        $error = true;
                    }
                    if ($error) {
                        // unhook this function so it doesn't loop infinitely
                        remove_action('save_post', 'FeFiction_Save_Custom_Fields');

                        $my_post = array();
                        $my_post['ID'] = $post_id;
                        $my_post['post_status'] = 'draft';
                        wp_update_post($my_post);

                        // re-hook this function
                        add_action('save_post', 'FeFiction_Save_Custom_Fields');

                        wp_redirect('/wp-admin/post.php?post=' . $post_id . '&action=edit&message=11');
                        exit;
                    }
                }
            }
		}
	}

	function FeFiction_Save_Post_Paginate_Content($post_id) {
		if ($parent_id = wp_is_post_revision($post_id))
			$post_id = $parent_id;

		// Prevent autosave from deleting the custom fields
		if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !isset($_POST['content']))
			return;

		if (isset($_POST['post_type']) && $_POST['post_type'] != 'fiction')
			return;

		// make sure this is for the single story pages
		global $template;
		$current_template = basename($template);
		if ($_POST['content'] == '[wp-fanfiction-writing-archive]')
			return;

		// unhook this function so it doesn't loop infinitely
		remove_action('save_post', 'FeFiction_Save_Post_Paginate_Content');

		$content = FeFiction_Remove_Invalid_Tags_From_Content($_POST['content']);
		$content = FeFiction_Create_Pages_From_Content($content);

		$_POST['content'] = $content;
		$_POST['post_content'] = $content;
		unset($content);
		wp_update_post(array('ID' => $post_id, 'post_content' => $_POST['content']));

		// no re-hook this function since the save is complete (wp_update_post triggers the save_post action)
		add_action('save_post', 'FeFiction_Save_Post_Paginate_Content');

	}

	function FeFiction_Remove_Invalid_Tags_From_Content($content) {

		$content = preg_replace("@</pre>@is", "", preg_replace("@<pre>@is", "", $content));
		$content = preg_replace("@</div>@is", "", preg_replace("@<div>@is", "", $content));
		/**
		$content = str_ireplace('<pre>','',$content);
		$content = str_ireplace('</pre>','',$content);
		 **/
		return $content;
	}

	function FeFiction_Create_Pages_From_Content($content) {
		if (!empty($content) && !empty($content)) {
			$this_num_sections = 0;
			$this_num_words = 0;

			$page_number = 1;
			$page_words_count = 0;

			$content = str_replace('<!--nextpage-->', '', $content);
			$content = apply_filters('the_content', $content);
			$content = str_replace(']]>', ']]&gt;', $content);

			preg_match_all("~<(h1|h2|h3|p|div|blockquote|pre)[^>]*>.*?</(h1|h2|h3|p|div|blockquote|pre)>~is", $content, $array);

			$insert_nextpage = false;
			if (count($array[0])) {
				for ($a = 0; $a < count($array[0]); $a++) {
					// increment the number of sections currently on this page
					$this_num_sections++;

					$this_raw_text = strip_tags($array[0][$a]);

					$words_tmp = split(' ', $this_raw_text);

					$count_words_tmp = count($words_tmp);
					$this_num_words += $count_words_tmp;

					$cur_page_content .= $array[0][$a];

					if ($this_num_words >= FIC_CONTENT_PAGINATE_MIN_WORDS_PER_PAGE) {
						$paginated_content .= '<!--nextpage-->' . $cur_page_content;
						$page_number++;
						$this_num_sections = 0;
						$this_num_words = 0;
						$cur_page_content = '';
						$insert_nextpage = true;
					}
				}
			} else {
				$insert_nextpage = false;
				$paginated_content = $content;
			}
			// whatever's left needs to go onto a new page
			if (strlen($cur_page_content)) {
				$paginated_content .= $cur_page_content;
				$cur_page_content = '';
			}
		}
		return preg_replace("@</p>@is", "\r\n\r\n", preg_replace("@<p>@is", "", preg_replace("@^<\!\-\-nextpage\-\->@", "", $paginated_content)));
	}

	function FeFiction_Delete_Post($post_id) {
		global $wpdb;
		$wpdb->query("DELETE FROM " . $wpdb->prefix . "fic_poststruct WHERE parent_id=" . $post_id . " OR child_id=" . $post_id);
	}

	function FeFiction_Generate_Terms() {
		foreach ($GLOBALS['FIC_TAXONOMIES'] as $taxonomy_name => $taxonomy_info) {
			if (array_key_exists('terms', $taxonomy_info)) {
				foreach ($taxonomy_info['terms'] as $term_name => $term_args) {
					wp_insert_term($term_name // the term
					, $taxonomy_name // the taxonomy
					, $term_args);
				}
			}
		}
	}

	function FeFiction_OverWrite_Post_Edit_Meta_Boxes() {
		global $post, $wp, $wpdb;

		$current_screen = get_current_screen();

		if ($current_screen->parent_base == 'edit' && $current_screen->post_type == 'fiction' && $post->ID > 0 && is_admin()
				&& (stristr($_SERVER['REQUEST_URI'], '/wp-admin/post-new.php?post_type=' . CUSTOM_POST_TYPE) || (stristr($_SERVER['REQUEST_URI'], '/wp-admin/post.php') && isset($_REQUEST['post']) && isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit'))) {

			$post_story_categories = wp_get_object_terms($post->ID, 'story_category', array('fields' => 'all'));

			ob_start();
?>
			<?php
			$category_letters = $wpdb
					->get_results(
							"SELECT
					  IF(SUBSTRING(`wpt`.`name`, 1, 1) REGEXP '[[:alpha:]]', SUBSTRING(`wpt`.`name`, 1, 1), '#') AS `CAT_LETTER`,
					  COUNT(SUBSTRING(`wpt`.`name`, 1, 1)) AS `NUM_CATS`
					FROM
					  `" . $wpdb->prefix . "terms` `wpt`
					  INNER JOIN `" . $wpdb->prefix . "term_taxonomy` `wptt` ON (`wpt`.`term_id` = `wptt`.`term_id`)
					WHERE
					  `wptt`.`taxonomy` = 'story_category'
					GROUP BY
					  CAT_LETTER
					ORDER BY
					  `CAT_LETTER`", ARRAY_A);
			?><div id="post_edit_fiction_search_fandoms_curr"><?php
						if (count($post_story_categories)) {
							$post_story_categories_simple_array = array();
							for ($psci = 0; $psci < count($post_story_categories); $psci++) {
                          ?><input type="hidden" name="curr_selected_in-story_category[]" id="curr_selected_in-story_category[]" value="<?php echo $post_story_categories[$psci]->term_id; ?>" /><?php

                                              $post_story_categories_simple_array[] = $post_story_categories[$psci]->term_id;
                                          }
                                      } else {
                                          $post_story_categories_simple_array = array();
                                      } ?></div><div id="post_edit_fiction_search_fandoms_block" name="post_edit_fiction_search_fandoms_block"><ul><?php for ($a = 0; $a < count($category_letters); $a++) { ?><li><?php /** <a href="/wp-admin/admin-ajax.php?action=fiction_fandoms_post_edit&cat_letter=<?php echo str_replace('#','NUM',$category_letters[$a]['CAT_LETTER']); ?>&pid=<?php echo $post->ID; ?><?php echo $post_story_categories_get_parm_list; ?>"><span><?php echo $category_letters[$a]['CAT_LETTER']; ?></span></a> **/ ?><a href="#post_edit_fiction_search_fandoms_block-t<?php echo str_replace('#', 'NUM', $category_letters[$a]['CAT_LETTER']); ?>"><?php echo str_replace('#', 'NUM', $category_letters[$a]['CAT_LETTER']); ?></a></li><?php }
                        ?></ul><?php for ($a = 0; $a < count($category_letters); $a++) {
					   ?><div id="post_edit_fiction_search_fandoms_block-t<?php echo str_replace('#', 'NUM', $category_letters[$a]['CAT_LETTER']); ?>"><p><?php echo addslashes(
											   FeFiction_Fandoms_Post_Edit_Actions(str_replace('#', 'NUM', $category_letters[$a]['CAT_LETTER']), $post->ID, $post_story_categories_simple_array)); ?></p></div><?php
								   }
                                   ?></div><?php
    $fandoms_html = ob_get_contents();
       ob_end_clean();

       //$ratings_ids = get_terms( 'rating' );
       $ratings_ids = $wpdb
               ->get_results(
                       "SELECT `"
                               . $wpdb
                                       ->prefix
                               . "terms`.`term_id`,`"
                               . $wpdb
                                       ->prefix
                               . "terms`.`name`,`"
                               . $wpdb
                                       ->prefix
                               . "terms`.`slug` FROM `"
                               . $wpdb
                                       ->prefix
                               . "term_taxonomy` INNER JOIN `"
                               . $wpdb
                                       ->prefix
                               . "terms` ON (`"
                               . $wpdb
                                       ->prefix
                               . "term_taxonomy`.`term_id` = `"
                               . $wpdb
                                       ->prefix
                               . "terms`.`term_id`) WHERE `"
                               . $wpdb
                                       ->prefix
                               . "term_taxonomy`.`taxonomy` = 'rating'");

       for ($a = 0; $a
               < count(
                       $GLOBALS['FIC_TAXONOMY_RATING_ORDER']); $a++) {
           for ($b = 0; $b
                   < count(
                           $ratings_ids); $b++) {
               if ($ratings_ids[$b]
                       ->slug
                       == $GLOBALS['FIC_TAXONOMY_RATING_ORDER'][$a]) {
                   $ratings_ids_sorted[] = $ratings_ids[$b];
                   break;
               }
           }
       }
       $ratings_ids = $ratings_ids_sorted;
       unset(
               $ratings_ids_sorted);

       $post_story_categories = wp_get_object_terms(
               $post
                       ->ID,
               'rating',
               array(
                       'orderby' => 'term_order',
                       'order' => 'ASC',
                       'fields' => 'ids'));

       $ratings_html = '<ul id="ratingchecklist" class="list:rating categorychecklist form-no-clear">';
       for ($a = 0; $a
               < count(
                       $ratings_ids); $a++) {
           $ratings_html .= '<li id="rating-'
                   . $ratings_ids[$a]
                           ->term_id
                   . '" class="popular-category"><label class="selectit"><input value="'
                   . $ratings_ids[$a]
                           ->term_id
                   . '" type="radio" name="tax_input[rating][]" id="in-rating-'
                   . $ratings_ids[$a]
                           ->term_id
                   . '"'
                   . (in_array(
                           $ratings_ids[$a]
                                   ->term_id,
                           $post_story_categories) ? ' checked="checked"'
                           : '')
                   . ' />&nbsp;<img src="'.plugins_url().'/wp-fanfiction-and-writing-archive-basic/views/images/'
                   . $ratings_ids[$a]
                           ->slug
                   . '_rating.gif" width="400" height="60" align="middle"></label></li>';
           }
           $ratings_html
                   . '</ul>'; ?>
			<script type="text/javascript">
			jQuery(document).ready(function($) {
				$('#taxonomy-story_category').html('<?php echo $fandoms_html; ?>');
				$('#post_edit_fiction_search_fandoms_block').tabs({cache:true});
				$('div#ratingdiv.postbox > h3.hndle > span').html('<?php _e('Story Rating', 'fe-fiction'); ?>');
				$('#taxonomy-rating').html('<?php echo $ratings_html; ?>');
			});

			function storyCategory(item)
			{
				if(typeof(item) == 'object')
				{
					if(item.checked)
					{
						jQuery('#post_edit_fiction_search_fandoms_curr').append('<input type="hidden" name="curr_selected_'+item.id+'" id="curr_selected_'+item.id+'" value="'+item.value+'" />');
					}
					else
					{
						jQuery('#curr_selected_'+item.id).remove();
					}
				}
				else
				{
						jQuery('#post_edit_fiction_search_fandoms_curr').append('<input type="hidden" name="curr_selected_'+item+'" id="curr_selected_'+item+'" value="'+item+'" />');
				}
			}
			</script>
			<?php
		}
	}

	function FeFiction_Copy_From_Existing_Stories() {
		global $post, $wp, $wpdb;

		$current_screen = get_current_screen();

		//echo get_the_title($post->ID);
		if (isset($post->post_type) && $post->post_type == CUSTOM_POST_TYPE && is_admin()) {
			$cur_user_story_list_query = "SELECT " . $wpdb->prefix . "posts.*," . $wpdb->prefix . "fic_poststruct.* FROM " . $wpdb->prefix . "posts INNER JOIN " . $wpdb->prefix . "fic_poststruct ON (" . $wpdb->prefix . "posts.ID = " . $wpdb->prefix . "fic_poststruct.parent_id) WHERE 1=1 AND "
					. $wpdb->prefix . "posts.ID NOT IN (SELECT DISTINCT " . $wpdb->prefix . "fic_poststruct.parent_id FROM " . $wpdb->prefix . "fic_poststruct WHERE " . $wpdb->prefix . "fic_poststruct.parent_id='0') AND " . $wpdb->prefix . "posts.post_type = 'fiction' AND (" . $wpdb->prefix
					. "posts.post_status = 'publish' OR " . $wpdb->prefix . "posts.post_status = 'future' OR " . $wpdb->prefix . "posts.post_status = 'pending') AND " . $wpdb->prefix . "posts.post_author = "
					. (current_user_can('manage_fic_options') ? $post->post_author : get_current_user_id() . " ORDER BY " . $wpdb->prefix . "posts.post_title ASC");

			$cur_user_story_list = $wpdb->get_results($cur_user_story_list_query, OBJECT);

			$new_cur_user_story_list = array();
			$parent_story_selected = 0;
			for ($a = 0; $a < count($cur_user_story_list); $a++) {
				if (!in_array($cur_user_story_list[$a]->ID, $new_cur_user_story_list)) {
					$new_cur_user_story_list[$cur_user_story_list[$a]->ID] = $cur_user_story_list[$a];
					//unset($cur_user_story_list[$a]);

					if ($post->ID && $cur_user_story_list[$a]->child_id == $post->ID) {
						$parent_story_selected = $cur_user_story_list[$a]->ID;
					}
				}
			}

			unset($cur_user_story_list);
			$cur_user_story_list = $new_cur_user_story_list;

			//if((stristr($_SERVER['REQUEST_URI'],'/wp-admin/post-new.php?post_type='.CUSTOM_POST_TYPE)))
			if ($current_screen->parent_base == 'edit' && $current_screen->post_type == 'fiction') {
				if ($current_screen->action == 'add') {
					//add_action('wp_print_footer_scripts', 'FeFiction_Copy_From_Existing_Stories_JS');
					include_once(FIC_PLUGIN_ABS_PATH_DIR . '/views/admin/fe-fiction-copy-from-existing-stories-select.php');
				} elseif (isset($_REQUEST['post'])) //must be editing a post
 {
					//add_action('wp_print_footer_scripts', 'FeFiction_Copy_From_Existing_Stories_JS');
					include_once(FIC_PLUGIN_ABS_PATH_DIR . '/views/admin/fe-fiction-copy-from-existing-stories-select_fiction_edit.php');
					include_once(FIC_PLUGIN_ABS_PATH_DIR . '/views/admin/fe-fiction-edit-story-limiter.php');
				}
			}

			//include_once(FIC_PLUGIN_ABS_PATH_DIR.'/views/fe-fiction-add-edit-validate.php');
			include_once(FIC_PLUGIN_ABS_PATH_DIR . '/views/admin/fe-fiction-remove-sidebar-JS.php');
		}
	}

	function FeFiction_Copy_From_Existing_Stories_Filter_Where($where = '') {
		//$where = " AND post_type='fiction' AND post_status IN('publish','future','pending') ";

		if (!current_user_can('editor') && !current_user_can('administer')) {
			$where .= " AND post_author = " . get_current_user_id();
		}
		//$where .= " AND posts_per_page = 100000";
		return $where;
	}

	function FeFiction_Copy_From_Existing_Stories_JS() {
		global $post;
		if (isset($post->post_type) && $post->post_type == CUSTOM_POST_TYPE && is_admin()) {
			include_once(FIC_PLUGIN_ABS_PATH_DIR . '/views/admin/fe-fiction-copy-from-existing-stories-js.php');
		}
	}

	function FeFiction_Remove_Media_Buttons($context) {
		global $post;
		if (isset($post->post_type) && $post->post_type == CUSTOM_POST_TYPE) {
			if (!current_user_can('editor') && !current_user_can('administrator')) {
				return;
			}
		}
		return $context;
	}

	function FeFiction_Create_FeFiction_Page($page_title) {
		global $user_ID;

		$new_page = array('filter' => 'db', 'post_type' => 'page', 'post_title' => stripslashes($page_title), 'post_content' => stripslashes('[wp-fanfiction-writing-archive]'), 'post_excerpt' => '', 'post_category' => array(''), 'post_author' => $user_ID, 'tags_input' => '', 'comment_status' => 'closed',
				'ping_status' => 'open', 'post_status' => 'publish');

		return wp_insert_post($new_page);
	}

	function FeFiction_Create_Post_Type_Files($post_type) {
		$file = TEMPLATEPATH . '/single.php';
		if (!empty($post_type)) {
			$newfile = TEMPLATEPATH . '/single-' . strtolower($post_type) . '.php';
			if (!file_exists($newfile)) {
				if (@copy($file, $newfile)) {
					chmod($newfile, 0777);
				} else {
					//echo "Failed to copy $file...\n";
				}
			}
		}
	}

	function FeFiction_Admin_Menu() {
		global $menu;

		/** remove the standard comments manager menu if not right capability **/

		add_options_page(__('Author Pages', 'fe-fiction'), __('Author Pages', 'fe-fiction'), 'manage_fic_options', 'wpu_admin', 'wpu_admin');

		add_options_page(__('User Limits', 'fe-fiction'), __('User Limits', 'fe-fiction'), 'administrator', 'fanficme_site_user_limits', 'FeFiction_Site_User_Limits');

		

		/** START fiction options for site owners and admin **/

		//$menu[80] = array('','read',"separator88",'','wp-menu-separator');

		add_menu_page(__('Fanfic Options', 'fe-fiction'), __('Fanfic Options', 'fe-fiction'), 'manage_fic_options', 'writing-options', 'FeFiction_Admin_Options_Page', '');

		//$menu[82] = array('','read',"separator81",'','wp-menu-separator');

		/** END fiction options for site owners and admin **/
	}

	function add_admin_menu_separator($position) {
		global $menu;
		$index = 0;
		foreach ($menu as $offset => $section) {
			if (substr($section[2], 0, 9) == 'separator')
				$index++;
			if ($offset >= $position) {
				$menu[$position] = array('', 'read', "separator{$index}", '', 'wp-menu-separator');
				break;
			}
		}
	}

	add_action('admin_footer', 'paginate_stories_footer_scripts');
	function paginate_stories_footer_scripts() {
			?>
	<script>
	jQuery(document).ready(function(){
	  jQuery('#paginate_stories_submit_next').trigger('click');
	});
	</script>
	<?php
	}

	function FeFiction_Site_Admin_Paginate_stories() {
		global $wpdb;

		$story_list_count_query = "SELECT COUNT(" . $wpdb->prefix . "posts.ID) FROM " . $wpdb->prefix . "posts WHERE " . $wpdb->prefix . "posts.post_type = 'fiction' AND (" . $wpdb->prefix . "posts.post_status = 'publish' OR " . $wpdb->prefix . "posts.post_status = 'future' OR " . $wpdb->prefix
				. "posts.post_status = 'pending')";

		$story_list_count = $wpdb->get_results($story_list_count_query, OBJECT);
	?>
		<form action="<?php echo admin_url('options-general.php'); ?>" method="get" class="fe-class-form">
			<input type="hidden" name="page" value="fanficme_site_admin_paginate_stories" />
			<label for="doitnow" class="fe-class-bold">Confirm:</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" size="10" name="doitnow" id="doitnow" value="<?php echo $_GET['doitnow']; ?>" />
			<br />
			<label for="row_start" class="fe-class-bold">Row Start:</label>&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" value="<?php echo ($_GET['submit'] == 'Next' ? $_GET['row_start'] + $_GET['how_many'] : $_GET['row_start']); ?>" size="10" name="row_start" id="row_start" />
			<br />
			<label for="how_many" class="fe-class-bold">How Many:</label>&nbsp;&nbsp;&nbsp;<input type="text" value="<?php echo $_GET['how_many']; ?>" size="10" name="how_many" id="how_many" />
			<br /><br />
			<input type="submit" name="submit" value="Submit" />&nbsp;&nbsp;&nbsp;<input type="submit" name="submit" value="Next" <?php if ($_REQUEST['doitnow'] == 'YES' && $story_list_count >= $_REQUEST['row_start'] + $_REQUEST['how_many']) { ?>id="paginate_stories_submit_next"<?php } ?> />
		</form>
		<?php
		if (isset($_GET['doitnow']) && $_GET['doitnow'] == 'YES') {
			$_GET['row_start'] = $_GET['submit'] == 'Next' ? $_GET['row_start'] + $_GET['how_many'] : $_GET['row_start'];

			if ($_GET['how_many'] > 0 && $_GET['row_start'] >= 0) {
				echo '<br />Executing ' . $_GET['how_many'] . ' records, starting with row: ' . $_GET['row_start'] . '<br />';
			}

            $story_list_query = "SELECT " . $wpdb->prefix . "posts.ID," . $wpdb->prefix . "posts.post_content FROM " . $wpdb->prefix . "posts WHERE " . $wpdb->prefix . "posts.post_type = 'fiction' AND (" . $wpdb->prefix . "posts.post_status = 'publish' OR " . $wpdb->prefix
                . "posts.post_status = 'future' OR " . $wpdb->prefix . "posts.post_status = 'pending') LIMIT " . $_GET['row_start'] . "," . $_GET['how_many'];

			$story_list = $wpdb->get_results($story_list_query, OBJECT);

			for ($a = 0; $a < count($story_list); $a++) {
				echo $story_list[$a]->ID . ' , ';
				wp_update_post(array('ID' => $story_list[$a]->ID, 'post_content' => FeFiction_Create_Pages_From_Content($story_list[$a]->post_content)));
			}
			echo '<strong>total stories paginated: ' . count($story_list) . '</strong>';
			unset($story_list[$a]);
			flush();

		?>
			<form action="/wp-admin/options-general.php" method="get" class="fe-class-form">
				<input type="hidden" name="page" value="fanficme_site_admin_paginate_stories" />
				<label for="doitnow" class="fe-class-bold">Confirm:</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" size="10" name="doitnow" id="doitnow" value="<?php echo $_GET['doitnow']; ?>" />
				<br />
				<label for="row_start" class="fe-class-bold">Row Start:</label>&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" value="<?php echo ($_GET['submit'] == 'Next' ? $_GET['row_start'] + $_GET['how_many'] : $_GET['row_start']); ?>" size="10" name="row_start" id="row_start" />
				<br />
				<label for="how_many" class="fe-class-bold">How Many:</label>&nbsp;&nbsp;&nbsp;<input type="text" value="<?php echo $_GET['how_many']; ?>" size="10" name="how_many" id="how_many" />
				<br /><br />
				<input type="submit" name="submit" value="Submit" />&nbsp;&nbsp;&nbsp;<input type="submit" name="submit" value="Next" />
			</form>
			<?php
		}
	}

	function FeFiction_Site_User_Limits() {
		$options_updated['success'] = false;

		if (isset($_POST) && is_array($_POST) && isset($_POST['Submit'])) {
			if (isset($_POST['user_limit'])) {
				update_option(FIC_OPTION_SITE_USER_LIMIT, $_POST['user_limit']);
			} else {
				update_option(FIC_OPTION_SITE_USER_LIMIT, '0');
			}
			$options_updated['success'] = true;
		}

		include_once(FIC_PLUGIN_ABS_PATH_DIR . '/views/admin/fe-fiction-admin-user-limits-form.php');
	}

	function FeFiction_Admin_Options_Page() {
		$siteurl = get_option('siteurl');
		$plugin_view_path = plugins_url().'/' . FIC_PLUGIN_DIR . '/views';

		$options_updated['success'] = false;
		$options_updated['hide_admin_menus_enabled'] = false;
		$options_updated['page_created_error'] = '';
		$options_updated['page_created'] = false;
		$options_updated['page_deleted'] = false;
		$options_updated['default_role_set'] = false;
		$options_updated['custom_dashboard_enabled'] = false;
		$options_updated['fiction_page_stylesheet_enabled'] = false;

		if (isset($_POST) && is_array($_POST) && isset($_POST['Submit'])) {
			if (isset($_POST['custom_dashboard'])) {
				$options_updated['custom_dashboard_enabled'] = true;
				$options_updated['custom_dashboard_disabled'] = false;
				update_option(FIC_OPTION_CUSTOM_DASHBOARD, '1');
			} else {
				$options_updated['custom_dashboard_disabled'] = true;
				$options_updated['custom_dashboard_enabled'] = false;
				update_option(FIC_OPTION_CUSTOM_DASHBOARD, '0');
			}
			if (isset($_POST['hide_admin_menus'])) {
				$options_updated['hide_admin_menus_enabled'] = true;
				$options_updated['hide_admin_menus_disabled'] = false;
				update_option(FIC_OPTION_HIDE_ADMIN_MENUS, '1');
			} else {
				$options_updated['hide_admin_menus_enabled'] = false;
				$options_updated['hide_admin_menus_disabled'] = true;
				update_option(FIC_OPTION_HIDE_ADMIN_MENUS, '0');
			}
			if (isset($_POST['enable_fe_fiction_default_role'])) {
				$options_updated['default_role_set'] = true;
				update_option(FIC_OPTION_ENABLE_DEFAULT_ROLE, FIC_DEFAULT_ROLE);
				if (is_multisite()) {
					update_site_option('default_user_role', FIC_DEFAULT_ROLE);
				} else {
					update_option('default_role', FIC_DEFAULT_ROLE);
				}
			} else {
				$options_updated['default_role_set'] = false;
				update_option(FIC_OPTION_ENABLE_DEFAULT_ROLE, '');
			}
			if (isset($_POST['fiction_page_stylesheet']) && trim($_POST['fiction_page_stylesheet']) != '') {
				$options_updated['fiction_page_stylesheet_enabled'] = true;
				$options_updated['fiction_page_stylesheet_disabled'] = false;
				FeFiction_Set_Fiction_Page_Stylesheet($_POST['fiction_page_stylesheet']);
			} else {
				$options_updated['fiction_page_stylesheet_enabled'] = true;
				$options_updated['fiction_page_stylesheet_enabled'] = false;
				FeFiction_Set_Fiction_Page_Stylesheet('DEFAULT');
			}

			if (isset($_POST['fiction_scoring']) && trim($_POST['fiction_scoring']) != '') {
				$options_updated['fiction_scores_enabled'] = true;
				$options_updated['fiction_scores_disabled'] = false;
				update_option(FIC_OPTION_FICTION_STORY_SCORING, '1');
			} else {
				$options_updated['fiction_scores_disabled'] = true;
				$options_updated['fiction_scores_enabled'] = false;
				update_option(FIC_OPTION_FICTION_STORY_SCORING, '0');
			}
            if (isset($_POST['fe_fiction_posting_page_id']) && trim($_POST['fe_fiction_posting_page_id']) != '') {
                update_option(FIC_OPTION_FICTION_POSTING_PAGE, $_POST['fe_fiction_posting_page_id']);
            }
            if (isset($_POST['fe_fiction_position']) && trim($_POST['fe_fiction_position']) != '') {
                update_option(FIC_OPTION_FICTION_POSITION, $_POST['fe_fiction_position']);
            }else{
                update_option(FIC_OPTION_FICTION_POSITION, 'left');
            }


            /** START FICTION PAGE CREATION * */
			if (isset($_POST['FeFiction_Create_FeFiction_Page'])) {
				$new_page_id = 0;
				if (strtolower($_POST['fe_fiction_page_title']) == CUSTOM_POST_TYPE) {
					$options_updated['page_created'] = false;
					$options_updated['page_created_error'] = __('Please do not use "Fiction" or "fiction" as your page title.  This is reserved.', 'fe-fiction');
				} elseif (trim($_POST['fe_fiction_page_title']) == '') {
					$options_updated['page_created'] = false;
					$options_updated['page_created_error'] = __('We need a title for the page if we are to create one for you.', 'fe-fiction');
				} else {
					$new_page_id = FeFiction_Create_FeFiction_Page($_POST['fe_fiction_page_title']);
					if ($new_page_id > 0) {
						$options_updated['page_created'] = true;
						update_option(FIC_OPTION_PAGE_ID, $new_page_id);
					} else {
						$options_updated['page_created'] = false;
						update_option(FIC_OPTION_PAGE_ID, '0');
					}
					update_option(FIC_OPTION_CREATE_PAGE, '1');
				}
			} else {
				$options_updated['page_created'] = false;
				update_option(FIC_OPTION_CREATE_PAGE, '');
			}
			/** END FICTION PAGE CREATION * */
			$options_updated['success'] = true;
		}

		$current_fe_fiction_page_title = stripslashes(get_the_title(get_option(FIC_OPTION_PAGE_ID)));
		$current_fe_fiction_posting_page_id = get_option(FIC_OPTION_FICTION_POSTING_PAGE);
        $current_fe_fiction_position = get_option(FIC_OPTION_FICTION_POSITION);
		if ((isset($_GET['trashed']) && (int) $_GET['trashed'] && isset($_GET['ids']) && (int) $_GET['ids']) || strlen($current_fe_fiction_page_title) == 0) {
			update_option(FIC_OPTION_PAGE_ID, '0');
			update_option(FIC_OPTION_PAGE_TITLE, '');
			update_option(FIC_OPTION_CREATE_PAGE, '');
			$options_updated['page_deleted'] = true;
		}

		$current_fe_fiction_page = get_option(FIC_OPTION_PAGE_ID, '0');

		$fiction_page_stylesheet = FeFiction_Get_Fiction_Page_Stylesheet();

		//add_filter('favorite_actions', 'no_favorites');
		add_action('admin_print_footer_scripts', 'fe_admin_options_js_scripts');
		include_once(FIC_PLUGIN_ABS_PATH_DIR . '/views/admin/fe-fiction-admin-options-form.php');
	}

	/** FILTER THE ROLES THAT A SITE OWNER CAN HAVE ACCESS TO **/
	function FeFiction_Filter_Roles_List($roles_list) {
		if (current_user_can('administrator') || (is_multisite() && is_super_admin(get_current_user_id()))) {
			return $roles_list;
		} else {
			$new_roles_list = array();
			foreach ($roles_list as $role_slug => $role_data) {
				switch ($role_slug) {
				case 'administrator':
				case 'editor':
					unset($roles_list[$role_slug]);
					break;
				default:
				}
			}
			return $roles_list;
		}
	}

	/** CHECK TO SEE IF USER IS TRYING TO ACCESS A PROFILE THEY SHOULDN'T BE
	 *  (LIKE THE ADMINISTRATOR)
	 */
	function FeFiction_Check_Can_Delete_Profile($user_ID) {
		if (user_can($user_ID, 'administrator') || is_super_admin($user_ID)) {
			wp_redirect('users.php');
			exit;
		}
	}

	/** CHECK TO SEE IF USER IS TRYING TO ACCESS A PROFILE THEY SHOULDN'T BE
	 *  (LIKE THE ADMINISTRATOR)
	 */
	function FeFiction_Check_Can_View_Profile($user_ID) {
		if (current_user_can('administrator') || (is_multisite() && is_super_admin(get_current_user_id()))) {
		}
	}

	/** CHECK TO SEE IF USER IS TRYING TO ACCESS A PROFILE THEY SHOULDN'T BE
	 *  (LIKE THE ADMINISTRATOR)
	 */
	function FeFiction_Check_Can_Edit_Profile($user) {
		if (current_user_can('administrator') || (is_multisite() && is_super_admin(get_current_user_id()))) {
		} elseif (user_can($user->ID, 'administrator') || is_super_admin($user->ID)) {
			echo '<script>location.replace("users.php");</script>';
			//wp_redirect( 'users.php' );
			exit;
		}
	}

	/** SHOW FICTION OPTIONS FOR USER ON PROFILE PAGE **/
	function FeFiction_Profile_Fields($user) {
		include_once(FIC_PLUGIN_ABS_PATH_DIR . '/views/admin/fe-fiction-user-options-form.php');
	}
	/** SAVE FICTION OPTIONS FOR USER ON PROFILE PAGE **/
	function FeFiction_Profile_Fields_Update($user_ID) {
		if (isset($_POST[FIC_OPTION_FICTION_STORY_SCORING])) {
			update_user_meta($user_ID, FIC_OPTION_FICTION_STORY_SCORING, '1');
		} else {
			update_user_meta($user_ID, FIC_OPTION_FICTION_STORY_SCORING, '0');
		}

		update_user_meta($user_ID, 'fic_profile_age', $_REQUEST['fic_profile_age']);

		$userdata = array();
		$userdata['ID'] = $user_ID;
		if (!user_can($user_ID, 'administrator') && !user_can($user_ID, $GLOBALS['FIC_SUB_ADMIN_ROLE']['slug'])) {
			update_user_meta($user_ID, 'ffme_user_role', $_POST['ffme_USERTYPE']);
			$userdata['role'] = ($_POST['ffme_USERTYPE'] == 'author2' ? 'author' : $_POST['ffme_USERTYPE']);
			wp_update_user($userdata);
		}
	}

	function FeFiction_Site_Fiction_Page_Title($title) {
		global $wp_query, $wpdb, $post;

		parse_str($GLOBALS['FIC_CUR_QUERY_STRING'], $cur_query_string);

		if (isset($cur_query_string['fiction']) && $cur_query_string['fiction'] != '') {
			$title = '';
			$this_story_data = $wpdb->get_results("SELECT ID,post_title FROM " . $wpdb->posts . " WHERE post_type='" . CUSTOM_POST_TYPE . "' AND post_name='" . $cur_query_string['fiction'] . "' AND post_status='publish'");

			$title = ' : ' . __('Fan Fiction', 'fe-fiction') . ' - ' . $this_story_data[0]->post_title;
		} else {
			if ($post->ID == FeFiction_Get_Page_ID()) {
				$title .= " - " . __('Search', 'fe-fiction');
			}
		}

		return $title;
	}

	/** bad word filtering system **/
	function FeFiction_Filter_Bad_Words($text) {
		return preg_replace_callback('!\w+!', 'FeFiction_Filter_Bad_Words_Replace', $text);
	}
	function FeFiction_Filter_Bad_Words_Replace($matches) {
		global $wpdb;

		if (!count($GLOBALS['BAD_WORDS_LIST'])) {
			/** load up bad words from bad words table **/
			$bad_words_r = $wpdb->get_results("SELECT `word`,`replacement` FROM `" . $wpdb->prefix . "fic_bad_words`", ARRAY_A);

			for ($a = 0; $a < count($bad_words_r); $a++) {
				$GLOBALS['BAD_WORDS_LIST'][strtolower($bad_words_r[$a]['word'])] = '<span class="fe-class-badwords">' . $bad_words_r[$a]['replacement'] . '</span>';
			}
		}

		$replace = $GLOBALS['BAD_WORDS_LIST'][strtolower($matches[0])];
		return isset($replace) ? $replace : $matches[0];
	}

	function FeFiction_The_Content_Bad_Word_Filter($content) {
		if ($GLOBALS['FIC_USERS_AGE'] < FIC_RATINGS_MIN_AGE) {
			return FeFiction_Filter_Bad_Words($content);
		} else {
			return $content;
		}
	}

	/**
	  by default, show listing of all fiction
	  display story if story name is passed (i.e. /fiction/my-story-name)
	  supports taxonomies as well for rating, category, genre
	 * */
    function FeFiction_Site_Display() {
        global $wp_query, $wp, $wpdb;
        //if(is_page(FeFiction_Get_Page_ID()))
        //{
        parse_str($GLOBALS['FIC_CUR_QUERY_STRING'], $cur_query_string);
        $pagename = FeFiction_Get_Page_Slug_Name();

        $query_posts_str = 'post_type=' . stripslashes(CUSTOM_POST_TYPE);



        foreach (array_keys($GLOBALS['FIC_TAXONOMIES']) as $taxonomy) {
            if (array_key_exists($taxonomy, $cur_query_string) && $cur_query_string[$taxonomy] != '') {
                if ($taxonomy == 'story_author') {
                    $query_field = 'author_name';
                } else {
                    $query_field = $taxonomy;
                }

                if (is_numeric($cur_query_string[$taxonomy]) && $cur_query_string[$taxonomy] != '0') {
                    $tax_obj = get_term_by('id', $cur_query_string[$taxonomy], $taxonomy, OBJECT);
                    $slug = $tax_obj->slug;

                    if ($taxonomy == 'story_category' && ($cur_query_string['cat_letter'] == '' || $cur_query_string['cat_name'] == '')) {
                        $_REQUEST['cat_name'] = $tax_obj->name;
                        $_REQUEST['cat_letter'] = substr($tax_obj->name, 0, 1);
                        $cur_query_string['cat_name'] = $tax_obj->name;
                        $cur_query_string['cat_letter'] = substr($tax_obj->name, 0, 1);
                    }

                } elseif ($cur_query_string[$taxonomy] != '0') {
                    $slug = $cur_query_string[$taxonomy];

                    if ($taxonomy == 'story_category' && ($cur_query_string['cat_letter'] == '' || $cur_query_string['cat_name'] == '')) {
                        $tax_obj = get_term_by('id', $cur_query_string[$taxonomy], $taxonomy, OBJECT);
                        $_REQUEST['cat_name'] = $tax_obj->name;
                        $_REQUEST['cat_letter'] = substr($tax_obj->name, 0, 1);
                        $cur_query_string['cat_name'] = $tax_obj->name;
                        $cur_query_string['cat_letter'] = substr($tax_obj->name, 0, 1);
                    }
                } else {
                    $slug = '';
                }
                if ($slug != '') {
                    $query_posts_str .= '&' . $query_field . '=' . stripslashes($slug);

                    if ($taxonomy == 'story_category' && ($cur_query_string['cat_letter'] == '' || $cur_query_string['cat_name'] == '')) {
                        $tax_obj = get_term_by('slug', $cur_query_string[$taxonomy], $taxonomy, OBJECT);
                        $_REQUEST['cat_name'] = $tax_obj->name;
                        $_REQUEST['cat_letter'] = substr($tax_obj->name, 0, 1);
                        $cur_query_string['cat_name'] = $tax_obj->name;
                        $cur_query_string['cat_letter'] = substr($tax_obj->name, 0, 1);
                    }
                }
            }
        }

        if (array_key_exists('story_author', $cur_query_string)) {
            $query_posts_str .= '&author=' . stripslashes($cur_query_string['story_author']);
        }

        if (array_key_exists('story_title', $cur_query_string)) {
            $query_posts_str .= '&name=' . stripslashes($cur_query_string['story_title']);
        }

        if (array_key_exists('paged', $cur_query_string)) {
            $query_posts_str .= '&paged=' . stripslashes($cur_query_string['paged']);
        }

        if (array_key_exists('s', $cur_query_string)) {
            $query_posts_str .= '&s=' . stripslashes($cur_query_string['s']);
        }

        $total_story_count_sql = "SELECT COUNT({$wpdb->posts}.ID) as num_stories FROM {$wpdb->posts} WHERE 1=1 AND {$wpdb->posts}.post_type = 'fiction' AND ({$wpdb->posts}.post_status = 'publish'" . (current_user_can('manage_fic_options') ? " OR {$wpdb->posts}.post_status = 'private' " : "") . ");";

        $num_stories = $wpdb->get_var($wpdb->prepare($total_story_count_sql, ""));

        $original_wp_query = $wp_query;

        $query_posts_str .= $cur_query_string['per_page'] != '' ? '&posts_per_page=' . $cur_query_string['per_page'] : '&posts_per_page=15';
        $query_posts_str .= "&orderby=ID&order=DESC";
        $query_stories = new WP_Query($query_posts_str);
        //$query_stories = new WP_Query('author = 32870');
        $wp_query = $query_stories;

        $num_stories = $query_stories->found_posts == '0' ? $query_stories->post_count : $query_stories->found_posts;

        if ($num_stories > 0) {
            //$query_stories = query_posts($query_posts_str);

            for ($sli = 0; $sli < $num_stories; $sli++) {
                if (trim($query_stories->posts[$sli]->ID) != '') {
                    $chapters_avail_struct = FeFiction_Get_Related_Stories($query_stories->posts[$sli]->ID);
                } /** end for loop **/
            }
        }

        ob_start();
        //include_once(FIC_PLUGIN_ABS_PATH_DIR.'/includes/fe-super-fiction-site-functions.php');

        if ($theme_file = locate_template(array('fiction-views/fe-fiction-site-browse.php'))) {
            include_once(locate_template(array('fiction-views/fe-fiction-site-browse.php')));
        } else {
            include_once(FIC_PLUGIN_ABS_PATH_DIR . '/views/fe-fiction-site-browse.php');
        }

        $wp_query = null;
        $wp_query = $original_wp_query;
        wp_reset_postdata();
        $output = ob_get_contents();
        ob_end_clean();
        return do_shortcode($output);

        //}
    }
	function FeFiction_Get_Related_Stories($postID) {
		global $wpdb;
		$chapters_avail_struct = array();
		$get_related_stories_sql = "SELECT DISTINCT * FROM " . $wpdb->prefix . "fic_poststruct WHERE parent_id=" . $postID . " OR child_id=" . $postID;
		$get_related_stories_r = $wpdb->get_results($get_related_stories_sql, ARRAY_A);
		$get_related_stories_ids = array();
		for ($grsi = 0; $grsi < count($get_related_stories_r); $grsi++) {
			if (!in_array($get_related_stories_r[$grsi]['parent_id'], $get_related_stories_ids)) {
				$get_related_stories_ids[] = $get_related_stories_r[$grsi]['parent_id'];
			}
			if (!in_array($get_related_stories_r[$grsi]['child_id'], $get_related_stories_ids)) {
				$get_related_stories_ids[] = $get_related_stories_r[$grsi]['child_id'];
			}
		}
		if (!in_array($postID, $get_related_stories_ids)) {
			$get_related_stories_ids[] = $postID;
		}

		/**********************************/
		if (is_array($get_related_stories_ids) && count($get_related_stories_ids) && trim($get_related_stories_ids[0]) != '') {
			$get_related_stories_sql = "SELECT DISTINCT * FROM " . $wpdb->prefix . "fic_poststruct WHERE parent_id IN(" . implode(",", $get_related_stories_ids) . ") OR child_id IN(" . implode(",", $get_related_stories_ids) . ")";
			$get_related_stories_r = $wpdb->get_results($get_related_stories_sql, ARRAY_A);

			unset($get_related_stories_ids);
			$get_related_stories_ids = array();

			for ($grsi = 0; $grsi < count($get_related_stories_r); $grsi++) {
				if (!in_array($get_related_stories_r[$grsi]['parent_id'], $get_related_stories_ids)) {
					$get_related_stories_ids[] = $get_related_stories_r[$grsi]['parent_id'];
				}
				if (!in_array($get_related_stories_r[$grsi]['child_id'], $get_related_stories_ids)) {
					$get_related_stories_ids[] = $get_related_stories_r[$grsi]['child_id'];
				}
			}
		}
		/**********************************/

		if (is_array($get_related_stories_ids) && count($get_related_stories_ids) && trim($get_related_stories_ids[0]) != '') {
			$chapters_avail_struct_sql = "SELECT
								  " . $wpdb->prefix . "posts.ID,
								  " . $wpdb->prefix . "posts.post_title,
								  " . $wpdb->prefix . "posts.post_name,
								  " . $wpdb->prefix . "postmeta.meta_key,
								  " . $wpdb->prefix . "postmeta.meta_value
								FROM
								  " . $wpdb->prefix . "posts
								  INNER JOIN " . $wpdb->prefix . "postmeta ON (" . $wpdb->prefix . "posts.ID = " . $wpdb->prefix . "postmeta.post_id)
								WHERE
								  " . $wpdb->prefix . "posts.ID IN (" . implode(",", $get_related_stories_ids) . ") AND
								  " . $wpdb->prefix . "posts.post_status IN ('publish','future','pending')
								  AND
								  " . $wpdb->prefix . "postmeta.meta_key IN ('" . FIC_POST_CUSTOM_FIELDS_PREFIX . "chapter_number','" . FIC_POST_CUSTOM_FIELDS_PREFIX . "chapter_title')";
			/**
			$chapters_avail_struct_sql .= " AND
			".$wpdb->prefix."postmeta.meta_value != ''";
			 **/
			$chapters_avail_struct_tmp = $wpdb->get_results($chapters_avail_struct_sql);

			$fic_page_base_slug = FeFiction_Get_Page_Slug_Name();
			$permalink = home_url() . '/' . $fic_page_base_slug . '/%postname%';

			for ($a = 0; $a < count($chapters_avail_struct_tmp); $a++) {
				$chapters_avail_struct[$chapters_avail_struct_tmp[$a]->ID]['post_title'] = $chapters_avail_struct_tmp[$a]->post_title;
				$chapters_avail_struct[$chapters_avail_struct_tmp[$a]->ID]['story_url'] = str_replace('%postname%', $chapters_avail_struct_tmp[$a]->post_name, $permalink);

				switch ($chapters_avail_struct_tmp[$a]->meta_key) {
				case FIC_POST_CUSTOM_FIELDS_PREFIX . 'chapter_number':
					$chapters_avail_struct[$chapters_avail_struct_tmp[$a]->ID]['chapter_number'] = $chapters_avail_struct_tmp[$a]->meta_value;
				case FIC_POST_CUSTOM_FIELDS_PREFIX . 'chapter_title':
					$chapters_avail_struct[$chapters_avail_struct_tmp[$a]->ID]['chapter_title'] = $chapters_avail_struct_tmp[$a]->meta_value;
				}
			}
		}
		return $chapters_avail_struct;
	}

	function FeFiction_Site_Display_Scripts() {
		global $wp_scripts, $wp_styles;

		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-button');
		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script('jquery-ui-dialog');
		wp_enqueue_script('jquery-ui-widget');
        wp_enqueue_style('fe-fiction-css',plugins_url().'/' . FIC_PLUGIN_DIR . '/views/css/fe-fiction-css.css');
		wp_enqueue_style('jquery-ui-theme', plugins_url().'/' . FIC_PLUGIN_DIR . '/views/jqueryui/themes/' . JQUERY_UI_THEME . '/jquery-ui-1.8.14.custom.css');
	}

	function FeFiction_Single_Story_template($template) {
		if ('fiction' == get_post_type()) {
			if ($theme_file = locate_template(array('fiction-views/fe-fiction-site-browse-single.php'))) {
				$file = $theme_file;
			} else {
				$file = FIC_PLUGIN_ABS_PATH_DIR . '/views/fe-fiction-site-browse-single.php';
			}

			$template = $file;
		}
		return $template;
	}

	function FeFiction_Fiction_Scoring_Enabled($post_id = 0, $author_id = 0) {
		if (get_option(FIC_OPTION_FICTION_STORY_SCORING, '0') == '1' && (get_user_meta($author_id, FIC_OPTION_FICTION_STORY_SCORING, true) == '1' || get_user_meta($author_id, FIC_OPTION_FICTION_STORY_SCORING, true) == '')) {
			return true;
		}
		return false;
	}

	function FeFiction_Site_Display_CSS() {
		$siteurl = get_option('siteurl');
		echo '<link rel="stylesheet" type="text/css" media="all" href="' . plugins_url() . '/' . FIC_PLUGIN_DIR . '/views/css/fe-fiction-pagination.css" />';
        echo '<link rel="stylesheet" type="text/css" media="all" href="' . plugins_url() . '/' . FIC_PLUGIN_DIR . '/views/css/fe-fiction-age.css" />';
		echo '<style type="text/css">' . FeFiction_Get_Fiction_Page_Stylesheet() . '</style>';
	}

	// disable default dashboard widgets
	function FeFiction_Disable_Default_Dashboard_Widgets() {

		remove_meta_box('dashboard_right_now', 'dashboard', 'core');
		remove_meta_box('dashboard_recent_comments', 'dashboard', 'core');
		remove_meta_box('dashboard_incoming_links', 'dashboard', 'core');
		remove_meta_box('dashboard_plugins', 'dashboard', 'core');

		remove_meta_box('dashboard_quick_press', 'dashboard', 'core');
		remove_meta_box('dashboard_recent_drafts', 'dashboard', 'core');
		remove_meta_box('dashboard_primary', 'dashboard', 'core');
		remove_meta_box('dashboard_secondary', 'dashboard', 'core');
	}

	/** Wordpress Users Below **/

	function wpu_get_users($content) {
		if (is_page(get_option('wpu_page_id'))) {

			if (isset($_GET['uid'])) {
				display_user();
			} else {
				echo $content;
				display_user_list();
			}
		} else {
			//display the content
			return $content;
		}
	}

	function wpu_get_roles() {
		global $wpdb;

		$administrator = get_option('wpu_roles_admin');
		$fic_site_owner = get_option('wpu_roles_fic_site_owner');
		$subscriber = get_option('wpu_roles_subscriber');
		$author = get_option('wpu_roles_author');
		$editor = get_option('wpu_roles_editor');
		$contributor = get_option('wpu_roles_contributor');

		$rolelist = array('administrator' => $administrator, 'fic_site_owner' => $fic_site_owner, 'subscriber' => $subscriber, 'author' => $author, 'editor' => $editor, 'contributor' => $contributor);

		$roles = array();

		foreach ($rolelist as $key => $value) {
			if ($value == 'yes')
				array_push($roles, $key);
			else {
			}
		}

		if (empty($roles))
			$roles = array('administrator', 'fic_site_owner', 'subscriber', 'author', 'editor', 'contributor');

		$searches = array();

		foreach ($roles as $role)
			$searches[] = "$wpdb->usermeta.meta_key = '{$wpdb->prefix}capabilities' AND $wpdb->usermeta.meta_value LIKE '%$role%'";

		//create a string for use in a MySQL statement
		$meta_values = implode(' OR ', $searches);

		return $meta_values;
	}

	function display_user_list() {

		// if $_GET['page'] defined, use it as page number
		if (isset($_GET['page'])) {
			$page = $_GET['page'];
		} else {
			// by default we show first page
			$page = 1;
		}
		$limit = get_option('wpu_users_per');

		// counting the offset
		$offset = ($page - 1) * $limit;

		// Get the authors from the database ordered by user nicename
		global $wpdb;
		$meta_values = wpu_get_roles();

		$query = "SELECT $wpdb->users.ID, $wpdb->users.user_nicename FROM $wpdb->users INNER JOIN $wpdb->usermeta ON $wpdb->users.ID = $wpdb->usermeta.user_id WHERE $meta_values ORDER BY $wpdb->users.user_nicename LIMIT $offset, $limit";
		$author_ids = $wpdb->get_results($query);

		$output = '';

		// Loop through each author
		foreach ($author_ids as $author) {

			// Get user data
			$curauth = get_userdata($author->ID);

			$output .= get_user_listing($curauth);
		}

		echo $output;

		// how many rows we have in database
		$totalitems = $wpdb->get_var("SELECT COUNT(ID) FROM $wpdb->users WHERE ID = ANY (SELECT user_id FROM $wpdb->usermeta WHERE $meta_values)");

		$adjacents = 3;

		$concat = wpu_concat_index();

		echo getPaginationString($page, $totalitems, $limit, $adjacents, $concat);
	}

	function getPaginationString($page = 1, $totalitems, $limit = 15, $adjacents = 1, $concat) {
		//defaults
		if (!$adjacents)
			$adjacents = 1;
		if (!$limit)
			$limit = 15;
		if (!$page)
			$page = 1;

		//other vars
		$prev = $page - 1; //previous page is page - 1
		$next = $page + 1; //next page is page + 1
		$lastpage = ceil($totalitems / $limit); //lastpage is = total items / items per page, rounded up.
		$lpm1 = $lastpage - 1; //last page minus 1

		/*
		    Now we apply our rules and draw the pagination object.
		    We're actually saving the code to a variable in case we want to draw it more than once.
		 */
		$pagination = "";
		if ($lastpage > 1) {
			$pagination .= "<div class=\"wpu-pagination\"";
			$pagination .= ">";

			//previous button
			if ($page > 1)
				$pagination .= "<a href=\"" . get_permalink() . $concat . "page=$prev\">« prev</a>";
			else
				$pagination .= "<span class=\"wpu-disabled\">« prev</span>";

			//pages
			if ($lastpage < 7 + ($adjacents * 2)) //not enough pages to bother breaking it up
 {
				for ($counter = 1; $counter <= $lastpage; $counter++) {
					if ($counter == $page)
						$pagination .= "<span class=\"wpu-current\">$counter</span>";
					else
						$pagination .= "<a href=\"" . get_permalink() . $concat . "page=$counter\">$counter</a>";
				}
			} elseif ($lastpage >= 7 + ($adjacents * 2)) //enough pages to hide some
 {
				//close to beginning; only hide later pages
				if ($page < 1 + ($adjacents * 3)) {
					for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
						if ($counter == $page)
							$pagination .= "<span class=\"wpu-current\">$counter</span>";
						else
							$pagination .= "<a href=\"" . get_permalink() . $concat . "page=$counter\">$counter</a>";
					}
					$pagination .= "<span class=\"wpu-elipses\">...</span>";
					$pagination .= "<a href=\"" . get_permalink() . $concat . "page=$lpm1\">$lpm1</a>";
					$pagination .= "<a href=\"" . get_permalink() . $concat . "page=$lastpage\">$lastpage</a>";
				}
				//in middle; hide some front and some back
 elseif ($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)) {
					$pagination .= "<a href=\"" . get_permalink() . $concat . "page=1\">1</a>";
					$pagination .= "<a href=\"" . get_permalink() . $concat . "page=2\">2</a>";
					$pagination .= "<span class=\"wpu-elipses\">...</span>";
					for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
						if ($counter == $page)
							$pagination .= "<span class=\"wpu-current\">$counter</span>";
						else
							$pagination .= "<a href=\"" . get_permalink() . $concat . "page=$counter\">$counter</a>";
					}
					$pagination .= "...";
					$pagination .= "<a href=\"" . get_permalink() . $concat . "page=$lpm1\">$lpm1</a>";
					$pagination .= "<a href=\"" . get_permalink() . $concat . "page=$lastpage\">$lastpage</a>";
				}
				//close to end; only hide early pages
 else {
					$pagination .= "<a href=\"" . get_permalink() . $concat . "page=1\">1</a>";
					$pagination .= "<a href=\"" . get_permalink() . $concat . "page=2\">2</a>";
					$pagination .= "<span class=\"wpu-elipses\">...</span>";
					for ($counter = $lastpage - (1 + ($adjacents * 3)); $counter <= $lastpage; $counter++) {
						if ($counter == $page)
							$pagination .= "<span class=\"wpu-current\">$counter</span>";
						else
							$pagination .= "<a href=\"" . get_permalink() . $concat . "page=$counter\">$counter</a>";
					}
				}
			}

			//next button
			if ($page < $counter - 1)
				$pagination .= "<a href=\"" . get_permalink() . $concat . "page=$next\">next »</a>";
			else
				$pagination .= "<span class=\"wpu-disabled\">next »</span>";
			$pagination .= "</div>\n";
		}

		return $pagination;

	}

	function wpu_concat_index() {
		$url = $_SERVER['REQUEST_URI'];
		$permalink = get_permalink(get_the_id());

		if (strpos($permalink, '?'))
			return '&';
		else
			return '?';
	}

	function wpu_concat_single() {
		$url = $_SERVER['REQUEST_URI'];
		$permalink = get_permalink(get_the_id());

		if (strpos($permalink, '?'))
			return '&';
		else
			return '?';
	}

	function get_user_listing($curauth) {
		$concat = wpu_concat_single();

		$html .= "<div class=\"wpu-user\">\n";
		if (get_option('wpu_image_list')) {
			if (get_option('wpu_avatars') == "gravatars") {
				$gravatar_type = get_option('wpu_gravatar_type');
				$gravatar_size = get_option('wpu_gravatar_size');
				$display_gravatar = get_avatar($curauth->user_email, $gravatar_size, $gravatar_type);
				$html .= "<div class=\"wpu-avatar\"><a href=\"" . get_permalink($post->ID) . $concat . "uid=$curauth->ID\" title=\"$curauth->display_name\">$display_gravatar</a></div>\n";
			} elseif (get_option('wpu_avatars') == "userphoto") {
				if (function_exists('userphoto_the_author_photo')) {
					$html .= "<div class=\"wpu-avatar\"><a href=\"" . get_permalink($post->ID) . $concat . "uid=$curauth->ID\" title=\"$curauth->display_name\">" . userphoto__get_userphoto($curauth->ID, USERPHOTO_THUMBNAIL_SIZE, "", "", array(), "") . "</a></div>\n";
				}
			}
		}
		$html .= "<div class=\"wpu-id\"><a href=\"" . get_permalink($post->ID) . $concat . "uid=$curauth->ID\" title=\"$curauth->display_name\">$curauth->display_name</a></div>\n";
		if (get_option('wpu_description_list')) {
			if ($curauth->description) {
				if (get_option('wpu_description_limit')) {
					$desc_limit = get_option('wpu_description_limit');
					$html .= "<div class=\"wpu-about\">" . substr($curauth->description, 0, $desc_limit) . " [...]</div>\n";
				} else {
					$html .= "<div class=\"wpu-about\">" . $curauth->description . "</div>\n";
				}
			}
		}
		$html .= "</div>";
		return $html;
	}

	function display_user() {
		if (isset($_GET['uid'])) {
			$uid = $_GET['uid'];
			$curauth = get_userdata($uid);
		}

		$recent_posts = get_recent_posts($uid);
		$recent_comments = wpu_recent_comments($uid);
		$created = date("F jS, Y", strtotime($curauth->user_registered));

		$html .= "<p><a href=" . get_permalink($post->ID) . ">&laquo; Back to " . get_the_title($post->ID) . " page</a></p>\n";

		$html .= "<h2>$curauth->display_name</h2>\n";

		if (get_option('wpu_image_profile')) {
			if (get_option('wpu_avatars') == "gravatars") {
				$html .= "<p><a href=\"http://en.gravatar.com/\" title=\"Get your own avatar.\">" . get_avatar($curauth->user_email, '96', $gravatar) . "</a></p>\n";
			} elseif (get_option('wpu_avatars') == "userphoto") {
				if (function_exists('userphoto_the_author_photo')) {
					$html .= "<p>" . userphoto__get_userphoto($curauth->ID, USERPHOTO_FULL_SIZE, "", "", array(), "") . "</p>\n";
				}
			}
		}

		if ($curauth->user_url && $curauth->user_url != "http://") {
			$html .= "<p><strong>Website:</strong> <a href=\"$curauth->user_url\" rel=\"nofollow\">$curauth->user_url</a></p>\n";
		}

		$html .= "<p><strong>Joined on:</strong>  " . $created . "</p>";

		if (get_option('wpu_description_profile')) {
			if ($curauth->description) {
				$html .= "<p><strong>Profile:</strong></p>\n";
				$html .= "<p>$curauth->description</p>\n";
			}
		}

		if ($recent_posts) {
			$html .= "<h3>Recent Posts by $curauth->display_name</h3>\n";
			$html .= "<ul>\n";
			foreach ($recent_posts as $key => $post) {
				$html .= "<li><a href=" . get_permalink($post->ID) . ">" . $post->post_title . "</a></li>";
			}
			$html .= "</ul>\n";
		}

		if ($recent_comments) {
			$html .= "<h3>Recent Comments by $curauth->display_name</h3>\n";
			$html .= "<ul>\n";
			foreach ($recent_comments as $key => $comment) {
				$html .= "<li>\"" . $comment->comment_content . "\" on <a href=" . get_permalink($comment->comment_post_ID) . "#comment-" . $comment->comment_ID . ">" . get_the_title($comment->comment_post_ID) . "</a></li>";
			}
			$html .= "</ul>\n";
		}

		echo "<div id=\"wpu-profile\">
		";
		echo $html;
		echo "</div>
		";
	}

	function get_recent_posts($uid) {
		global $wpdb;

		$posts = $wpdb->get_results("SELECT post_title, ID
		FROM $wpdb->posts
		WHERE post_author = $uid AND post_type = 'post' AND post_status = 'publish'
		ORDER BY post_date DESC
		LIMIT 10
		");

		return $posts;
	}

	function wpu_recent_comments($uid) {
		global $wpdb;

		$comments = $wpdb->get_results("SELECT comment_ID, comment_post_ID, SUBSTRING(comment_content, 1, 150) AS comment_content
		FROM $wpdb->comments
		WHERE user_id = $uid
		ORDER BY comment_ID DESC
		LIMIT 10
		");

		return $comments;
	}

	function noindex_users() {
		if (is_page(get_option('wpu_page_id')) && get_option('wpu_noindex_users') == 'yes') {
			if ($_GET['uid'] == "")
				echo '	<meta name="robots" content="noindex, follow"/>
				';
		}
	}

	// 2.0 Feature
	function wpu_styles() {
		if (is_page(get_option('wpu_page_id'))) {
			echo '<link href="' . plugins_url() . '/wordpress-users/wpu-styles.css" rel="stylesheet" type="text/css" />
			';
		}
	}

	function wpu_admin() {
		$updated_wpu_options = false;
		if ($_POST['wpu_hidden'] == 'Y') {
			//Form data sent
			$pageid = $_POST['wpu_page_id'];
			update_option('wpu_page_id', $pageid);

			$usersperpage = $_POST['wpu_users_per'];
			update_option('wpu_users_per', $usersperpage);

			$avatars = $_POST['wpu_avatars'];
			update_option('wpu_avatars', $avatars);

			$gravatar_type = $_POST['wpu_gravatar_type'];
			update_option('wpu_gravatar_type', $gravatar_type);

			$gravatar_size = $_POST['wpu_gravatar_size'];
			update_option('wpu_gravatar_size', $gravatar_size);

			$noindex_users = $_POST['wpu_noindex_users'];
			update_option('wpu_noindex_users', $noindex_users);

			$roles_admin = $_POST['wpu_roles_admin'];
			update_option('wpu_roles_admin', $roles_admin);

			$roles_fic_site_owner = $_POST['wpu_roles_fic_site_owner'];
			update_option('wpu_roles_fic_site_owner', $roles_fic_site_owner);

			$roles_editor = $_POST['wpu_roles_editor'];
			update_option('wpu_roles_editor', $roles_editor);

			$roles_author = $_POST['wpu_roles_author'];
			update_option('wpu_roles_author', $roles_author);

			$roles_contributor = $_POST['wpu_roles_contributor'];
			update_option('wpu_roles_contributor', $roles_contributor);

			$roles_subscriber = $_POST['wpu_roles_subscriber'];
			update_option('wpu_roles_subscriber', $roles_subscriber);

			$image_list = $_POST['wpu_image_list'];
			update_option('wpu_image_list', $image_list);

			$description_list = $_POST['wpu_description_list'];
			update_option('wpu_description_list', $description_list);

			$image_profile = $_POST['wpu_image_profile'];
			update_option('wpu_image_profile', $image_profile);

			$description_profile = $_POST['wpu_description_profile'];
			update_option('wpu_description_profile', $description_profile);

			$desc_limit = $_POST['wpu_description_limit'];
			update_option('wpu_description_limit', $desc_limit);

			$updated_wpu_options = true;
		} else {
			//Normal page display
			$pageid = get_option('wpu_page_id');
			$usersperpage = get_option('wpu_users_per');
			$gravatar_type = get_option('wpu_gravatar_type');
			$gravatar_size = get_option('wpu_gravatar_size');
			$noindex_users = get_option('wpu_noindex_users');
			$image_list = get_option('wpu_image_list');
			$description_list = get_option('wpu_description_list');
			$image_profile = get_option('wpu_image_profile');
			$description_profile = get_option('wpu_description_profile');
			$desc_limit = get_option('wpu_description_limit');

			if (empty($usersperpage))
				$usersperpage = 10;
			if (get_option('wpu_avatars') == 1) {
				if (empty($gravatar_type))
					$gravatar_type = "mystery";
				if (empty($gravatar_size))
					$gravatar_size = 80;
			}
		}
		include_once(FIC_PLUGIN_ABS_PATH_DIR . '/views/admin/wpu-admin-options-form.php');
	}

	function FeFiction_the_permalink($the_data, $echo = true) {
		global $pagename;

		$new_permalink = str_replace('/' . CUSTOM_POST_TYPE . '/', 'F/fan_fiction_stories/', $the_data);

		if ($echo) {
			echo $new_permalink;
		} else {
			return $new_permalink;
		}
	}

	function FeFiction_the_excerpt($the_data, $echo = true) {
		if ($the_data == '') {
			add_filter('excerpt_more', 'FeFiction_remove_excerpt_continue_reading');
		} else {
			//add_filter( 'excerpt_more', 'FeFiction_change_excerpt_continue_reading');
		}
		if (isset($echo)) {
			the_excerpt();
		} else {
			ob_start();
			the_excerpt();
			$excerpt = ob_get_contents();
			ob_end_clean();
			return $excerpt;
		}
	}

	function FeFiction_remove_excerpt_continue_reading($output) {
		$output = '';
		return $output;
	}

	function FeFiction_change_excerpt_continue_reading($output) {
		$output = str_replace('&hellip;', '&hellip;<br />', str_replace(CUSTOM_POST_TYPE, FeFiction_Get_Page_Slug_Name() . '/story', $output));
		return $output;
	}

    function FeFiction_the_terms($post_ID, $name, $before, $separator, $after) {
        $the_term = str_replace($name, FeFiction_Get_Page_Slug_Name() . '/' . $name, get_the_term_list($post_ID, $name, $before, $separator, $after));

        if ($the_term == '') {
            echo $before . ' ' . __('N/A', 'fe-fiction');
        } else {
            echo $the_term;
        }
    }
    function FeFiction_the_terms_fandom($post_ID, $name, $before, $separator, $after) {
        $the_term = str_replace($name, 'fan-fiction/' . $name, get_the_term_list($post_ID, $name, $before, $separator, $after));

        if ($the_term == '') {
            echo $before . ' ' . __('N/A', 'fe-fiction');
        } else {
            echo $the_term;
        }
    }

	function FeFiction_the_metas($post_ID, $name, $display_before, $display_after, $echo = true) {
		$the_meta = get_post_meta($post_ID, FIC_POST_CUSTOM_FIELDS_PREFIX . $name, true);
		if ($the_meta == '') {
		}
		if ($echo) {
			echo $display_before . $the_meta . $display_after;

		} else {
			return $display_before . $the_meta . $display_after;
		}
	}

	function FeFiction_the_ID($echo = true) {
		ob_start();
		the_ID();
		$tid = ob_get_contents();
		ob_end_clean();

		if ($echo) {
			echo $tid;
		} else {
			return $tid;
		}
	}


    function fanfic_nofollowrobots(){
        ?>
            <meta name="robots" content="noindex,follow" />
        <?php
        if($paged>1){
            remove_action('genesis_meta','nofollowrobots');
            add_action('genesis_meta','fanfic_nofollowrobots');
        }
    }

	function FeFiction_Pagination($pages = '', $paged = 1, $range = 2, $search_or_story = 'search',$story_fandom='') {
		global $wp_query;

		$showitems = ($range * 2) + 1;

		if (empty($paged)) {
			$paged = 1;
		}

		if ($pages == '') {
			$pages = $wp_query->max_num_pages;
			if (!$pages) {
				$pages = 1;
			}
		}
		if ($pages <> 1) {
			echo "<div class=\"FeFiction_pagination\"><span>Page " . $paged . " of " . $pages . "</span>";
			if ($paged > 2 && $paged > $range + 1 && $showitems < $pages) {

                if(!is_numeric($story_fandom)){
                    echo "<a href='" . ($search_or_story == 'story' ? FeFiction_get_pagenum_link_current_story(1) : FeFiction_get_pagenum_link_fandom(1)) . "'>&laquo; First</a>";
                }else{
				echo "<a href='" . ($search_or_story == 'story' ? FeFiction_get_pagenum_link_current_story(1) : get_pagenum_link(1)) . "'>&laquo; First</a>";
		    	}
            }
			if ($paged > 1 && $showitems < $pages) {
                if(!is_numeric($story_fandom)){
                    echo "<a href='" . ($search_or_story == 'story' ? FeFiction_get_pagenum_link_current_story($paged - 1) : FeFiction_get_pagenum_link_fandom($paged - 1)) . "'>&lsaquo; Previous</a>";
                }else{
				echo "<a href='" . ($search_or_story == 'story' ? FeFiction_get_pagenum_link_current_story($paged - 1) : get_pagenum_link($paged - 1)) . "'>&lsaquo; Previous</a>";

                }
            }

			for ($i = 1; $i <= $pages; $i++) {
				if ($pages != 1 && (!($i >= $paged + $range + 1 || $i <= $paged - $range - 1) || $pages <= $showitems)) {
                    if(!is_numeric($story_fandom)){
                        echo ($paged == $i) ? "<span class=\"current\">" . $i . "</span>" : "<a href='" . ($search_or_story == 'story' ? FeFiction_get_pagenum_link_current_story($i) : FeFiction_get_pagenum_link_fandom($i)) . "' class=\"inactive\">" . $i . "</a>";
                    }else{
					echo ($paged == $i) ? "<span class=\"current\">" . $i . "</span>" : "<a href='" . ($search_or_story == 'story' ? FeFiction_get_pagenum_link_current_story($i) : get_pagenum_link($i)) . "' class=\"inactive\">" . $i . "</a>";
                    }
                }
			}

			if ($paged < $pages && $showitems < $pages) {
                if(!is_numeric($story_fandom)){
                    echo "<a href='" . ($search_or_story == 'story' ? FeFiction_get_pagenum_link_current_story($paged + 1) : FeFiction_get_pagenum_link_fandom($paged + 1)) . "'>Next &rsaquo;</a>";
                }else{
				echo "<a href=\"" . ($search_or_story == 'story' ? FeFiction_get_pagenum_link_current_story($paged + 1) : get_pagenum_link($paged + 1)) . "\">Next &rsaquo;</a>";

                }
            }
			if ($paged < $pages - 1 && $paged + $range - 1 < $pages && $showitems < $pages) {
                if(!is_numeric($story_fandom)){
                    echo "<a href='" . ($search_or_story == 'story' ? FeFiction_get_pagenum_link_current_story($pages) : FeFiction_get_pagenum_link_fandom($pages)) . "'>Last &raquo;</a>";
                }else{
				echo "<a href='" . ($search_or_story == 'story' ? FeFiction_get_pagenum_link_current_story($pages) : get_pagenum_link($pages)) . "'>Last &raquo;</a>";
                }
            }
			echo "</div>\n";
		}
	}
    function FeFiction_get_pagenum_link_fandom($pagenum = 1, $escape = true){
        global $wp_rewrite;
        $pagenum = (int) $pagenum;

        $request = remove_query_arg('paged');


            $result = add_query_arg('paged', $pagenum, $base . $request);
            $result = $result . $query_string;


        $result = apply_filters('get_pagenum_link', $result);

        if ($escape)
            return esc_url($result);
        else
            return esc_url_raw($result);
    }

	function FeFiction_get_pagenum_link_current_story($pagenum = 1, $escape = true) {
		global $wp_rewrite, $post;

		$pagenum = (int) $pagenum;

		$request = remove_query_arg('paged');

		$home_root = parse_url(home_url());
		$home_root = (isset($home_root['path'])) ? $home_root['path'] : '';
		$home_root = preg_quote($home_root, '|');

		$request = preg_replace('|^' . $home_root . '|', '', $request);
		$request = preg_replace('|^/+|', '', $request);
		$request = preg_replace('|' . $post->post_name . '(/[0-9]+/?)$|', $post->post_name, $request);



		if (!$wp_rewrite->using_permalinks() || is_admin()) {
			$base = trailingslashit(get_bloginfo('url'));

			if ($pagenum > 1) {
				$result = add_query_arg('paged', $pagenum, $base . $request);
			} else {
				$result = $base . $request;
			}
		} else {
			$qs_regex = '|\?.*?$|';
			preg_match($qs_regex, $request, $qs_match);

			if (!empty($qs_match[0])) {
				$query_string = $qs_match[0];
				$request = preg_replace($qs_regex, '', $request);
			} else {
				$query_string = '';
			}

			$request = preg_replace("|$wp_rewrite->pagination_base/\d+/?$|", '', $request);
			$request = preg_replace('|^index\.php|', '', $request);
			$request = ltrim($request, '/');

			$base = trailingslashit(get_bloginfo('url'));

			if ($wp_rewrite->using_index_permalinks() && ($pagenum > 1 || '' != $request))
				$base .= 'index.php/';

			if ($pagenum > 1) {
				$request = ((!empty($request)) ? trailingslashit($request) : $request) . user_trailingslashit($pagenum, 'paged');
			}

			$result = $base . $request . $query_string;
		}

		$result = apply_filters('get_pagenum_link', $result);

		if ($escape)
			return esc_url($result);
		else
			return esc_url_raw($result);
	}

	function FeFiction_Count_Story_Words($postid = 0, $string = '') {
		if ($postid > 0) {
			$content_post = get_post($postid);
			$string = $content_post->post_content;
			$string = str_replace(']]>', ']]>', $string);
		} elseif ($string != '') {
			$string = htmlspecialchars_decode(strip_tags($string));
		} else {
			return 0;
		}

		if (strlen($string) == 0)
			return 0;
		$t = array(' ' => 1, '_' => 1, "\x20" => 1, "\xA0" => 1, "\x0A" => 1, "\x0D" => 1, "\x09" => 1, "\x0B" => 1, "\x2E" => 1, "\t" => 1, '=' => 1, '+' => 1, '-' => 1, '*' => 1, '/' => 1, '\\' => 1, ',' => 1, '.' => 1, ';' => 1, ':' => 1, '"' => 1, '\'' => 1, '[' => 1, ']' => 1, '{' => 1,
				'}' => 1, '(' => 1, ')' => 1, '<' => 1, '>' => 1, '&' => 1, '%' => 1, '$' => 1, '@' => 1, '#' => 1, '^' => 1, '!' => 1, '?' => 1); // separators
		$count = isset($t[$string[0]]) ? 0 : 1;
		if (strlen($string) == 1)
			return $count;
		for ($i = 1; $i < strlen($string); $i++)
			if (isset($t[$string[$i - 1]]) && !isset($t[$string[$i]])) // if new word starts
				$count++;
		return $count;
	}
}






//hook into the init action and call create_book_taxonomies when it fires
add_action( 'init', 'create_topics_hierarchical_taxonomy', 0 );

//create a custom taxonomy name it topics for your posts

function create_topics_hierarchical_taxonomy() {

// Add new taxonomy, make it hierarchical like categories
//first do the translations part for GUI

  $labels = array(
    'name' => _x( 'Books', 'taxonomy general name' ),
    'singular_name' => _x( 'Book', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Books' ),
    'all_items' => __( 'All Books' ),
    'parent_item' => __( 'Parent Book' ),
    'parent_item_colon' => __( 'Parent Book:' ),
    'edit_item' => __( 'Edit Book' ),
    'update_item' => __( 'Update Book' ),
    'add_new_item' => __( 'Add New Book' ),
    'new_item_name' => __( 'New Book Name' ),
    'menu_name' => __( 'Books' ),
  );

// Now register the taxonomy

  register_taxonomy('books',array('fiction'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_admin_column' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'book' ),
	'capabilities' => array(

      'manage_terms'=> 'manage_books',
      'edit_terms'=> 'edit_books',
      'delete_terms'=> 'delete_books',
      'assign_terms' => 'read'
	)
  ));


}
add_action( 'wp_enqueue_style', 'post_story_style' );
function post_story_style()
    {
	wp_enqueue_style( 'custom_fanfic_css', plugins_url( '/custom_fanfic_css', __FILE__ ) );
	}
add_action( 'wp_enqueue_scripts', 'post_story_script' );
function post_story_script()
    {
    wp_enqueue_script( 'custom-script', plugins_url( '/js/custom-script.js', __FILE__ ));
	}

function fandomfilter($letter){ ?>


	<?php
	$storycatterms = get_terms( "story_category", array(
	'hide_empty' => 0,
	'parent' => 0,
) );

	$count = count($storycatterms);
	if($count>20){
	foreach ( $storycatterms as $storycatterm ){ ?>

		<?php
		$storycatterm_children = get_term_children($storycatterm->term_id,'story_category');
		$storycat_first = $storycatterm->name;

		if((strtoupper($storycat_first[0])==$letter)&&($letter!="NUM")){ ?>
			<input type="checkbox" name="storycatname[]" value="<?php echo $storycatterm->name; ?>" onclick="radiostorycat('<?php echo $storycatterm->name; ?>')"><?php echo $storycatterm->name; ?><br>

		<?php

    	foreach($storycatterm_children as $storycatterm_child_id) {
        $storycatterm_child = get_term_by('id',$storycatterm_child_id,'story_category'); ?>
        <input class="fe-class-fandom-input" type="checkbox" name="storycatname[]" value="<?php echo $storycatterm_child->name; ?>" onclick="radiostorycat('<?php echo $storycatterm_child->name;  ?>')"><?php echo $storycatterm_child->name;  ?><br>

    <?php


		}

			}else if((!preg_match("#^[a-zA-Z]+$#", $storycat_first[0]))&&($letter=="NUM")){?>
			<input type="checkbox" name="storycatname[]" value="<?php echo $storycatterm->name; ?>" onclick="radiostorycat('<?php echo $storycatterm->name; ?>')"><?php echo $storycatterm->name; ?><br>

		<?php

    	foreach($storycatterm_children as $storycatterm_child_id) {
        $storycatterm_child = get_term_by('id',$storycatterm_child_id,'story_category'); ?>
        <input class="fe-class-fandom-input" type="checkbox" name="storycatname[]" value="<?php echo $storycatterm_child->name; ?>" onclick="radiostorycat('<?php echo $storycatterm_child->name;  ?>')"><?php echo $storycatterm_child->name;  ?><br>

    <?php


		}


				}else{
    	foreach($storycatterm_children as $storycatterm_child_id) {
        $storycatterm_child = get_term_by('id',$storycatterm_child_id,'story_category');
		$storycatfirstletter = $storycatterm_child->name;
		if((strtoupper($storycatfirstletter[0])==$letter)&&($letter!="NUM")){
		$a[] = $storycatterm_child->name;
				}else if((!preg_match("#^[a-zA-Z]+$#", $storycatfirstletter[0]))&&($letter=="NUM")){
					$b[] = $storycatterm_child->name;
					}
			}

			if(count($a)>0){


		?>
        <input type="checkbox" name="storycatname[]" value="<?php echo $storycatterm->name; ?>" onclick="radiostorycat('<?php echo $storycatterm->name; ?>')"><?php echo $storycatterm->name; ?><br>
        <?php for($i=0;$i<count($a);$i++){?>
        <input class="fe-class-fandom-input" type="checkbox" name="storycatname[]" value="<?php echo $a[$i]; ?>" onclick="radiostorycat('<?php echo $a[$i];  ?>')"><?php echo $a[$i];  ?><br>

    <?php

				}


				unset($a);
				$a = array();

			}

			if(count($b)>0){


		?>
        <input type="checkbox" name="storycatname[]" value="<?php echo $storycatterm->name; ?>" onclick="radiostorycat('<?php echo $storycatterm->name; ?>')"><?php echo $storycatterm->name; ?><br>
        <?php for($i=0;$i<count($b);$i++){?>
        <input class="fe-class-fandom-input" type="checkbox" name="storycatname[]" value="<?php echo $b[$i]; ?>" onclick="radiostorycat('<?php echo $b[$i];  ?>')"><?php echo $b[$i];  ?><br>

    <?php

				}


				unset($b);
				$b = array();

			}



		} }

	}else{


		foreach ( $storycatterms as $storycatterm ){ ?>

		<?php
		$storycatterm_children = get_term_children($storycatterm->term_id,'story_category');
		$storycat_first = $storycatterm->name;

		 ?>
			<input type="checkbox" name="storycatname[]" value="<?php echo $storycatterm->name; ?>" onclick="radiostorycat('<?php echo $storycatterm->name; ?>')"><?php echo $storycatterm->name; ?><br>

		<?php

    	foreach($storycatterm_children as $storycatterm_child_id) {
        $storycatterm_child = get_term_by('id',$storycatterm_child_id,'story_category'); ?>
        <input class="fe-class-fandom-input" type="checkbox" name="storycatname[]" value="<?php echo $storycatterm_child->name; ?>" onclick="radiostorycat('<?php echo $storycatterm_child->name;  ?>')"><?php echo $storycatterm_child->name;  ?><br>

    <?php


		}


		}





    }

}


function cfandomfilter($cletter){

	$pairingterms = get_terms( "pairings", array(
	'hide_empty' => 0,
	'parent' => 0,
) );
	$count = count($pairingterms);
	if($count>20){

	foreach ( $pairingterms as $pairingterm ){ ?>

		<?php
		$pairingterm_children = get_term_children($pairingterm->term_id,'pairings');
		$pairingterm_first = $pairingterm->name;
		if((strtoupper($pairingterm_first[0])==$cletter)&&($cletter!="NUM")){ ?>
			<input type="checkbox" name="pairingname[]" value="<?php echo $pairingterm->name; ?>" onclick="radiostorycat('<?php echo $pairingterm->name; ?>')"><?php echo $pairingterm->name; ?><br>

		<?php

    	foreach($pairingterm_children as $pairingterm_child_id) {
        $pairingterm_child = get_term_by('id',$pairingterm_child_id,'pairings'); ?>
        <input class="fe-class-fandom-input" type="checkbox" name="pairingname_child[]" value="<?php echo $pairingterm_child->name; ?>" onclick="radiostorycat('<?php echo $pairingterm_child->name;  ?>')"><?php echo $pairingterm_child->name;  ?><br>

    <?php


		}

			}else if((!preg_match("#^[a-zA-Z]+$#", $pairingtermfirstletter[0]))&&($cletter=="NUM")){?>
			<input type="checkbox" name="pairingname[]" value="<?php echo $pairingterm->name; ?>" onclick="radiostorycat('<?php echo $pairingterm->name; ?>')"><?php echo $pairingterm->name; ?><br>

		<?php

    	foreach($pairingterm_children as $pairingterm_child_id) {
        $pairingterm_child = get_term_by('id',$pairingterm_child_id,'pairings'); ?>
        <input class="fe-class-fandom-input" type="checkbox" name="pairingname_child[]" value="<?php echo $pairingterm_child->name; ?>" onclick="radiostorycat('<?php echo $pairingterm_child->name;  ?>')"><?php echo $pairingterm_child->name;  ?><br>

    <?php


		}


				}else{
    	foreach($pairingterm_children as $pairingterm_child_id) {
        $pairingterm_child = get_term_by('id',$pairingterm_child_id,'pairings');
		$pairingtermfirstletter = $pairingterm_child->name;
		if((strtoupper($pairingtermfirstletter[0])==$cletter)&&($cletter!="NUM")){
		$a[] = $pairingterm_child->name;
				}else if((!preg_match("#^[a-zA-Z]+$#", $pairingtermfirstletter[0]))&&($cletter=="NUM")){
					$b[] = $pairingterm_child->name;
					}
			}

			if(count($a)>0){


		?>
        <input type="checkbox" name="pairingname[]" value="<?php echo $pairingterm->name; ?>" onclick="radiostorycat('<?php echo $pairingterm->name; ?>')"><?php echo $pairingterm->name; ?><br>
        <?php for($i=0;$i<count($a);$i++){?>
        <input class="fe-class-fandom-input" type="checkbox" name="pairingname[]" value="<?php echo $a[$i]; ?>" onclick="radiostorycat('<?php echo $a[$i];  ?>')"><?php echo $a[$i];  ?><br>

    <?php

				}


				unset($a);
				$a = array();

			}

			if(count($b)>0){


		?>
        <input type="checkbox" name="pairingname[]" value="<?php echo $pairingterm->name; ?>" onclick="radiostorycat('<?php echo $pairingterm->name; ?>')"><?php echo $pairingterm->name; ?><br>
        <?php for($i=0;$i<count($b);$i++){?>
        <input class="fe-class-fandom-input" type="checkbox" name="pairingname[]" value="<?php echo $b[$i]; ?>" onclick="radiostorycat('<?php echo $b[$i];  ?>')"><?php echo $b[$i];  ?><br>

    <?php

				}


				unset($b);
				$b = array();

			}



		} }?>
        <?php
	}else{

		foreach ( $pairingterms as $pairingterm ){ ?>

		<?php
		$pairingterm_children = get_term_children($pairingterm->term_id,'pairings');
		$pairingterm_first = $pairingterm->name; ?>

			<input type="checkbox" name="pairingname[]" value="<?php echo $pairingterm->name; ?>" onclick="radiostorycat('<?php echo $pairingterm->name; ?>')"><?php echo $pairingterm->name; ?><br>

		<?php

    	foreach($pairingterm_children as $pairingterm_child_id) {
        $pairingterm_child = get_term_by('id',$pairingterm_child_id,'pairings'); ?>
        <input class="fe-class-fandom-input" type="checkbox" name="pairingname_child[]" value="<?php echo $pairingterm_child->name; ?>" onclick="radiostorycat('<?php echo $pairingterm_child->name;  ?>')"><?php echo $pairingterm_child->name;  ?><br>

    <?php


		}

		 }?>
        <?php
		}

}




add_shortcode('post_story', 'post_story_function');
function post_story_function(){
	if ( is_user_logged_in() ) { ?>
		<div id="post-story-wrap">
<?php    // Check to see if correct form is being submitted, if so continue to process



	echo '
		<form action="" method="post" id="inputtypeselectfic">
			<input type="radio" name="posttype" value="1" id="posttypestory" onclick="selectstory()">Posting a One-shot story?
			<input type="radio" name="posttype" value="2" id="posttypebook" onclick="selectbook()">Working on a book?


		</form>
	';
    bookss();

?>

	<div id="frontendform" >

    <script type="text/javascript">
		function validateForm()
		{
		var x=document.forms["poststory"]["post_title"].value;
		var y=document.forms["poststory"]["rating_name_input"].value;

		if (x==null || x=="")
		  {
		  alert("Story Rating, Fandom, Title must be filled out");
		  return false;
		  }
		if (y==null || y=="")
		  {
		  alert("Story Rating, Fandom must be filled out");
		  return false;
		  }
		var chks = document.getElementsByName('storycatname[]');
		var hasChecked = false;
		for (var i = 0; i < chks.length; i++)
			{
			if (chks[i].checked)
				{
				hasChecked = true;
				break;
				}
			}
			if (hasChecked == false)
			{
			alert("Story Rating, Fandom must be filled out");
			return false;
			}
			return true;
		}


	</script>
    <!-- Title & content -->
	<form method="post" action="" id="poststory" onsubmit="return validateForm()">
    <label id="postingtitlechapter" class="postingtitle">Chapter Title</label>
	<label id="postingtitlestory" class="postingtitle">Story Title</label>
    <input type="text" name="post_title" size="45" id="input-title" />
    <!-- <?php wp_dropdown_categories('orderby=name&hide_empty=0&exclude=1&hierarchical=1'); ?> -->
    <label>Content</label>
    <?php wp_editor(
        $post_obj->post_content,
        'userpostcontent',
        array( 'textarea_name' => 'post_content',
            'media_buttons' => false
        )
    )  ?>
    <div class="fe-frontend-div">

    <h3><strong>Detail</strong></h3>
    <table width="100%"  id="chapternumberdesc">
    <tr id="chapternumber"><td>
    <label>Chapter Number</label>
    </td><td>
    <input type="text" size="5" name="chapter_number" />
    </td></tr>
    <tr><td></td><td class="chapternumberdesc-td">This can be any value but cannot exceed 5 characters<br />
	Only needed if this is a book.</td></tr>
    <tr><td>
    <label>Author Notes</label>
    </td><td>
    <input type="text" class="chapternumberdesc-input" name="author_notes_chap" />
    </td></tr>
    <tr><td></td><td class="chapternumberdesc-td">Any information relevant to this chapter. Any notes you would like to add.</td></tr>
    <tr><td valign="top">
    <label>Summary</label>
    </td><td>
    <textarea rows="5" cols="40" name="summary_chap" class="chapternumberdesc-input"></textarea>
    </td></tr>
    <tr><td></td><td class="chapternumberdesc-td">Chapter Summary (optional). If you do not fill this in, we will create a summary from your chapter.</td></tr>
    </table>
    <table width="100%"  id="storynumberdesc">
    <tr><td>
    <label>Author Notes</label>
    </td><td>
    <input type="text" class="chapternumberdesc-input" name="author_notes" />
    </td></tr>
    <tr><td></td><td class="chapternumberdesc-td">Any information relevant to this story. Any notes you would like to add.</td></tr>
    <tr><td valign="top">
    <label>Summary</label>
    </td><td>
    <textarea rows="5" cols="40" name="summary" class="chapternumberdesc-input"></textarea>
    </td></tr>
    <tr><td></td><td class="chapternumberdesc-td">Story Summary (optional). If you do not fill this in, we will create a summary from your story.</td></tr>
    </table>

    </div>
    <input type="hidden" name="new_post" value="1"/>
    <input type="hidden" name="book_name_input" id="book_name_input" />
    <input type="hidden" name="genre_name_input" id="genre_name_input" />
    <input type="hidden" name="pairing_name_input" id="pairing_name_input" />
    <input type="hidden" name="rating_name_input" id="rating_name_input" />
    <input type="hidden" name="storycat_name_input" id="storycat_name_input" />
	<!-- Title & content -->

    <div class="fe-frontend-select-div"><h3><strong>Select Fandoms</strong></h3></div>
    <div id="filteringtabs">
    <?php $storycatterms = get_terms( "story_category", array(
	'hide_empty' => 0,
	'parent' => 0,
) );

	$count = count($storycatterms);
	if($count>20){?>
    <div class="filtertabs filtertabsselect" onclick="jfandomfilter('A')" id="filtertabA">A</div>
    <div class="filtertabs" onclick="jfandomfilter('B')" id="filtertabB">B</div>
    <div class="filtertabs" onclick="jfandomfilter('C')" id="filtertabC">C</div>
    <div class="filtertabs" onclick="jfandomfilter('D')" id="filtertabD">D</div>
    <div class="filtertabs" onclick="jfandomfilter('E')" id="filtertabE">E</div>
    <div class="filtertabs" onclick="jfandomfilter('F')" id="filtertabF">F</div>
    <div class="filtertabs" onclick="jfandomfilter('G')" id="filtertabG">G</div>
    <div class="filtertabs" onclick="jfandomfilter('H')" id="filtertabH">H</div>
    <div class="filtertabs" onclick="jfandomfilter('I')" id="filtertabI">I</div>
    <div class="filtertabs" onclick="jfandomfilter('J')" id="filtertabJ">J</div>
    <div class="filtertabs" onclick="jfandomfilter('K')" id="filtertabK">K</div>
    <div class="filtertabs" onclick="jfandomfilter('L')" id="filtertabL">L</div>
    <div class="filtertabs" onclick="jfandomfilter('M')" id="filtertabM">M</div>
    <div class="filtertabs" onclick="jfandomfilter('N')" id="filtertabN">N</div>
    <div class="filtertabs" onclick="jfandomfilter('O')" id="filtertabO">O</div>
    <div class="filtertabs" onclick="jfandomfilter('P')" id="filtertabP">P</div>
    <div class="filtertabs" onclick="jfandomfilter('Q')" id="filtertabQ">Q</div>
    <div class="filtertabs" onclick="jfandomfilter('R')" id="filtertabR">R</div>
    <div class="filtertabs" onclick="jfandomfilter('S')" id="filtertabS">S</div>
    <div class="filtertabs" onclick="jfandomfilter('T')" id="filtertabT">T</div>
    <div class="filtertabs" onclick="jfandomfilter('U')" id="filtertabU">U</div>
    <div class="filtertabs" onclick="jfandomfilter('V')" id="filtertabV">V</div>
    <div class="filtertabs" onclick="jfandomfilter('W')" id="filtertabW">W</div>
    <div class="filtertabs" onclick="jfandomfilter('X')" id="filtertabX">X</div>
    <div class="filtertabs" onclick="jfandomfilter('Y')" id="filtertabY">Y</div>
    <div class="filtertabs" onclick="jfandomfilter('Z')" id="filtertabZ">Z</div>
    <div class="filtertabs" onclick="jfandomfilter('NUM')" id="filtertabNUM">NUM</div>
    <div class="fe-frontend-clear"></div>
    <?php }else{}?>
    </div>
    <div id="fandoms">
		<div id="filterforA" class="activefandom">
    		<?php fandomfilter(A);?>
		</div>
        <div id="filterforB">
    		<?php fandomfilter(B);?>
		</div>
        <div id="filterforC">
    		<?php fandomfilter(C);?>
		</div>
        <div id="filterforD">
    		<?php fandomfilter(D);?>
		</div>
        <div id="filterforE">
    		<?php fandomfilter(E);?>
		</div>
        <div id="filterforF">
    		<?php fandomfilter(F);?>
		</div>
        <div id="filterforG">
    		<?php fandomfilter(G);?>
		</div>
        <div id="filterforH">
    		<?php fandomfilter(H);?>
		</div>
        <div id="filterforI">
    		<?php fandomfilter(I);?>
		</div>
        <div id="filterforJ">
    		<?php fandomfilter(J);?>
		</div>
        <div id="filterforK">
    		<?php fandomfilter(K);?>
		</div>
        <div id="filterforL">
    		<?php fandomfilter(L);?>
		</div>
        <div id="filterforM">
    		<?php fandomfilter(M);?>
		</div>
        <div id="filterforN">
    		<?php fandomfilter(N);?>
		</div>
        <div id="filterforO">
    		<?php fandomfilter(O);?>
		</div>
        <div id="filterforP">
    		<?php fandomfilter(P);?>
		</div>
        <div id="filterforQ">
    		<?php fandomfilter(Q);?>
		</div>
        <div id="filterforR">
    		<?php fandomfilter(R);?>
		</div>
        <div id="filterforS">
    		<?php fandomfilter(S);?>
		</div>
        <div id="filterforT">
    		<?php fandomfilter(T);?>
		</div>
        <div id="filterforU">
    		<?php fandomfilter(U);?>
		</div>
        <div id="filterforV">
    		<?php fandomfilter(V);?>
		</div>
        <div id="filterforW">
    		<?php fandomfilter(W);?>
		</div>
        <div id="filterforX">
    		<?php fandomfilter(X);?>
		</div>
        <div id="filterforY">
    		<?php fandomfilter(Y);?>
		</div>
        <div id="filterforZ">
    		<?php fandomfilter(Z);?>
		</div>
        <div id="filterforNUM">
    		<?php fandomfilter(NUM);?>
		</div>
    </div>
    <div class="fe-frontend-border-div">
    <h3><strong>Select Story Rating</strong></h3>
    <?php
	$ratingterms = get_terms( "rating", array(
	'hide_empty' => 0,
) );
	foreach ( $ratingterms as $ratingterm ){ ?>
		<input type="radio" name="ratingname" value="<?php echo $ratingterm->name; ?>" onclick="radiorating('<?php echo $ratingterm->name; ?>')"><img src="<?php echo plugins_url();?>/wp-fanfiction-and-writing-archive-basic/views/images/<?php echo strtolower($ratingterm->name); ?>_rating.gif" class="fe-frontend-gif" /><br>
	
    	<?php }
	?>
	
    </div>
        
        
    <div id="genrehide1">
	<?php genres();?>
    </div>
    <div id="charhide">
    <?php  char()?>
	</div>
	<div class="fe-frontend-clear"></div>
		
		
<?php		
if(isset($_POST['book_name_input'])) {
    $post_title = $_POST['post_title'];
    $post_category = $_POST['cat'];
    $post_content = FeFiction_Create_Pages_From_Content($_POST['post_content']);

    $new_post = array(
          'ID' => '',
          'post_author' => $user->ID, 
          'post_type' => 'fiction',
          'post_category' => array($post_category),
          'post_content' => $post_content, 
          'post_title' => $post_title,
          'post_status' => 'publish'
        );
    remove_all_filters('publish_fiction');
    $post_id = wp_insert_post($new_post);
	$postbooks = $_POST['book_name_input'];
	wp_set_object_terms($post_id,$postbooks,'books');
	$postpairing = $_POST['pairing_name_input'];
	wp_set_object_terms($post_id,(array)$_POST['pairingname'],'pairings',true);
	wp_set_object_terms($post_id,(array)$_POST['pairingname_child'],'pairings',true);
	$postgenre = $_POST['genre_name_input'];
	wp_set_object_terms($post_id,(array)$_POST['genrename'],'genre',true);
	wp_set_object_terms($post_id,(array)$_POST['genrename_child'],'genre',true);
	$postgenre = $_POST['rating_name_input'];
	wp_set_object_terms($post_id,$postgenre,'rating');
	$poststorycat = $_POST['storycat_name_input'];
	wp_set_object_terms($post_id,(array)$_POST['storycatname'],'story_category',true);
	if($_POST['summary']==''){
		$postsummary = $_POST['summary_chap'];
		}else{
		$postsummary = $_POST['summary'];}
	if($_POST['author_notes']==''){
		$postauthornotes = $_POST['author_notes_chap'];
		}else{
		$postauthornotes = $_POST['author_notes'];}
	add_post_meta($post_id,FIC_POST_CUSTOM_FIELDS_PREFIX . 'chapter_number',$_POST['chapter_number']);
	add_post_meta($post_id,FIC_POST_CUSTOM_FIELDS_PREFIX . 'chapter_title',$_POST['chapter_title']);
	add_post_meta($post_id,FIC_POST_CUSTOM_FIELDS_PREFIX . 'author_notes',$postauthornotes);
	add_post_meta($post_id,FIC_POST_CUSTOM_FIELDS_PREFIX . 'summary',$postsummary);
    // This will redirect you to the newly created post
    $post = get_post($post_id);
	$shortlink = wp_get_shortlink($post_id);
    $userid = $post->post_author;
    $author_name = get_userdata($userid);
    $authorname = $author_name->display_name;
    $fandoms = $_POST['storycatname'];
    $fandom = $fandoms[0];
    $directlink = FeFiction_the_permalink(post_permalink());
    wp_redirect($shortlink, 301);
    exit;
}      
?>      


    <div class="fe-frontend-publish">
    <input type="submit" name="submit" value="Publish" id="publishfrontend"/>
	</div>
    </form>
    
    
    
    </div>
    
    
    
    </div>
<?php
	} else {
		echo 'Welcome, visitor!';
	}
	?>
    
	
<?php }

// Add term page
function books_taxonomy_add_new_meta_field() {
	// this will add the custom meta field to the add new term page
	?>
	<div class="form-field">
		<label for="term_meta[custom_term_meta]">Enter user_id (Your user_id =<?php echo get_current_user_id(); ?>) </label>
		<input type="text" name="term_meta[custom_term_meta]" id="term_meta[custom_term_meta]" value="<?php echo get_current_user_id(); ?>">
	</div>
<?php
}
add_action( 'books_add_form_fields', 'books_taxonomy_add_new_meta_field', 10, 2 );
?>
<?php
// Edit term page
function books_taxonomy_edit_meta_field($term) {
 
	// put the term ID into a variable
	$t_id = $term->term_id;
 
	// retrieve the existing value(s) for this meta field. This returns an array
	$term_meta = get_option( "taxonomy_$t_id" ); ?>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[custom_term_meta]">Enter User_id</label></th>
		<td>
			<input type="text" name="term_meta[custom_term_meta]" id="term_meta[custom_term_meta]" value="<?php echo esc_attr( $term_meta['custom_term_meta'] ) ? esc_attr( $term_meta['custom_term_meta'] ) : ''; ?>">
		</td>
	</tr>
<?php
}
add_action( 'books_edit_form_fields', 'books_taxonomy_edit_meta_field', 10, 2 );
// Save extra taxonomy fields callback function.
function save_taxonomy_custom_meta( $term_id ) {
	if ( isset( $_POST['term_meta'] ) ) {
		$t_id = $term_id;
		$term_meta = get_option( "taxonomy_$t_id" );
		$cat_keys = array_keys( $_POST['term_meta'] );
		foreach ( $cat_keys as $key ) {
			if ( isset ( $_POST['term_meta'][$key] ) ) {
				$term_meta[$key] = $_POST['term_meta'][$key];
			}
		}
		// Save the option array.
		update_option( "taxonomy_$t_id", $term_meta );
	}
}  
add_action( 'edited_books', 'save_taxonomy_custom_meta', 10, 2 );  
add_action( 'create_books', 'save_taxonomy_custom_meta', 10, 2 );

function disallowed_admin_pages(){
    global $pagenow;
    /* Check current admin page. */
    if($pagenow == 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'fiction'){
        wp_redirect(site_url('/'.get_option(FIC_OPTION_FICTION_POSTING_PAGE), 'http'), 301);
        exit;
    }
}
add_action('admin_init', ('disallowed_admin_pages'));


function bookss(){
		// Get a list of all terms in a taxonomy

    echo '<div id="bookslist"><h3><strong>Select Book</strong></h3><br>';
	echo '<div>';
	$terms = get_terms( "books", array(
	'hide_empty' => 0,
	) );
	foreach ( $terms as $term ){ 
	$term_meta = get_option('taxonomy_' . $term->term_id);
	if(get_current_user_id()==$term_meta['custom_term_meta']){
	?>
		<input type="radio" name="bookname" value="<?php echo $term->name; ?>" onclick="radiobookname('<?php echo $term->name; ?>')"><?php echo $term->name; ?><br>
	<?php } }?>
    </div>
	<form id="insert_term" name="insert_term" method="post" action=""> 
	<br />
    <input type="text" value="" name="term" id="term" /> 
    <input type="submit" value="Add a New Book" id="submit" name="submit" />
    <input type="hidden" name="action" value="new_term" />
    <input type="hidden" name="term_meta[custom_term_meta]" value="<?php echo get_current_user_id();?>" />
	<input type="hidden" name="action" value="addBook"/>

	</form>
<?php	
	echo '</div>'; ?>
	<script type="text/javascript">
						jQuery('#insert_term').submit(ajaxSubmit); 
						
						function ajaxSubmit(){
							
							var newCustomerForm = jQuery(this).serialize();
							
							jQuery.ajax({
								type:"POST",
								url: "<?php echo admin_url('admin-ajax.php'); ?>",
								data: newCustomerForm,
								success:function(data){
									jQuery('#bookslist>div').slideUp();
					                jQuery("#bookslist").html(data);
					   			},
					            error: function(errorThrown){
					                alert(errorThrown);
					            }	
							});
							
							return false;
						}
					</script>
<?php	}
function addBook(){
	
	// Check to see if input field for new term is set 
    	if (isset ($_POST['term'])) {

        // If set, stores user input in variable
        $new_term =  $_POST['term'];
		
        // Function to handle inserting of new term into taxonomy
        $book_id = wp_insert_term(

          // The term (user input)
          $new_term,

          // The club taxonomy
          'books'  

        );
		
		$t_id = $book_id->term_id;
		$term_meta = get_option( "taxonomy_$t_id" );
		$cat_keys = array_keys( $_POST['term_meta'] );
		foreach ( $cat_keys as $key ) {
			if ( isset ( $_POST['term_meta'][$key] ) ) {
				$term_meta[$key] = $_POST['term_meta'][$key];
			}
		}
		// Save the option array.
		update_option( "taxonomy_$t_id", $term_meta );

		}
		$terms = get_terms( "books", array(
	'hide_empty' => 0,
	) );
	echo '<h3><strong>Select Book</strong></h3><br><div>';
	foreach ( $terms as $term ){ 
	$term_meta = get_option('taxonomy_' . $term->term_id);
	if(get_current_user_id()==$term_meta['custom_term_meta']){
	?>
		<input type="radio" name="bookname" value="<?php echo $term->name; ?>" onclick="radiobookname('<?php echo $term->name; ?>')"><?php echo $term->name; ?><br>
	<?php } }?>
    </div>
	<form id="insert_term" name="insert_term" method="post" action=""> 
	<br />
    <input type="text" value="" name="term" id="term" /> 
    <input type="submit" value="Add Chapter" id="submit" name="submit" />
    <input type="hidden" name="action" value="new_term" />
    <input type="hidden" name="term_meta[custom_term_meta]" value="<?php echo get_current_user_id();?>" />
	<input type="hidden" name="action" value="addBook"/>

	</form>
<?php	die();	
}
add_action('wp_ajax_addBook', 'addBook');
add_action('wp_ajax_nopriv_addBook', 'addBook');




function genres(){ ?>

    <div class="genrehide" id="genrehide"><h3><strong>Select Genres</strong></h3></div>
	<div class="fe-frontend-genrediv">
	<?php
	$genreterms = get_terms( "genre", array(
	'hide_empty' => 0,
	'parent' => 0,
) ); 
	foreach ( $genreterms as $genreterm ){ ?>
		<input type="checkbox" name="genrename[]" value="<?php echo $genreterm->name; ?>" onclick="radiogenre('<?php echo $genreterm->name; ?>')"><?php echo $genreterm->name; ?><br>
		<?php  $genreterm_children = get_term_children($genreterm->term_id,'genre');
    foreach($genreterm_children as $genreterm_child_id) {
        $genreterm_child = get_term_by('id',$genreterm_child_id,'genre'); ?>
        <input class="fe-class-fandom-input" type="checkbox" name="genrename_child[]" value="<?php echo strtolower($genreterm_child->name); ?>" onclick="radiogenre('<?php echo $genreterm_child->name; ?>')"><?php echo $genreterm_child->name; ?><br>
    <?php }
		
		
		} ?>
		</div>
    <div class="addnew">
        <br />
        <select name="genreparent" id="genreparent">
        <option value="0"> ----- Parent Genre -----</option>
        <?php foreach ( $genreterms as $genreterm ){ ?>
		<option value="<?php echo $genreterm->term_id; ?>"><?php echo $genreterm->name; }?></option>
        </select>
        <input type="text" value="" name="term_genre" id="term_genre" /> 
        <button id="genrebutton">Add Genre</button>
        <input type="hidden" name="action" value="addGenre"/>
    
    
    
    </div>
    
    <script type="text/javascript">
						jQuery('#genrebutton').click(ajaxSubmitgenre); 
						
						function ajaxSubmitgenre(){
							
							var newCustomerFormgenre = jQuery('#term_genre').val();
							var genreparent = jQuery('#genreparent').val();
							jQuery.ajax({
								type:"POST",
								url: "<?php echo admin_url('admin-ajax.php'); ?>",
								 data: {
											'action':'addGenre',
											'term_genre' : newCustomerFormgenre,
											'genre_parent':genreparent
										},
								success:function(data){
									jQuery('#genrehide>div').slideUp();
					                jQuery("#genrehide").html(data);
					   			},
					            error: function(errorThrown){
					                alert(errorThrown);
					            }	
							});
							
							return false;
							
							
						}
						
						
						
						
					</script>
    
    
<?php	}
function addGenre(){
// Check to see if input field for new term is set 
    	if (isset ($_POST['term_genre'])) {

        // If set, stores user input in variable
        $new_term =  $_POST['term_genre'];
        $new_pterm =  $_POST['genre_parent'];
		if($new_pterm == "" || $new_pterm == "0"){
			$genre_id = wp_insert_term(

          // The term (user input)
          $new_term,

          // The club taxonomy
          'genre'

        );
			
			}else{
		
        // Function to handle inserting of new term into taxonomy
        $genre_id = wp_insert_term(

          // The term (user input)
          $new_term,

          // The club taxonomy
          'genre',
		  array("parent" => $new_pterm)

        );
		
		delete_option("genre_children");
			}
		} ?>
        
    <div class="genrehide" id="genrehide"><h3><strong>Select Genres</strong></h3></div>
<div class="fe-frontend-genrediv">
	<?php
	$genreterms = get_terms( "genre", array(
	'hide_empty' => 0,
	'parent' => 0,
) ); 
	foreach ( $genreterms as $genreterm ){ ?>
		<input type="checkbox" name="genrename[]" value="<?php echo $genreterm->name; ?>" onclick="radiogenre('<?php echo $genreterm->name; ?>')"><?php echo $genreterm->name; ?><br>
		<?php  $genreterm_children = get_term_children($genreterm->term_id,'genre');
    foreach($genreterm_children as $genreterm_child_id) {
        $genreterm_child = get_term_by('id',$genreterm_child_id,'genre'); ?>
        <input class="fe-class-fandom-input" type="checkbox" name="genrename[]" value="<?php echo strtolower($genreterm_child->name); ?>" onclick="radiogenre('<?php echo $genreterm_child->name; ?>')"><?php echo $genreterm_child->name; ?><br>
    <?php }
		
		
		} ?>
		</div>
    <div class="addnew">
        <br />
        <select name="genreparent" id="genreparent">
        <option value="0"> ----- Parent Genre -----</option>
        <?php foreach ( $genreterms as $genreterm ){ ?>
		<option value="<?php echo $genreterm->term_id; ?>"><?php echo $genreterm->name; }?></option>
        </select>
        <input type="text" value="" name="term_genre" id="term_genre" /> 
        <button id="genrebutton">Add Genre</button>
        <input type="hidden" name="action" value="addGenre"/>
    
    
    </div><script type="text/javascript">
						jQuery('#genrebutton').click(ajaxSubmitgenre); 
						
						function ajaxSubmitgenre(){
							
							var newCustomerFormgenre = jQuery('#term_genre').val();
							var genreparent = jQuery('#genreparent').val();
							jQuery.ajax({
								type:"POST",
								url: "<?php echo admin_url('admin-ajax.php'); ?>",
								 data: {
											'action':'addGenre',
											'term_genre' : newCustomerFormgenre,
											'genre_parent':genreparent
										},
								success:function(data){
									jQuery('#genrehide>div').slideUp();
					                jQuery("#genrehide").html(data);
					   			},
					            error: function(errorThrown){
					                alert(errorThrown);
					            }	
							});
							
							return false;
							
							
						}
						
						
						
						
					</script>
<?php	die();	
}
add_action('wp_ajax_addGenre', 'addGenre');
add_action('wp_ajax_nopriv_addGenre', 'addGenre');




function char(){ ?>

    <div class="genrehide"><h3><strong>Select Characters</strong></h3></div>
    <?php $pairingterms = get_terms( "pairings", array(
	'hide_empty' => 0,
	'parent' => 0,
	) ); 
	$count = count($pairingterms);?>
	<?php if($count>20){ ?>
    <div id="cfilteringtabs">
        <div class="filtertabs filtertabsselect" onclick="cjfandomfilter('A')" id="cfiltertabA">A</div>
        <div class="filtertabs" onclick="cjfandomfilter('B')" id="cfiltertabB">B</div>
        <div class="filtertabs" onclick="cjfandomfilter('C')" id="cfiltertabC">C</div>
        <div class="filtertabs" onclick="cjfandomfilter('D')" id="cfiltertabD">D</div>
        <div class="filtertabs" onclick="cjfandomfilter('E')" id="cfiltertabE">E</div>
        <div class="filtertabs" onclick="cjfandomfilter('F')" id="cfiltertabF">F</div>
        <div class="filtertabs" onclick="cjfandomfilter('G')" id="cfiltertabG">G</div>
        <div class="filtertabs" onclick="cjfandomfilter('H')" id="cfiltertabH">H</div>
        <div class="filtertabs" onclick="cjfandomfilter('I')" id="cfiltertabI">I</div>
        <div class="filtertabs" onclick="cjfandomfilter('J')" id="cfiltertabJ">J</div>
        <div class="filtertabs" onclick="cjfandomfilter('K')" id="cfiltertabK">K</div>
        <div class="filtertabs" onclick="cjfandomfilter('L')" id="cfiltertabL">L</div>
        <div class="filtertabs" onclick="cjfandomfilter('M')" id="cfiltertabM">M</div>
        <div class="filtertabs" onclick="cjfandomfilter('N')" id="cfiltertabN">N</div>
        <div class="filtertabs" onclick="cjfandomfilter('O')" id="cfiltertabO">O</div>
        <div class="filtertabs" onclick="cjfandomfilter('P')" id="cfiltertabP">P</div>
        <div class="filtertabs" onclick="cjfandomfilter('Q')" id="cfiltertabQ">Q</div>
        <div class="filtertabs" onclick="cjfandomfilter('R')" id="cfiltertabR">R</div>
        <div class="filtertabs" onclick="cjfandomfilter('S')" id="cfiltertabS">S</div>
        <div class="filtertabs" onclick="cjfandomfilter('T')" id="cfiltertabT">T</div>
        <div class="filtertabs" onclick="cjfandomfilter('U')" id="cfiltertabU">U</div>
        <div class="filtertabs" onclick="cjfandomfilter('V')" id="cfiltertabV">V</div>
        <div class="filtertabs" onclick="cjfandomfilter('W')" id="cfiltertabW">W</div>
        <div class="filtertabs" onclick="cjfandomfilter('X')" id="cfiltertabX">X</div>
        <div class="filtertabs" onclick="cjfandomfilter('Y')" id="cfiltertabY">Y</div>
        <div class="filtertabs" onclick="cjfandomfilter('Z')" id="cfiltertabZ">Z</div>
        <div class="filtertabs" onclick="cjfandomfilter('NUM')" id="cfiltertabNUM">NUM</div>
        <div class="fe-frontend-clear"></div>
    </div>
    <?php }else{ ?>
    <div id="cfilteringtabs">
    </div>
	<?php }?>
	<div id="cfandoms">
		<div id="cfilterforA" class="activefandom">
    		<?php cfandomfilter(A);?>
		</div>
        <div id="cfilterforB">
    		<?php cfandomfilter(B);?>
		</div>
        <div id="cfilterforC">
    		<?php cfandomfilter(C);?>
		</div>
        <div id="cfilterforD">
    		<?php cfandomfilter(D);?>
		</div>
        <div id="cfilterforE">
    		<?php cfandomfilter(E);?>
		</div>
        <div id="cfilterforF">
    		<?php cfandomfilter(F);?>
		</div>
        <div id="cfilterforG">
    		<?php cfandomfilter(G);?>
		</div>
        <div id="cfilterforH">
    		<?php cfandomfilter(H);?>
		</div>
        <div id="cfilterforI">
    		<?php cfandomfilter(I);?>
		</div>
        <div id="cfilterforJ">
    		<?php cfandomfilter(J);?>
		</div>
        <div id="cfilterforK">
    		<?php cfandomfilter(K);?>
		</div>
        <div id="cfilterforL">
    		<?php cfandomfilter(L);?>
		</div>
        <div id="cfilterforM">
    		<?php cfandomfilter(M);?>
		</div>
        <div id="cfilterforN">
    		<?php cfandomfilter(N);?>
		</div>
        <div id="cfilterforO">
    		<?php cfandomfilter(O);?>
		</div>
        <div id="cfilterforP">
    		<?php cfandomfilter(P);?>
		</div>
        <div id="cfilterforQ">
    		<?php cfandomfilter(Q);?>
		</div>
        <div id="cfilterforR">
    		<?php cfandomfilter(R);?>
		</div>
        <div id="cfilterforS">
    		<?php cfandomfilter(S);?>
		</div>
        <div id="cfilterforT">
    		<?php cfandomfilter(T);?>
		</div>
        <div id="cfilterforU">
    		<?php cfandomfilter(U);?>
		</div>
        <div id="cfilterforV">
    		<?php cfandomfilter(V);?>
		</div>
        <div id="cfilterforW">
    		<?php cfandomfilter(W);?>
		</div>
        <div id="cfilterforX">
    		<?php cfandomfilter(X);?>
		</div>
        <div id="cfilterforY">
    		<?php cfandomfilter(Y);?>
		</div>
        <div id="cfilterforZ">
    		<?php cfandomfilter(Z);?>
		</div>
        <div id="cfilterforNUM">
    		<?php cfandomfilter(NUM);?>
		</div>
    </div>
    <div class="addnew">
        <br />
        <?php $pairings_terms = get_terms( "pairings", array(
	'hide_empty' => 0,
	'parent' => 0,
) ); ?>
        <select name="pairingsparent" id="pairingsparent">
        <option value="0"> ----- Parent Character -----</option>
        <?php foreach ( $pairings_terms as $pairings_term ){ ?>
		<option value="<?php echo $pairings_term->term_id; ?>"><?php echo $pairings_term->name; }?></option>
        </select>
        <input type="text" value="" name="term_char" id="term_char" /> 
        <button id="charbutton">Add Character</button>
        <input type="hidden" name="action" value="addChar"/>
    
    
    
    </div>
    
    <script type="text/javascript">
						jQuery('#charbutton').click(ajaxSubmitgenre); 
						
						function ajaxSubmitgenre(){
							
							var newCustomerFormchar = jQuery('#term_char').val();
							var pairingparent = jQuery('#pairingsparent').val();
							jQuery.ajax({
								type:"POST",
								url: "<?php echo admin_url('admin-ajax.php'); ?>",
								 data: {
											'action':'addChar',
											'term_char' : newCustomerFormchar,
											'pairings_parent':pairingparent
										},
								success:function(data){
									jQuery('#charhide>div').slideUp();
					                jQuery("#charhide").html(data);
					   			},
					            error: function(errorThrown){
					                alert(errorThrown);
					            }	
							});
							
							return false;
							
							
						}
						
						
						
						
					</script>
    
    
<?php	}
function addChar(){
// Check to see if input field for new term is set 
    	if (isset ($_POST['term_char'])) {

        // If set, stores user input in variable
        $new_term =  $_POST['term_char'];
		$new_pterm =  $_POST['pairings_parent'];
		if($new_pterm == "" || $new_pterm == "0"){
			$genre_id = wp_insert_term(

          // The term (user input)
          $new_term,

          // The club taxonomy
          'pairings'

        );
			
			}else{
		
        // Function to handle inserting of new term into taxonomy
        $genre_id = wp_insert_term(

          // The term (user input)
          $new_term,

          // The club taxonomy
          'pairings',
		  array("parent" => $new_pterm)

        );
		delete_option("pairings_children");
			}
        // Function to handle inserting of new term into taxonomy
        
		
		} ?>
        
    <div class="genrehide"><h3><strong>Select Characters</strong></h3></div>
    <div id="cfilteringtabs">
    <div class="filtertabs filtertabsselect" onclick="cjfandomfilter('A')" id="cfiltertabA">A</div>
    <div class="filtertabs" onclick="cjfandomfilter('B')" id="cfiltertabB">B</div>
    <div class="filtertabs" onclick="cjfandomfilter('C')" id="cfiltertabC">C</div>
    <div class="filtertabs" onclick="cjfandomfilter('D')" id="cfiltertabD">D</div>
    <div class="filtertabs" onclick="cjfandomfilter('E')" id="cfiltertabE">E</div>
    <div class="filtertabs" onclick="cjfandomfilter('F')" id="cfiltertabF">F</div>
    <div class="filtertabs" onclick="cjfandomfilter('G')" id="cfiltertabG">G</div>
    <div class="filtertabs" onclick="cjfandomfilter('H')" id="cfiltertabH">H</div>
    <div class="filtertabs" onclick="cjfandomfilter('I')" id="cfiltertabI">I</div>
    <div class="filtertabs" onclick="cjfandomfilter('J')" id="cfiltertabJ">J</div>
    <div class="filtertabs" onclick="cjfandomfilter('K')" id="cfiltertabK">K</div>
    <div class="filtertabs" onclick="cjfandomfilter('L')" id="cfiltertabL">L</div>
    <div class="filtertabs" onclick="cjfandomfilter('M')" id="cfiltertabM">M</div>
    <div class="filtertabs" onclick="cjfandomfilter('N')" id="cfiltertabN">N</div>
    <div class="filtertabs" onclick="cjfandomfilter('O')" id="cfiltertabO">O</div>
    <div class="filtertabs" onclick="cjfandomfilter('P')" id="cfiltertabP">P</div>
    <div class="filtertabs" onclick="cjfandomfilter('Q')" id="cfiltertabQ">Q</div>
    <div class="filtertabs" onclick="cjfandomfilter('R')" id="cfiltertabR">R</div>
    <div class="filtertabs" onclick="cjfandomfilter('S')" id="cfiltertabS">S</div>
    <div class="filtertabs" onclick="cjfandomfilter('T')" id="cfiltertabT">T</div>
    <div class="filtertabs" onclick="cjfandomfilter('U')" id="cfiltertabU">U</div>
    <div class="filtertabs" onclick="cjfandomfilter('V')" id="cfiltertabV">V</div>
    <div class="filtertabs" onclick="cjfandomfilter('W')" id="cfiltertabW">W</div>
    <div class="filtertabs" onclick="cjfandomfilter('X')" id="cfiltertabX">X</div>
    <div class="filtertabs" onclick="cjfandomfilter('Y')" id="cfiltertabY">Y</div>
    <div class="filtertabs" onclick="cjfandomfilter('Z')" id="cfiltertabZ">Z</div>
    <div class="filtertabs" onclick="cjfandomfilter('NUM')" id="cfiltertabNUM">NUM</div>
    <div class="fe-frontend-clear"></div>
    </div>
	<div id="cfandoms">
		<div id="cfilterforA" class="activefandom">
    		<?php cfandomfilter(A);?>
		</div>
        <div id="cfilterforB">
    		<?php cfandomfilter(B);?>
		</div>
        <div id="cfilterforC">
    		<?php cfandomfilter(C);?>
		</div>
        <div id="cfilterforD">
    		<?php cfandomfilter(D);?>
		</div>
        <div id="cfilterforE">
    		<?php cfandomfilter(E);?>
		</div>
        <div id="cfilterforF">
    		<?php cfandomfilter(F);?>
		</div>
        <div id="cfilterforG">
    		<?php cfandomfilter(G);?>
		</div>
        <div id="cfilterforH">
    		<?php cfandomfilter(H);?>
		</div>
        <div id="cfilterforI">
    		<?php cfandomfilter(I);?>
		</div>
        <div id="cfilterforJ">
    		<?php cfandomfilter(J);?>
		</div>
        <div id="cfilterforK">
    		<?php cfandomfilter(K);?>
		</div>
        <div id="cfilterforL">
    		<?php cfandomfilter(L);?>
		</div>
        <div id="cfilterforM">
    		<?php cfandomfilter(M);?>
		</div>
        <div id="cfilterforN">
    		<?php cfandomfilter(N);?>
		</div>
        <div id="cfilterforO">
    		<?php cfandomfilter(O);?>
		</div>
        <div id="cfilterforP">
    		<?php cfandomfilter(P);?>
		</div>
        <div id="cfilterforQ">
    		<?php cfandomfilter(Q);?>
		</div>
        <div id="cfilterforR">
    		<?php cfandomfilter(R);?>
		</div>
        <div id="cfilterforS">
    		<?php cfandomfilter(S);?>
		</div>
        <div id="cfilterforT">
    		<?php cfandomfilter(T);?>
		</div>
        <div id="cfilterforU">
    		<?php cfandomfilter(U);?>
		</div>
        <div id="cfilterforV">
    		<?php cfandomfilter(V);?>
		</div>
        <div id="cfilterforW">
    		<?php cfandomfilter(W);?>
		</div>
        <div id="cfilterforX">
    		<?php cfandomfilter(X);?>
		</div>
        <div id="cfilterforY">
    		<?php cfandomfilter(Y);?>
		</div>
        <div id="cfilterforZ">
    		<?php cfandomfilter(Z);?>
		</div>
        <div id="cfilterforNUM">
    		<?php cfandomfilter(NUM);?>
		</div>
    </div>
    <div class="addnew">
        <br />
        <?php $pairings_terms = get_terms( "pairings", array(
	'hide_empty' => 0,
	'parent' => 0,
) ); ?>
        <select name="pairingsparent" id="pairingsparent">
        <option value="0"> ----- Parent Character -----</option>
        <?php foreach ( $pairings_terms as $pairings_term ){ ?>
		<option value="<?php echo $pairings_term->term_id; ?>"><?php echo $pairings_term->name; }?></option>
        </select>
        <input type="text" value="" name="term_char" id="term_char" /> 
        <button id="charbutton">Add Character</button>
        <input type="hidden" name="action" value="addChar"/>
    
    
    
    </div>
    <script type="text/javascript">
						jQuery('#charbutton').click(ajaxSubmitgenre); 
						
						function ajaxSubmitgenre(){
							
							var newCustomerFormchar = jQuery('#term_char').val();
							var pairingparent = jQuery('#pairingsparent').val();
							jQuery.ajax({
								type:"POST",
								url: "<?php echo admin_url('admin-ajax.php'); ?>",
								 data: {
											'action':'addChar',
											'term_char' : newCustomerFormchar,
											'pairings_parent':pairingparent
										},
								success:function(data){
									jQuery('#charhide>div').slideUp();
					                jQuery("#charhide").html(data);
					   			},
					            error: function(errorThrown){
					                alert(errorThrown);
					            }	
							});
							
							return false;
							
							
						}
						
						
						
						
					</script>
<?php	die();	
}
add_action('wp_ajax_addChar', 'addChar');
add_action('wp_ajax_nopriv_addChar', 'addChar');
add_filter( 'post_row_actions', 'remove_row_actions', 10, 1 );
function remove_row_actions( $actions )
{
	global $post;
	$nonce = wp_create_nonce( 'trash-post_' . $post->ID );
    if( get_post_type() == 'fiction' )
        $actions['edit'] = '<a title="'.__('Edit this item', 'quotable').'" href="'.get_admin_url().'post.php?post='.$post->ID.'&amp;action=edit">'.__('Edit', 'quotable').'</a>';
        $actions['view'];
        $actions['trash']='<a href="'.get_admin_url().'post.php?post='.$post->ID.'&amp;action=trash&amp;_wpnonce='.$nonce.'" />'.__('Trash', 'quotable').'</a>';
    
    return $actions;
}
// http://scribu.net/wordpress/prevent-blog-authors-from-editing-comments.html
function restrict_comment_editing( $caps, $cap, $user_id, $args ) {
if ( 'edit_comment' == $cap ) {
$comment = get_comment( $args[0] );
if ( $comment->user_id != $user_id )
$caps[] = 'moderate_comments';
}
return $caps;
}
add_filter( 'map_meta_cap', 'restrict_comment_editing', 10, 4 );
add_action( 'admin_bar_menu', 'customize_my_wp_admin_bar', 80 );
function customize_my_wp_admin_bar( $wp_admin_bar ) {

    //Get a reference to the new-content node to modify.
    $new_content_node = $wp_admin_bar->get_node('new-content');

    // Parent Properties for new-content node:
        //$new_content_node->id     // 'new-content'
        //$new_content_node->title  // '<span class="ab-icon"></span><span class="ab-label">New</span>'
        //$new_content_node->parent // false
        //$new_content_node->href   // 'http://www.somedomain.com/wp-admin/post-new.php'
        //$new_content_node->group  // false
        //$new_content_node->meta['title']   // 'Add New'

    //Change href
    $new_content_node->href = '#';

    //Update Node.
    $wp_admin_bar->add_node($new_content_node);

    //Remove an existing menu item.
    $wp_admin_bar->remove_menu('new-post');

    // Properties for new-post node:
        //$new_content_node->id     // 'new-post'
        //$new_content_node->title  // 'Post'
        //$new_content_node->parent // 'new-content'
        //$new_content_node->href   // 'http://www.somedomain.com/wp-admin/post-new.php'
        //$new_content_node->group  // false
        //$new_content_node->meta   // array()



}

