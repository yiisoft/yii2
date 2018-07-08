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
 * @property string[][] $headers the message's headers.
 * @property StreamInterface $body the body of the message.
 * @property HeaderCollection $headerCollection The header collection. This property is read-only.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 3.0.0
 */
trait MessageTrait
{
    /**
     * @var string HTTP protocol version as a string.
     */
    private $_protocolVersion;
    /**
     * @var HeaderCollection header collection, which is used for headers storage.
     */
    private $_headerCollection;
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
     * This method retains the immutability of the message and returns an instance that has the
     * new protocol version.
     *
     * @param string $version HTTP protocol version
     * @return static
     */
    public function withProtocolVersion($version)
    {
        if ($this->getProtocolVersion() === $version) {
            return $this;
        }

        $newInstance = clone $this;
        $newInstance->setProtocolVersion($version);
        return $newInstance;
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
     * Returns the header collection.
     * The header collection contains the currently registered HTTP headers.
     * @return HeaderCollection the header collection
     */
    public function getHeaderCollection()
    {
        if ($this->_headerCollection === null) {
            $headerCollection = new HeaderCollection();
            $headerCollection->fromArray($this->defaultHeaders());
            $this->_headerCollection = $headerCollection;
        }
        return $this->_headerCollection;
    }

    /**
     * Returns default message's headers, which should be present once [[headerCollection]] is instantiated.
     * @return string[][] an associative array of the message's headers.
     */
    protected function defaultHeaders()
    {
        return [];
    }

    /**
     * Sets up message's headers at batch, removing any previously existing ones.
     * @param string[][] $headers an associative array of the message's headers.
     */
    public function setHeaders($headers)
    {
        $headerCollection = $this->getHeaderCollection();
        $headerCollection->removeAll();
        $headerCollection->fromArray($headers);
    }

    /**
     * Sets up a particular message's header, removing any its previously existing value.
     * @param string $name Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     */
    public function setHeader($name, $value)
    {
        $this->getHeaderCollection()->set($name, $value);
    }

    /**
     * Appends the given value to the specified header.
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     * @param string $name Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     */
    public function addHeader($name, $value)
    {
        $this->getHeaderCollection()->add($name, $value);
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
        return $this->getHeaderCollection()->toArray();
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
        return $this->getHeaderCollection()->has($name);
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     *
     * If the header does not appear in the message, this method will return an
     * empty array.
     *
     * @param string $name Case-insensitive header field name.
     * @return string[] An array of string values as provided for the given
     *    header. If the header does not appear in the message, this method MUST
     *    return an empty array.
     */
    public function getHeader($name)
    {
        return $this->getHeaderCollection()->get($name, [], false);
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
        return implode(',', $this->getHeader($name));
    }

    /**
     * Return an instance with the provided value replacing the specified header.
     * This method retains the immutability of the message and returns an instance that has the
     * new and/or updated header and value.
     * @param string $name Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     * @return static
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withHeader($name, $value)
    {
        $newInstance = clone $this;
        $newInstance->setHeader($name, $value);
        return $newInstance;
    }

    /**
     * Return an instance with the specified header appended with the given value.
     *
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     *
     * This method retains the immutability of the message and returns an instance that has the
     * new header and/or value.
     *
     * @param string $name Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     * @return static
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withAddedHeader($name, $value)
    {
        $newInstance = clone $this;
        $newInstance->addHeader($name, $value);
        return $newInstance;
    }

    /**
     * Return an instance without the specified header.
     * Header resolution performed without case-sensitivity.
     * This method retains the immutability of the message and returns an instance that removes
     * the named header.
     * @param string $name Case-insensitive header field name to remove.
     * @return static
     */
    public function withoutHeader($name)
    {
        $newInstance = clone $this;
        $newInstance->getHeaderCollection()->remove($name);
        return $newInstance;
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
     * This method retains the immutability of the message and returns an instance that has the
     * new body stream.
     * @param StreamInterface $body Body.
     * @return static
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function withBody(StreamInterface $body)
    {
        if ($this->getBody() === $body) {
            return $this;
        }

        $newInstance = clone $this;
        $newInstance->setBody($body);
        return $newInstance;
    }

    /**
     * Returns default message body to be used in case it is not explicitly set.
     * @return StreamInterface default body instance.
     */
    protected function defaultBody()
    {
        return new MemoryStream();
    }

    /**
     * This method is called after the object is created by cloning an existing one.
     */
    public function __clone()
    {
        $this->cloneHttpMessageInternals();
    }

    /**
     * Ensures any internal object-type fields related to `MessageTrait` are cloned from their origins.
     * In case actual trait owner implementing method [[__clone()]], it must invoke this method within it.
     */
    private function cloneHttpMessageInternals()
    {
        if (is_object($this->_headerCollection)) {
            $this->_headerCollection = clone $this->_headerCollection;
        }
        if (is_object($this->_body)) {
            $this->_body = clone $this->_body;
        }
    }
}