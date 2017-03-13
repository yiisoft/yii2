<?php

namespace yiiunit\framework\web\stubs;

use Psr\Http\Message\StreamInterface;

/**
*
*/
class StreamStub implements StreamInterface
{
    private $_stream;
    private $_size;
    private $_seekable;
    private $_readable;
    private $_writable;
    private $_uri;
    private $_customMetadata;

    /** @var array Hash of readable and writable stream types */
    private static $readWriteHash = [
        'read' => [
            'r' => true, 'w+' => true, 'r+' => true, 'x+' => true, 'c+' => true,
            'rb' => true, 'w+b' => true, 'r+b' => true, 'x+b' => true,
            'c+b' => true, 'rt' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a+' => true
        ],
        'write' => [
            'w' => true, 'w+' => true, 'rw' => true, 'r+' => true, 'x+' => true,
            'c+' => true, 'wb' => true, 'w+b' => true, 'r+b' => true,
            'x+b' => true, 'c+b' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a' => true, 'a+' => true
        ]
    ];

    /**
     * This constructor accepts an associative array of options.
     *
     * - size: (int) If a read stream would otherwise have an indeterminate
     *   size, but the size is known due to foreknownledge, then you can
     *   provide that size, in bytes.
     * - metadata: (array) Any additional metadata to return when the metadata
     *   of the stream is accessed.
     *
     * @param string $stream  String to stream.
     * @param array    $options Associative array of options.
     *
     * @throws \InvalidArgumentException if the stream is not a stream resource
     */
    public function __construct($streamContent, $options = [])
    {
        $this->_stream = fopen('php://temp', 'r+');
        if ($streamContent !== '') {
            fwrite($this->_stream, $streamContent);
            fseek($this->_stream, 0);
        }

        if (isset($options['size'])) {
            $this->_size = $options['size'];
        }

        $this->_customMetadata = isset($options['metadata'])
            ? $options['metadata']
            : [];

        $meta = stream_get_meta_data($this->_stream);
        $this->_seekable = $meta['seekable'];
        $this->_readable = isset(self::$readWriteHash['read'][$meta['mode']]);
        $this->_writable = isset(self::$readWriteHash['write'][$meta['mode']]);
        $this->_uri = $this->getMetadata('uri');
    }

    public function __get($name)
    {
        if ($name == 'stream') {
            throw new \RuntimeException('The stream is detached');
        }

        throw new \BadMethodCallException('No value for ' . $name);
    }

    /**
     * Closes the stream when the destructed
     */
    public function __destruct()
    {
        $this->close();
    }

    public function __toString()
    {
        try {
            $this->seek(0);
            return (string) stream_get_contents($this->_stream);
        } catch (\Exception $e) {
            return '';
        }
    }

    public function getContents()
    {
        $contents = stream_get_contents($this->_stream);

        if ($contents === false) {
            throw new \RuntimeException('Unable to read stream contents');
        }

        return $contents;
    }

    public function close()
    {
        if (isset($this->_stream)) {
            if (is_resource($this->_stream)) {
                fclose($this->_stream);
            }
            $this->detach();
        }
    }

    public function detach()
    {
        if (!isset($this->_stream)) {
            return null;
        }

        $result = $this->_stream;
        unset($this->_stream);
        $this->_size = $this->_uri = null;
        $this->_readable = $this->_writable = $this->_seekable = false;

        return $result;
    }

    public function getSize()
    {
        if ($this->_size !== null) {
            return $this->_size;
        }

        if (!isset($this->_stream)) {
            return null;
        }

        // Clear the stat cache if the stream has a URI
        if ($this->_uri) {
            clearstatcache(true, $this->_uri);
        }

        $stats = fstat($this->_stream);
        if (isset($stats['size'])) {
            $this->_size = $stats['size'];
            return $this->_size;
        }

        return null;
    }

    public function isReadable()
    {
        return $this->_readable;
    }

    public function isWritable()
    {
        return $this->_writable;
    }

    public function isSeekable()
    {
        return $this->_seekable;
    }

    public function eof()
    {
        return !$this->_stream || feof($this->_stream);
    }

    public function tell()
    {
        $result = ftell($this->_stream);

        if ($result === false) {
            throw new \RuntimeException('Unable to determine stream position');
        }

        return $result;
    }

    public function rewind()
    {
        $this->seek(0);
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        if (!$this->_seekable) {
            throw new \RuntimeException('Stream is not seekable');
        } elseif (fseek($this->_stream, $offset, $whence) === -1) {
            throw new \RuntimeException('Unable to seek to stream position '
                . $offset . ' with whence ' . var_export($whence, true));
        }
    }

    public function read($length)
    {
        if (!$this->_readable) {
            throw new \RuntimeException('Cannot read from non-readable stream');
        }

        return fread($this->_stream, $length);
    }

    public function write($string)
    {
        if (!$this->_writable) {
            throw new \RuntimeException('Cannot write to a non-writable stream');
        }

        // We can't know the size after writing anything
        $this->_size = null;
        $result = fwrite($this->_stream, $string);

        if ($result === false) {
            throw new \RuntimeException('Unable to write to stream');
        }

        return $result;
    }

    public function getMetadata($key = null)
    {
        if (!isset($this->_stream)) {
            return $key ? null : [];
        } elseif (!$key) {
            return $this->_customMetadata + stream_get_meta_data($this->_stream);
        } elseif (isset($this->_customMetadata[$key])) {
            return $this->_customMetadata[$key];
        }

        $meta = stream_get_meta_data($this->_stream);

        return isset($meta[$key]) ? $meta[$key] : null;
    }
}
