<?php
/**
 * Custom factory for photos with method to get next sort order
 *
 * @package Modules
 * @subpackage PhotoGallery
 * @author Peter Epp
 * @version $Id: album_factory.php 13843 2011-07-27 19:45:49Z teknocat $
 */
class AlbumFactory extends ModelFactory {
	/**
	 * Find and return the next sort order to use
	 *
	 * @return void
	 * @author Peter Epp
	 */
	public function next_sort_order() {
		return parent::next_highest('sort_order');
	}
}
