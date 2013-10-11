<?php
	if (!$photo->is_new()) {
		$submit_url = $PhotoGalleryManager->url('edit_photo',$photo->id());
	}
	else {
		$submit_url = $PhotoGalleryManager->url('new_photo',$photo->album_id());
	}
?>
<form name="photo_editor" id="photo_editor" method="post" accept-charset="utf-8" action="<?php echo $submit_url?>" enctype="multipart/form-data">
	<?php echo RequestTokens::render_token_field(); ?>
	<input type="hidden" name="photo[album_id]" value="<?php echo $photo->album_id() ?>">
	<p>
		<?php echo FormField::text('title','photo[title]',$photo->title_label(),$photo->title(),$photo->title_is_required(),$photo->title_is_valid(),array('maxlength' => '255')); ?>
	</p>
	<p>
		<?php echo FormField::textarea('description','photo[description]',$photo->description_label(),$photo->description(),'6',$photo->description_is_required(),$photo->description_is_valid(),array('cols' => '20')); ?>
	</p>
	<p>
		<?php echo FormField::text('sort_order','photo[sort_order]',$photo->sort_order_label(),$photo->sort_order(),$photo->sort_order_is_required(),$photo->sort_order_is_valid(),array('size' => '4','maxlength' => '4')); ?>
	</p>
	<p>
		<?php
		$finfo = $photo->file_info('image');
		echo FormField::file('image','photo[image]',$photo->image_label(),$finfo,$photo->image_is_required(),$photo->image_is_valid(),array('upload_rules' => 'You can upload JPEG, GIF and PNG images up to 5MB.'));
		?>
	</p>
	<p class="controls">
		<input type="submit" name="SubmitButton" id="SubmitButton" class="SubmitButton" value="Save">
<?php
	if (!$photo->is_new()) /* if not new */ {
		?><a href="<?php echo $PhotoGalleryManager->url('delete_photo', $photo->id()); ?>" class="delete_photo" class="delete">Delete</a><?php
	}
	?></p>
	<p><a href="<?php echo $PhotoGalleryManager->url('show_album',$album->id()); ?>">&larr; <?php echo $album->title()?></a></p>
</form>
