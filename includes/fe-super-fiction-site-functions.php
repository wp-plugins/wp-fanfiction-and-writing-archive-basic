<?php
function FeFiction_the_permalink($the_data,$echo=true)
{
	global $pagename;

	$new_permalink = str_replace('/'.CUSTOM_POST_TYPE.'/','/'.$pagename.'/story/',$the_data);

	if($echo)
	{
		echo $new_permalink;
	}
	else
	{
		return $new_permalink;
	}
}

function FeFiction_the_excerpt($the_data,$echo=true)
{
	if($the_data == '')
	{
		add_filter( 'excerpt_more', 'FeFiction_remove_excerpt_continue_reading');
	}
	else
	{
		add_filter( 'excerpt_more', 'FeFiction_change_excerpt_continue_reading');
	}
	if($echo)
	{
		the_excerpt();
	}
	else
	{
		ob_start();
		the_excerpt();
		$excerpt = ob_get_contents();
		ob_end_clean();
		return $excerpt;
	}
}

function FeFiction_remove_excerpt_continue_reading($output)
{
	$output = '';
	return $output;
}

function FeFiction_change_excerpt_continue_reading($output)
{
	global $pagename;

	$output = str_replace('&hellip;','&hellip;<br />',str_replace(CUSTOM_POST_TYPE,$pagename.'/story',$output));
	return $output;
}
function FeFiction_the_terms($post_ID,$name,$before,$separator,$after)
{
	global $pagename;

	$the_term = str_replace($name,$pagename.'/'.$name,get_the_term_list( $post_ID, $name, $before, $separator, $after ));

	if($the_term == '')
	{
		echo $before.' '.__('N/A','fe-fiction');
	}
	else
	{
		echo $the_term;
	}
}

function FeFiction_the_metas($post_ID,$name,$display_before,$display_after,$echo=true)
{
	$the_meta = get_post_meta( $post_ID, FIC_POST_CUSTOM_FIELDS_PREFIX.$name, true );
	if($the_meta == '')
	{
		$the_meta = __('N/A','fe-fiction');
	}
	if($echo)
	{
		echo $display_before.$the_meta.$display_after;
	}
	else
	{
		return $display_before.$the_meta.$display_after;
	}
}

function FeFiction_the_ID($echo = true)
{
	global $id;
	if($echo) { echo $id; } else { return $id; }
}

function FeFiction_Pagination($pages = '',$paged=1, $range = 2)
{
	global $wp_query;

	$showitems = ($range * 2)+1;

	if(empty($paged)) { $paged = 1; }

	if($pages == '')
	{
		$pages = $wp_query->max_num_pages;
		if(!$pages)
		{
			$pages = 1;
		}
	}

	if($pages <> 1)
	{
		echo "<div class=\"FeFiction_pagination\"><span>Page ".$paged." of ".$pages."</span>";
		if($paged > 2 && $paged > $range+1 && $showitems < $pages)
		{
			echo "<a href='".get_pagenum_link(1)."'>&laquo; First</a>";
		}
		if($paged > 1 && $showitems < $pages)
		{
			echo "<a href='".get_pagenum_link($paged - 1)."'>&lsaquo; Previous</a>";
		}

		for ($i=1; $i <= $pages; $i++)
		{
			if ($pages != 1 &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))
			{
				echo ($paged == $i)? "<span class=\"current\">".$i."</span>":"<a href='".get_pagenum_link($i)."' class=\"inactive\">".$i."</a>";
			}
		}

		if ($paged < $pages && $showitems < $pages)
		{
			echo "<a href=\"".get_pagenum_link($paged + 1)."\">Next &rsaquo;</a>";
		}
		if ($paged < $pages-1 && $paged+$range-1 < $pages && $showitems < $pages)
		{
			echo "<a href='".get_pagenum_link($pages)."'>Last &raquo;</a>";
		}
		echo "</div>\n";
	}
}