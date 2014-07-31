<?php get_header(); ?>
<?php
echo $_GET['story_category'];
fanfic_process_fiction_views_content();
$chapters_avail_struct = FeFiction_Get_Related_Stories(FeFiction_the_ID(false));
?>
<div class="single_fiction" style="float:<?php echo get_option(FIC_OPTION_FICTION_POSITION); ?>">
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<div id="content">
	<div class="post">
		<div class="entry">
			<h1 class="entry-title"><?php _e('Fan Fiction','fe-fiction'); ?></h1>

    <?php if(!is_user_logged_in()){ ?>

        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#fic_age_verification_submit_btn').button().click(function(){
                    var ficprofileage = document.getElementById('fic_profile_age').value;
                    if(ficprofileage<17){
                        window.scrollTo(0, 0);
                    }else{
                        window.scrollTo(0, 0);
                        $('#singlefic1').removeClass('fictionhide');
                        $('#singlefic').addClass('fictionhide');
                    }
                });
            });
        </script>
        <?php

        $story_rating = wp_get_object_terms(FeFiction_the_ID(false), 'rating');
        $story_rating_slug = $story_rating[0]->slug;
        $story_rating_display_value = $story_rating[0]->name; ?>
        <div id="singlefic" <?php if(in_array(strtolower($story_rating_slug),$GLOBALS['FIC_RATINGS_REQUIRING_AGE_VERIFICATION']) && $GLOBALS['FIC_USERS_AGE'] < FIC_RATINGS_MIN_AGE){ }else{ echo "class='fictionhide'"; } ?>>
            <?php

            include(FIC_PLUGIN_ABS_PATH_DIR.'/views/fe-fiction-site-profile_age_form.php');



            ?>
        </div>
        <div id="singlefic1" <?php if(in_array(strtolower($story_rating_slug),$GLOBALS['FIC_RATINGS_REQUIRING_AGE_VERIFICATION']) && $GLOBALS['FIC_USERS_AGE'] < FIC_RATINGS_MIN_AGE){ echo "class='fictionhide'";} ?>>
            <h2 class="entry-title fe-fiction"><a href="<?php FeFiction_the_permalink(post_permalink()); ?>" title="<?php printf( esc_attr__( 'Permalink to %s'), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?><?php


				if($chapters_avail_struct[FeFiction_the_ID(false)]['chapter_number'] != '' || $chapters_avail_struct[FeFiction_the_ID(false)]['chapter_number'] != '')
                {
                    echo ' ( ';
                    if($chapters_avail_struct[FeFiction_the_ID(false)]['chapter_number'] != '')
                    {
                        echo $chapters_avail_struct[FeFiction_the_ID(false)]['chapter_number'];
                        echo '. ';
                    }
                    if($chapters_avail_struct[FeFiction_the_ID(false)]['chapter_title'] != '')
                    {
                        echo $chapters_avail_struct[FeFiction_the_ID(false)]['chapter_title'];
                    }
                    echo ' ) ';
                }
			?></a></h2>

        <div class="post-info-top fe-fiction">
            <span class="post-info-date fe-fiction"><?php printf(__('Story written on %1$s by %2$s.','fe-fiction'), '<strong>'.get_the_date().'</strong>', '<strong><a href="/author-profile/?user='.get_the_author_meta('ID').'">'.get_the_author().'</a></strong>'); ?></span>
        </div>

        <div>
            <?php
            $terms = get_the_terms( FeFiction_the_ID(false) , 'books');
            $term_list = wp_get_post_terms(FeFiction_the_ID(false), 'books', array("fields" => "ids"));
            $book_term_id = $term_list[0];
            if($terms){
                $category = $book_term_id;
                $args = array(
                    'post_type' => 'fiction',
                    'order' => 'DESC',
                    'posts_per_page' => -1,
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'books',
                            'terms' => "$category"
                        )
                    )
                );?>
                <?php $homePost = get_posts( $args ); $lastpart = $homePost[0];
                $booksarray = array();
                for($i=0;$i<count($homePost);$i++){
                    $bookss = $homePost[$i];

                    $booksarray[$i] = array('ID'=>$bookss->ID,'Title'=>$bookss->post_title,'Link'=>$bookss->guid);

                }
                $my_query = new WP_Query(); $my_query->query($args);?>
                <form>
                    <select name="related_stories" id="related_stories" onChange="location.href=jQuery('#related_stories option:selected').val()">
                        <?php


                        for($j=0;$j<count($booksarray);$j++){


                            ?>
                            <option value="<?php echo FeFiction_the_permalink(get_post_permalink($booksarray[$j]['ID'])); ?>" <?php if($booksarray[$j]['ID'] == FeFiction_the_ID(false)) { ?>selected="selected"<?php } ?>><?php
                                echo $booksarray[$j]['Title'];
                                $chapter_number = get_post_meta( $booksarray[$j]['ID'], '_fic_chapter_number', true );
                                $chapter_title = get_post_meta( $booksarray[$j]['ID'], '_fic_chapter_title', true );
                                if($chapter_number != '' || $chapter_title != '')
                                {
                                    echo ' (';
                                    if($chapter_number != '')
                                    {
                                        echo $chapter_number;
                                        echo '. ';
                                    }
                                    if($chapter_title != '')
                                    {
                                        echo $chapter_title;
                                    }
                                    echo ')';
                                }
                                ?></option>

                        <?php
                        }
                        ?>


                    </select>
                </form>
            <?php
            }?>
        </div>
        <!-- Weptile for advertisement-->
        <div class="fe-view-single-weptile">
            <?php if(function_exists('the_ratings') && FeFiction_Fiction_Scoring_Enabled(FeFiction_the_ID(false),$post->post_author)) { ?>

            <?php } ?>

            <div class="entry-meta fe-fiction">
                <?php echo FeFiction_the_terms( FeFiction_the_ID(false), 'genre', '<strong>'.__('Genre(s)','fe-fiction').':</strong> ', ' , ', '' ); ?>
                <br /><?php FeFiction_the_terms( FeFiction_the_ID(false), 'rating', '<strong>'.__('Rating','fe-fiction').':</strong> ', ' , ', '' ); ?>
                <br /><?php echo FeFiction_the_terms( FeFiction_the_ID(false), 'story_category', '<strong>'.__('Fandom(s)','fe-fiction').':</strong> ', ' , ', '' ); ?>
                <br /><?php echo FeFiction_the_terms( FeFiction_the_ID(false), 'pairings', '<strong>'.__('Character(s)','fe-fiction').':</strong> ', ' , ', '' ); ?>
                <br /><strong><?php echo __('Number of Comments','fe-fiction'); ?>:</strong> <a href="#comments"><?php comments_number(__('None','fe-fiction'),__('1','fe-fiction'),__('%','fe-fiction')); ?></a>
                <br /><strong><?php echo __('Words','fe-fiction'); ?>:</strong> <?php echo FeFiction_Count_Story_Words (FeFiction_the_ID(false),''); ?>
                <?php if(function_exists('get_fiction_views')) { ?>
                    <br /><strong><?php echo __('Views','fe-fiction'); ?>:</strong> <?php echo get_fiction_views('normal','total','content',false,$post->ID,false); ?>
                <?php } ?>
                <br /><strong><?php echo __('Story Shortlink','fe-fiction'); ?>:</strong> <a href="<?php echo wp_get_shortlink(); ?>"><?php echo wp_get_shortlink(); ?></a>
            </div>
            <div class="entry-meta fe-fiction">
                <?php FeFiction_the_metas( FeFiction_the_ID(false), 'author_notes', '<strong>'.__('Author Notes','fe-fiction').':</strong> ','' ); ?>
            </div>
            <?php
            /**
            ?>
            <div class="entry-meta fe-fiction">
            <?php FeFiction_the_metas( FeFiction_the_ID(false), 'disclaimer', '<strong>'.__('Disclaimer','fe-fiction').':</strong><br />','' ); ?>
            </div>
            <?php
             **/
            ?>


            <div class="entry-meta fe-fiction">
                <?php
                if(trim(FeFiction_the_metas( FeFiction_the_ID(false), 'summary', '','',false)) != '')
                {
                    if($filter_bad_words)
                    {
                        echo FeFiction_Filter_Bad_Words(FeFiction_the_metas( FeFiction_the_ID(false), 'summary', '<strong>'.__('Summary','fe-fiction').':</strong><br />','',false));
                    }
                    else
                    {
                        FeFiction_the_metas( FeFiction_the_ID(false), 'summary', '<strong>'.__('Summary','fe-fiction').':</strong><br />','' );
                    }
                }
                else
                {
                    if($filter_bad_words)
                    {
                        echo FeFiction_Filter_Bad_Words(FeFiction_the_excerpt('',false));
                    }
                    else
                    {
                        FeFiction_the_excerpt('');
                    }
                }
                ?>
            </div>

        </div>
        <div class="fe-frontend-clear">

        </div>
        <!-- end weptile for advertisment -->
        <div class="entry-content fe-fiction">
            <?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>','fe-fiction' ) ); ?>
            <?php
            if (function_exists("FeFiction_Pagination")) {
                global $numpages,$page;
                FeFiction_Pagination($numpages,$page,2,'story');
            }
            ?>
            <?php //wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:','fe-fiction'), 'after' => '</div>' ) ); ?>

        </div><!-- .entry-content -->
        <div class="weptile_buttons">
            <div class="weptile_button">
                <?php get_prev_post_by_author();?>
            </div>
            <div class="weptile_button">
                <?php ffmain();?>
            </div>
            <div class="weptile_button">
                <?php get_next_post_by_author();?>
            </div>
            <div class="fe-frontend-clear"></div>

        </div>


        <div class="fe-single-view-margin">
            <?php if(function_exists('get_shr_like_buttonset')) { get_shr_like_buttonset('Top'); } ?>
        </div>
        <?php if(function_exists('selfserv_shareaholic')) { selfserv_shareaholic(); } ?>

        <?php comments_template( '', true ); ?>
        <!-- END AGE -->
        </div>
    <?php }else{ ?>


            <?php
			$story_rating = wp_get_object_terms(FeFiction_the_ID(false), 'rating');
			$story_rating_slug = $story_rating[0]->slug;
			$story_rating_display_value = $story_rating[0]->name;
                if(in_array(strtolower($story_rating_slug),$GLOBALS['FIC_RATINGS_REQUIRING_AGE_VERIFICATION']) && $GLOBALS['FIC_USERS_AGE'] < FIC_RATINGS_MIN_AGE)
                {
                include(FIC_PLUGIN_ABS_PATH_DIR.'/views/fe-fiction-site-profile_age_form.php');
                }
                else
                {
                ?>
                <h2 class="entry-title fe-fiction"><a href="<?php FeFiction_the_permalink(post_permalink()); ?>" title="<?php printf( esc_attr__( 'Permalink to %s'), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?><?php


                    if($chapters_avail_struct[FeFiction_the_ID(false)]['chapter_number'] != '' || $chapters_avail_struct[FeFiction_the_ID(false)]['chapter_number'] != '')
                    {
                        echo ' ( ';
                        if($chapters_avail_struct[FeFiction_the_ID(false)]['chapter_number'] != '')
                        {
                            echo $chapters_avail_struct[FeFiction_the_ID(false)]['chapter_number'];
                            echo '. ';
                        }
                        if($chapters_avail_struct[FeFiction_the_ID(false)]['chapter_title'] != '')
                        {
                            echo $chapters_avail_struct[FeFiction_the_ID(false)]['chapter_title'];
                        }
                        echo ' ) ';
                    }
                ?></a></h2>

                <div class="post-info-top fe-fiction">
                    <span class="post-info-date fe-fiction"><?php printf(__('Story written on %1$s by %2$s.','fe-fiction'), '<strong>'.get_the_date().'</strong>', '<strong><a href="/author-profile/?user='.get_the_author_meta('ID').'">'.get_the_author().'</a></strong>'); ?></span>
                </div>

                <div>
                <?php
                    $terms = get_the_terms( FeFiction_the_ID(false) , 'books');
                    $term_list = wp_get_post_terms(FeFiction_the_ID(false), 'books', array("fields" => "ids"));
                    $book_term_id = $term_list[0];
                    if($terms){
                        $category = $book_term_id;
                    $args = array(
                    'post_type' => 'fiction',
                    'order' => 'DESC',
                    'posts_per_page' => -1,
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'books',
                            'terms' => "$category"
                            )
                        )
                    );?>
                    <?php $homePost = get_posts( $args ); $lastpart = $homePost[0];
                    $booksarray = array();
                    for($i=0;$i<count($homePost);$i++){
                        $bookss = $homePost[$i];

                        $booksarray[$i] = array('ID'=>$bookss->ID,'Title'=>$bookss->post_title,'Link'=>$bookss->guid);

                        }
                    $my_query = new WP_Query(); $my_query->query($args);?>
                    <form>
                    <select name="related_stories" id="related_stories" onChange="location.href=jQuery('#related_stories option:selected').val()">
                    <?php


                                for($j=0;$j<count($booksarray);$j++){


                ?>
                            <option value="<?php echo FeFiction_the_permalink(get_post_permalink($booksarray[$j]['ID'])); ?>" <?php if($booksarray[$j]['ID'] == FeFiction_the_ID(false)) { ?>selected="selected"<?php } ?>><?php
                            echo $booksarray[$j]['Title'];
                            $chapter_number = get_post_meta( $booksarray[$j]['ID'], '_fic_chapter_number', true );
                            $chapter_title = get_post_meta( $booksarray[$j]['ID'], '_fic_chapter_title', true );
                            if($chapter_number != '' || $chapter_title != '')
                            {
                                echo ' (';
                                if($chapter_number != '')
                                {
                                    echo $chapter_number;
                                    echo '. ';
                                }
                                if($chapter_title != '')
                                {
                                    echo $chapter_title;
                                }
                                echo ')';
                            }
                            ?></option>

                            <?php
                            }
                        ?>


                        </select>
                    </form>
                    <?php
                        }?>
                </div>
                    <!-- Weptile for advertisement-->
                <div class="fe-view-single-weptile">


                    <?php if(function_exists('the_ratings') && FeFiction_Fiction_Scoring_Enabled(FeFiction_the_ID(false),$post->post_author)) { ?>

                    <?php } ?>

                    <div class="entry-meta fe-fiction">
                        <?php echo FeFiction_the_terms( FeFiction_the_ID(false), 'genre', '<strong>'.__('Genre(s)','fe-fiction').':</strong> ', ' , ', '' ); ?>
                        <br /><?php FeFiction_the_terms( FeFiction_the_ID(false), 'rating', '<strong>'.__('Rating','fe-fiction').':</strong> ', ' , ', '' ); ?>
                        <br /><?php echo FeFiction_the_terms( FeFiction_the_ID(false), 'story_category', '<strong>'.__('Fandom(s)','fe-fiction').':</strong> ', ' , ', '' ); ?>
                        <br /><?php echo FeFiction_the_terms( FeFiction_the_ID(false), 'pairings', '<strong>'.__('Character(s)','fe-fiction').':</strong> ', ' , ', '' ); ?>
                        <br /><strong><?php echo __('Number of Comments','fe-fiction'); ?>:</strong> <a href="#comments"><?php comments_number(__('None','fe-fiction'),__('1','fe-fiction'),__('%','fe-fiction')); ?></a>
                        <br /><strong><?php echo __('Words','fe-fiction'); ?>:</strong> <?php echo FeFiction_Count_Story_Words (FeFiction_the_ID(false),''); ?>
                        <?php if(function_exists('get_fiction_views')) { ?>
                            <br /><strong><?php echo __('Views','fe-fiction'); ?>:</strong> <?php echo get_fiction_views('normal','total','content',false,$post->ID,false); ?>
                        <?php } ?>
                        <br /><strong><?php echo __('Story Shortlink','fe-fiction'); ?>:</strong> <a href="<?php echo wp_get_shortlink(); ?>"><?php echo wp_get_shortlink(); ?></a>
                    </div>
                    <div class="entry-meta fe-fiction">
                        <?php FeFiction_the_metas( FeFiction_the_ID(false), 'author_notes', '<strong>'.__('Author Notes','fe-fiction').':</strong> ','' ); ?>
                    </div>
                    <?php
                    /**
                    ?>
                    <div class="entry-meta fe-fiction">
                        <?php FeFiction_the_metas( FeFiction_the_ID(false), 'disclaimer', '<strong>'.__('Disclaimer','fe-fiction').':</strong><br />','' ); ?>
                    </div>
                    <?php
                    **/
                    ?>


                    <div class="entry-meta fe-fiction">
                        <?php
                        if(trim(FeFiction_the_metas( FeFiction_the_ID(false), 'summary', '','',false)) != '')
                        {
                            if($filter_bad_words)
                            {
                                echo FeFiction_Filter_Bad_Words(FeFiction_the_metas( FeFiction_the_ID(false), 'summary', '<strong>'.__('Summary','fe-fiction').':</strong><br />','',false));
                            }
                            else
                            {
                                FeFiction_the_metas( FeFiction_the_ID(false), 'summary', '<strong>'.__('Summary','fe-fiction').':</strong><br />','' );
                            }
                        }
                        else
                        {
                            if($filter_bad_words)
                            {
                                echo FeFiction_Filter_Bad_Words(FeFiction_the_excerpt('',false));
                            }
                            else
                            {
                                FeFiction_the_excerpt('');
                            }
                        }
                        ?>
                    </div>

                </div>
                <div class="fe-frontend-clear">

                </div>
                    <!-- end weptile for advertisment -->
                <div class="entry-content fe-fiction">
                    <?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>','fe-fiction' ) ); ?>
                    <?php
                    if (function_exists("FeFiction_Pagination")) {
                        global $numpages,$page;
                        FeFiction_Pagination($numpages,$page,2,'story');
                    }
                    ?>
                    <?php //wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:','fe-fiction'), 'after' => '</div>' ) ); ?>

                </div><!-- .entry-content -->
                <div class="weptile_buttons">
                    <div class="weptile_button">
                        <?php get_prev_post_by_author();?>
                    </div>
                    <div class="weptile_button">
                        <?php ffmain();?>
                    </div>
                    <div class="weptile_button">
                        <?php get_next_post_by_author();?>
                    </div>
                    <div class="fe-frontend-clear"></div>

                </div>


                <div class="fe-single-view-margin">
                    <?php if(function_exists('get_shr_like_buttonset')) { get_shr_like_buttonset('Top'); } ?>
                </div>
                <?php if(function_exists('selfserv_shareaholic')) { selfserv_shareaholic(); } ?>

                <?php comments_template( '', true ); ?>
                <!-- END AGE -->
                <?php }
            }?>
		</div>
	</div>
</div>
<?php endwhile; else: ?>
<p><?php _e('Sorry, no story matched your criteria.'); ?></p>
<?php endif; ?>
</div>
<?php get_sidebar(); ?>


<?php get_footer(); ?>
