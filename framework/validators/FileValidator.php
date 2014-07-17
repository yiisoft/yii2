<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

/**
 * FileValidator verifies if an attribute is receiving a valid uploaded file.
 *
 * @property integer $sizeLimit The size limit for uploaded files. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class FileValidator extends Validator
{
    /**
     * @var array|string a list of file name extensions that are allowed to be uploaded.
     * This can be either an array or a string consisting of file extension names
     * separated by space or comma (e.g. "gif, jpg").
     * Extension names are case-insensitive. Defaults to null, meaning all file name
     * extensions are allowed.
     * @see wrongType
     */
    public $extensions;
    /**
     * @var boolean whether to check file type (extension) with mime-type. If extension produced by
     * file mime-type check differs from uploaded file extension, the file will be considered as invalid.
     */
    public $checkExtensionByMimeType = true;
    /**
     * @var array|string a list of file MIME types that are allowed to be uploaded.
     * This can be either an array or a string consisting of file MIME types
     * separated by space or comma (e.g. "text/plain, image/png").
     * Mime type names are case-insensitive. Defaults to null, meaning all MIME types
     * are allowed.
     * @see wrongMimeType
     */
    public $mimeTypes;
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
     * @see tooMany
     */
    public $maxFiles = 1;
    /**
     * @var string the error message used when a file is not uploaded correctly.
     */
    public $message;
    /**
     * @var string the error message used when no file is uploaded.
     * Note that this is the text of the validation error message. To make uploading files required,
     * you have to set [[skipOnEmpty]] to `false`.
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
     * @var string the error message used if the count of multiple uploads exceeds limit.
     * You may use the following tokens in the message:
     *
     * - {attribute}: the attribute name
     * - {limit}: the value of [[maxFiles]]
     */
    public $tooMany;
    /**
     * @var string the error message used when the uploaded file has an extension name
     * that is not listed in [[extensions]]. You may use the following tokens in the message:
     *
     * - {attribute}: the attribute name
     * - {file}: the uploaded file name
     * - {extensions}: the list of the allowed extensions.
     */
    public $wrongExtension;
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
        if ($this->message === null) {
            $this->message = Yii::t('yii', 'File upload failed.');
        }
        if ($this->uploadRequired === null) {
            $this->uploadRequired = Yii::t('yii', 'Please upload a file.');
        }
        if ($this->tooMany === null) {
            $this->tooMany = Yii::t('yii', 'You can upload at most {limit, number} {limit, plural, one{file} other{files}}.');
        }
        if ($this->wrongExtension === null) {
            $this->wrongExtension = Yii::t('yii', 'Only files with these extensions are allowed: {extensions}.');
        }
        if ($this->tooBig === null) {
            $this->tooBig = Yii::t('yii', 'The file "{file}" is too big. Its size cannot exceed {limit, number} {limit, plural, one{byte} other{bytes}}.');
        }
        if ($this->tooSmall === null) {
            $this->tooSmall = Yii::t('yii', 'The file "{file}" is too small. Its size cannot be smaller than {limit, number} {limit, plural, one{byte} other{bytes}}.');
        }
        if (!is_array($this->extensions)) {
            $this->extensions = preg_split('/[\s,]+/', strtolower($this->extensions), -1, PREG_SPLIT_NO_EMPTY);
        }
        if ($this->wrongMimeType === null) {
            $this->wrongMimeType = Yii::t('yii', 'Only files with these MIME types are allowed: {mimeTypes}.');
        }
        if (!is_array($this->mimeTypes)) {
            $this->mimeTypes = preg_split('/[\s,]+/', strtolower($this->mimeTypes), -1, PREG_SPLIT_NO_EMPTY);
        }
    }

    /**
     * @inheritdoc
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
                if (!$file instanceof UploadedFile || $file->error == UPLOAD_ERR_NO_FILE) {
                    unset($files[$i]);
                }
            }
            $object->$attribute = array_values($files);
            if (empty($files)) {
                $this->addError($object, $attribute, $this->uploadRequired);
            }
            if (count($files) > $this->maxFiles) {
                $this->addError($object, $attribute, $this->tooMany, ['limit' => $this->maxFiles]);
            } else {
                foreach ($files as $file) {
                    $result = $this->validateValue($file);
                    if (!empty($result)) {
                        $this->addError($object, $attribute, $result[0], $result[1]);
                    }
                }
            }
        } else {
            $result = $this->validateValue($object->$attribute);
            if (!empty($result)) {
                $this->addError($object, $attribute, $result[0], $result[1]);
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($file)
    {
        if (!$file instanceof UploadedFile || $file->error == UPLOAD_ERR_NO_FILE) {
            return [$this->uploadRequired, []];
        }

        switch ($file->error) {
            case UPLOAD_ERR_OK:
                if ($this->maxSize !== null && $file->size > $this->maxSize) {
                    return [$this->tooBig, ['file' => $file->name, 'limit' => $this->getSizeLimit()]];
                } elseif ($this->minSize !== null && $file->size < $this->minSize) {
                    return [$this->tooSmall, ['file' => $file->name, 'limit' => $this->minSize]];
                } elseif (!empty($this->extensions) && !$this->validateExtension($file)) {
                    return [$this->wrongExtension, ['file' => $file->name, 'extensions' => implode(', ', $this->extensions)]];
                } elseif (!empty($this->mimeTypes) &&  !in_array(FileHelper::getMimeType($file->tempName), $this->mimeTypes, true)) {
                    return [$this->wrongMimeType, ['file' => $file->name, 'mimeTypes' => implode(', ', $this->mimeTypes)]];
                } else {
                    return null;
                }
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return [$this->tooBig, ['file' => $file->name, 'limit' => $this->getSizeLimit()]];
            case UPLOAD_ERR_PARTIAL:
                Yii::warning('File was only partially uploaded: ' . $file->name, __METHOD__);
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                Yii::warning('Missing the temporary folder to store the uploaded file: ' . $file->name, __METHOD__);
                break;
            case UPLOAD_ERR_CANT_WRITE:
                Yii::warning('Failed to write the uploaded file to disk: ' . $file->name, __METHOD__);
                break;
            case UPLOAD_ERR_EXTENSION:
                Yii::warning('File upload was stopped by some PHP extension: ' . $file->name, __METHOD__);
                break;
            default:
                break;
        }

        return [$this->message, []];
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
        $limit = $this->sizeToBytes(ini_get('upload_max_filesize'));
        if ($this->maxSize !== null && $limit > 0 && $this->maxSize < $limit) {
            $limit = $this->maxSize;
        }
        if (isset($_POST['MAX_FILE_SIZE']) && $_POST['MAX_FILE_SIZE'] > 0 && $_POST['MAX_FILE_SIZE'] < $limit) {
            $limit = (int) $_POST['MAX_FILE_SIZE'];
        }

        return $limit;
    }

    /**
     * @inheritdoc
     */
    public function isEmpty($value, $trim = false)
    {
        $value = is_array($value) && !empty($value) ? $value[0] : $value;
        return !($value instanceof UploadedFile) || $value->error == UPLOAD_ERR_NO_FILE;
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
                return (int) $sizeStr * 1048576;
            case 'K':
            case 'k':
                return (int) $sizeStr * 1024;
            case 'G':
            case 'g':
                return (int) $sizeStr * 1073741824;
            default:
                return (int) $sizeStr;
        }
    }

    /**
     * Checks if given uploaded file have correct type (extension) according current validator settings.
     * @param UploadedFile $file
     * @return boolean
     */
    protected function validateExtension($file)
    {
        $extension = mb_strtolower($file->extension, 'utf-8');

        if ($this->checkExtensionByMimeType) {

            $mimeType = FileHelper::getMimeType($file->tempName);
            if ($mimeType === null) {
                return false;
            }

            $extensionsByMimeType = FileHelper::getExtensionsByMimeType($mimeType);

            if (!in_array($extension, $extensionsByMimeType, true)) {
                return false;
            }
        }

        if (!in_array($extension, $this->extensions, true)) {
            return false;
        }

        return true;
    }

}
