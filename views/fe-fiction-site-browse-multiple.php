<?php
				$terms = get_the_terms( $post->ID , 'books');
				$term_list = wp_get_post_terms($post->ID, 'books', array("fields" => "ids"));
				$book_term_id = $term_list[0];
				if($terms){?>

                <?php
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
                <?php $homePost = get_posts( $args );
				$lastnumber = count($homePost)-1;
				$lastpart = $homePost[0];

				$booksarray = array();
				for($i=0;$i<count($homePost);$i++){
					$bookss = $homePost[$i];

					$booksarray[$i] = array('Title'=>$bookss->post_title,'Link'=>$bookss->guid,'ID'=>$bookss->ID);

					}
				$my_query = new WP_Query(); $my_query->query($args);
				if($lastpart->ID == $post->ID){?>


               	 	<div id="post-<?php FeFiction_the_ID(); ?>" <?php post_class(); ?>>
					<h2 class="entry-title"><?php $term_list = wp_get_post_terms($post->ID, 'books', array("fields" => "names"));echo $term_list[0]; ?>
                        <h3><a href="<?php echo FeFiction_the_permalink(get_post_permalink($booksarray[0]['ID'])); ?>"><?php echo $booksarray[0]['Title']; ?></a></h3>
                        <form>
                            <select name="prev_stories_<?php echo $post->ID; ?>" id="prev_stories_<?php echo $post->ID; ?>" onChange="location.href=jQuery('#prev_stories_<?php echo $post->ID; ?> option:selected').val()">
                                <option value="" selected="selected">PREVIOUS CHAPTERS</option>
                                <?php
                                for($j=1;$j<count($booksarray);$j++){
                                        ?>
                                        <option value="<?php echo FeFiction_the_permalink(get_post_permalink($booksarray[$j]['ID'])); ?>"><?php echo $booksarray[$j]['Title']; ?></option>
                                    <?php } ?>


                            </select>
                        </form>


					<?php
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

							<span class="post-info-date fe-fiction">
								<?php printf(__('Book written on %1$s by %2$s.','fe-fiction'), '<strong>'.get_the_date().'</strong>', '<strong>'.get_the_author().'</strong>'); ?>
							</span>
						</div>

						<div class="entry-meta entry-utility fe-fiction">
							<?php if(function_exists('the_ratings_results') && FeFiction_Fiction_Scoring_Enabled(FeFiction_the_ID(false),$post->post_author)) { echo '<div class="entry-utility-scores fe-fiction">'.the_ratings_results(FeFiction_the_ID(false)).'</div>'; } ?>
							<?php echo FeFiction_the_terms( FeFiction_the_ID(false), 'genre', '<strong>'.__('Genre(s)','fe-fiction').':</strong> ', ' , ', '' ); ?>
							<br /><?php FeFiction_the_terms( FeFiction_the_ID(false), 'rating', '<strong>'.__('Rating','fe-fiction').':</strong> ', ' , ', '' ); ?>
							<br /><?php echo FeFiction_the_terms_fandom( FeFiction_the_ID(false), get_option(FIC_OPTION_FICTION_FANDOM_URL), '<strong>'.__('Fandom(s)','fe-fiction').':</strong> ', ' , ', '' ); ?>
							<br /><?php echo FeFiction_the_terms( FeFiction_the_ID(false), 'pairings', '<strong>'.__('Character(s)','fe-fiction').':</strong> ', ' , ', '' ); ?>
							<br /><strong><?php echo __('Story Shortlink','fe-fiction'); ?>:</strong> <a href="<?php echo wp_get_shortlink(); ?>"><?php echo wp_get_shortlink(); ?></a>
							<br /><?php printf(__('%1$d words.','fe-fiction'),FeFiction_Count_Story_Words (FeFiction_the_ID(false),'')); ?> <?php comments_number(__('No Comments','fe-fiction'),__('1 Comment','fe-fiction'),__('% Comments','fe-fiction')); ?>
						</div>

						<div class="entry-summary entry-utility fe-fiction">
							<?php
							if(trim(FeFiction_the_metas( FeFiction_the_ID(false), 'summary', '','',false)) != '')
							{
								if($filter_bad_words)
								{
									echo FeFiction_Filter_Bad_Words(FeFiction_the_metas( FeFiction_the_ID(false), 'summary', '','<br />',false ));
								}
								else
								{
									FeFiction_the_metas( FeFiction_the_ID(false), 'summary', '','<br />');
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

						<hr />
                </div>

				<?php	}
				?>

				<?php }else{	?>

					<div id="post-<?php FeFiction_the_ID(); ?>" <?php post_class(); ?>>
						<h2 class="entry-title"><a href="<?php FeFiction_the_permalink(post_permalink()); ?>" title="<?php printf( esc_attr__( 'Permalink to %s'), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?><?php
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

							<span class="post-info-date fe-fiction">
								<?php printf(__('Story written on %1$s by %2$s.','fe-fiction'), '<strong>'.get_the_date().'</strong>', '<strong>'.get_the_author().'</strong>'); ?>
							</span>
						</div>

						<div class="entry-meta entry-utility fe-fiction">
							<?php if(function_exists('the_ratings_results') && FeFiction_Fiction_Scoring_Enabled(FeFiction_the_ID(false),$post->post_author)) { echo '<div class="entry-utility-scores fe-fiction">'.the_ratings_results(FeFiction_the_ID(false)).'</div>'; } ?>
							<?php echo FeFiction_the_terms( FeFiction_the_ID(false), 'genre', '<strong>'.__('Genre(s)','fe-fiction').':</strong> ', ' , ', '' ); ?>
							<br /><?php FeFiction_the_terms( FeFiction_the_ID(false), 'rating', '<strong>'.__('Rating','fe-fiction').':</strong> ', ' , ', '' ); ?>
							<br /><?php echo FeFiction_the_terms( FeFiction_the_ID(false), 'story_category', '<strong>'.__('Fandom(s)','fe-fiction').':</strong> ', ' , ', '' ); ?>
							<br /><?php echo FeFiction_the_terms( FeFiction_the_ID(false), 'pairings', '<strong>'.__('Character(s)','fe-fiction').':</strong> ', ' , ', '' ); ?>
							<br /><strong><?php echo __('Story Shortlink','fe-fiction'); ?>:</strong> <a href="<?php echo wp_get_shortlink(); ?>"><?php echo wp_get_shortlink(); ?></a>
							<br /><?php printf(__('%1$d words.','fe-fiction'),FeFiction_Count_Story_Words (FeFiction_the_ID(false),'')); ?> <?php comments_number(__('No Comments','fe-fiction'),__('1 Comment','fe-fiction'),__('% Comments','fe-fiction')); ?>
						</div>

						<div class="entry-summary entry-utility fe-fiction">
							<?php
							if(trim(FeFiction_the_metas( FeFiction_the_ID(false), 'summary', '','',false)) != '')
							{
								if($filter_bad_words)
								{
									echo FeFiction_Filter_Bad_Words(FeFiction_the_metas( FeFiction_the_ID(false), 'summary', '','<br />',false ));
								}
								else
								{
									FeFiction_the_metas( FeFiction_the_ID(false), 'summary', '','<br />');
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

						<hr />
					</div>
				<?php } ?>