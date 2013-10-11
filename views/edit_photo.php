<?php
$return_url = $PhotoGalleryManager->return_url();
$album_url = $PhotoGalleryManager->url('show',$photo->album_id());
if ($photo->is_new() || $return_url == $album_url) {
	$cancel_url = $album_url;
} else {
	$cancel_url = $PhotoGalleryManager->url('show_photo',$photo->id());
}
?>
<?php print Form::header($photo,'photo-edit-form'); ?>

	<?php print ModelForm::hidden($photo, 'album_id'); ?>

	<?php print ModelForm::hidden($photo, 'sort_order'); ?>

	<?php print ModelForm::select($album_select_options, $photo, 'album_id'); ?>

	<?php print ModelForm::text($photo, 'title'); ?>

	<?php print ModelForm::textarea($photo, 'description'); ?>

	<?php print ModelForm::file($photo, 'image', sprintf(__('Select a JPG, GIF or PNG image up to 5MB or %d megapixels in size.'), Image::megapixel_limit())); ?>

	<?php print Form::footer($PhotoGalleryManager,$photo,(!$photo->is_new() && $PhotoGalleryManager->user_can_delete()),__('Save'),$cancel_url); ?>
	
<script type="text/javascript">
	$('#photo-edit-form').submit(function(){
		new Biscuit.Ajax.FormValidator('photo-edit-form');
		return false;
	});
</script>
