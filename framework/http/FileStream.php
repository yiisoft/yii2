<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\http;

use Psr\Http\Message\StreamInterface;
use Yii;
use yii\base\ErrorHandler;
use yii\base\InvalidConfigException;
use yii\base\Object;

/**
 * FileStream
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
class FileStream extends Object implements StreamInterface
{
    /**
     * @var string file name.
     * Path alias can be used here, for example: '@app/runtime/items.csv'.
     * This field can also be PHP stream name, for example: 'php://input'.
     */
    public $filename;
    /**
     * @var string file open mode.
     */
    public $mode = 'r';

    /**
     * @var resource|null
     */
    private $_resource;


    /**
     * Destructor.
     * Closes the stream when the destructed.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @return resource a file pointer resource.
     * @throws InvalidConfigException if unable to open a resource.
     */
    public function getResource()
    {
        if ($this->_resource === null) {
            $resource = fopen(Yii::getAlias($this->filename), $this->mode);
            if ($resource === false) {
                throw new InvalidConfigException("Unable to open file '{$this->filename}' with mode '{$this->mode}'");
            }
        }
        return $this->_resource;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        // __toString cannot throw exception
        // use trigger_error to bypass this limitation
        try {
            $this->seek(0);
            return (string) stream_get_contents($this->getResource());
        } catch (\Exception $e) {
            ErrorHandler::convertExceptionToError($e);
            return '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if ($this->_resource !== null) {
            fclose($this->_resource);
            $this->_resource = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        if ($this->_resource === null) {
            return null;
        }
        $result = $this->_resource;
        $this->_resource = null;
        return $result;
    }

    /**
     * Get the size of the stream if known.
     *
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize()
    {
        $stats = fstat($this->getResource());
        if (isset($stats['size'])) {
            return $stats['size'];
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function tell()
    {
        $result = ftell($this->getResource());
        if ($result === false) {
            throw new \RuntimeException('Unable to determine stream position');
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        return feof($this->getResource());
    }

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable()
    {
        // TODO: Implement isSeekable() method.
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (fseek($this->getResource(), $offset, $whence) === -1) {
            throw new \RuntimeException("Unable to seek to stream position '{$offset}' with whence '{$whence}'");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable()
    {
        // TODO: Implement isWritable() method.
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        $result = fwrite($this->getResource(), $string);
        if ($result === false) {
            throw new \RuntimeException('Unable to write to stream');
        }
        return $result;
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable()
    {
        // TODO: Implement isReadable() method.
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        $string = fread($this->getResource(), $length);
        if ($string === false) {
            throw new \RuntimeException('Unable to read from stream');
        }
        return $string;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        $contents = stream_get_contents($this->getResource());
        if ($contents === false) {
            throw new \RuntimeException('Unable to read stream contents');
        }
        return $contents;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        $metaData = stream_get_meta_data($this->getResource());
        if ($key === null) {
            return $metaData;
        }

        return isset($metaData[$key]) ?: null;
    }
}