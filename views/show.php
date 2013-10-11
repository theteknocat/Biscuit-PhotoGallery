<?php
$photos = $album->photos(array('sort_order' => 'ASC'));
if (!Request::is_ajax()) {
	print $Navigation->render_admin_bar($PhotoGalleryManager,$album,array(
		'bar_title' => __('Gallery Admin'),
		'has_edit_button' => $PhotoGalleryManager->user_can_edit(),
		'edit_button_label' => __('Edit Album'),
		'has_new_button' => false
	));
	if ($PhotoGalleryManager->user_can_create_photo()) {
	?>
<fieldset>
	<form name="add-photos-form" id="add-photos-form" method="POST" action=""><?php
		$photo = ModelFactory::instance('Photo')->create(array('album_id' => $album->id()));
		$full_upload_path = $photo->upload_path('image');
		$upload_path = substr($full_upload_path,12);	// Everything after "/var/uploads"
		?>
		<div class="<?php echo $Navigation->tiger_stripe('striped_Album_form'); ?> complex-element">
			<label><?php echo __('Add Photos'); ?>:</label>
			<div class="complex-element-container">
				<div id="uploadify-alt-content" style="display: none;">
					<p style="clear: none;"><?php echo __('You must add photos one at a time since you do not have an adequate version of Flash installed to use the Flash uploader.'); ?></p>
					<div class="controls"><a href="<?php echo $PhotoGalleryManager->url('new_photo'); ?>?photo_defaults[album_id]=<?php echo $album->id(); ?>" class="btn-left new-button"><?php echo __('Add Photo'); ?></a></div>
					<div class="clearance"></div>
				</div>
				<div id="uploadify-field-content" style="display: none;">
					<?php
					$update_url = $PhotoGalleryManager->url('show',$album->id());
					$album_refresh = <<<JAVASCRIPT
function() {
	Gallery.show_throbber(__('refreshing'));
	Biscuit.Ajax.Request('$update_url','update',{
		update_container_id: 'photo-gallery-content'
	});
}
JAVASCRIPT;
					$onselect = <<<JAVASCRIPT
function() {
	Gallery.show_throbber(__('processing_uploads'));
}
JAVASCRIPT;
					$uploadify_options = array(
						'fileDesc'      => __('Image Files'),
						'fileExt'       => '*.jpg;*.gif;*.png',
						'multi'         => true,
						'scriptData'    => array('album_id' => $album->id()),
						'buttonText'    => __('Select Images'),
						'onAllComplete' => $album_refresh,
						'onSelectOnce'  => $onselect
					);
					print $UploadifyManager->render_upload_field('photo_upload','photo-upload',$upload_path, UploadifyManager::CHECK_EXISTING, UploadifyManager::OVERWRITE_EXISTING, $uploadify_options);
					?>
					<p class="instructions"><?php echo sprintf(__('Select one or more JPEG, GIF or PNG images up to %s or %d megapixels in size (each) to add to this album. Selected photos will appear in the album immediately.'), FileUpload::max_size(true), Image::megapixel_limit()); ?></p>
					<p class="instructions"><?php echo sprintf(__('You are using the Flash uploader. If you have problems, <a href="%s">use the alternate upload form</a>.'), $PhotoGalleryManager->url('new_photo').'?photo_defaults[album_id]='.$album->id()); ?></p>
				</div>
			</div>
			<script type="text/javascript">
				$(document).ready(function() {
					if (swfobject.hasFlashPlayerVersion("9.0.24")) {
						$('#uploadify-field-content').show();
					} else {
						$('#uploadify-alt-content').show();
					}
				});
			</script>
		</div>
	</form>
</fieldset><?php
	}
	if (!empty($photos)) {
		ob_start();
		?><div class="controls"><a href="<?php echo $PhotoGalleryManager->url(); ?>" class="prev-button-bigjump"><?php echo __('Gallery Home'); ?></a><a style="float: right; margin: 0 0 0 10px" href="<?php echo $PhotoGalleryManager->url('download_album',$album->id()); ?>" class="save-button"><?php echo __('Download Album (ZIP)'); ?></a></div><?php
		$album_nav = ob_get_flush();
	}
	?><div id="photo-gallery-content"<?php if (empty($photos)) { ?> class="no-items"<?php } ?>><?php
}
if (!empty($bad_uploads) && is_array($bad_uploads)) {
	$dialog_content = '<h4>'.__('The following files were not saved as they are not valid image files:').'</h4>';
	$dialog_content .= '<ul><li>'.implode('</li><li>',$bad_uploads).'</li></ul>';
	?>
<script type="text/javascript">
	<?php
	if (!Request::is_ajax()) {
		?>
	$(document).ready(function() {
		<?php
	}
	?>
	Biscuit.Crumbs.Alert('<?php echo $dialog_content; ?>','Failed Uploads');
	<?php
	if (!Request::is_ajax()) {
		?>
	});
		<?php
	}
	?>
</script>
	<?php
}
if (empty($photos)) {
	?><p class="none-found"><?php echo __('There are currently no photos in this album.'); ?></p><?php
} else {
	foreach ($photos as $photo) {
		if ($photo->title()) {
			$display_title = $photo->title();
		} else {
			$display_title = $photo->image();
		}
		?><div id="photo-item-<?php echo $photo->id(); ?>" class="gallery-item" style="width: <?php echo THUMB_WIDTH+2; ?>px;"><?php
		if ($PhotoGalleryManager->user_can_edit_photo() || $PhotoGalleryManager->user_can_delete_photo()) {
			?><div class="controls"><?php
			if ($PhotoGalleryManager->user_can_edit_photo() && count($photos) > 1) {
				?><div class="drag-handle" title="<?php echo __('Drag to Sort') ?>"><div class="drag-icon"><?php echo __('Drag to Sort'); ?></div></div><?php
			}
			if ($PhotoGalleryManager->user_can_delete_photo()) {
				?><a href="<?php echo $PhotoGalleryManager->url('delete_photo',$photo->id()); ?>" data-item-type="<?php echo __('Photo'); ?>" data-item-title="<?php echo Crumbs::entitize_utf8($display_title); ?>" title="<?php echo __('Delete this Photo'); ?>" class="delete-button"><?php echo __('Delete'); ?></a><?php
			}
			if ($PhotoGalleryManager->user_can_edit_photo()) {
				?><a href="<?php echo $PhotoGalleryManager->url('edit_photo',$photo->id()); ?>?return_url=<?php echo $PhotoGalleryManager->url('show',$album->id()); ?>" title="<?php echo __('Edit this Photo'); ?>" class="edit-button"><?php echo __('Edit'); ?></a><?php
			}
			?></div><?php
		}
		$fcache = new FragmentCache('Photo',$photo->id());
		if ($fcache->start('photo-thumbnail')) {
			$finfo = $photo->image_info('image');
			?><a href="<?php echo $PhotoGalleryManager->url('show_photo',$photo->id()); ?>" class="gallery-item-link image" style="width: <?php echo THUMB_WIDTH; ?>px; height: <?php echo THUMB_HEIGHT; ?>px;" title="<?php echo $photo->title() ?>">
				<img src="<?php echo $finfo['thumbnail_url'] ;?>" <?php echo $finfo['thumb_attributes']; ?> alt="<?php echo $photo->title() ?>" border="0">
			</a>
			<a href="<?php echo $PhotoGalleryManager->url('show_photo',$photo->id()); ?>" class="gallery-item-link text"><?php echo $display_title; ?></a><?php
			$fcache->end('photo-thumbnail');
		}
		?></div><?php
	}
	?><div class="clearance"></div><?php
}
if (!Request::is_ajax()) {
	?></div><?php
	if (!empty($album_nav)) {
		echo $album_nav;
	}
}
if (!empty($photos) && ($PhotoGalleryManager->user_can_edit_photo() || $PhotoGalleryManager->user_can_delete_photo())) {
?>
<script type="text/javascript">
	<?php
	if (count($photos) > 1) {
		$token_info = RequestTokens::get();
		?>
		var sortable_request_token = '<?php echo $token_info['token']; ?>';
		var sortable_token_form_id = '<?php echo $token_info['form_id']; ?>';
		<?php
		if (!Request::is_ajax()) {
			?>$(document).ready(function() { <?php
		}
		?>Gallery.init_sortable('photo','<?php echo $PhotoGalleryManager->url(); ?>'); <?php
		if (!Request::is_ajax()) {
			?> }); <?php
		}
	}
	if (Request::is_ajax()) {
		// On Ajax requests, apply delete and edit button jquery UI button styles and make sure the 'no-items' class is not present on the gallery content container
		?>
	$('.delete-button').button({
		icons: {
			primary: 'ui-icon-trash'
		}
	});
	$('.edit-button').button({
		icons: {
			primary: 'ui-icon-pencil'
		}
	});
	$('#photo-gallery-content').removeClass('no-items');
		<?php
	}
	?>
</script>
<?php
}
