<?php

@define('FANDOMS_DEFAULT_SEARCH_MAX_NUM_FANDOMS_FOR_DROPDOWN',25);
@define('FANDOMS_DEFAULT_SEARCH_FANDOMS_LIST_SHOW_COUNT',1);
@define('FANDOMS_DEFAULT_SEARCH_FANDOMS_LIST_HIDE_EMPTY',1);
@define('FANDOMS_DEFAULT_SEARCH_FANDOMS_LIST_SHOW_FANDOM_SELECTION_TEXT',0);
@define('FIC_RATINGS_MIN_AGE',17);
@define('FIC_CONTENT_PAGINATE_MIN_WORDS_PER_PAGE',375);
@define('FIC_CONTENT_PAGINATE_MAX_SECTIONS_PER_PAGE',2); //not currently implemented
@define('FIC_PLUGIN_OPTION_NAME','fe_super_fiction_loaded');
@define('CUSTOM_POST_TYPE','fiction');
@define('FIC_DEFAULT_ROLE','subscriber');
@define('FIC_OPTION_CUSTOM_DASHBOARD','fic_option_custom_dashboard');
@define('FIC_OPTION_HIDE_ADMIN_MENUS','fic_option_hide_admin_menus');
@define('FIC_OPTION_ENABLE_DEFAULT_ROLE','fic_option_enable_default_role');
@define('FIC_OPTION_PAGE_ID','fic_option_page_id');
@define('FIC_OPTION_CREATE_PAGE','fic_option_create_page');
@define('FIC_OPTION_ON_FRONT_PAGE','fic_option_on_front_page');
@define('FIC_OPTION_FICTION_PAGE_STYLESHEET','fic_option_fiction_page_stylesheet');
@define('FIC_OPTION_FICTION_STORY_SCORING','fic_option_fiction_story_scoring');
@define('FANDOMS_OPTION_SEARCH_MAX_NUM_FANDOMS_FOR_DROPDOWN','fic_option_smnffd');
@define('FANDOMS_OPTION_SEARCH_FANDOMS_LIST_SHOW_COUNT','fic_option_sflsc');
@define('FANDOMS_OPTION_SEARCH_FANDOMS_LIST_HIDE_EMPTY','fic_option_sflhe');
@define('FANDOMS_OPTION_SEARCH_FANDOMS_LIST_SHOW_FANDOM_SELECTION_TEXT','fic_option_flsfst');
@define('FIC_POST_CUSTOM_FIELDS_PREFIX', '_fic_');

$GLOBALS['FIC_RATINGS_REQUIRING_AGE_VERIFICATION'] = array('r','nc-17');
$GLOBALS['FIC_TAXONOMY_RATING_ORDER'] = array('g','pg','pg-13','r','nc-17');

$GLOBALS['FIC_CUSTOM_POST_TYPE_ARGS'] = array(
	'labels' => Array(
		'name' => 'Fan Fiction'
		, 'singular_name' => 'Fan Fiction'
		, 'add_new' => 'Add New'
		, 'add_new_item' => 'Create Book Chapter or Single Story'
		, 'edit' => 'Edit Book Chapter or Single Story Edit'
		, 'edit_item' => 'Edit Book Chapter or Single Story'
		, 'new_item' => 'Create Book Chapter or Single Story'
		, 'view_item' => 'View Book Chapter or Single Story'
		, 'search_items' => 'Search Stories'
		, 'not_found' => 'No stories found'
		, 'not_found_in_trash' => 'No stories found in trash'
		, 'parent_item_colon' => 'Book Name'
	)
	, 'supports' => Array(
		'title' => 'title'
		, 'editor' => 'editor'
		, 'author' => 'author'
		, 'comments' => 'comments'
		/** , 'revisions' => 'revisions' **/
	)
	,'capability_type' => 'post'
	,'capabilities' => array(
		'publish_posts' => 'publish_fiction',
		'edit_posts' => 'edit_fiction',
		'edit_post' => 'edit_fictions',
		'delete_posts' => 'delete_fiction',
		'delete_post' => 'delete_fictions',
		'read_private_posts' => 'read_private_fiction',
		'delete_others_posts' => 'delete_others_fiction',
		'delete_private_posts' => 'delete_private_fiction',
		'delete_published_posts' => 'delete_published_fiction',
		'edit_others_posts' => 'edit_others_fiction',
		'edit_private_posts' => 'edit_private_fiction',
		'edit_published_posts' => 'edit_published_fiction',
	)
	, 'menu_position' => 5
	, 'public' => true
	, 'hierarchical' => false
	,'rewrite' => array(
		'slug' => 'fan_fiction_stories'
		,'with_front' => true
	)
	, 'query_var' => true
	, 'can_export' => true
	, 'has_archive' => false
	, 'description' => 'Fan fiction is fiction set in the universe of a popular story, television series, movie, etc and written as an homage to the original work. All copyrights to the characters and other story elements of the original work are presumed to still reside with the author(s) who created them.'
);
add_filter('post_type_link', 'events_permalink_structure', 10, 4);
function events_permalink_structure($post_link, $post, $leavename, $sample)
{
    if ( false !== strpos( $post_link, '%story_category%' ) ) {
        $event_type_term = get_the_terms( $post->ID, 'story_category' );
        if ( !empty( $event_type_term ) ){
        $post_link = str_replace( '%story_category%', array_pop( $event_type_term )->slug, $post_link );}
    }
    return $post_link;
}

