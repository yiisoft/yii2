<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\http;

use Psr\Http\Message\StreamInterface;
use yii\base\BaseObject;
use yii\base\InvalidArgumentException;

/**
 * MemoryStream uses internal field as a stream source. Thus data associated with this stream exists only in
 * memory and will be lost once stream is closed.
 *
 * Example:
 *
 * ```php
 * $stream = new MemoryStream();
 *
 * $stream->write('some content...');
 * // ...
 * $stream->rewind();
 * echo $stream->getContents();
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 3.0.0
 */
class MemoryStream extends BaseObject implements StreamInterface
{
    /**
     * @var string internal content.
     */
    private $buffer = '';
    /**
     * @var int internal stream pointer.
     */
    private $pointer = 0;


    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->buffer;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->buffer = '';
        $this->pointer = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        $this->close();
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return strlen($this->buffer);
    }

    /**
     * {@inheritdoc}
     */
    public function tell()
    {
        return $this->pointer;
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        return $this->pointer >= $this->getSize();
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        switch ($whence) {
            case SEEK_SET:
                $this->pointer = $offset;
                break;
            case SEEK_CUR:
                $this->pointer += $offset;
                break;
            case SEEK_END:
                $this->pointer = $this->getSize() + $offset;
                break;
            default:
                throw new InvalidArgumentException("Unknown seek whence: '{$whence}'.");
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
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        $size = $this->getSize();
        $writeSize = strlen($string);

        if ($this->pointer >= $size) {
            $this->buffer .= $string;
            $this->pointer = $size + $writeSize;
            return $writeSize;
        }

        $begin = substr($this->buffer, 0, $this->pointer);
        $end = substr($this->buffer, $this->pointer + $writeSize);

        $this->buffer = $begin . $string . $end;
        $this->pointer += $writeSize;
        return $writeSize;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        $data = substr($this->buffer, $this->pointer, $length);
        $this->pointer += $length;
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        if ($this->pointer === 0) {
            return $this->buffer;
        }
        return substr($this->buffer, $this->pointer);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        $metadata = [
            'mode' => 'rw',
            'seekable' => $this->isSeekable(),
        ];

        if ($key === null) {
            return $metadata;
        }

        return (isset($metadata[$key])) ? $metadata[$key] : null;
    }
}