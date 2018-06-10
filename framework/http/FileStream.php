<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\http;

use Psr\Http\Message\StreamInterface;
use Yii;
use yii\base\BaseObject;
use yii\base\ErrorHandler;
use yii\base\InvalidConfigException;

/**
 * FileStream represents file stream.
 *
 * Example:
 *
 * ```php
 * $stream = new FileSteam([
 *     'filename' => '@app/files/items.txt',
 *     'mode' => 'w+',
 * ]);
 *
 * $stream->write('some content...');
 * $stream->close();
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 3.0.0
 */
class FileStream extends BaseObject implements StreamInterface
{
    /**
     * @var string file or stream name.
     * Path alias can be used here, for example: '@app/runtime/items.csv'.
     * This field can also be PHP stream name, e.g. anything which can be passed to `fopen()`, for example: 'php://input'.
     */
    public $filename;
    /**
     * @var string file open mode.
     */
    public $mode = 'r';

    /**
     * @var resource|null stream resource
     */
    private $_resource;
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
            $this->_resource = $resource;
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
        if ($this->_resource !== null) {
            fclose($this->_resource);
            $this->_resource = null;
            $this->_metadata = null;
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
        $result = fwrite($this->getResource(), $string);
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
        if ($this->_metadata === null) {
            $this->_metadata = stream_get_meta_data($this->getResource());
        }

        if ($key === null) {
            return $this->_metadata;
        }

        return isset($this->_metadata[$key]) ? $this->_metadata[$key] : null;
    }
}