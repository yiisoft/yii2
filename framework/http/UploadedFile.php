<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\http;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use yii\base\BaseObject;
use yii\base\InvalidArgumentException;
use yii\di\Instance;

/**
 * UploadedFile represents the information for an uploaded file.
 *
 * You can retrieve the set of an uploaded file from application 'request' component:
 *
 * ```php
 * $uploadedFiles = Yii::$app->request->getUploadedFiles();
 * ```
 *
 * You can use [[saveAs()]] to save file on the server.
 * You may also query other information about the file, including [[clientFilename]],
 * [[tempFilename]], [[clientMediaType]], [[size]] and [[error]].
 *
 * For more details and usage information on UploadedFile, see the [guide article on handling uploads](guide:input-file-upload)
 * and [PSR-7 Uploaded Files specs](http://www.php-fig.org/psr/psr-7/#16-uploaded-files).
 *
 * @property string $clientFilename the original name of the file being uploaded.
 * @property int $error an error code describing the status of this file uploading.
 * @property int $size the actual size of the uploaded file in bytes.
 * @property string $clientMediaType  the MIME-type of the uploaded file (such as "image/gif").
 * Since this MIME type is not checked on the server-side, do not take this value for granted.
 * Instead, use [[\yii\helpers\FileHelper::getMimeType()]] to determine the exact MIME type.
 * @property string $baseName Original file base name. This property is read-only.
 * @property string $extension File extension. This property is read-only.
 * @property bool $hasError Whether there is an error with the uploaded file. Check [[error]] for detailed
 * error code information. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class UploadedFile extends BaseObject implements UploadedFileInterface
{
    /**
     * @var string the path of the uploaded file on the server.
     * Note, this is a temporary file which will be automatically deleted by PHP
     * after the current request is processed.
     */
    public $tempFilename;

    /**
     * @var string the original name of the file being uploaded
     */
    private $_clientFilename;
    /**
     * @var string the MIME-type of the uploaded file (such as "image/gif").
     * Since this MIME type is not checked on the server-side, do not take this value for granted.
     * Instead, use [[\yii\helpers\FileHelper::getMimeType()]] to determine the exact MIME type.
     */
    private $_clientMediaType;
    /**
     * @var int the actual size of the uploaded file in bytes
     */
    private $_size;
    /**
     * @var int an error code describing the status of this file uploading.
     * @see http://www.php.net/manual/en/features.file-upload.errors.php
     */
    private $_error;
    /**
     * @var StreamInterface stream for this file.
     * @since 3.0.0
     */
    private $_stream;


    /**
     * String output.
     * This is PHP magic method that returns string representation of an object.
     * The implementation here returns the uploaded file's name.
     * @return string the string representation of the object
     */
    public function __toString()
    {
        return $this->clientFilename;
    }

    /**
     * Saves the uploaded file.
     * Note that this method uses php's move_uploaded_file() method. If the target file `$file`
     * already exists, it will be overwritten.
     * @param string $file the file path used to save the uploaded file
     * @param bool $deleteTempFile whether to delete the temporary file after saving.
     * If true, you will not be able to save the uploaded file again in the current request.
     * @return bool true whether the file is saved successfully
     * @see error
     */
    public function saveAs($file, $deleteTempFile = true)
    {
        if ($this->error == UPLOAD_ERR_OK) {
            if ($deleteTempFile) {
                $this->moveTo($file);
                return true;
            } elseif (is_uploaded_file($this->tempFilename)) {
                return copy($this->tempFilename, $file);
            }
        }
        return false;
    }

    /**
     * @return string original file base name
     */
    public function getBaseName()
    {
        // https://github.com/yiisoft/yii2/issues/11012
        $pathInfo = pathinfo('_' . $this->getClientFilename(), PATHINFO_FILENAME);
        return mb_substr($pathInfo, 1, mb_strlen($pathInfo, '8bit'), '8bit');
    }

    /**
     * @return string file extension
     */
    public function getExtension()
    {
        return strtolower(pathinfo($this->getClientFilename(), PATHINFO_EXTENSION));
    }

    /**
     * @return bool whether there is an error with the uploaded file.
     * Check [[error]] for detailed error code information.
     */
    public function getHasError()
    {
        return $this->error != UPLOAD_ERR_OK;
    }

    /**
     * {@inheritdoc}
     * @since 3.0.0
     */
    public function getStream()
    {
        if (!$this->_stream instanceof StreamInterface) {
            if ($this->_stream === null) {
                if ($this->getError() !== UPLOAD_ERR_OK) {
                    throw new \RuntimeException('Unable to create file stream due to upload error: ' . $this->getError());
                }
                $stream = [
                    '__class' => FileStream::class,
                    'filename' => $this->tempFilename,
                    'mode' => 'r',
                ];
            } elseif ($this->_stream instanceof \Closure) {
                $stream = call_user_func($this->_stream, $this);
            } else {
                $stream = $this->_stream;
            }

            $this->_stream = Instance::ensure($stream, StreamInterface::class);
        }
        return $this->_stream;
    }

    /**
     * @param StreamInterface|\Closure|array $stream stream instance or its DI compatible configuration.
     * @since 3.0.0
     */
    public function setStream($stream)
    {
        $this->_stream = $stream;
    }

    /**
     * {@inheritdoc}
     * @since 3.0.0
     */
    public function moveTo($targetPath)
    {
        if ($this->error !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Unable to move file due to upload error: ' . $this->error);
        }
        if (!move_uploaded_file($this->tempFilename, $targetPath)) {
            throw new \RuntimeException('Unable to move uploaded file.');
        }
    }

    /**
     * {@inheritdoc}
     * @since 3.0.0
     */
    public function getSize()
    {
        return $this->_size;
    }

    /**
     * @param int $size the actual size of the uploaded file in bytes.
     * @throws InvalidArgumentException on invalid size given.
     * @since 3.0.0
     */
    public function setSize($size)
    {
        if (!is_int($size)) {
            throw new InvalidArgumentException('"' . get_class($this) . '::$size" must be an integer.');
        }
        $this->_size = $size;
    }

    /**
     * {@inheritdoc}
     * @since 3.0.0
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * @param int $error upload error code.
     * @throws InvalidArgumentException on invalid error given.
     * @since 3.0.0
     */
    public function setError($error)
    {
        if (!is_int($error)) {
            throw new InvalidArgumentException('"' . get_class($this) . '::$error" must be an integer.');
        }
        $this->_error = $error;
    }

    /**
     * {@inheritdoc}
     * @since 3.0.0
     */
    public function getClientFilename()
    {
        return $this->_clientFilename;
    }

    /**
     * @param string $clientFilename the original name of the file being uploaded.
     * @since 3.0.0
     */
    public function setClientFilename($clientFilename)
    {
        $this->_clientFilename = $clientFilename;
    }

    /**
     * {@inheritdoc}
     * @since 3.0.0
     */
    public function getClientMediaType()
    {
        return $this->_clientMediaType;
    }

    /**
     * @param string $clientMediaType the MIME-type of the uploaded file (such as "image/gif").
     * @since 3.0.0
     */
    public function setClientMediaType($clientMediaType)
    {
        $this->_clientMediaType = $clientMediaType;
    }
}
