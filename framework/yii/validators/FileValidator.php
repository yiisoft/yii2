<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\web\UploadedFile;

/**
 * FileValidator verifies if an attribute is receiving a valid uploaded file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class FileValidator extends Validator
{
	/**
	 * @var mixed a list of file name extensions that are allowed to be uploaded.
	 * This can be either an array or a string consisting of file extension names
	 * separated by space or comma (e.g. "gif, jpg").
	 * Extension names are case-insensitive. Defaults to null, meaning all file name
	 * extensions are allowed.
	 */
	public $types;
	/**
	 * @var integer the minimum number of bytes required for the uploaded file.
	 * Defaults to null, meaning no limit.
	 * @see tooSmall
	 */
	public $minSize;
	/**
	 * @var integer the maximum number of bytes required for the uploaded file.
	 * Defaults to null, meaning no limit.
	 * Note, the size limit is also affected by 'upload_max_filesize' INI setting
	 * and the 'MAX_FILE_SIZE' hidden field value.
	 * @see tooBig
	 */
	public $maxSize;
	/**
	 * @var integer the maximum file count the given attribute can hold.
	 * It defaults to 1, meaning single file upload. By defining a higher number,
	 * multiple uploads become possible.
	 */
	public $maxFiles = 1;
	/**
	 * @var string the error message used when a file is not uploaded correctly.
	 */
	public $message;
	/**
	 * @var string the error message used when no file is uploaded.
	 */
	public $uploadRequired;
	/**
	 * @var string the error message used when the uploaded file is too large.
	 * You may use the following tokens in the message:
	 *
	 * - {attribute}: the attribute name
	 * - {file}: the uploaded file name
	 * - {limit}: the maximum size allowed (see [[getSizeLimit()]])
	 */
	public $tooBig;
	/**
	 * @var string the error message used when the uploaded file is too small.
	 * You may use the following tokens in the message:
	 *
	 * - {attribute}: the attribute name
	 * - {file}: the uploaded file name
	 * - {limit}: the value of [[minSize]]
	 */
	public $tooSmall;
	/**
	 * @var string the error message used when the uploaded file has an extension name
	 * that is not listed in [[extensions]]. You may use the following tokens in the message:
	 *
	 * - {attribute}: the attribute name
	 * - {extensions}: the list of the allowed extensions.
	 */
	public $wrongType;
	/**
	 * @var string the error message used if the count of multiple uploads exceeds limit.
	 * You may use the following tokens in the message:
	 *
	 * - {attribute}: the attribute name
	 * - {file}: the uploaded file name
	 * - {limit}: the value of [[maxFiles]]
	 */
	public $tooMany;

	/**
	 * Initializes the validator.
	 */
	public function init()
	{
		parent::init();
		if ($this->message === null) {
			$this->message = Yii::t('yii', 'File upload failed.');
		}
		if ($this->uploadRequired === null) {
			$this->uploadRequired = Yii::t('yii', 'Please upload a file.');
		}
		if ($this->tooMany === null) {
			$this->tooMany = Yii::t('yii', 'You can upload at most {limit} files.');
		}
		if ($this->wrongType === null) {
			$this->wrongType = Yii::t('yii', 'Only files with these extensions are allowed: {extensions}.');
		}
		if ($this->tooBig === null) {
			$this->tooBig = Yii::t('yii', 'The file "{file}" is too big. Its size cannot exceed {limit} bytes.');
		}
		if ($this->tooSmall === null) {
			$this->tooSmall = Yii::t('yii', 'The file "{file}" is too small. Its size cannot be smaller than {limit} bytes.');
		}
		if (!is_array($this->types)) {
			$this->types = preg_split('/[\s,]+/', strtolower($this->types), -1, PREG_SPLIT_NO_EMPTY);
		}
	}

	/**
	 * Validates the attribute.
	 * @param \yii\base\Model $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	public function validateAttribute($object, $attribute)
	{
		if ($this->maxFiles > 1) {
			$files = $object->$attribute;
			if (!is_array($files)) {
				$this->addError($object, $attribute, $this->uploadRequired);
				return;
			}
			foreach ($files as $i => $file) {
				if (!$file instanceof UploadedFile || $file->getError() == UPLOAD_ERR_NO_FILE) {
					unset($files[$i]);
				}
			}
			$object->$attribute = array_values($files);
			if (empty($files)) {
				$this->addError($object, $attribute, $this->uploadRequired);
			}
			if (count($files) > $this->maxFiles) {
				$this->addError($object, $attribute, $this->tooMany, array('{attribute}' => $attribute, '{limit}' => $this->maxFiles));
			} else {
				foreach ($files as $file) {
					$this->validateFile($object, $attribute, $file);
				}
			}
		} else {
			$file = $object->$attribute;
			if ($file instanceof UploadedFile && $file->getError() != UPLOAD_ERR_NO_FILE) {
				$this->validateFile($object, $attribute, $file);
			} else {
				$this->addError($object, $attribute, $this->uploadRequired);
			}
		}
	}

	/**
	 * Internally validates a file object.
	 * @param \yii\base\Model $object the object being validated
	 * @param string $attribute the attribute being validated
	 * @param UploadedFile $file uploaded file passed to check against a set of rules
	 */
	protected function validateFile($object, $attribute, $file)
	{
		switch ($file->getError()) {
			case UPLOAD_ERR_OK:
				if ($this->maxSize !== null && $file->getSize() > $this->maxSize) {
					$this->addError($object, $attribute, $this->tooBig, array('{file}' => $file->getName(), '{limit}' => $this->getSizeLimit()));
				}
				if ($this->minSize !== null && $file->getSize() < $this->minSize) {
					$this->addError($object, $attribute, $this->tooSmall, array('{file}' => $file->getName(), '{limit}' => $this->minSize));
				}
				if (!empty($this->types) && !in_array(strtolower(pathinfo($file->getName(), PATHINFO_EXTENSION)), $this->types, true)) {
					$this->addError($object, $attribute, $this->wrongType, array('{file}' => $file->getName(), '{extensions}' => implode(', ', $this->types)));
				}
				break;
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				$this->addError($object, $attribute, $this->tooBig, array('{file}' => $file->getName(), '{limit}' => $this->getSizeLimit()));
				break;
			case UPLOAD_ERR_PARTIAL:
				$this->addError($object, $attribute, $this->message);
				Yii::warning('File was only partially uploaded: ' . $file->getName(), __METHOD__);
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$this->addError($object, $attribute, $this->message);
				Yii::warning('Missing the temporary folder to store the uploaded file: ' . $file->getName(), __METHOD__);
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$this->addError($object, $attribute, $this->message);
				Yii::warning('Failed to write the uploaded file to disk: ' . $file->getName(), __METHOD__);
				break;
			case UPLOAD_ERR_EXTENSION:
				$this->addError($object, $attribute, $this->message);
				Yii::warning('File upload was stopped by some PHP extension: ' . $file->getName(), __METHOD__);
				break;
			default:
				break;
		}
	}

	/**
	 * Returns the maximum size allowed for uploaded files.
	 * This is determined based on three factors:
	 *
	 * - 'upload_max_filesize' in php.ini
	 * - 'MAX_FILE_SIZE' hidden field
	 * - [[maxSize]]
	 *
	 * @return integer the size limit for uploaded files.
	 */
	public function getSizeLimit()
	{
		$limit = ini_get('upload_max_filesize');
		$limit = $this->sizeToBytes($limit);
		if ($this->maxSize !== null && $limit > 0 && $this->maxSize < $limit) {
			$limit = $this->maxSize;
		}
		if (isset($_POST['MAX_FILE_SIZE']) && $_POST['MAX_FILE_SIZE'] > 0 && $_POST['MAX_FILE_SIZE'] < $limit) {
			$limit = (int)$_POST['MAX_FILE_SIZE'];
		}
		return $limit;
	}

	/**
	 * Converts php.ini style size to bytes
	 *
	 * @param string $sizeStr $sizeStr
	 * @return int
	 */
	private function sizeToBytes($sizeStr)
	{
		switch (substr($sizeStr, -1)) {
			case 'M':
			case 'm':
				return (int)$sizeStr * 1048576;
			case 'K':
			case 'k':
				return (int)$sizeStr * 1024;
			case 'G':
			case 'g':
				return (int)$sizeStr * 1073741824;
			default:
				return (int)$sizeStr;
		}
	}
}
