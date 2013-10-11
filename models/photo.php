<?php
/**
 * Photo model for basic photo gallery
 *
 * @package Modules
 * @subpackage PhotoGallery
 * @author Peter Epp
 * @version $Id: photo.php 13843 2011-07-27 19:45:49Z teknocat $
 */
class Photo extends AbstractModel {
	protected $_attributes_with_uploads = array('image');
	/**
	 * Models this model belongs to
	 *
	 * @var array
	 */
	protected $_belongs_to = array('Album');
	/**
	 * Validate user input
	 *
	 * @return void
	 * @author Peter Epp
	 */
	public function _set_attribute_defaults() {
		if (!$this->sort_order() || $this->sort_order() == 0) {
			$this->set_sort_order(ModelFactory::instance('Photo')->next_sort_order($this->album_id()));
		}
	}
	/**
	 * Return the upload path for image attribute with the album-id included
	 *
	 * @param string $attribute 
	 * @return string
	 * @author Peter Epp
	 */
	public function upload_path($attribute_name) {
		if (!$this->album_id()) {
			$backtrace = debug_backtrace();
			Console::log("Call to photo upload path when there is no album ID!\n".print_r($backtrace[0],true));
		}
		return parent::upload_path($attribute_name).'/album-'.$this->album_id();
	}
	/**
	 * Make sure the friendly slug has an image file extension
	 *
	 * @return void
	 * @author Peter Epp
	 */
	public function friendly_slug() {
		$slug = parent::friendly_slug();
		$extension = strtolower(substr($slug,-4));
		if ($extension != '.jpg' && $extension != '.jpeg' && $extension != '.gif' && $extension != '.png') {
			$extensions = array(1 => 'gif', 2 => 'jpg', 3 => 'png');
			$finfo = $this->image_info('image');
			$slug .= '.'.$extensions[$finfo['type']];
		}
		return $slug;
	}
	/**
	 * Which attribute to use for the friendly slug
	 *
	 * @return string
	 * @author Peter Epp
	 */
	protected function slug_attribute() {
		if (!$this->title()) {
			return 'image';
		}
		return 'title';
	}
}
