<?php
	if (!$album->is_new()) {
		$submit_url = $PhotoGalleryManager->url('edit_album',$album->id());
	}
	else {
		$submit_url = $PhotoGalleryManager->url('new_album');
	}
?>
<form name="album_editor" id="album_editor" method="post" accept-charset="utf-8" action="<?php echo $submit_url?>">
	<?php echo RequestTokens::render_token_field(); ?>
	<p>
		<?php echo FormField::text('title','album[title]',$album->title_label(),$album->title(),$album->title_is_required(),$album->title_is_valid(),array('maxlength' => '255')); ?>
	</p>
	<p>
		<?php echo FormField::text('sort_order','album[sort_order]',$album->sort_order_label(),$album->sort_order(),$album->sort_order_is_required(),$album->sort_order_is_valid(),array('size' => '4','maxlength' => '4')); ?>
	</p>
	<p class="controls">
		<input type="submit" name="SubmitButton" id="SubmitButton" class="SubmitButton" value="Save">
<?php
	if (!$album->is_new()) {
		?><a href="<?php echo $PhotoGalleryManager->url('delete_album', $album->id()); ?>" class="delete_album" class="delete">Delete</a><?php
	}
	?></p>
	<p><a href="<?php echo $PhotoGalleryManager->url(); ?>">&larr; Gallery Home</a></p>
</form>
