<?php

class AlbumsController extends GalleryAppController {

	public $helpers = array('Form' => array('className' => 'Gallery.CakePHPFTPForm'));

	public function add() {
	}

	public function create() {
		if ($this->request->is('post')) {
			if ($this->Album->save($this->request->data)) {
				# Create folder at files/gallery/{album_id}
				mkdir(WWW_ROOT . 'files/gallery/' . $this->Album->id);
				$this->redirect(array('action' => 'upload', $this->Album->id));
			} else {
				$this->Error->set($this->Album->invalidFields());
			}
		}
	}

	public function update() {
		if ($this->request->is('post')) {
			if ($this->Album->save($this->request->data)) {
				echo "You configurations are saved.";
			}
		}
		$this->render(false, false);
	}

	public function upload($model = null, $model_id = null, $gallery_id = null) {
		ini_set("memory_limit", "10000M");

		# If there is a Model and ModelID on parameters, get or create a folder for it
		if ($model && $model_id) {
			# Searching for folder that belongs to this particular $model and $model_id
			if (!$album = $this->_getModelAlbum($model, $model_id)) {
				# If there is no Album , lets create one for it
				$album = $this->_createAlbum($model, $model_id);
			}
		} else if(isset($this->params['gallery_id']) && !empty($this->params['gallery_id'])) {
			$album = $this->Album->findById($this->params['gallery_id']);
		} else {
			# If there is no model on parameters, lets create a generic folder
			$album = $this->_createAlbum(null, null);
		}

		$files = $album['Picture'];

		$this->set(compact('model', 'model_id', 'album', 'files'));
	}

	private function _getModelAlbum($model = null, $model_id = null) {
		return $this->Album->find('first', array(
			'conditions' => array(
				'Album.model' => $model,
				'Album.model_id' => $model_id
			)));
	}

	private function _createAlbum($model = null, $model_id = null) {
		$this->Album->save(array(
			'Album' => array(
				'model' => $model,
				'model_id' => $model_id,
				'status' => 'draft',
				'tags' => '',
				'title' => $this->_generateAlbumName($model, $model_id)
			)
		));
		return $this->Album->read(null);
	}


	private function _generateAlbumName($model = null, $model_id = null){
		$name = 'Album - ' . rand(111,999);

		if($model && $model_id){
			$name = Inflector::humanize('Album ' . $model . ' - ' . $model_id);
		}

		return $name;
	}
}

?>