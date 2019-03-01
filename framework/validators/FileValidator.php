<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\web\UploadedFile;

/**
 * FileValidator 校验一个指定的属性接受的是一个合法的上传文件。
 *
 * 注意你需要开启 `fileinfo` PHP扩展。
 *
 * @property int $sizeLimit 上传的文件大小限制。这个属性是只读的。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class FileValidator extends Validator
{
    /**
     * @var array|string 可上传的文件扩展名列表。
     * 它可以是一个数组，
     * 或者一个由空格或者逗号分隔的字符串（例如："gif, jpg"）。
     * 扩展名称大小写不敏感，默认为 null ，
     * 代表允许所有的文件扩展名。
     * @see wrongExtension for the customized message for wrong file type.
     */
    public $extensions;
    /**
     * @var bool 是否检查文件类型（扩展名）和 mime-type 是否符合。
     * 如果 mime-type 得到的扩展名和文件名称扩展名不一致，文件会被认为非法。
     */
    public $checkExtensionByMimeType = true;
    /**
     * @var array|string 允许被上传的文件 MIME 类型列表。
     * 它可以是一个 MIME 类型字符串数组，
     * 或者一个由空格或者逗号分隔的字符串（例如："text/plain, image/png"）。
     * 可以使用通配符 `*` 来匹配一组 MIME 类型。
     * 例如：`image/*` 将会匹配所有以 `image/` 开头的 MIME 类型。（例如：`image/jpeg`, `image/png`）。
     * MIME 类型名称大小写不敏感。默认为 null，代表允许所有的 MIME 类型。
     * @see wrongMimeType for the customized message for wrong MIME type.
     */
    public $mimeTypes;
    /**
     * @var int 上传文件的最小字节数，
     * 默认为null，代表无限制。
     * @see tooSmall for the customized message for a file that is too small.
     */
    public $minSize;
    /**
     * @var int 上传文件的最大字节数，
     * 默认为null，代表无限制。
     * 注意：大小限制同样会受 `upload_max_filesize` 和 `post_max_size` INI 参数以及 MAX_FILE_SIZE 隐藏字段值影响。
     * 更多详情参考 [[getSizeLimit()]]。
     * @see http://php.net/manual/en/ini.core.php#ini.upload-max-filesize
     * @see http://php.net/post-max-size
     * @see getSizeLimit
     * @see tooBig for the customized message for a file that is too big.
     */
    public $maxSize;
    /**
     * @var int 指定的属性可最多持有的文件数量。
     * 默认为1，代表上传单文件。
     * 你可以设置一个大一点的值，这样允许同时上传多个文件。
     * 设置为 `0` 意味着同时上传的文件数目没有限制。
     *
     * > 注意：同时上传的最大文件数量同样受 `max_file_uploads` 限制，
     * 其默认值为 20。
     *
     * @see http://php.net/manual/en/ini.core.php#ini.max-file-uploads
     * @see tooMany for the customized message when too many files are uploaded.
     */
    public $maxFiles = 1;
    /**
     * @var int 指定的属性最少持有的文件数量。
     * 默认为0，代表无限制。大一点的值意味着最少那么多的文件应该被上传。
     *
     * @see tooFew for the customized message when too few files are uploaded.
     * @since 2.0.14
     */
    public $minFiles = 0;
    /**
     * @var string 一个文件没有被正确上传时的错误消息。
     */
    public $message;
    /**
     * @var string 没有文件被上传的错误消息。
     * 注意：这是校验错误消息。
     * 为了确保文件上传是必须的，你需要设置属性 [[skipOnEmpty]] 值为 `false`。
     */
    public $uploadRequired;
    /**
     * @var string 当上传文件太大时的错误消息。
     * 你可以在消息中使用下面的这些 token ：
     *
     * - {attribute}: the attribute name
     * - {file}: the uploaded file name
     * - {limit}: the maximum size allowed (see [[getSizeLimit()]])
     * - {formattedLimit}: the maximum size formatted
     *   with [[\yii\i18n\Formatter::asShortSize()|Formatter::asShortSize()]]
     */
    public $tooBig;
    /**
     * @var string the error message used when the uploaded file is too small.
     * You may use the following tokens in the message:
     *
     * - {attribute}: the attribute name
     * - {file}: the uploaded file name
     * - {limit}: the value of [[minSize]]
     * - {formattedLimit}: the value of [[minSize]] formatted
     *   with [[\yii\i18n\Formatter::asShortSize()|Formatter::asShortSize()]
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
     * @var string the error message used if the count of multiple uploads less that minFiles.
     * You may use the following tokens in the message:
     *
     * - {attribute}: the attribute name
     * - {limit}: the value of [[minFiles]]
     *
     * @since 2.0.14
     */
    public $tooFew;
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
     * that is not allowed by [[mimeTypes]] property.
     * You may use the following tokens in the message:
     *
     * - {attribute}: the attribute name
     * - {file}: the uploaded file name
     * - {mimeTypes}: the value of [[mimeTypes]]
     */
    public $wrongMimeType;


    /**
     * {@inheritdoc}
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
        if ($this->tooFew === null) {
            $this->tooFew = Yii::t('yii', 'You should upload at least {limit, number} {limit, plural, one{file} other{files}}.');
        }
        if ($this->wrongExtension === null) {
            $this->wrongExtension = Yii::t('yii', 'Only files with these extensions are allowed: {extensions}.');
        }
        if ($this->tooBig === null) {
            $this->tooBig = Yii::t('yii', 'The file "{file}" is too big. Its size cannot exceed {formattedLimit}.');
        }
        if ($this->tooSmall === null) {
            $this->tooSmall = Yii::t('yii', 'The file "{file}" is too small. Its size cannot be smaller than {formattedLimit}.');
        }
        if (!is_array($this->extensions)) {
            $this->extensions = preg_split('/[\s,]+/', strtolower($this->extensions), -1, PREG_SPLIT_NO_EMPTY);
        } else {
            $this->extensions = array_map('strtolower', $this->extensions);
        }
        if ($this->wrongMimeType === null) {
            $this->wrongMimeType = Yii::t('yii', 'Only files with these MIME types are allowed: {mimeTypes}.');
        }
        if (!is_array($this->mimeTypes)) {
            $this->mimeTypes = preg_split('/[\s,]+/', strtolower($this->mimeTypes), -1, PREG_SPLIT_NO_EMPTY);
        } else {
            $this->mimeTypes = array_map('strtolower', $this->mimeTypes);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        if ($this->maxFiles != 1 || $this->minFiles > 1) {
            $rawFiles = $model->$attribute;
            if (!is_array($rawFiles)) {
                $this->addError($model, $attribute, $this->uploadRequired);

                return;
            }

            $files = $this->filterFiles($rawFiles);
            $model->$attribute = $files;

            if (empty($files)) {
                $this->addError($model, $attribute, $this->uploadRequired);

                return;
            }

            $filesCount = count($files);
            if ($this->maxFiles && $filesCount > $this->maxFiles) {
                $this->addError($model, $attribute, $this->tooMany, ['limit' => $this->maxFiles]);
            }
            
            if ($this->minFiles && $this->minFiles > $filesCount) {
                $this->addError($model, $attribute, $this->tooFew, ['limit' => $this->minFiles]);
            }

            foreach ($files as $file) {
                $result = $this->validateValue($file);
                if (!empty($result)) {
                    $this->addError($model, $attribute, $result[0], $result[1]);
                }
            }
        } else {
            $result = $this->validateValue($model->$attribute);
            if (!empty($result)) {
                $this->addError($model, $attribute, $result[0], $result[1]);
            }
        }
    }

    /**
     * Files filter.
     * @param array $files
     * @return UploadedFile[]
     */
    private function filterFiles(array $files)
    {
        $result = [];

        foreach ($files as $fileName => $file) {
            if ($file instanceof UploadedFile && $file->error !== UPLOAD_ERR_NO_FILE) {
                $result[$fileName] = $file;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        if (!$value instanceof UploadedFile || $value->error == UPLOAD_ERR_NO_FILE) {
            return [$this->uploadRequired, []];
        }

        switch ($value->error) {
            case UPLOAD_ERR_OK:
                if ($this->maxSize !== null && $value->size > $this->getSizeLimit()) {
                    return [
                        $this->tooBig,
                        [
                            'file' => $value->name,
                            'limit' => $this->getSizeLimit(),
                            'formattedLimit' => Yii::$app->formatter->asShortSize($this->getSizeLimit()),
                        ],
                    ];
                } elseif ($this->minSize !== null && $value->size < $this->minSize) {
                    return [
                        $this->tooSmall,
                        [
                            'file' => $value->name,
                            'limit' => $this->minSize,
                            'formattedLimit' => Yii::$app->formatter->asShortSize($this->minSize),
                        ],
                    ];
                } elseif (!empty($this->extensions) && !$this->validateExtension($value)) {
                    return [$this->wrongExtension, ['file' => $value->name, 'extensions' => implode(', ', $this->extensions)]];
                } elseif (!empty($this->mimeTypes) && !$this->validateMimeType($value)) {
                    return [$this->wrongMimeType, ['file' => $value->name, 'mimeTypes' => implode(', ', $this->mimeTypes)]];
                }

                return null;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return [$this->tooBig, [
                    'file' => $value->name,
                    'limit' => $this->getSizeLimit(),
                    'formattedLimit' => Yii::$app->formatter->asShortSize($this->getSizeLimit()),
                ]];
            case UPLOAD_ERR_PARTIAL:
                Yii::warning('File was only partially uploaded: ' . $value->name, __METHOD__);
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                Yii::warning('Missing the temporary folder to store the uploaded file: ' . $value->name, __METHOD__);
                break;
            case UPLOAD_ERR_CANT_WRITE:
                Yii::warning('Failed to write the uploaded file to disk: ' . $value->name, __METHOD__);
                break;
            case UPLOAD_ERR_EXTENSION:
                Yii::warning('File upload was stopped by some PHP extension: ' . $value->name, __METHOD__);
                break;
            default:
                break;
        }

        return [$this->message, []];
    }

    /**
     * 返回允许上传文件的最大大小。
     *
     * 这个参数由下面四个因素决定：
     *
     * - 'upload_max_filesize' in php.ini
     * - 'post_max_size' in php.ini
     * - 'MAX_FILE_SIZE' hidden field
     * - [[maxSize]]
     *
     * @return int the size limit for uploaded files.
     */
    public function getSizeLimit()
    {
        // Get the lowest between post_max_size and upload_max_filesize, log a warning if the first is < than the latter
        $limit = $this->sizeToBytes(ini_get('upload_max_filesize'));
        $postLimit = $this->sizeToBytes(ini_get('post_max_size'));
        if ($postLimit > 0 && $postLimit < $limit) {
            Yii::warning('PHP.ini\'s \'post_max_size\' is less than \'upload_max_filesize\'.', __METHOD__);
            $limit = $postLimit;
        }
        if ($this->maxSize !== null && $limit > 0 && $this->maxSize < $limit) {
            $limit = $this->maxSize;
        }
        if (isset($_POST['MAX_FILE_SIZE']) && $_POST['MAX_FILE_SIZE'] > 0 && $_POST['MAX_FILE_SIZE'] < $limit) {
            $limit = (int) $_POST['MAX_FILE_SIZE'];
        }

        return $limit;
    }

    /**
     * {@inheritdoc}
     * @param bool $trim
     */
    public function isEmpty($value, $trim = false)
    {
        $value = is_array($value) ? reset($value) : $value;
        return !($value instanceof UploadedFile) || $value->error == UPLOAD_ERR_NO_FILE;
    }

    /**
     * Converts php.ini style size to bytes.
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
     * 根据当前校验器配置检查指定的上传文件是否含有正确的类型（扩展）
     * @param UploadedFile $file
     * @return bool
     */
    protected function validateExtension($file)
    {
        $extension = mb_strtolower($file->extension, 'UTF-8');

        if ($this->checkExtensionByMimeType) {
            $mimeType = FileHelper::getMimeType($file->tempName, null, false);
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

    /**
     * {@inheritdoc}
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);
        return 'yii.validation.file(attribute, messages, ' . Json::encode($options) . ');';
    }

    /**
     * {@inheritdoc}
     */
    public function getClientOptions($model, $attribute)
    {
        $label = $model->getAttributeLabel($attribute);

        $options = [];
        if ($this->message !== null) {
            $options['message'] = $this->formatMessage($this->message, [
                'attribute' => $label,
            ]);
        }

        $options['skipOnEmpty'] = $this->skipOnEmpty;

        if (!$this->skipOnEmpty) {
            $options['uploadRequired'] = $this->formatMessage($this->uploadRequired, [
                'attribute' => $label,
            ]);
        }

        if ($this->mimeTypes !== null) {
            $mimeTypes = [];
            foreach ($this->mimeTypes as $mimeType) {
                $mimeTypes[] = new JsExpression(Html::escapeJsRegularExpression($this->buildMimeTypeRegexp($mimeType)));
            }
            $options['mimeTypes'] = $mimeTypes;
            $options['wrongMimeType'] = $this->formatMessage($this->wrongMimeType, [
                'attribute' => $label,
                'mimeTypes' => implode(', ', $this->mimeTypes),
            ]);
        }

        if ($this->extensions !== null) {
            $options['extensions'] = $this->extensions;
            $options['wrongExtension'] = $this->formatMessage($this->wrongExtension, [
                'attribute' => $label,
                'extensions' => implode(', ', $this->extensions),
            ]);
        }

        if ($this->minSize !== null) {
            $options['minSize'] = $this->minSize;
            $options['tooSmall'] = $this->formatMessage($this->tooSmall, [
                'attribute' => $label,
                'limit' => $this->minSize,
                'formattedLimit' => Yii::$app->formatter->asShortSize($this->minSize),
            ]);
        }

        if ($this->maxSize !== null) {
            $options['maxSize'] = $this->maxSize;
            $options['tooBig'] = $this->formatMessage($this->tooBig, [
                'attribute' => $label,
                'limit' => $this->getSizeLimit(),
                'formattedLimit' => Yii::$app->formatter->asShortSize($this->getSizeLimit()),
            ]);
        }

        if ($this->maxFiles !== null) {
            $options['maxFiles'] = $this->maxFiles;
            $options['tooMany'] = $this->formatMessage($this->tooMany, [
                'attribute' => $label,
                'limit' => $this->maxFiles,
            ]);
        }

        return $options;
    }

    /**
     * 根据通配符构造正则表达式。
     *
     * @param string $mask
     * @return string the regular expression
     * @see mimeTypes
     */
    private function buildMimeTypeRegexp($mask)
    {
        return '/^' . str_replace('\*', '.*', preg_quote($mask, '/')) . '$/';
    }

    /**
     * 检查 $file 的 mimeType 是否在 [[mimeTypes]] 属性列表中。
     *
     * @param UploadedFile $file
     * @return bool whether the $file mimeType is allowed
     * @throws \yii\base\InvalidConfigException
     * @see mimeTypes
     * @since 2.0.8
     */
    protected function validateMimeType($file)
    {
        $fileMimeType = FileHelper::getMimeType($file->tempName);

        foreach ($this->mimeTypes as $mimeType) {
            if ($mimeType === $fileMimeType) {
                return true;
            }

            if (strpos($mimeType, '*') !== false && preg_match($this->buildMimeTypeRegexp($mimeType), $fileMimeType)) {
                return true;
            }
        }

        return false;
    }
}
