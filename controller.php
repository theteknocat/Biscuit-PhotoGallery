<?php
/**
 * Basic photo gallery module
 *
 * @package Modules
 * @author Peter Epp
 * @copyright Copyright (c) 2009 Peter Epp (http://teknocat.org)
 * @license GNU Lesser General Public License (http://www.gnu.org/licenses/lgpl.html)
 * @version 2.0
 */
class PhotoGalleryManager extends AbstractModuleController {
	protected $_dependencies = array("Authenticator","PrototypeJs","LightviewJs","MultifileJs");
	protected $_models = array(
		"Album" => "Album",
		"Photo" => "Photo"
	);
	protected $_actions_requiring_id = array('new_photo','batch_add');
	protected $_index_sort_options = array();
	
	public function run() {
		$this->register_js("footer","photo_gallery.js");
		if (!empty($params['data_type'])) {
			$this->data_type = $params['data_type'];
		}
		else {
			if ($this->Biscuit->Page->short_slug() == "photos") {	// Viewing an album (default)
				$this->data_type = 'album';
			}
			elseif ($this->Biscuit->Page->short_slug() == "photo") {	// Viewing a photo
				$this->data_type = 'photo';
			}
		}
		parent::run();
	}
	/**
	 * Show the contents of a single album
	 *
	 * @return void
	 * @author Peter Epp
	 */
	protected function action_show_album() {
		$this->set_view_var('photos',$this->Photo->find_all_by('album_id', $this->params['id'], array('sort_order' => 'ASC')));
		parent::action_show('album');
	}
	protected function action_edit() {
		if (!empty($this->params['photo']['album_id'])) {
			$this->set_view_var('album',$this->Album->find($this->params['photo']['album_id']));
		}
		parent::action_edit();
	}
	/**
	 * Render XML output for an album
	 *
	 * @return void
	 * @author Peter Epp
	 */
	protected function action_xml_output() {
		$this->set_view_var('photos', $this->Photo->find_all_by('album_id',$this->params['album_id'],array('sort_order' => 'ASC')));
		$this->Biscuit->render_with_template(false);
		Response::content_type("application/xml");
		$this->render();
	}
	/**
	 * Validate batch add data
	 *
	 * @return bool
	 * @author Peter Epp
	 */
	protected function validate_batch_add() {
		$item_data = array(
			'album_id'		=> $this->params['album_id']
		);
		$photo = $this->Photo->create($item_data);
		$is_valid = $photo->validate_batch();
		if (!$is_valid) {
			$this->_validation_errors = $photo->errors();
			$this->_invalid_fields = $photo->invalid_attributes();
		}
		return $is_valid;
	}
	/**
	 * Handle batch photo uploads
	 *
	 * @return void
	 * @author Peter Epp
	 */
	protected function action_batch_add() {
		$this->title('Batch Photo Upload');
		$photo = $this->Photo->create();
		if (!empty($this->params['photo'])) {
			$photo->set_attributes($this->params['photo']);
		}
		$this->set_view_var('album', $this->Album->find($item->album_id()));
		if (Request::is_post()) {
			if ($item->batch_save()) {
				$this->success_save_response($this->url('show_album',$photo->album_id()));
			}
			else {
				$this->failed_save_response(&$photo,"photo");
			}
		}
		else {
			$this->set_view_var('photo', &$photo);
			$this->render();
		}
	}
	/**
	 * Enforce the presence of some data(notably ID) for certain actions. This function
     * is called before the action by AbstractModuleController#run
	 *
	 * @return boolean
	 **/
	public function before_filter() {
		$can_do = true;
		if (in_array($this->params['action'], array('edit_album', 'edit_photo', 'delete_album', 'delete_photo', 'download'))) {
			// require ID
			$can_do = (!empty($this->params['id']));
		}
		else if (in_array($this->params['action'], array('batch_add', 'new_photo', 'xml_output'))) {
			// require album_id
			$can_do = (!empty($this->params['id']));
			if ($can_do && $this->params['action'] == 'new_photo') {
				$this->params['photo']['album_id'] = $this->params['id'];
				unset($this->params['id']);
			}
		}
		return $can_do;
	}

	public function db_tablename() {
		return array($this->Photo->db_table(),$this->Album->db_table());
	}

	public function db_create_query($table_name) {
		if ($table_name == $this->Photo->db_table()) {
			return $this->Photo->db_create_query();
		}
		else if ($table_name == $this->Album->db_table()) {
			return $this->Album->db_create_query();
		}
	}
}
?>