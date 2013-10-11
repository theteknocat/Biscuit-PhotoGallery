<?php
print $Navigation->render_admin_bar($PhotoGalleryManager,$photo,array(
	'bar_title' => __('Gallery Admin'),
	'has_edit_button' => $PhotoGalleryManager->user_can_edit_photo(),
	'edit_button_label' => __('Edit Photo'),
	'edit_button_class' => 'photo-edit-button',
	'has_new_button' => false,
	'has_del_button' => $PhotoGalleryManager->user_can_delete_photo(),
	'del_button_rel' => __('this photo'),
	'del_button_class' => 'photo-delete-button'
));
ob_start();
?>
<div class="controls photo-show-controls"><span class="admin"><?php echo sprintf(__('%d of %d'), $photo->sort_order(), $record_count); ?></span><a href="<?php echo $PhotoGalleryManager->url('show',$photo->album_id()); ?>" class="prev-button-bigjump"><?php echo __('Back to Album'); ?></a>
	<?php
	// We always render both prev and next buttons and simply hide with CSS if there is no prev or next image as we need the anchor tags to exist for the ajaxy nav to work properly. If we don't render one, then it won't be able to be shown upon navigating to another image
	?><a style="<?php if (empty($next_photo)) { ?> display: none;<?php } ?>" href="<?php if (!empty($next_photo)) { echo $PhotoGalleryManager->url('show_photo', $next_photo->id()); } ?>" class="btn-right next-button"><?php echo __('Next'); ?></a>
	<a style="<?php if (empty($prev_photo)) { ?> display: none;<?php } ?>" href="<?php if (!empty($prev_photo)) { echo $PhotoGalleryManager->url('show_photo', $prev_photo->id()); } ?>" class="btn-right prev-button"><?php echo __('Prev'); ?></a>
</div>
<?php
$nav_controls = ob_get_flush();
?>
<div id="photo-gallery-content" class="individual-photo"><?php
$fcache = new FragmentCache('Photo',$photo->id());
if ($fcache->start('full-photo-view')) {
	$finfo = $photo->image_info('image');
	?><div class="large-image" style="width: <?php echo $finfo['width']+2; ?>px; height: <?php echo $finfo['height']+2; ?>px;"><?php
	if (!empty($finfo['original_url'])) {
		?><a href="<?php echo $PhotoGalleryManager->url('download_original', $photo->id()); ?>" title="<?php echo __('Download original image file'); ?>" style="width: <?php echo $finfo['width']+2; ?>px; height: <?php echo $finfo['height']+2; ?>px"><?php
	}
	?><img src="<?php echo $finfo['download_url'] ?>" width="<?php echo $finfo['width']; ?>" height="<?php echo $finfo['height']; ?>" alt="<?php echo $photo->title() ?>" border="0"><?php
	if (!empty($finfo['original_url'])) {
		?></a><?php
	}
	if ($photo->description()) {
		$description = Crumbs::auto_paragraph(H::purify_text($photo->description()));
		?><div id="photo-description" style="width: <?php echo $finfo['width']; ?>px;"><?php echo $description; ?></div><?php
	}
	?></div><?php
	// Cache this image's data in a JSON object for the JS navigation, so it doesn't have to ajax load this one when flipping back to it
	$json_data = Crumbs::to_json(array(
		'id'             => $photo->id(),
		'original_url'   => ((!empty($finfo['original_url'])) ? $PhotoGalleryManager->url('download_original', $photo->id()) : false),
		'photo_url'      => $finfo['download_url'],
		'photo_edit_url' => ($PhotoGalleryManager->user_can_edit_photo() ? $PhotoGalleryManager->url('edit_photo',$photo->id()) : false),
		'photo_del_url'  => ($PhotoGalleryManager->user_can_delete_photo() ? $PhotoGalleryManager->url('delete_photo',$photo->id()) : false),
		'img_width'      => $finfo['width'],
		'img_height'     => $finfo['height'],
		'title'          => $Biscuit->Page->title(),
		'full_title'     => $Biscuit->Page->full_title(),
		'description'    => ((!empty($description)) ? $description : false),
		'count_text'     => sprintf(__('%d of %d'), $photo->sort_order(), $PhotoGalleryManager->Photo->record_count('`album_id` = '.$photo->album_id())),
		'prev_url'       => ((!empty($prev_photo)) ? $PhotoGalleryManager->url('show_photo', $prev_photo->id()) : false),
		'next_url'       => ((!empty($next_photo)) ? $PhotoGalleryManager->url('show_photo', $next_photo->id()) : false)
	));
	?>
<script type="text/javascript">
	$(document).ready(function() {
		Gallery.Nav.cache_json_data(<?php echo $json_data; ?>);
		// IE7< workaround, since it doesn't usually fire the load event on images once cached
		Gallery.Nav.image_cached[<?php echo $photo->id(); ?>] = true;
	});
</script>
	<?php
	$fcache->end('full-photo-view');
}
?></div>
<?php
echo $nav_controls;
?><script type="text/javascript">
	$(document).ready(function() {
		Gallery.description_init();<?php
		if ($record_count > 1) {
			?>

		Gallery.Nav.init();
<?php
		}
		?>
	});
</script>
