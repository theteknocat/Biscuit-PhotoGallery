<h2>Album: <?php echo $album->title()?></h2>
<?php if ($PhotoGalleryManager->user_can_create_photo()) {
	?><p><a href="<?php echo $PhotoGalleryManager->url('new_photo',$album->id())?>">Add Photo</a></p><?php
}
if (!empty($photos)) {
	foreach ($photos as $photo) {
		$finfo = $photo->image_info('image');
		?>
			<div class="photo"><a href="<?php echo $finfo['download_url'] ;?>" class="lightview" rel="gallery[<?php echo $album->id()?>]" title="<?php echo $photo->title() ?>"><img src="<?php echo $finfo['thumbnail_url'] ;?>" <?php echo $finfo['thumb_attributes'] ?> alt="<?php echo $photo->title() ?>" border="0"><br><?php echo $photo->title() ?></a>
		<?php
		if ($PhotoGalleryManager->user_can_edit_photo() || $PhotoGalleryManager->user_can_delete_photo()) {
			?><br>[<?php
			if ($PhotoGalleryManager->user_can_edit_photo()) {
				?><a href="<?php echo $PhotoGalleryManager->url('edit_photo',$photo->id()); ?>">Edit</a><?php
				if ($PhotoGalleryManager->user_can_delete_photo()) {
					?> | <?php
				}
			}
			if ($PhotoGalleryManager->user_can_delete_photo()) {
				?><a href="<?php echo $PhotoGalleryManager->url('delete_photo',$photo->id()); ?>" class="delete_photo">Delete</a><?php
			}
			?>]<?php
		}
		?></div><?php
	}
}
else { ?>
	<p>There are currently no photos in this album.</p>
<?php
}
?>
<p><a href="<?php echo $PhotoGalleryManager->url(); ?>">&larr; Gallery Home</a></p>
