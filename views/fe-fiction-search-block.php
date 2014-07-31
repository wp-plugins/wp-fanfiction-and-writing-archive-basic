<script type="text/javascript">
    jQuery(document).ready(function($) {

        $('#fiction_search_submit_btn').button().click(function(){
            $('#fiction_search_frm').trigger('submit');
        });

    <?php
    if($cur_query_string['story_category'] != '')
    {
        ?>
        $('#story_category').val('<?php echo $cur_query_string['story_category']; ?>');
        <?php
    }
    ?>
    <?php
    if(isset($cur_query_string['cat_letter']) && isset($cur_query_string['cat_name']))
    {
        ?>
        $('#fiction_search_fandoms_block_breadcrumb').show().html('<strong><?php _e('Fandom: ','fe-fiction'); ?></strong><?php echo $cur_query_string['cat_letter']; ?>&nbsp;-&gt;&nbsp;<?php echo $cur_query_string['cat_name']; ?>');
        $('#cat_letter').val('<?php echo $cur_query_string['cat_letter']; ?>');
        $('#cat_name').val('<?php echo $cur_query_string['cat_name']; ?>');
        $('#fiction_search_fandoms_block_breadcrumb').append('&nbsp;<span id="fandom_search_clear_btn" name="fandom_search_clear_btn"><?php echo __('CLEAR','fe-fiction'); ?></span>');

        $('#fandom_search_clear_btn').button().css('fontSize','.75em').click(function() {
            $('#cat_letter').val('');
            $('#cat_name').val('');
            $('#story_category').val('');
            $('#fiction_search_fandoms_block_breadcrumb').html('').hide();
            $('#fiction_search_frm').trigger('submit');
        });

        <?php
    }
    ?>
        $("[id^=fiction_search_fandoms_block_cat_letters-]").click(function() {
            var split_data = this.id.split('-');
            if(split_data[1].length > 0)
            {
                $('#fiction_search_fandoms_block_breadcrumb').show().html('<strong><?php _e('Fandom: ','fe-fiction'); ?></strong>' + split_data[1]);

                $('#cat_letter').val(split_data[1]);

                $('#fiction_search_fandoms_block_categories').show();

                $('#fiction_search_fandoms_block_categories_content').show().html('<div class="fe-posting-center"><img src="<?php echo plugins_url();?>/wp-fanfiction-writing-archive-basic/views/images/ajax-loader.gif" alt="<?php _e('Loading ...','fe-fiction') ?>" /></div>');

                $.ajax({
                    type: "GET",
                    url: "<?php echo admin_url('admin-ajax.php'); ?>",
                    data: "action=fiction_fandoms_browse&cat_letter="+ (split_data[1] == '#' ? 'NUM' : split_data[1]),
                    dataType: "html",
                    success: function(cat_data){
                        $('#fiction_search_fandoms_block_categories_content').html("<div style='padding-left:3%;padding-right:3%;font-style:italic;'><?php _e('Displaying fandoms for which we currently have stories, but we welcome stories in all fandoms.','fe-fiction'); ?></div>" + cat_data);
                        $("[id^=fandom_search_list-]").click(function() {
                            var split_data = this.id.split('-');
                            if(split_data[1].length > 0)
                            {
                                $('#story_category').val(split_data[1]);
                                $('#cat_name').val($('#'+this.id).html());
                                $('#fiction_search_fandoms_block_breadcrumb').append('&nbsp;-&gt;&nbsp;'+$('#'+this.id).html());
                                $('#fiction_search_frm').submit();
                            }
                        });
                    }
                });
            }
        });

        $("#story_author_nouse").click(function() {
            $.ajax({
                type: "GET",
                url: "<?php echo admin_url('admin-ajax.php'); ?>",
                data: "action=fiction_authors_get&type=dropdown",
                dataType: "html",
                success: function(author_list){
                    $('#fiction_search_story_author').html(author_list);
                }
            });
        });
    });
</script>

<?php

