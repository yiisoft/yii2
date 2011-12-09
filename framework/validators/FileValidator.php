<?php
/**
 * CFileValidator class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

/**
 * CFileValidator verifies if an attribute is receiving a valid uploaded file.
 *
 * It uses the model class and attribute name to retrieve the information
 * about the uploaded file. It then checks if a file is uploaded successfully,
 * if the file size is within the limit and if the file type is allowed.
 *
 * This validator will attempt to fetch uploaded data if attribute is not
 * previously set. Please note that this cannot be done if input is tabular:
 * <pre>
 *  foreach($models as $i=>$model)
 *     $model->attribute = CUploadedFile::getInstance($model, "[$i]attribute");
 * </pre>
 * Please note that you must use {@link CUploadedFile::getInstances} for multiple
 * file uploads.
 *
 * When using CFileValidator with an active record, the following code is often used:
 * <pre>
 *  if($model->save())
 *  {
 *     // single upload
 *     $model->attribute->saveAs($path);
 *     // multiple upload
 *     foreach($model->attribute as $file)
 *        $file->saveAs($path);
 *  }
 * </pre>
 *
 * You can use {@link CFileValidator} to validate the file attribute.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CFileValidator.php 2799 2011-01-01 19:31:13Z qiang.xue $
 * @package system.validators
 * @since 2.0
 */
class CFileValidator extends Validator
{
	/**
	 * @var boolean whether the attribute requires a file to be uploaded or not.
	 * Defaults to false, meaning a file is required to be uploaded.
	 */
	public $allowEmpty = false;
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
	 * @see tooLarge
	 */
	public $maxSize;
	/**
	 * @var string the error message used when the uploaded file is too large.
	 * @see maxSize
	 */
	public $tooLarge;
	/**
	 * @var string the error message used when the uploaded file is too small.
	 * @see minSize
	 */
	public $tooSmall;
	/**
	 * @var string the error message used when the uploaded file has an extension name
	 * that is not listed among {@link extensions}.
	 */
	public $wrongType;
	/**
	 * @var integer the maximum file count the given attribute can hold.
	 * It defaults to 1, meaning single file upload. By defining a higher number,
	 * multiple uploads become possible.
	 */
	public $maxFiles = 1;
	/**
	 * @var string the error message used if the count of multiple uploads exceeds
	 * limit.
	 */
	public $tooMany;

