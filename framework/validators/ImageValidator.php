<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\http\UploadedFile;

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
     * @var int the minimum width in pixels.
     * Defaults to null, meaning no limit.
     * @see underWidth for the customized message used when image width is too small.
     */
    public $minWidth;
    /**
     * @var int the maximum width in pixels.
     * Defaults to null, meaning no limit.
     * @see overWidth for the customized message used when image width is too big.
     */
    public $maxWidth;
    /**
     * @var int the minimum height in pixels.
     * Defaults to null, meaning no limit.
     * @see underHeight for the customized message used when image height is too small.
     */
    public $minHeight;
    /**
     * @var int the maximum width in pixels.
     * Defaults to null, meaning no limit.
     * @see overWidth for the customized message used when image height is too big.
     */
    public $maxHeight;
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
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if ($this->notImage === null) {
            $this->notImage = Yii::t('yii', 'The file "{file}" is not an image.');
        }
        if ($this->underWidth === null) {
            $this->underWidth = Yii::t('yii', 'The image "{file}" is too small. The width cannot be smaller than {limit, number} {limit, plural, one{pixel} other{pixels}}.');
        }
        if ($this->underHeight === null) {
            $this->underHeight = Yii::t('yii', 'The image "{file}" is too small. The height cannot be smaller than {limit, number} {limit, plural, one{pixel} other{pixels}}.');
        }
        if ($this->overWidth === null) {
            $this->overWidth = Yii::t('yii', 'The image "{file}" is too large. The width cannot be larger than {limit, number} {limit, plural, one{pixel} other{pixels}}.');
        }
        if ($this->overHeight === null) {
            $this->overHeight = Yii::t('yii', 'The image "{file}" is too large. The height cannot be larger than {limit, number} {limit, plural, one{pixel} other{pixels}}.');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        $result = parent::validateValue($value);

        return empty($result) ? $this->validateImage($value) : $result;
    }

    /**
     * Validates an image file.
     * @param UploadedFile $image uploaded file passed to check against a set of rules
     * @return array|null the error message and the parameters to be inserted into the error message.
     * Null should be returned if the data is valid.
     */
    protected function validateImage($image)
    {
        if (false === ($imageInfo = getimagesize($image->tempFilename))) {
            return [$this->notImage, ['file' => $image->name]];
        }

        [$width, $height] = $imageInfo;

        if ($width == 0 || $height == 0) {
            return [$this->notImage, ['file' => $image->getClientFilename()]];
        }

        if ($this->minWidth !== null && $width < $this->minWidth) {
            return [$this->underWidth, ['file' => $image->getClientFilename(), 'limit' => $this->minWidth]];
        }

        if ($this->minHeight !== null && $height < $this->minHeight) {
            return [$this->underHeight, ['file' => $image->getClientFilename(), 'limit' => $this->minHeight]];
        }

        if ($this->maxWidth !== null && $width > $this->maxWidth) {
            return [$this->overWidth, ['file' => $image->getClientFilename(), 'limit' => $this->maxWidth]];
        }

        if ($this->maxHeight !== null && $height > $this->maxHeight) {
            return [$this->overHeight, ['file' => $image->getClientFilename(), 'limit' => $this->maxHeight]];
        }

        return null;
    }
}
