<?php
/**
 * Album model for basic photo gallery
 *
 * @package Modules
 * @subpackage PhotoGallery
 * @author Peter Epp
 * @version $Id: album.php 13843 2011-07-27 19:45:49Z teknocat $
 */
class Album extends AbstractModel {
	/**
	 * Place to store the first photo in the album
	 *
	 * @var string
	 */
	protected $_first_photo = null;
	/**
	 * Models this model has many of
	 *
	 * @var array
	 */
	protected $_has_many = array('Photo');
	/**
	 * Return the number of photos in a specified album
	 *
	 * @return string
	 * @author Peter Epp
	 */
	public function photo_count() {
		$db_table = $this->_db_table();
		return (int)DB::fetch_one("SELECT COUNT(*) AS `photo_count` FROM `{$db_table}` WHERE `album_id` = ?", $this->id());
	}
	/**
	 * Return the filename of the first photo in the album
	 *
	 * @return string
	 * @author Peter Epp
	 */
	public function first_photo() {
		if ($this->_first_photo === null) {
			$photos = ModelFactory::instance('Photo')->find_all_by('album_id',$this->id(),array('sort_order' => 'ASC'),'',1);
			if (!empty($photos)) {
				$this->_first_photo = reset($photos);
			} else {
				$this->_first_photo = false;
			}
		}
		return $this->_first_photo;
	}
	/**
	 * Validate user input
	 *
	 * @return void
	 * @author Peter Epp
	 */
	public function _set_attribute_defaults() {
		$this->set_updated(date('Y-m-d H:i:s'));
		if (!$this->sort_order() || $this->sort_order() == 0) {
			$this->set_sort_order(ModelFactory::instance('Album')->next_sort_order());
		}
	}
	/**
	 * Delete all photos in the album and then delete the album
	 *
	 * @return void
	 * @author Peter Epp
	 */
	public function delete() {
		if (parent::delete()) {
			Recursive::rmdir(SITE_ROOT.'/var/uploads/photo/album-'.$this->id());
			return true;
		}
		return false;
	}
}
?>