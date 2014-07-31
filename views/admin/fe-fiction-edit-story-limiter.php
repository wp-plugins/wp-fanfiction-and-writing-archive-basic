<?php
$parent_story = (int)get_post_meta( $post->ID, FIC_POST_CUSTOM_FIELDS_PREFIX . 'parent_story', true );

if($parent_story != '' && $parent_story > 0 && $parent_story != $post->ID)
{
?>
<script type="text/javascript">
jQuery('#titlewrap').html('<div class="fe-admin-titlewrap"><?php echo __("Book Title: ","fe-fiction"); ?><span class="fe-admin-title-span"><?php echo $post->post_title; ?></span></div><input type="hidden" name="post_title" value="<?php echo $post->post_title; ?>" />');
</script>
<?php
}
?>
