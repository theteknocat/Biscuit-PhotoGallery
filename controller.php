<?php
/**
 * Basic photo gallery module
 *
 * @package Modules
 * @subpackage PhotoGallery
 * @author Peter Epp
 * @copyright Copyright (c) 2009 Peter Epp (http://teknocat.org)
 * @license GNU Lesser General Public License (http://www.gnu.org/licenses/lgpl.html)
 * @version 2.0 $Id: controller.php 14533 2012-02-17 19:40:25Z teknocat $
 */
class PhotoGalleryManager extends AbstractModuleController {
	protected $_actions_requiring_id = array('download_original', 'download_album');
	protected $_dependencies = array('edit' => "Uploadify");
	protected $_models = array(
		"Album" => "Album",
		"Photo" => "Photo"
	);
	/**
	 * Array of sorting options for the index action in the format array("column_name" => "[ASC/DESC]")
	 *
	 * @var string
	 */
	protected $_index_sort_options = array('sort_order' => 'ASC');
	
	public function run() {
		$this->register_js("footer","photo-gallery.js");
		$this->register_css(array('filename' => 'photo_gallery.css', 'media' => 'screen'));
		parent::run();
	}
	/**
	 * Show the contents of a single album
	 *
	 * @return void
	 * @author Peter Epp
	 */
	protected function action_show() {
		$bad_uploads = Session::get('bad_gallery_uploads');
		Session::unset_var('bad_gallery_uploads');
		$this->set_view_var('bad_uploads', $bad_uploads);
		parent::action_show();
	}
	/**
	 * Set special view vars for showing a photo
	 *
	 * @return void
	 * @author Peter Epp
	 */
	protected function action_show_photo() {
		$photo = $this->model_for_showing($this->params['id']);
		$record_count = $this->Photo->record_count('`album_id` = '.$photo->album_id());
		$prev_photo = null;
		$next_photo = null;
		if ($record_count > 1) {
			$prev_photo = $this->Photo->find_by('album_id',$photo->album_id(),'`sort_order` = '.$photo->sort_order().'-1');
			if (!$prev_photo) {
				$prev_photo = $this->Photo->find_by('album_id',$photo->album_id(),'1 ORDER BY `sort_order` DESC');
			}
			$next_photo = $this->Photo->find_by('album_id',$photo->album_id(),'`sort_order` = '.$photo->sort_order().'+1');
			if (!$next_photo) {
				$next_photo = $this->Photo->find_by('album_id',$photo->album_id(),'1 ORDER BY `sort_order` ASC');
			}
		}
		if (Request::is_ajax()) {
			Event::fire('instantiated_model', $photo);
			$finfo = $photo->image_info('image');
			// Compose an array so we can send back a json response
			$this->set_show_title($photo);
			$response_vars = array(
				'id'             => $photo->id(),
				'original_url'   => ((!empty($finfo['original_url'])) ? $this->url('download_original', $photo->id()) : false),
				'photo_url'      => $finfo['download_url'],
				'photo_edit_url' => ($this->user_can_edit_photo() ? $this->url('edit_photo',$photo->id()) : false),
				'photo_del_url'  => ($this->user_can_delete_photo() ? $this->url('delete_photo',$photo->id()) : false),
				'img_width'      => $finfo['width'],
				'img_height'     => $finfo['height'],
				'title'          => $this->Biscuit->Page->title(),
				'full_title'     => $this->Biscuit->Page->full_title(),
				'description'    => (($photo->description()) ? Crumbs::auto_paragraph(H::purify_text($photo->description())) : false),
				'count_text'     => sprintf(__('%d of %d'), $photo->sort_order(), $record_count),
				'prev_url'       => ((!empty($prev_photo)) ? $this->url('show_photo', $prev_photo->id()) : false),
				'next_url'       => ((!empty($next_photo)) ? $this->url('show_photo', $next_photo->id()) : false)
			);
			$this->Biscuit->render_json($response_vars);
		} else {
			$this->set_view_var('prev_photo', $prev_photo);
			$this->set_view_var('next_photo', $next_photo);
			$this->set_view_var('record_count', $record_count);
			parent::action_show();
		}
	}
	/**
	 * Cause browser to download the original filename
	 *
	 * @return void
	 * @author Peter Epp
	 */
	protected function action_download_original() {
		$photo = $this->model_for_showing($this->params['id'], 'show_photo');
		if (!$photo) {
			throw new RecordNotFoundException();
		}
		$this->enforce_canonical_download_url('download_original',$this->params['id']);
		$finfo = $photo->image_info('image');
		if (!empty($finfo['original_url'])) {
			$full_file_path = SITE_ROOT.$finfo['original_url'];
			// This header is the HTTP specification for forcing browser to download and will always work with all browsers
			Response::content_type('application/octet-stream');
			Response::add_header('Content-Length',filesize($full_file_path));
			$this->Biscuit->render(file_get_contents($full_file_path));
		} else {
			Session::flash('user_error',__('There is no original file for this photo.'));
			Response::redirect($this->url('show',$photo->id()));
		}
	}
	/**
	 * ZIP all the photos in an album and output for user to download
	 *
	 * @return void
	 * @author Peter Epp
	 */
	protected function action_download_album() {
		$album = $this->model_for_showing($this->params['id'], 'show');
		if (!$album) {
			throw new RecordNotFoundException();
		}
		if (!is_writable(TEMP_DIR)) {
			Session::flash('user_error',__('Album cannot be downloaded as the temporary folder is not writable. Please contact the system administrator to resolve this problem.'));
			Response::redirect($this->url('show',$this->params['id']));
		}
		$photos = $album->photos();
		if (empty($photos)) {
			Session::flash('user_error',__("This album cannot be downloaded because it's empty."));
			Response::redirect($this->url('show',$this->params['id']));
		}
		$this->enforce_canonical_download_url('download_album',$this->params['id']);
		$output_file = TEMP_DIR.'/'.$album->friendly_slug().'.zip';
		$files = array();
		foreach ($photos as $photo) {
			$original_filepath = SITE_ROOT.$photo->upload_path('image').'/_originals/'.$photo->image();
			$normal_filepath = SITE_ROOT.$photo->upload_path('image').'/'.$photo->image();
			if (file_exists($original_filepath)) {
				$files[] = $original_filepath;
			} else {
				$files[] = $normal_filepath;
			}
		}
		$zip = new Zip($files,$output_file);
		if ($zip->generate()) {
			Response::content_type('application/zip');
			Response::add_header('Content-Length',filesize($output_file));
			$this->Biscuit->render(file_get_contents($output_file));
			@unlink($output_file);
		} else {
			Session::flash('user_error',__('Unable to generate ZIP file to download. Please report this to the system administrator.'));
			Response::redirect($this->url('show',$this->params['id']));
		}
	}
	/**
	 * Same as abstract enforce_canonical_show_url, but for the download_original action
	 *
	 * @param string $id 
	 * @return void
	 * @author Peter Epp
	 */
	protected function enforce_canonical_download_url($download_action, $id) {
		$download_url = $this->url($download_action,$id);
		$request_uri = Request::uri();
		$query_string = '';
		if (preg_match('/\?/',$request_uri)) {
			// Separate the query string, if present
			$query_string = substr($request_uri,strpos($request_uri,'?'));
			$request_uri = substr($request_uri,0,strpos($request_uri,'?'));
		}
		if ($download_url != $request_uri) {
			Response::redirect($download_url.$query_string, true);
		}
	}
	/**
	 * Set album title if viewing album, otherwise defer to default
	 *
	 * @param string $model 
	 * @return void
	 * @author Peter Epp
	 */
	protected function set_show_title($model) {
		$model_name = Crumbs::normalized_model_name($model);
		if ($model_name == 'Album') {
			$this->title('Album: '.$model->title());
			return;
		} else if ($model_name == 'Photo') {
			if ($model->title()) {
				$this->title('Photo: '.$model->title());
			} else {
				$this->title('Photo: '.$model->image());
			}
			return;
		}
		parent::set_show_title($model);
	}
	/**
	 * Defer to edit_photo action on new_photo
	 *
	 * @return void
	 * @author Peter Epp
	 */
	protected function action_new_photo() {
		$this->action_edit_photo('new');
	}
	/**
	 * Set some special view vars for edit_photo action
	 *
	 * @return void
	 * @author Peter Epp
	 */
	protected function action_edit_photo($mode = 'edit') {
		$albums = $this->Album->find_all(array('title' => 'ASC'));
		if (empty($albums)) {
			Session::flash('user_error','You must have at least one album in which to put photos! Please create a new album and try again.');
			Response::redirect($this->url());
			return;
		}
		$album_select_options = array();
		foreach ($albums as $album) {
			$album_select_options[] = array(
				'label' => $album->title(),
				'value' => $album->id()
			);
		}
		$this->set_view_var('album_select_options', $album_select_options);
		parent::action_edit($mode);
	}
	/**
	 * Enforce the presence of some data(notably ID) for certain actions. This function
     * is called before the action by AbstractModuleController#run
	 *
	 * @return boolean
	 **/
	public function before_filter() {
		$can_do = true;
		if (in_array($this->action(), array('edit_photo', 'edit', 'delete_photo', 'delete', 'xml_output'))) {
			// require ID
			return (!empty($this->params['id']));
		}
		return true;
	}
	/**
	 * When a file is uploaded, create a new Photo and add it to the album
	 *
	 * @param string $uploaded_filename 
	 * @return void
	 * @author Peter Epp
	 */
	protected function act_on_file_uploaded($uploaded_file) {
		if (!empty($this->params['album_id'])) {
			if ($uploaded_file->type() != 'image') {
				// Ignore non-images
				$bad_uploads = Session::get('bad_gallery_uploads');
				if (empty($bad_uploads)) {
					$bad_uploads = array();
				}
				$bad_uploads[] = $uploaded_file->file_name();
				Session::set('bad_gallery_uploads', $bad_uploads);
				@unlink(SITE_ROOT.$this->params['folder'].'/'.$uploaded_file->file_name());
				return;
			}
			$filename = $uploaded_file->file_name();
			$photo = $this->Photo->find_by('album_id',$this->params['album_id'],"`image` = '".$filename."'");
			if (empty($photo)) {
				// Only bother doing anything if the photo using the uploaded file is not already in the album
				$photo_data = array(
					'album_id' => $this->params['album_id'],
					'image' => $filename
				);
				$photo = $this->Photo->create($photo_data);
				if (!$photo->save()) {
					$errors = $photo->errors();
					Console::log("Unable to save photo:\n".print_r($errors,true));
				}
			}
		}
	}
	/**
	 * On deletion of a model, set the return URL to the show action of the album. Also ensure that sort order is kept sequential to prevent navigation problems
	 *
	 * @param string $model 
	 * @return void
	 * @author Peter Epp
	 */
	protected function act_on_successful_delete($model) {
		$model_name = Crumbs::normalized_model_name($model);
		if ($model_name == 'Photo') {
			$this->_return_url = $this->url('show',$model->album_id());
			$photos = $this->Photo->find_all_by('album_id',$model->album_id(),array('sort_order' => 'ASC'));
			if (!empty($photos)) {
				$photo_ids = array();
				foreach ($photos as $photo) {
					$photo_ids[] = $photo->id();
				}
				$this->Photo->resort($photo_ids);
			}
		} else if ($model_name == 'Album') {
			$albums = $this->Album->find_all(array('sort_order' => 'ASC'));
			if (!empty($albums)) {
				$album_ids = array();
				foreach ($albums as $album) {
					$album_ids[] = $album->id();
				}
				$this->Album->resort($album_ids);
			}
		}
	}
	/**
	 * On deletion of a model, set the return URL to the show action of the album
	 *
	 * @param string $model 
	 * @return void
	 * @author Peter Epp
	 */
	protected function act_on_successful_save($model) {
		$model_name = Crumbs::normalized_model_name($model);
		if ($model_name == 'Photo') {
			$this->_return_url = $this->url('show_photo',$model->id());
		} else if ($model_name == 'Album') {
			if ($this->action() == 'new') {
				Session::flash('user_success',__('Your new album has been created. You can now select photos to add to it.'));
			}
			$this->_return_url = $this->url('show',$model->id());
		}
	}
	/**
	 * When photos are re-sorted, invalidate the fragment cache for the associated album
	 *
	 * @param string $model_name 
	 * @param string $sort_ids 
	 * @return void
	 * @author Peter Epp
	 */
	protected function act_on_resorted_items($model_name, $sort_ids) {
		if ($model_name == 'Photo') {
			// Find the first photo out of all the ones that were sorted and use that to get the album id:
			$use_id = reset($sort_ids);
			$photo = $this->Photo->find($use_id);
			$album_id = $photo->album_id();
			$fcache = new FragmentCache('Album',$album_id);
			$fcache->invalidate_all();
		}
	}
	/**
	 * Add breadcrumbs for special cases like viewing a photo
	 *
	 * @param Navigation $Navigation 
	 * @return void
	 * @author Peter Epp
	 */
	protected function act_on_build_breadcrumbs($Navigation) {
		if ($this->action() == 'show_photo') {
			$photo = $this->model_for_showing($this->params['id'],'show_photo');
			$url = $this->url('show',$photo->album_id());
			$Navigation->add_breadcrumb($url,'Album: '.$photo->album()->title());
		}
		parent::act_on_build_breadcrumbs($Navigation);
	}
	/**
	 * Add help menu link
	 *
	 * @param string $caller 
	 * @return void
	 * @author Peter Epp
	 */
	protected function act_on_build_help_menu($caller) {
		$caller->add_help_for('PhotoGallery');
	}
	/**
	 * Special URL method that deals with the download-original action
	 *
	 * @param string $action 
	 * @param string $id 
	 * @return void
	 * @author Peter Epp
	 */
	public function url($action = 'index', $id = null) {
		$url = parent::url($action,$id);
		if ($action == 'download_original') {
			$url .= $this->friendly_show_slug('show_photo',$id);
		} else if ($action == 'download_album') {
			$url .= $this->friendly_show_slug('show',$id).'.zip';
		}
		return $url;
	}
	/**
	 * Provide URI mapping rule for the download_original action
	 *
	 * @return array
	 * @author Peter Epp
	 */
	public static function uri_mapping_rules() {
		return array(
			'/^(?P<page_slug>[^\.]+)\/(?P<action>download_original)\/(?P<id>[0-9]+)\/.+\/?$/',
			'/^(?P<page_slug>[^\.]+)\/(?P<action>download_album)\/(?P<id>[0-9]+)\/.+\/?$/'
		);
	}
	/**
	 * Install the module
	 *
	 * @return void
	 * @author Peter Epp
	 */
	public static function install_migration($module_id) {
		$uploadify_is_installed = DB::fetch_one("SELECT `installed` FROM `modules` WHERE `name` = 'Uploadify'");
		if (!$uploadify_is_installed) {
			Session::flash_unset('user_message');
			Session::flash('user_error',__('The Photo Gallery module cannot be installed because it requires Uploadify. Please install the Uploadify module first and then try again.'));
			Response::redirect('/');
			return;
		}
		// Create "albums" table
		DB::query("CREATE TABLE IF NOT EXISTS `albums` (
		  `id` int(8) NOT NULL AUTO_INCREMENT,
		  `title` varchar(255) NOT NULL,
		  `sort_order` int(3) DEFAULT '0',
		  `updated` datetime DEFAULT '0000-00-00 00:00:00',
		  PRIMARY KEY (`id`),
		  KEY `title` (`title`,`sort_order`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		// Create "photos" table
		DB::query("CREATE TABLE IF NOT EXISTS `photos` (
		  `id` int(8) NOT NULL AUTO_INCREMENT,
		  `album_id` int(8) NOT NULL,
		  `title` varchar(255) DEFAULT NULL,
		  `description` text,
		  `sort_order` int(3) DEFAULT NULL,
		  `image` varchar(255) NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `album_id` (`album_id`),
		  CONSTRAINT `photos_ibfk_1` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		// Add a default page, if not already present, for viewing the albums and install the module on it:
		$existing_page = DB::fetch_one("SELECT `id` FROM `page_index` WHERE `slug` = 'photo-gallery'");
		if (!$existing_page) {
			DB::query("INSERT INTO `page_index` (`parent`, `slug`, `title`, `sort_order`) (SELECT 0, 'photo-gallery', 'Photo Gallery', MAX(`sort_order`)+1 FROM `page_index` WHERE `parent` = 0)");
		}
		$uploadify_id = DB::fetch_one("SELECT `id` FROM `modules` WHERE `name` = 'Uploadify'");
		// Ensure clean install
		DB::query("DELETE FROM `module_pages` WHERE `module_id` = {$module_id} OR `page_name` = 'photo-gallery'");
		// Install module:
		DB::query("INSERT INTO `module_pages` SET `module_id` = {$module_id}, `page_name` = 'photo-gallery', `is_primary` = 1");
		// Install uploadify on the photo gallery page:
		DB::query("INSERT INTO `module_pages` SET `module_id` = {$uploadify_id}, `page_name` = 'photo-gallery', `is_primary` = 0");
		// Install the photo gallery module as secondary on the "uploadify" page, so it can act when uploadify uploads files:
		DB::query("INSERT INTO `module_pages` SET `module_id` = {$module_id}, `page_name` = 'uploadify', `is_primary` = 0");
		// Add permissions:
		Permissions::add(__CLASS__,array('edit' => 99, 'new' => 99, 'delete' => 99, 'edit_photo' => 99, 'new_photo' => 99, 'delete_photo' => 99),true);
	}
	/**
	 * Uninstall the module
	 *
	 * @return void
	 * @author Peter Epp
	 */
	public static function uninstall_migration($module_id) {
		DB::query("DROP TABLE IF EXISTS `photos`");
		DB::query("DROP TABLE IF EXISTS `albums`");
		DB::query("DELETE FROM `module_pages` WHERE `module_id` = {$module_id}");
		DB::query("DELETE FROM `module_pages` WHERE `page_name` = 'photo-gallery'");
		DB::query("DELETE FROM `page_index` WHERE `slug` = 'photo-gallery'");
		Permissions::remove(__CLASS__);
		Session::flash('user_message','Photo Gallery uninstall: Note that if you changed the page the gallery was installed on from it\'s default, you will need to manually uninstall Uploadify from that page.');
	}
}