$GLOBALS['FIC_POST_CUSTOM_FIELDS'] = array(
	'parent_story' => array(
		'field_title' => 'Parent Story'
		, 'field_type' => 'hidden'
		, 'field_sort_order' => 'default'
		, 'field_default_option' => ''
		, 'field_description' => ''
		, 'object_type' => array('0' => 'fiction')
		, 'field_id' => 'parent_story'
		, 'field_length' => '10'
	)
	,'chapter_number' => array(
		'field_title' => 'Chapter Number'
		, 'field_type' => 'text'
		, 'field_sort_order' => 'default'
		, 'field_default_option' => ''
		, 'field_description' => 'This can be any value but cannot exceed 5 characters<br /><strong>Only needed if this is a book.</strong>'
		, 'object_type' => array('0' => 'fiction')
		, 'field_id' => 'chapter_number'
		, 'field_length' => '5'
	)
	,'chapter_title' => array(
		'field_title' => 'Chapter Title'
		, 'field_type' => 'text'
		, 'field_sort_order' => 'default'
		, 'field_default_option' => ''
		, 'field_description' => 'This can be any value but cannot exceed 75 characters<br /><strong>Only needed if this is a book.</strong>. 100 characters maximum.'
		, 'object_type' => array('0' => 'fiction')
		, 'field_id' => 'chapter_title'
		, 'field_length' => '100'
	)
	,'author_notes' => array(
		'field_title' => 'Author Notes'
		, 'field_type' => 'text'
		, 'field_sort_order' => 'default'
		, 'field_default_option' => ''
		, 'field_description' => 'Any information relevant to this story.  Any notes you would like to add.'
		, 'object_type' => array('0' => 'fiction')
		, 'field_id' => 'author_notes'
		/** , 'field_length' => '250' **/
	)
	,'summary' => array(
		'field_title' => 'Summary'
		, 'field_type' => 'textarea'
		, 'field_sort_order' => 'default'
		, 'field_default_option' => ''
		, 'field_description' => '<strong>Story Summary (optional)</strong>.  If you do not fill this in, we will create a summary from your story.'
		, 'object_type' => array('0' => 'fiction')
		, 'field_id' => 'summary'
	)
	/**, 'disclaimer' => array(
		'field_title' => 'Disclaimer'
		, 'field_type' => 'textarea'
		, 'field_sort_order' => 'default'
		, 'field_default_option' => ''
		, 'field_description' => 'If there are any disclaimers you need to state, do so here.'
		, 'object_type' => array('0' => 'fiction')
		, 'field_id' => 'disclaimer'
	)**/
);

