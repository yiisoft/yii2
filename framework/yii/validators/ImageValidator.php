<?php
/**
 * Image validator class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

/**
 * ImageValidator verifies if an attribute is receiving a valid image.
 *
 * @author Taras Gudz <gudz.taras@gmail.com>
 * @since 2.0
 */
class ImageValidator extends FileValidator
{
	/**
	 * @var string the error message used when the uploaded file is not an image.
	 * You may use the following tokens in the message:
	 *
	 * - {attribute}: the attribute name
	 * - {file}: the uploaded file name
	 */
	public $notImage;
	/**
	 * @var integer the minimum width in pixels.
	 * Defaults to null, meaning no limit.
	 * @see underWidth
	 */
	public $minWidth;
	/**
	 * @var integer the maximum width in pixels.
	 * Defaults to null, meaning no limit.
	 * @see overWidth
	 */
	public $maxWidth;
	/**
	 * @var integer the minimum height in pixels.
	 * Defaults to null, meaning no limit.
	 * @see underHeight
	 */
	public $minHeight;
	/**
	 * @var integer the maximum width in pixels.
	 * Defaults to null, meaning no limit.
	 * @see overWidth
	 */
	public $maxHeight;
	/**
	 * @var array|string a list of file mime types that are allowed to be uploaded.
	 * This can be either an array or a string consisting of file mime types
	 * separated by space or comma (e.g. "image/jpeg, image/png").
	 * Mime type names are case-insensitive. Defaults to null, meaning all mime types
	 * are allowed.
	 * @see wrongMimeType
	 */
	public $mimeTypes;
	/**
	 * @var string the error message used when the image is under [[minWidth]].
	 * You may use the following tokens in the message:
	 *
	 * - {attribute}: the attribute name
	 * - {file}: the uploaded file name
	 * - {limit}: the value of [[minWidth]]
	 */
	public $underWidth;
	/**
	 * @var string the error message used when the image is over [[maxWidth]].
	 * You may use the following tokens in the message:
	 *
	 * - {attribute}: the attribute name
	 * - {file}: the uploaded file name
	 * - {limit}: the value of [[maxWidth]]
	 */
	public $overWidth;
	/**
	 * @var string the error message used when the image is under [[minHeight]].
	 * You may use the following tokens in the message:
	 *
	 * - {attribute}: the attribute name
	 * - {file}: the uploaded file name
	 * - {limit}: the value of [[minHeight]]
	 */
	public $underHeight;
	/**
	 * @var string the error message used when the image is over [[maxHeight]].
	 * You may use the following tokens in the message:
	 *
	 * - {attribute}: the attribute name
	 * - {file}: the uploaded file name
	 * - {limit}: the value of [[maxHeight]]
	 */
	public $overHeight;
	/**
	 * @var string the error message used when the file has an mime type
	 * that is not listed in [[mimeTypes]].
	 * You may use the following tokens in the message:
	 *
	 * - {attribute}: the attribute name
	 * - {file}: the uploaded file name
	 * - {mimeTypes}: the value of [[mimeTypes]]
	 */
	public $wrongMimeType;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		if ($this->notImage === null) {
			$this->notImage = Yii::t('yii', 'The file "{file}" is not an image.');
		}
		if ($this->underWidth === null) {
			$this->underWidth = Yii::t('yii', 'The file "{file}" is too small. The width cannot be smaller than {limit} pixels.');
		}
		if ($this->underHeight === null) {
			$this->underHeight = Yii::t('yii', 'The file "{file}" is too small. The height cannot be smaller than {limit} pixels.');
		}
		if ($this->overWidth === null) {
			$this->overWidth = Yii::t('yii', 'The file "{file}" is too large. The width cannot be larger than {limit} pixels.');
		}
		if ($this->overHeight === null) {
			$this->overHeight = Yii::t('yii', 'The file "{file}" is too large. The height cannot be larger than {limit} pixels.');
		}
		if ($this->wrongMimeType === null) {
			$this->wrongMimeType = Yii::t('yii', 'Only files with these mimeTypes are allowed: {mimeTypes}.');
		}
		if (!is_array($this->mimeTypes)) {
			$this->mimeTypes = preg_split('/[\s,]+/', strtolower($this->mimeTypes), -1, PREG_SPLIT_NO_EMPTY);
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function validateValue($file)
	{
		$result = parent::validateValue($file);
		return empty($result) ? $this->validateImage($file) : $result;
	}

	/**
	 * Validates an image file.
	 * @param UploadedFile $image uploaded file passed to check against a set of rules
	 * @return array|null the error message and the parameters to be inserted into the error message.
	 * Null should be returned if the data is valid.
	 */
	protected function validateImage($image)
	{
		if (!empty($this->mimeTypes) && !in_array(FileHelper::getMimeType($image->tempName), $this->mimeTypes, true)) {
			return [$this->wrongMimeType, ['file' => $image->name, 'mimeTypes' => implode(', ', $this->mimeTypes)]];
		}

		if (false === ($imageInfo = getimagesize($image->tempName))) {
			return [$this->notImage, ['file' => $image->name]];
		}

		list($width, $height, $type) = $imageInfo;

		if ($width == 0 || $height == 0) {
			return [$this->notImage, ['file' => $image->name]];
		}

		if ($this->minWidth !== null && $width < $this->minWidth) {
			return [$this->underWidth, ['file' => $image->name, 'limit' => $this->minWidth]];
		}

		if ($this->minHeight !== null && $height < $this->minHeight) {
			return [$this->underHeight, ['file' => $image->name, 'limit' => $this->minHeight]];
		}

		if ($this->maxWidth !== null && $width > $this->maxWidth) {
			return [$this->overWidth, ['file' => $image->name, 'limit' => $this->maxWidth]];
		}

		if ($this->maxHeight !== null && $height > $this->maxHeight) {
			return [$this->overHeight, ['file' => $image->name, 'limit' => $this->maxHeight]];
		}
		return null;
	}
}
