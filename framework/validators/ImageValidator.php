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
 * ImageValidator 校验一个属性是否在接受一个合法的图片。
 *
 * @author Taras Gudz <gudz.taras@gmail.com>
 * @since 2.0
 */
class ImageValidator extends FileValidator
{
    /**
     * @var string 当上传文件不是一个图片的时候的错误消息
     * 你可以在消息中使用如下 token ：
     *
     * - {attribute}: the attribute name
     * - {file}: the uploaded file name
     */
    public $notImage;
    /**
     * @var int 最小的像素宽度。
     * 默认为 null，代表无限制。
     * @see underWidth 当图片宽度小于指定像素时的自定义错误消息
     */
    public $minWidth;
    /**
     * @var int 最大的像素宽度。
     * 默认为 null，代表无限制。
     * @see overWidth 当图片宽度大于指定像素时的自定义错误消息
     */
    public $maxWidth;
    /**
     * @var int 最小的像素高度。
     * 默认为 null，代表无限制。
     * @see underHeight 当图片高度小于指定像素时的自定义错误消息
     */
    public $minHeight;
    /**
     * @var int 最大的像素高度。
     * 默认为 null，代表无限制。
     * @see overHeight 当图片高度大于指定像素时的自定义错误消息
     */
    public $maxHeight;
    /**
     * @var string 当图片宽度小于指定像素 [[minWidth]] 时的自定义错误消息。
     * 你可以在消息中使用以下占位符：
     *
     * - {attribute}: 属性标签名
     * - {file}: 上传文件名
     * - {limit}: [[minWidth]] 的限制
     */
    public $underWidth;
    /**
     * @var string 当图片宽度大于指定像素 [[maxWidth]] 时的自定义错误消息。
     * 你可以在消息中使用以下占位符：
     *
     * - {attribute}: 属性标签名
     * - {file}: 上传文件名
     * - {limit}: [[maxWidth]] 的限制
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
     * 校验一个图片文件。
     * @param UploadedFile $image 传来的上传文件用于使用一系列规则进行校验
     * @return array|null 错误消息和待插入消息中的参数。
     * 如果数据是合法的将返回 Null。
     */
    protected function validateImage($image)
    {
        if (false === ($imageInfo = getimagesize($image->tempName))) {
            return [$this->notImage, ['file' => $image->name]];
        }

        list($width, $height) = $imageInfo;

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

    /**
     * {@inheritdoc}
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);
        return 'yii.validation.image(attribute, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ', deferred);';
    }

    /**
     * {@inheritdoc}
     */
    public function getClientOptions($model, $attribute)
    {
        $options = parent::getClientOptions($model, $attribute);

        $label = $model->getAttributeLabel($attribute);

        if ($this->notImage !== null) {
            $options['notImage'] = $this->formatMessage($this->notImage, [
                'attribute' => $label,
            ]);
        }

        if ($this->minWidth !== null) {
            $options['minWidth'] = $this->minWidth;
            $options['underWidth'] = $this->formatMessage($this->underWidth, [
                'attribute' => $label,
                'limit' => $this->minWidth,
            ]);
        }

        if ($this->maxWidth !== null) {
            $options['maxWidth'] = $this->maxWidth;
            $options['overWidth'] = $this->formatMessage($this->overWidth, [
                'attribute' => $label,
                'limit' => $this->maxWidth,
            ]);
        }

        if ($this->minHeight !== null) {
            $options['minHeight'] = $this->minHeight;
            $options['underHeight'] = $this->formatMessage($this->underHeight, [
                'attribute' => $label,
                'limit' => $this->minHeight,
            ]);
        }

        if ($this->maxHeight !== null) {
            $options['maxHeight'] = $this->maxHeight;
            $options['overHeight'] = $this->formatMessage($this->overHeight, [
                'attribute' => $label,
                'limit' => $this->maxHeight,
            ]);
        }

        return $options;
    }
}
