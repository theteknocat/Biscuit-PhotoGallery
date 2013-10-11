<div id="help-tabs">
	<ul>
		<li><a href="#help-basics">Basics</a></li>
		<li><a href="#help-album-view">Album View</a></li>
	</ul>
	<div id="help-basics">
		<p>The photo gallery module allows you to setup albums in which you can upload many photos. The photo gallery page provides a thumbnail view of albums, using the first photo in the album as the "cover", for example:</p>
		<p><img src="/modules/photo_gallery/images/help/albums-example.gif" alt="Example albums view"></p>
		<p>If no albums have been created yet, it will instead show a message indicating that.</p>
		<h4>Functions</h4>
		<ul>
			<li><strong>Create Album</strong><br>
				Click the "New Album" button in the Gallery Admin bar at the top. After creating the new album, you will be redirected to the album view to upload photos.</li>
			<li><strong>Rename Album</strong><br>
				Click the small pencil button above the album you wish to rename.</li>
			<li><strong>Delete Album</strong><br>
				To remove an entire album and all of it's photos, click the small trash button above the album you wish to delete.</li>
			<li><strong>Re-order Albums</strong><br>
				If there is more than one album, you will see a small drag widget <img src="/modules/photo_gallery/images/help/drag-widget.gif" alt="Drag widget" style="vertical-align: bottom;"> above each album. Click and drag this widget to move the album to a new position. When you let go the change will be saved.</li>
			<li><strong>Open Album</strong><br>
				Click the thumbnail or album label to open the album and view the photos.</li>
		</ul>
		<p>To create a new album, click the "New Album" button in the Gallery Admin bar at the top.</p>
	</div>
	<div id="help-album-view">
		<p>When you open an album, you will see administrative functions at the top followed by thumbnails of the photos, or text indicating if the album is empty, for example:</p>
		<p><a href="/modules/photo_gallery/images/help/album-view-example.jpg" title="Example Album View" class="lightbox"><img src="/modules/photo_gallery/images/help/album-view-example-sml.jpg"></a></p>
		<p>To rename the album, just click the "Edit Album" button at the top.</p>
		<h4>Adding Photos</h4>
		<p>If you have Flash version 9.0.24 or newer installed, you will see the "Select Images" button like in the example above:</p>
		<p><img src="/modules/photo_gallery/images/help/add-photos-flash.gif" alt="Flash uploader"></p>
		<p>Click the button to select one or more images from your computer to upload. You will see a progress bar for each image as it uploads. Wait until all uploads are complete, then the thumbnail view will be refreshed to reflect the newly uploaded photos. You can then re-order the photos and/or edit each one's individual title and description if desired.</p>
		<p>If you do not have Flash version 9.0.24 or newer installed, you will instead see a message indicating that you must add photos one at a time along with an "Add Photo" button:</p>
		<p><img src="/modules/photo_gallery/images/help/add-photo-noflash.gif" alt="No flash add photo"></p>
		<p>Clicking the "Add Photo" button takes you to a form with the following fields:</p>
		<ul>
			<li><strong>Album</strong> (required)<br>
				Select the album in which you want to add the photo. The album you came from will be selected by default.</li>
			<li><strong>Title</strong><br>
				If left blank, the filename will be used instead.</li>
			<li><strong>Description</strong><br>
				If provided, will be shown when you mouse over the photo in the default individual photo view. If your site was customized to display photos in a lightbox or by some other method, consult your web developer on where the description will be displayed, if at all.</li>
			<li><strong>Image</strong> (required)<br>
				Select the image file.</li>
		</ul>
		<h4>Other Functions</h4>
		<ul>
			<li><strong>Re-order Photos</strong><br>
				If there is more than one photo in the album, you will see a small drag widget <img src="/modules/photo_gallery/images/help/drag-widget.gif" alt="Drag widget" style="vertical-align: bottom;"> above each photo. Click and drag this widget to move the photo to a new position. When you let go, the change will be saved.</li>
			<li><strong>Edit Photo</strong><br>
				Click the pencil button above a photo to edit the photo's details (title, description, image file) or to move it to another album. See above for details on the form fields. When you edit an existing photo, however, the image file field will become optional, showing the current image and allowing you to select a different one to replace it with. This makes it very easy to replace an image with a better one, for example, where you already have a title and description entered instead of having to delete it then upload a new one.</li>
			<li><strong>Delete Photo</strong><br>
				Click the trash button above a photo do delete it.</li>
			<li><strong>View Photo</strong><br>
				Click anywhere on a photo thumbnail or the text title underneath it to view the larger image.</li>
		</ul>
	</div>
</div>
<p class="small" style="text-align: center;">Digital Blasphemy sample photos are Copyright &copy; 1997-2011 Ryan Bliss, <a href="http://digitalblasphemy.com">digitalblasphemy.com</a>.</p>
<script type="text/javascript" charset="utf-8">
	$(document).ready(function() {
		$('a.lightbox').fancybox();
	});
</script>