<?php
/**
 * Photo model for basic photo gallery
 *
 * @package Modules
 * @author Peter Epp
 */

class Photo extends AbstractModel {
	protected $_attributes_with_uploads = array('image');
	/**
	 * Validate a batch upload
	 *
	 * @return bool
	 * @author Peter Epp
	 */
	public function validate_batch() {
		$no_files = true;
		if (Request::form('batch_image') != null && (is_array(Request::form('batch_image')) || Request::files('batch_image'))) {
			// Grab the array of files from the form post with empty elements filtered out
			$photo_files = array_values(array_filter(Request::form('batch_image')));
			$no_files = empty($photo_files);
		}
		if ($no_files) {
			$this->set_error(null,"Please select at least one image file to upload");
		}
		$this->_has_been_validated(true);
		return !$this->errors();
	}
	/**
	 * Save a batch of uploaded photos
	 *
	 * @return bool Success
	 * @author Peter Epp
	 */
	public function batch_save() {
		$upload_path = $this->upload_path('image');
		Console::log("                        Saving batch upload...");
		$this->set_batch_image('');
		Console::log("                        Checking for uploaded file...");
		$photo_files = Request::files('batch_image');
		$warning = '';
		if ($photo_files != null && is_array($photo_files['name'])) {
			Console::log_var_dump('All submitted photo file uploads',$photo_files);
			$uploaded_files = new MultiFileUpload($photo_files, $upload_path);
			if ($uploaded_files->is_partially_okay()) {
				// At least 1 file uploaded, processed okay
				Console::log_var_dump('Successful batch upload photo filenames',$uploaded_files->file_names());
				// Were there any failures?
				if ($uploaded_files->failed_uploads() !== false) {
					$warning = 'Some files did not upload and were therefore not added to the album:\n\n'.$uploaded_files->failure_list('\n');
				}
			} elseif ($uploaded_files->no_file_sent()) {
				$this->set_error(null,"Please select at least one image file to upload");
			} else {
				$this->set_error(null,'None of the files uploaded and were therefore not added to the album:\n\n'.$uploaded_files->failure_list('\n'));
			}
		}
		else {
			$this->set_error(null,"Please select at least one image file to upload");
		}

		if (!$this->errors()) {
			Console::log('                        Saving data now...');
			// Save the data:
			foreach ($uploaded_files->files as $file) {
				if ($file->is_okay()) {
					$id = DB::insert("INSERT INTO `photos` SET `album_id` = ?, `image` = ?", array($this->album_id(), $file->file_name));
				}
			}
			if (!empty($warning)) {
				$this->set_error(null,$warning);
			}
		} else {
			Console::log("                        Skipping DB save");
		}
		return (!$this->errors());
	}

	public static function db_create_query() {
		return 'CREATE TABLE  `photos` (
		`id` INT( 8 ) NOT NULL AUTO_INCREMENT,
		`album_id` INT( 8 ) NOT NULL,
		`title` VARCHAR( 255 ) DEFAULT NULL ,
		`description` TEXT DEFAULT NULL,
		`sort_order` INT( 3 ) NULL DEFAULT NULL ,
		`image` VARCHAR( 255 ) NOT NULL ,
		PRIMARY KEY (`id`)
		) TYPE = MyISAM';
	}
}
?>