$category_letters = $wpdb->get_results("SELECT 
		  IF(SUBSTRING(`wpt`.`name`, 1, 1) REGEXP '[[:alpha:]]', SUBSTRING(`wpt`.`name`, 1, 1), '#') AS `CAT_LETTER`,
		  COUNT(SUBSTRING(`wpt`.`name`, 1, 1)) AS `NUM_CATS`
		FROM
		  `".$wpdb->prefix."terms` `wpt`
		  INNER JOIN `".$wpdb->prefix."term_taxonomy` `wptt` ON (`wpt`.`term_id` = `wptt`.`term_id`)
		WHERE
		  `wptt`.`taxonomy` = 'story_category'
		GROUP BY
		  CAT_LETTER
		ORDER BY
		  `CAT_LETTER`", ARRAY_A);

$total_num_fandoms = 0;
for($nca=0;$nca<count($category_letters);$nca++)
{
    $total_num_fandoms = $total_num_fandoms + $category_letters[$nca]['NUM_CATS'];
}
?>
<?php $per_page_select = '<select id="per_page" name="per_page">
		<option value="10" '.selected($cur_query_string['per_page'],10,false).'>10</option>
		<option value="25" '.selected($cur_query_string['per_page'],25,false).'>25</option>
		<option value="50" '.selected($cur_query_string['per_page'],50,false).'>50</option>
		<option value="75" '.selected($cur_query_string['per_page'],75,false).'>75</option>
		<option value="100" '.selected($cur_query_string['per_page'],100,false).'>100</option>
	</select>';
?>
<div id="fiction_search_block">
    <h2><?php echo __('Search Fan Fiction','fe-fiction'); ?></h2>
    <div id="fiction_search" class="ui-widget ui-widget-content ui-corner-all">
        <form id="fiction_search_frm" name="fiction_search_frm" method="get" action="/<?php echo FeFiction_Get_Page_Slug_Name(); ?>/">
            <input type="hidden" id="story_category" name="story_category" value="" />
            <input type="hidden" id="cat_letter" name="cat_letter" value="" />
            <input type="hidden" id="cat_name" name="cat_name" value="" />

            <label for="keyword"><strong>Keyword:</strong></label>&nbsp;<input type="text" name="s" id="keyword" value="<?php echo isset($cur_query_string['s']) ? stripslashes($cur_query_string['s']) : ''; ?>" /><br />

            <?php
            if($total_num_fandoms > FANDOMS_SEARCH_MAX_NUM_FANDOMS_FOR_DROPDOWN)
            {
                ?>
                <div id="fiction_search_fandoms_block" name="fiction_search_fandoms_block">
                    <h4><strong><?php _e('Search by Fandom','fe-fiction'); ?></strong></h4>

                    <div class="fe-class-search-fandom"><?php _e('select the first letter of your fandom', 'fe-fiction'); ?></div>

                    <div id="fiction_search_fandoms_block_cat_letters" name="fiction_search_fandoms_block_cat_letters">
                        <?php for($a=0;$a<count($category_letters);$a++)
                    {
                        ?>
                        <?php echo $a>0 ? '&nbsp;' : ''; ?><span id="fiction_search_fandoms_block_cat_letters-<?php echo $category_letters[$a]['CAT_LETTER']; ?>" class="fe-search-fandom-blocks"><a><?php echo $category_letters[$a]['CAT_LETTER']; ?></a></span>
                        <?php
                    }
                        ?>
                    </div>
                    <div id="fiction_search_fandoms_block_breadcrumb" name="fiction_search_fandoms_block_breadcrumb"></div>

                    <div id="fiction_search_fandoms_block_categories" name="fiction_search_fandoms_block_categories">
                        <div id="fiction_search_fandoms_block_categories_header" name="fiction_search_fandoms_block_categories_header" class="ui-widget-header ui-corner-all fiction-search-fandoms-block-categories"><?php _e('Fandom Selection','fe-fiction'); ?></div>
                        <div id="fiction_search_fandoms_block_categories_content" name="fiction_search_fandoms_block_categories_content" class="ui-widget-content ui-corner-all fiction-search-fandoms-block-categories"></div>
                    </div>
                    <br />
                </div>
                <?php
            }
            ?>

            <?php
           $search_cat_args = array(
                'show_option_all'	=> __('Fandoms','fe-fiction'),
                'show_option_none'   => '',
                'orderby'			=> 'name',
                'order'			  => 'ASC',
                'show_last_update'   => 0,
                'show_count'		 => 1,
                'hide_empty'		 => 0,
                'child_of'		   => 0,
                'exclude'			=> '3',
                'echo'			   => 1,
                'selected'		   => $cur_query_string['story_category'],
                'hierarchical'	   => 0,
                'name'			   => 'story_category',
                'id'				 => 'story_category',
                'class'			  => 'postform',
                'depth'			  => 0,
                'tab_index'		  => 0,
                'taxonomy'		   => 'story_category',
                'hide_if_empty'	  => 0,
            );
			
            /*$search_cat_args['show_option_all'] = __('Fandoms','fe-fiction');
            $search_cat_args['name'] = 'story_category';
            $search_cat_args['id'] = 'story_category';
            $search_cat_args['taxonomy'] = 'story_category';
            $search_cat_args['hierarchical'] = 0;
            $search_cat_args['selected'] = $cur_query_string['story_category'];
            $search_cat_args['show_count'] = 0;*/
            wp_dropdown_categories( $search_cat_args );

            if($total_num_fandoms <= FANDOMS_SEARCH_MAX_NUM_FANDOMS_FOR_DROPDOWN)
            {
                wp_dropdown_categories( $search_cat_args );
            }
            ?>
            <?php
            $search_user_args = array(
                'show_option_all'		 => __('Authors','fe-fiction'),
                'show_option_none'		=> '',
                'hide_if_only_one_author' => 0,
                'orderby'				 => 'display_name',
                'order'				   => 'ASC',
                'include'				 => '',
                'exclude'				 => '1',
                'multi'				   => 0,
                'show'					=> 'display_name',
                'echo'					=> 1,
                'selected'				=> $cur_query_string['story_author'],
                'include_selected'		=> 1,
                'name'					=> 'story_author',
                'id'					  => 'story_author',
                'class'				   => 'postform',
                'show_count'			  => 0,
                'blog_id'				 => $GLOBALS['blog_id'],
                'who'					 => '',
                'hide_empty'		 => 1,
                'hide_if_empty'	  => 0
            );

            wp_dropdown_users( $search_user_args );
            ?>
            <?php
            $search_cat_args['show_option_all'] = __('Genres','fe-fiction');
            $search_cat_args['name'] = 'genre';
            $search_cat_args['id'] = 'genre';
            $search_cat_args['taxonomy'] = 'genre';
            $search_cat_args['hierarchical'] = 0;
            $search_cat_args['selected'] = $cur_query_string['genre'];
            $search_cat_args['show_count'] = 0;
            wp_dropdown_categories( $search_cat_args );

            $search_cat_args['show_option_all'] = __('Ratings','fe-fiction');
            $search_cat_args['name'] = 'rating';
            $search_cat_args['id'] = 'rating';
            $search_cat_args['taxonomy'] = 'rating';
            $search_cat_args['hierarchical'] = 0;
            $search_cat_args['selected'] = $cur_query_string['rating'];
            $search_cat_args['show_count'] = 0;
            wp_dropdown_categories( $search_cat_args );

            $search_cat_args['show_option_all'] = __('Characters','fe-fiction');
            $search_cat_args['name'] = 'pairings';
            $search_cat_args['id'] = 'pairings';
            $search_cat_args['taxonomy'] = 'pairings';
            $search_cat_args['hierarchical'] = 0;
            $search_cat_args['hide_if_empty'] = true;
            $search_cat_args['selected'] = $cur_query_string['pairings'];
            $search_cat_args['show_count'] = 0;
            wp_dropdown_categories( $search_cat_args );

            ?>
            <br /><br />
            <?php printf(__('Display %s results per page'),$per_page_select); ?>
                <br /><br />
                <span id="fiction_search_submit_btn" name="submit"><?php echo __('Search','fe-fiction'); ?></span>
        </form>
    </div>
</div>
