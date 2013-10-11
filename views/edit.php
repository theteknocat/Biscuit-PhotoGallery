<?php
$cancel_url = null;
if (!$album->is_new()) {
	$cancel_url = $PhotoGalleryManager->url('show',$album->id());
}

print Form::header($album,'album-edit-form'); ?>

	<?php print ModelForm::hidden($album, 'sort_order'); ?>

	<?php print ModelForm::text($album, 'title'); ?>

	<?php print Form::footer($PhotoGalleryManager,$album,(!$album->is_new() && $PhotoGalleryManager->user_can_delete_album()),__('Save'),$cancel_url); ?>

<script type="text/javascript">
	$('#album-edit-form').submit(function(){
		new Biscuit.Ajax.FormValidator('album-edit-form');
		return false;
	});
</script>
