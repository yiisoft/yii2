<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\http;

use Psr\Http\Message\StreamInterface;
use yii\base\BaseObject;
use yii\base\ErrorHandler;

/**
 * ResourceStream wraps existing PHP stream resource, e.g. one opened by `fopen()`.
 *
 * Example:
 *
 * ```php
 * $stream = new ResourceStream([
 *     'resource' => tmpfile(),
 * ]);
 *
 * $stream->write('some content...');
 * $stream->close();
 * ```
 *
 * Usage of this class make sense in case you already have an opened PHP stream from elsewhere and wish to wrap it into `StreamInterface`.
 *
 * > Note: closing this stream will close the resource associated with it, so it becomes invalid for usage elsewhere.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 3.0.0
 */
class ResourceStream extends BaseObject implements StreamInterface
{
    /**
     * @var resource stream resource.
     */
    public $resource;

    /**
     * @var array a resource metadata.
     */
    private $_metadata;


    /**
     * Destructor.
     * Closes the stream resource when destroyed.
     */
    public function __destruct()
    {
        $this->close();
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
            return $this->getContents();
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
        if ($this->resource !== null && is_resource($this->resource)) {
            fclose($this->resource);
            $this->_metadata = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        if ($this->resource === null) {
            return null;
        }
        $result = $this->resource;
        $this->resource = null;
        $this->_metadata = null;
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        $uri = $this->getMetadata('uri');
        if (!empty($uri)) {
            // clear the stat cache in case stream has a URI
            clearstatcache(true, $uri);
        }

        $stats = fstat($this->resource);
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
        $result = ftell($this->resource);
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
        return feof($this->resource);
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        return (bool)$this->getMetadata('seekable');
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (fseek($this->resource, $offset, $whence) === -1) {
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
     * {@inheritdoc}
     */
    public function isWritable()
    {
        $mode = $this->getMetadata('mode');
        foreach (['w', 'c', 'a', 'x', 'r+'] as $key) {
            if (strpos($mode, $key) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        $result = fwrite($this->resource, $string);
        if ($result === false) {
            throw new \RuntimeException('Unable to write to stream');
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable()
    {
        $mode = $this->getMetadata('mode');
        foreach (['r', 'w+', 'a+', 'c+', 'x+'] as $key) {
            if (strpos($mode, $key) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        $string = fread($this->resource, $length);
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
        $contents = stream_get_contents($this->resource);
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
        if ($this->_metadata === null) {
            $this->_metadata = stream_get_meta_data($this->resource);
        }

        if ($key === null) {
            return $this->_metadata;
        }

        return isset($this->_metadata[$key]) ? $this->_metadata[$key] : null;
    }
}