	/**
	 * Set the attribute and then validates using {@link validateFile}.
	 * If there is any error, the error message is added to the object.
	 * @param \yii\base\Model $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	public function validateAttribute($object, $attribute)
	{
		if ($this->maxFiles > 1)
		{
			$files = $object->$attribute;
			if (!is_array($files) || !isset($files[0]) || !$files[0] instanceof CUploadedFile)
				$files = CUploadedFile::getInstances($object, $attribute);
			if (array() === $files)
				return $this->emptyAttribute($object, $attribute);
			if (count($files) > $this->maxFiles)
			{
				$message = $this->tooMany !== null ? $this->tooMany : Yii::t('yii', '{attribute} cannot accept more than {limit} files.');
				$this->addError($object, $attribute, $message, array('{attribute}' => $attribute, '{limit}' => $this->maxFiles));
			}
			else
				foreach ($files as $file)
					$this->validateFile($object, $attribute, $file);
		}
		else
		{
			$file = $object->$attribute;
			if (!$file instanceof CUploadedFile)
			{
				$file = CUploadedFile::getInstance($object, $attribute);
				if (null === $file)
					return $this->emptyAttribute($object, $attribute);
			}
			$this->validateFile($object, $attribute, $file);
		}
	}

	/**
	 * Internally validates a file object.
	 * @param \yii\base\Model $object the object being validated
	 * @param string $attribute the attribute being validated
	 * @param CUploadedFile $file uploaded file passed to check against a set of rules
	 */
	public function validateFile($object, $attribute, $file)
	{
		if (null === $file || ($error = $file->getError()) == UPLOAD_ERR_NO_FILE)
			return $this->emptyAttribute($object, $attribute);
		elseif ($error == UPLOAD_ERR_INI_SIZE || $error == UPLOAD_ERR_FORM_SIZE || $this->maxSize !== null && $file->getSize() > $this->maxSize)
		{
			$message = $this->tooLarge !== null ? $this->tooLarge : Yii::t('yii', 'The file "{file}" is too large. Its size cannot exceed {limit} bytes.');
			$this->addError($object, $attribute, $message, array('{file}' => $file->getName(), '{limit}' => $this->getSizeLimit()));
		}
		elseif ($error == UPLOAD_ERR_PARTIAL)
			throw new CException(Yii::t('yii', 'The file "{file}" was only partially uploaded.', array('{file}' => $file->getName())));
		elseif ($error == UPLOAD_ERR_NO_TMP_DIR)
			throw new CException(Yii::t('yii', 'Missing the temporary folder to store the uploaded file "{file}".', array('{file}' => $file->getName())));
		elseif ($error == UPLOAD_ERR_CANT_WRITE)
			throw new CException(Yii::t('yii', 'Failed to write the uploaded file "{file}" to disk.', array('{file}' => $file->getName())));
		elseif (defined('UPLOAD_ERR_EXTENSION') && $error == UPLOAD_ERR_EXTENSION)  // available for PHP 5.2.0 or above
			throw new CException(Yii::t('yii', 'File upload was stopped by extension.'));

		if ($this->minSize !== null && $file->getSize() < $this->minSize)
		{
			$message = $this->tooSmall !== null ? $this->tooSmall : Yii::t('yii', 'The file "{file}" is too small. Its size cannot be smaller than {limit} bytes.');
			$this->addError($object, $attribute, $message, array('{file}' => $file->getName(), '{limit}' => $this->minSize));
		}

		if ($this->types !== null)
		{
			if (is_string($this->types))
				$types = preg_split('/[\s,]+/', strtolower($this->types), -1, PREG_SPLIT_NO_EMPTY);
			else
				$types = $this->types;
			if (!in_array(strtolower($file->getExtensionName()), $types))
			{
				$message = $this->wrongType !== null ? $this->wrongType : Yii::t('yii', 'The file "{file}" cannot be uploaded. Only files with these extensions are allowed: {extensions}.');
				$this->addError($object, $attribute, $message, array('{file}' => $file->getName(), '{extensions}' => implode(', ', $types)));
			}
		}
	}

	/**
	 * Raises an error to inform end user about blank attribute.
	 * @param \yii\base\Model $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	public function emptyAttribute($object, $attribute)
	{
		if (!$this->allowEmpty)
		{
			$message = $this->message !== null ? $this->message : Yii::t('yii', '{attribute} cannot be blank.');
			$this->addError($object, $attribute, $message);
		}
	}

	/**
	 * Returns the maximum size allowed for uploaded files.
	 * This is determined based on three factors:
	 * <ul>
	 * <li>'upload_max_filesize' in php.ini</li>
	 * <li>'MAX_FILE_SIZE' hidden field</li>
	 * <li>{@link maxSize}</li>
	 * </ul>
	 *
	 * @return integer the size limit for uploaded files.
	 */
	public function getSizeLimit()
	{
		$limit = ini_get('upload_max_filesize');
		$limit = $this->sizeToBytes($limit);
		if ($this->maxSize !== null && $limit > 0 && $this->maxSize < $limit)
			$limit = $this->maxSize;
		if (isset($_POST['MAX_FILE_SIZE']) && $_POST['MAX_FILE_SIZE'] > 0 && $_POST['MAX_FILE_SIZE'] < $limit)
			$limit = $_POST['MAX_FILE_SIZE'];
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
		switch (substr($sizeStr, -1))
		{
			case 'M': case 'm': return (int)$sizeStr * 1048576;
			case 'K': case 'k': return (int)$sizeStr * 1024;
			case 'G': case 'g': return (int)$sizeStr * 1073741824;
			default: return (int)$sizeStr;
		}
	}
}