$GLOBALS['FIC_TAXONOMIES'] = array(
	'genre' => Array(
		'object_type' => Array('fiction')
		, 'args' => Array(
			'labels' => Array(
				'name' => 'Genres'
				, 'singular_name' => 'Genre'
				, 'add_new_item' => 'Add New Genre'
				, 'new_item_name' => 'New Genre Name'
				, 'edit_item' => 'Edit Genre'
				, 'update_item' => 'Update Genre'
				, 'search_items' => 'Search Genres'
				, 'popular_items' => 'Popular Genres'
				, 'all_items' => 'All Genres'
				, 'parent_item' => 'Parent Genre'
				, 'parent_item_colon' => 'Parent Genre:'
				, 'add_or_remove_items' => 'Add or remove genres'
				, 'separate_items_with_commas' => 'Separate genres with commas'
				, 'choose_from_most_used' => 'All Genres'
			)
			, 'show_ui' => true
			, 'show_tagcloud' => true
			, 'show_in_nav_menus' => true
			, 'hierarchical' => true
			, 'rewrite' => true
			, 'query_var' => true
			, 'capabilities' => array(
				'manage_terms' => 'only_by_fanficme'
				,'edit_terms' => 'only_by_fanficme'
				,'delete_terms' => 'only_by_fanficme'
				,'assign_terms' => 'edit_posts'
				)
		)
		, 'terms' => array(
			'Action / Adventure' => array()
			, 'Angst' => array()
			, 'Drama' => array()
			, 'Fantasy' => array()
			, 'General' => array()
			, 'Horror' => array()
			, 'Humor' => array()
			, 'Mystery' => array()
			, 'Parody' => array()
			, 'Poetry' => array()
			, 'Romance' => array()
			, 'Sci-Fi' => array()
			, 'Slash' => array()
			, 'Spiritual' => array()
			, 'Supernatural' => array()
			, 'Suspense' => array()
			, 'Tragedy' => array()
		)
	)
	, 'story_category' => Array(
		'object_type' => Array('fiction')
		, 'args' => Array(
			'labels' => Array(
				'name' => 'Fandom'
				, 'singular_name' => get_option(FIC_OPTION_FICTION_FANDOM_LABEL)
				, 'add_new_item' => 'Add New '.get_option(FIC_OPTION_FICTION_FANDOM_LABEL)
				, 'new_item_name' => 'New '.get_option(FIC_OPTION_FICTION_FANDOM_LABEL).' Name'
				, 'edit_item' => 'Edit '.get_option(FIC_OPTION_FICTION_FANDOM_LABEL)
				, 'update_item' => 'Update '.get_option(FIC_OPTION_FICTION_FANDOM_LABEL)
				, 'search_items' => 'Search '.get_option(FIC_OPTION_FICTION_FANDOM_LABEL_P)
				, 'popular_items' => 'Popular '.get_option(FIC_OPTION_FICTION_FANDOM_LABEL_P)
				, 'all_items' => 'All '.get_option(FIC_OPTION_FICTION_FANDOM_LABEL_P)
				, 'parent_item' => 'Parent '.get_option(FIC_OPTION_FICTION_FANDOM_LABEL)
				, 'parent_item_colon' => 'Parent '.get_option(FIC_OPTION_FICTION_FANDOM_LABEL).':'
				, 'add_or_remove_items' => 'Add or remove '.get_option(FIC_OPTION_FICTION_FANDOM_LABEL_P)
				, 'separate_items_with_commas' => 'Separate '.get_option(FIC_OPTION_FICTION_FANDOM_LABEL_P).' with commas'
				, 'choose_from_most_used' => 'All '.get_option(FIC_OPTION_FICTION_FANDOM_LABEL_P)
			)
			, 'show_ui' => true
			, 'show_tagcloud' => true
			, 'show_in_nav_menus' => true
			, 'hierarchical' => true
            , 'rewrite' => array('slug'=> 'fandom'
            , 'with_front' => true)
			, 'query_var' => true
			, 'capabilities' => array(
				'manage_terms' => 'only_by_fanficme'
				,'edit_terms' => 'only_by_fanficme'
				,'delete_terms' => 'only_by_fanficme'
				,'assign_terms' => 'edit_posts'
				)
		)
	)
	, 'rating' => Array(
		'object_type' => Array('fiction')
		, 'args' => Array(
			'labels' => Array(
				'name' => 'Ratings'
				, 'singular_name' => 'Rating'
				, 'add_new_item' => 'Add New Rating'
				, 'new_item_name' => 'New Rating Name'
				, 'edit_item' => 'Edit Rating'
				, 'update_item' => 'Update Rating'
				, 'search_items' => 'Search Ratings'
				, 'popular_items' => 'Popular Ratings'
				, 'all_items' => 'All Ratings'
				, 'parent_item' => 'Parent Rating'
				, 'parent_item_colon' => 'Parent Rating:'
				, 'add_or_remove_items' => 'Add or remove ratings'
				, 'separate_items_with_commas' => 'Separate ratings with commas'
				, 'choose_from_most_used' => 'All Ratings'
			)
			, 'public' => true
			, 'hierarchical' => true
			, 'rewrite' => true
			, 'query_var' => true
			, 'capabilities' => array(
				'manage_terms' => 'only_by_fanficme'
				,'edit_terms' => 'only_by_fanficme'
				,'delete_terms' => 'only_by_fanficme'
				,'assign_terms' => 'edit_posts'
				)
		)
		, 'terms' => Array(
			'G' => Array()
			, 'PG' => Array()
			, 'PG-13' => Array()
			, 'R' => Array()
			, 'NC-17' => Array()
		)
	)
	, 'pairings' => Array(
		'object_type' => Array('fiction')
		, 'args' => Array(
			'labels' => Array(
				'name' => 'Characters'
				, 'singular_name' => 'Character'
				, 'add_new_item' => 'Add New Character'
				, 'new_item_name' => 'New Character Name'
				, 'edit_item' => 'Edit Character'
				, 'update_item' => 'Update Character'
				, 'search_items' => 'Search Characters'
				, 'popular_items' => 'Popular Characters'
				, 'all_items' => 'All Characters'
				, 'parent_item' => 'Parent Character'
				, 'parent_item_colon' => 'Parent Character:'
				, 'add_or_remove_items' => 'Add or remove characters'
				, 'separate_items_with_commas' => 'Separate characters with commas'
				, 'choose_from_most_used' => 'All Characters'
			)
			, 'public' => true
			, 'hierarchical' => true
			, 'rewrite' => true
			, 'query_var' => true
			, 'capabilities' => array(
				'manage_terms' => 'manage_fic_options'
				,'edit_terms' => 'edit_posts'
				,'delete_terms' => 'manage_fic_options'
				,'assign_terms' => 'edit_posts'
				)
		)
	)
);

