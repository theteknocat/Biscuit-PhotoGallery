<?php
print $Navigation->render_admin_bar($PhotoGalleryManager,null,array(
	'bar_title' => __('Gallery Admin'),
	'has_new_button' => $PhotoGalleryManager->user_can_create(),
	'new_button_label' => __('New Album')
));
if (empty($albums)) {
	?><p class="none-found"><?php echo __('There are currently no albums in the gallery.'); ?></p><?php
} else {
	?><div id="photo-gallery-content"><?php
	foreach ($albums as $album) {
		?><div id="album-item-<?php echo $album->id(); ?>" class="gallery-item" style="width: <?php echo THUMB_WIDTH+2; ?>px;"><?php
		if ($PhotoGalleryManager->user_can_edit() || $PhotoGalleryManager->user_can_delete()) {
			?><div class="controls"><?php
			if ($PhotoGalleryManager->user_can_edit() && count($albums) > 1) {
				?><div class="drag-handle" title="<?php echo __('Drag to Sort') ?>"><div class="drag-icon"><?php echo __('Drag to Sort'); ?></div></div><?php
			}
			if ($PhotoGalleryManager->user_can_delete()) {
				?><a href="<?php echo $PhotoGalleryManager->url('delete', $album->id()); ?>" data-item-type="<?php echo __('Album'); ?>" data-item-title="<?php echo Crumbs::entitize_utf8($album->title()) ?>" title="<?php echo sprintf(__('Delete %s'),$album->title()); ?>" class="delete-button"><?php echo __('Delete') ?></a><?php
			}
			if ($PhotoGalleryManager->user_can_edit()) {
				?><a href="<?php echo $PhotoGalleryManager->url('edit', $album->id()); ?>" title="<?php echo sprintf(__('Edit %s'),$album->title()); ?>" class="edit-button"><?php echo __('Edit'); ?></a><?php
			}
			?></div><?php
		}
		$fcache = new FragmentCache('Album',$album->id());
		if ($fcache->start('album-thumbnail')) {
			$first_photo = $album->first_photo();
			if (!empty($first_photo)) {
				$img_src = $first_photo->thumbnail_path('image').'/_'.$first_photo->image();
			} else {
				$img_src = '/modules/photo_gallery/images/empty.jpg';
			}
			?>
			<a class="gallery-item-link image" style="width: <?php echo THUMB_WIDTH; ?>px; height: <?php echo THUMB_HEIGHT; ?>px;" title="<?php echo sprintf(__('View %s'),$album->title()); ?>" href="<?php echo $PhotoGalleryManager->url('show', $album->id()); ?>">
				<img width="<?php echo THUMB_WIDTH; ?>" src="<?php echo $img_src; ?>" alt="<?php echo $album->title(); ?>">
			</a>
			<a href="<?php echo $PhotoGalleryManager->url('show', $album->id()); ?>" class="gallery-item-link text"><?php echo $album->title(); ?></a>
			<?php
			$fcache->end('album-thumbnail');
		}
		?></div><?php
	}
	?><div class="clearance"></div></div><?php
}
if (!empty($albums) && count($albums) > 1 && $PhotoGalleryManager->user_can_edit()) {
?>
<script type="text/javascript">
	<?php
	$token_info = RequestTokens::get();
	?>
	var sortable_request_token = '<?php echo $token_info['token']; ?>';
	var sortable_token_form_id = '<?php echo $token_info['form_id']; ?>';
	$(document).ready(function() {
		Gallery.init_sortable('album','<?php echo $PhotoGalleryManager->url(); ?>');
	});
</script>
<?php
}
