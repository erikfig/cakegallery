<?php

class Picture extends GalleryAppModel {
	public $name = 'Picture';
	public $tablePrefix = 'gallery_';
	public $belongsTo = array('Gallery.Album');
	public $order = 'Picture.id ASC';

	public function getNextNumber($album_id) {
		return (int)$this->find('count', array('conditions' => array('Picture.album_id' => $album_id))) + 1;
	}

	/**
	 * @param $results
	 * @param bool $primary
	 * @return mixed
	 */
	public function afterFind($results, $primary = false) {
		foreach ($results as $key => $val) {
			$root_url = WWW_ROOT;
			$relative_url = Router::url('/');

			if (isset($val['Picture']['path'])) {
				# Add custom styles
				$results[$key]['Picture']['styles'] = $this->_getChildrens($val['Picture']['id']);

				# Add relative image path
				$results[$key]['Picture']['link'] = str_replace($root_url, $relative_url, $val['Picture']['path']);
			}
		}
		return $results;
	}


	public function _getChildrens($picture_id = null){
		$this->unbindModel(
			array('belongsTo' => array('Gallery.Album'))
		);

		$childrens = $this->find('all',
			array(
				'conditions' => array(
					'main_id' => $picture_id
				),
				'fields' => array('Picture.path', 'Picture.id', 'Picture.style')
			));

		$childs = array();
		foreach($childrens as $child){
				$childs[$child['Picture']['style']] = $child['Picture']['link'];
		}

		return $childs;
	}


	/**
	 * Add image styles configured in bootstrap.php
	 * @param $path
	 * @param array $sizes
	 * @return array
	 */
	public function addImageStyles($path) {
		# Styles configured in bootstrap.php
		$sizes = Configure::read('GalleryOptions.Pictures.styles');

		$links = array();

		if (count($sizes)) {
			$root_url = WWW_ROOT;
			$relative_url = Router::url('/');

			# Current filename
			$filename = end(explode("/", $path));

			foreach ($sizes as $sizename => $size) {
				# Filename with size prefix. E.g: small-filename.jpg
				$modified = $sizename . "-" . $filename;
				# Get final path replacing absolute URl to application relative URL
				$final_path = str_replace($root_url, $relative_url, str_replace($filename, $modified, $path));
				# Add to array
				$links[$sizename] = $final_path;
			}
		}

		return $links;
	}

}

?>