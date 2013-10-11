<?php
/**
 * Album model for basic photo gallery
 *
 * @package Modules
 * @author Peter Epp
 */
class Album extends AbstractModel {
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
		$album_id = (int)$this->id();
		$db_table = $this->_db_table();
		return DB::fetch_one("SELECT `image` FROM `{$db_table}` WHERE `album_id` = ? ORDER BY `sort_order` LIMIT 1", $album_id);
	}
	/**
	 * Validate user input
	 *
	 * @return void
	 * @author Peter Epp
	 */
	public function _set_attribute_defaults() {
		$this->set_updated(date('Y-m-d H:i:s'));
	}

	public function delete() {
		if (parent::delete()) {
			$photo_factory = new ModelFactory("Photo");
			$photos = $photo_factory->find_all_by('album_id',$this->id());
			if (!empty($photos)) {
				foreach ($photos as $photo) {
					$photo->delete();
				}
			}
			return true;
		}
		return false;
	}

	public static function db_create_query() {
		return "CREATE TABLE  `albums` (
		`id` INT( 8 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`title` VARCHAR( 255 ) NOT NULL,
		`sort_order` INT( 3 ) DEFAULT '0',
		`updated` DATETIME DEFAULT '0000-00-00 00:00;00',
		INDEX (  `title` ,  `sort_order` )
		) TYPE = MyISAM";
	}
}
?>