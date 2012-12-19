<?php

namespace mjohnson\transit;

use \Exception;

class Uploader extends Transit {

	/**
	 * Form post data.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_data;

	/**
	 * Store the $_FILES data.
	 *
	 * @access public
	 * @param array $data
	 */
	public function __construct(array $data) {
		if (empty($data['tmp_name'])) {
			throw new Exception(sprintf('Invalid upload; no tmp_name detected!'));
		}

		$this->_data = $data;
	}


	/**
	 * Upload the file to the target directory.
	 *
	 * @access public
	 * @return \mjohnson\transit\File
	 * @throws \Exception
	 */
	public function upload() {
		$data = $this->_data;

		// Validate errors
		if ($data['error'] > 0 || !is_uploaded_file($data['tmp_name']) || !is_file($data['tmp_name'])) {
			switch ($data['error']) {
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					$error = 'File exceeds the maximum file size.';
				break;
				case UPLOAD_ERR_PARTIAL:
					$error = 'File was only partially uploaded.';
				break;
				case UPLOAD_ERR_NO_FILE:
					$error = 'No file was found for upload.';
				break;
				default:
					$error = 'File failed to upload.';
				break;
			}

			throw new Exception($error);
		}

		// Upload the file
		$target = $this->findTarget(true, $data['name']);

		if (move_uploaded_file($data['tmp_name'], $target) || copy($data['tmp_name'], $target)) {
			return new File($target);
		}

		throw new Exception('An unknown error has occurred.');
	}

}