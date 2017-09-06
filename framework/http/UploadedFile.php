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
use yii\helpers\Html;

/**
 * UploadedFile represents the information for an uploaded file.
 *
 * You can call [[getInstance()]] to retrieve the instance of an uploaded file,
 * and then use [[saveAs()]] to save it on the server.
 * You may also query other information about the file, including [[clientFilename]],
 * [[tempFilename]], [[clientMediaType]], [[size]] and [[error]].
 *
 * For more details and usage information on UploadedFile, see the [guide article on handling uploads](guide:input-file-upload).
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
     * @since 2.1.0
     */
    private $_stream;

    private static $_files;


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
     * Returns an uploaded file for the given model attribute.
     * The file should be uploaded using [[\yii\widgets\ActiveField::fileInput()]].
     * @param \yii\base\Model $model the data model
     * @param string $attribute the attribute name. The attribute name may contain array indexes.
     * For example, '[1]file' for tabular file uploading; and 'file[1]' for an element in a file array.
     * @return UploadedFile the instance of the uploaded file.
     * Null is returned if no file is uploaded for the specified model attribute.
     * @see getInstanceByName()
     */
    public static function getInstance($model, $attribute)
    {
        $name = Html::getInputName($model, $attribute);
        return static::getInstanceByName($name);
    }

    /**
     * Returns all uploaded files for the given model attribute.
     * @param \yii\base\Model $model the data model
     * @param string $attribute the attribute name. The attribute name may contain array indexes
     * for tabular file uploading, e.g. '[1]file'.
     * @return UploadedFile[] array of UploadedFile objects.
     * Empty array is returned if no available file was found for the given attribute.
     */
    public static function getInstances($model, $attribute)
    {
        $name = Html::getInputName($model, $attribute);
        return static::getInstancesByName($name);
    }

    /**
     * Returns an uploaded file according to the given file input name.
     * The name can be a plain string or a string like an array element (e.g. 'Post[imageFile]', or 'Post[0][imageFile]').
     * @param string $name the name of the file input field.
     * @return null|UploadedFile the instance of the uploaded file.
     * Null is returned if no file is uploaded for the specified name.
     */
    public static function getInstanceByName($name)
    {
        $files = self::loadFiles();
        return isset($files[$name]) ? new static($files[$name]) : null;
    }

    /**
     * Returns an array of uploaded files corresponding to the specified file input name.
     * This is mainly used when multiple files were uploaded and saved as 'files[0]', 'files[1]',
     * 'files[n]'..., and you can retrieve them all by passing 'files' as the name.
     * @param string $name the name of the array of files
     * @return UploadedFile[] the array of UploadedFile objects. Empty array is returned
     * if no adequate upload was found. Please note that this array will contain
     * all files from all sub-arrays regardless how deeply nested they are.
     */
    public static function getInstancesByName($name)
    {
        $files = self::loadFiles();
        if (isset($files[$name])) {
            return [new static($files[$name])];
        }
        $results = [];
        foreach ($files as $key => $file) {
            if (strpos($key, "{$name}[") === 0) {
                $results[] = new static($file);
            }
        }
        return $results;
    }

    /**
     * Cleans up the loaded UploadedFile instances.
     * This method is mainly used by test scripts to set up a fixture.
     */
    public static function reset()
    {
        self::$_files = null;
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
     * Creates UploadedFile instances from $_FILE.
     * @return array the UploadedFile instances
     */
    private static function loadFiles()
    {
        if (self::$_files === null) {
            self::$_files = [];
            if (isset($_FILES) && is_array($_FILES)) {
                foreach ($_FILES as $class => $info) {
                    self::loadFilesRecursive($class, $info['name'], $info['tmp_name'], $info['type'], $info['size'], $info['error']);
                }
            }
        }
        return self::$_files;
    }

    /**
     * Creates UploadedFile instances from $_FILE recursively.
     * @param string $key key for identifying uploaded file: class name and sub-array indexes
     * @param mixed $names file names provided by PHP
     * @param mixed $tempNames temporary file names provided by PHP
     * @param mixed $types file types provided by PHP
     * @param mixed $sizes file sizes provided by PHP
     * @param mixed $errors uploading issues provided by PHP
     */
    private static function loadFilesRecursive($key, $names, $tempNames, $types, $sizes, $errors)
    {
        if (is_array($names)) {
            foreach ($names as $i => $name) {
                self::loadFilesRecursive($key . '[' . $i . ']', $name, $tempNames[$i], $types[$i], $sizes[$i], $errors[$i]);
            }
        } elseif ((int) $errors !== UPLOAD_ERR_NO_FILE) {
            self::$_files[$key] = [
                'clientFilename' => $names,
                'tempFilename' => $tempNames,
                'clientMediaType' => $types,
                'size' => $sizes,
                'error' => $errors,
            ];
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getStream()
    {
        if (!$this->_stream instanceof StreamInterface) {
            if ($this->_stream === null) {
                if ($this->getError() !== UPLOAD_ERR_OK) {
                    throw new \RuntimeException('Unable to create file stream due to upload error: ' . $this->getError());
                }
                $stream = [
                    'class' => FileStream::class,
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
     * @since 2.1.0
     */
    public function setStream($stream)
    {
        $this->_stream = $stream;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function getSize()
    {
        return $this->_size;
    }

    /**
     * @param int $size the actual size of the uploaded file in bytes.
     * @throws InvalidArgumentException on invalid size given.
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * @param int $error upload error code.
     * @throws InvalidArgumentException on invalid error given.
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function getClientFilename()
    {
        return $this->_clientFilename;
    }

    /**
     * @param string $clientFilename the original name of the file being uploaded.
     * @since 2.1.0
     */
    public function setClientFilename($clientFilename)
    {
        $this->_clientFilename = $clientFilename;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getClientMediaType()
    {
        return $this->_clientMediaType;
    }

    /**
     * @param string $clientMediaType the MIME-type of the uploaded file (such as "image/gif").
     * @since 2.1.0
     */
    public function setClientMediaType($clientMediaType)
    {
        $this->_clientMediaType = $clientMediaType;
    }
}
