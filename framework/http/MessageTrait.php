<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\http;

use Psr\Http\Message\StreamInterface;
use yii\di\Instance;

/**
 * MessageTrait provides set of methods to satisfy [[\Psr\Http\Message\MessageInterface]].
 *
 * This trait should be applied to descendant of [[\yii\base\BaseObject]] implementing [[\Psr\Http\Message\MessageInterface]].
 *
 * @property string $protocolVersion HTTP protocol version as a string.
 * @property StreamInterface $body the body of the message.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
trait MessageTrait
{
    /**
     * @var string HTTP protocol version as a string.
     */
    private $_protocolVersion;
    /**
     * @var HeaderCollection
     */
    private $_headers;
    /**
     * @var StreamInterface the body of the message.
     */
    private $_body;


    /**
     * Retrieves the HTTP protocol version as a string.
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion()
    {
        if ($this->_protocolVersion === null) {
            $this->_protocolVersion = $this->defaultProtocolVersion();
        }
        return $this->_protocolVersion;
    }

    /**
     * Specifies HTTP protocol version.
     * @param string $version HTTP protocol version
     */
    public function setProtocolVersion($version)
    {
        $this->_protocolVersion = $version;
    }

    /**
     * Return an instance with the specified HTTP protocol version.
     *
     * @param string $version HTTP protocol version
     * @return static
     */
    public function withProtocolVersion($version)
    {
        $this->setProtocolVersion($version);
        return $this;
    }

    /**
     * Returns default HTTP protocol version to be used in case it is not explicitly set.
     * @return string HTTP protocol version.
     */
    protected function defaultProtocolVersion()
    {
        if (!empty($_SERVER['SERVER_PROTOCOL'])) {
            return str_replace('HTTP/', '',  $_SERVER['SERVER_PROTOCOL']);
        }
        return '1.0';
    }

    /**
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return string[][] Returns an associative array of the message's headers. Each
     *     key MUST be a header name, and each value MUST be an array of strings
     *     for that header.
     */
    public function getHeaders()
    {
        ;
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     * @return bool Returns true if any header names match the given header
     *     name using a case-insensitive string comparison. Returns false if
     *     no matching header name is found in the message.
     */
    public function hasHeader($name)
    {
        ;
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     *
     * If the header does not appear in the message, this method MUST return an
     * empty array.
     *
     * @param string $name Case-insensitive header field name.
     * @return string[] An array of string values as provided for the given
     *    header. If the header does not appear in the message, this method MUST
     *    return an empty array.
     */
    public function getHeader($name)
    {
        ;
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * This method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     *
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     *
     * If the header does not appear in the message, this method MUST return
     * an empty string.
     *
     * @param string $name Case-insensitive header field name.
     * @return string A string of values as provided for the given header
     *    concatenated together using a comma. If the header does not appear in
     *    the message, this method MUST return an empty string.
     */
    public function getHeaderLine($name)
    {
        ;
    }

    /**
     * Return an instance with the provided value replacing the specified header.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new and/or updated header and value.
     *
     * @param string $name Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     * @return static
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withHeader($name, $value)
    {
        ;
    }

    /**
     * Return an instance with the specified header appended with the given value.
     *
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new header and/or value.
     *
     * @param string $name Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     * @return static
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withAddedHeader($name, $value)
    {
        ;
    }

    /**
     * Return an instance without the specified header.
     *
     * Header resolution MUST be done without case-sensitivity.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the named header.
     *
     * @param string $name Case-insensitive header field name to remove.
     * @return static
     */
    public function withoutHeader($name)
    {
        ;
    }

    /**
     * Gets the body of the message.
     * @return StreamInterface Returns the body as a stream.
     */
    public function getBody()
    {
        if (!$this->_body instanceof StreamInterface) {
            if ($this->_body === null) {
                $body = $this->defaultBody();
            } elseif ($this->_body instanceof \Closure) {
                $body = call_user_func($this->_body, $this);
            } else {
                $body = $this->_body;
            }

            $this->_body = Instance::ensure($body, StreamInterface::class);
        }
        return $this->_body;
    }

    /**
     * Specifies message body.
     * @param StreamInterface|\Closure|array $body stream instance or its DI compatible configuration.
     */
    public function setBody($body)
    {
        $this->_body = $body;
    }

    /**
     * Return an instance with the specified message body.
     * @param StreamInterface $body Body.
     * @return static
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function withBody(StreamInterface $body)
    {
        $this->setBody($body);
        return $this;
    }

    /**
     * Returns default message body to be used in case it is not explicitly set.
     * @return StreamInterface default body instance.
     */
    protected function defaultBody()
    {
        return new MemoryStream();
    }
}