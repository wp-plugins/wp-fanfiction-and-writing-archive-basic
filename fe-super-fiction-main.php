<?php
/**
Plugin Name: WP Fanfiction and Writing Archive Basic
Version: 1.2.4
Plugin URI: http://writing-archive.com/downloads/wp-fanfiction-writing-archive-basic/
Description: Additional functionality over WP Fanfiction and Writing Archive Basic for larger communities of writers. Easily configure integrated Twitter API for auto-tweet of new submissions. Easily input advertising code to embed ads. Private messaging between members. Turn on/off age verification. Configured for optimum SEO.
Author: Fandom Entertainment LLC
Author URI: http://writing-archive.com/
Text Domain: fe-fiction
Copyright: 2011
WordPress Versions: 3.3 and above
For full acknowledgements click on the copyright/version strip
at the bottom of forum pages

$Rev$
$Author$
$Date$

WP Fanfiction and Writing Archive Basic - turn your WordPress blog into an online writing community
Copyright (C) 2014  Fandom Entertainment LLC

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, GPLv2.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details. See http://www.gnu.org/licenses/.

Contact Us at info@fandomentertainment.com

GNU General Public License, Free Software Foundation <http://creativecommons.org/licenses/GPL/2.0/>
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
**/



$plugin_dir = basename(dirname(__FILE__));
@define('FIC_PLUGIN_DIR',$plugin_dir);

@define('JQUERY_UI_THEME','black-tie');
//@define('JQUERY_UI_THEME','cupertino');
@define('FIC_PLUGIN_ABS_PATH_DIR',plugin_dir_path( __FILE__ ));

/** START OF FE SUPER FICTION PLUGIN **/
if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']))
{
	die('Access Denied');
}

global $is_IIS;

# hack for some IIS installations
if ($is_IIS && @ini_get('error_log') == '') @ini_set('error_log', 'syslog');

/** INCLUDE THE CLASS FILES **/
//include_once('includes/globals.php');
include_once('includes/fe-super-fiction-class.php');

function thematic_enqueue_scripts() {
wp_enqueue_script('my-ajax-request');
wp_localize_script( 'my-ajax-request', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}
add_action('wp_enqueue_scripts', 'thematic_enqueue_scripts');

initialize();

register_activation_hook(__FILE__, 'FeFiction_Activate');

include_once('includes/fe-super-fiction-widget.php');
add_action('widgets_init', create_function('', 'return register_widget("FanficMeSidebarWidget");'));

include_once('includes/ffme-fan-fiction-search-widget.php');
add_action('widgets_init', create_function('', 'return register_widget("FanficMeFanFictionSearchSidebarWidget");'));
include_once('includes/fiction-views.php');

add_action('init', 'fanfic_fiction_views_textdomain');
add_action('init', 'fanfic_fiction_views_setcookie');
add_action('admin_menu', 'fanfic_fiction_views_menu');
add_action('wp_head', 'fanfic_process_fiction_views_content');
add_action('widgets_init', 'fanfic_widget_fiction_views_init');
add_filter('query_vars', 'fanfic_fiction_views_variables');
add_action('pre_get_posts', 'fanfic_fiction_views_sorting');
add_action('delete_post', 'fanfic_delete_fiction_views');
add_filter('get_the_excerpt', 'fanfic_update_fiction_excerpt_views',5);
//add_action('admin_init','add_fiction_views_contextual_help');
fanfic_fiction_cache_support();
?>