$GLOBALS['FIC_TAXONOMIES_STORY_CATEGORY_TERMS'] = array();

$GLOBALS['FIC_SUB_ADMIN_ROLE'] = array(
	'slug' => 'manage_fic_options'
	,'name' => 'Fiction Site Owner'
	,'capabilities' => array(
		'activate_plugins' => true
		,'add_users' => true
		,'create_users' => true
		,'delete_others_pages' => true
		,'delete_others_posts' => true
		,'delete_pages' => true
		,'delete_plugins' => true
		,'delete_posts' => true
		,'delete_private_pages' => true
		,'delete_private_posts' => true
		,'delete_published_pages' => true
		,'delete_published_posts' => true
		,'delete_themes' => true
		,'delete_users' => true
		,'edit_dashboard' => false
		,'edit_files' => false
		,'edit_others_pages' => true
		,'edit_others_posts' => true
		,'edit_pages' => true
		,'edit_plugins' => false
		,'edit_posts' => true
		,'edit_private_pages' => true
		,'edit_private_posts' => true
		,'edit_published_pages' => true
		,'edit_published_posts' => true
		,'edit_theme_options' => true
		,'edit_themes' => true
		,'edit_users' => true
		,'export' => false
		,'import' => true
		,'install_plugins' => true
		,'install_themes' => true
		,'list_users' => true
		,'manage_categories' => true
		,'manage_links' => false
		,'manage_options' => true
		,'moderate_comments' => true
		,'promote_users' => false
		,'publish_pages' => true
		,'publish_posts' => true
		,'read_private_pages' => true
		,'read_private_posts' => true
		,'read' => true
		,'remove_users' => true
		,'switch_themes' => true
		,'unfiltered_html' => false
		,'unfiltered_upload' => false
		,'update_core' => false
		,'update_plugins' => true
		,'update_themes' => true
		,'upload_files' => false
	)
);

$GLOBALS['FIC_CUR_QUERY_STRING'] = '';
$GLOBALS['FANDOMS_SEARCH_MAX_NUM_FANDOMS_FOR_DROPDOWN'] = 0;
$GLOBALS['FANDOMS_SEARCH_FANDOMS_LIST_SHOW_COUNT'] = 1;
$GLOBALS['FANDOMS_SEARCH_FANDOMS_LIST_HIDE_EMPTY'] = 1;
$GLOBALS['FANDOMS_SEARCH_FANDOMS_LIST_SHOW_FANDOM_SELECTION_TEXT'] = 1;
$GLOBALS['BAD_WORDS_LIST'] = array();
$GLOBALS['FIC_USERS_AGE'] = 16;
