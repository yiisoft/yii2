<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\httpclient;
use yii\base\ErrorHandler;
use yii\web\HeaderCollection;

/**
 * HttpDocumentTrait satisfies [[HttpDocumentInterface]].
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
trait HttpDocumentTrait
{
    /**
     * @var HeaderCollection
     */
    private $_headers;
    /**
     * @var string|null
     */
    private $_body;
    /**
     * @var array
     */
    private $_bodyFields;
    /**
     * @var string
     */
    private $_format;

    /**
     * Sets the HTTP headers associated with HTTP document.
     * @param array|HeaderCollection $headers headers collection or headers list in format: [headerName => headerValue]
     * @return static self reference.
     */
    public function setHeaders($headers)
    {
        $this->_headers = $headers;
        return $this;
    }

    /**
     * Returns the header collection.
     * The header collection contains the HTTP headers associated with HTTP document.
     * @return HeaderCollection the header collection
     */
    public function getHeaders()
    {
        if (!is_object($this->_headers)) {
            $headerCollection = new HeaderCollection();
            if (is_array($this->_headers)) {
                foreach ($this->_headers as $name => $value) {
                    $headerCollection->add($name, $value);
                }
            }
            $this->_headers = $headerCollection;
        }
        return $this->_headers;
    }

    /**
     * Adds HTTP headers to the headers collection.
     * @param array $headers headers list in format: [headerName => headerValue]
     * @return static self reference.
     */
    public function addHeaders(array $headers)
    {
        $headerCollection = $this->getHeaders();
        foreach ($headers as $name => $value) {
            $headerCollection->add($name, $value);
        }
        return $this;
    }

    /**
     * Sets the HTTP document raw body.
     * @param string $body raw body.
     * @return static self reference.
     */
    public function setBody($body)
    {
        $this->_body = $body;
        return $this;
    }

    /**
     * Returns HTTP document raw body.
     * @return string raw body.
     */
    public function getBody()
    {
        if ($this->_body === null && !empty($this->_bodyFields)) {
            ;
        }
        return $this->_body;
    }

    /**
     * Sets the fields, which composes document body.
     * @param array $fields body fields.
     * @return static self reference.
     */
    public function setBodyFields(array $fields)
    {
        $this->_bodyFields = $fields;
        return $this;
    }

    /**
     * Returns the fields, parsed from raw body.
     * @return array body fields.
     */
    public function getBodyFields()
    {
        if (!is_array($this->_bodyFields)) {
            ;
        }
        return $this->_bodyFields;
    }

    /**
     * Sets body format.
     * @param string $format body format name.
     * @return static self reference.
     */
    public function setFormat($format)
    {
        $this->_format = $format;
        return $this;
    }

    /**
     * Returns body format.
     * @return string body format name.
     */
    public function getFormat()
    {
        return $this->_format;
    }

    /**
     * Returns string representation of this HTTP document.
     * @return string the string representation of this HTTP document.
     */
    public function toString()
    {
        $headerParts = [];
        foreach ($this->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $headerParts[] = "$name : $value";
            }
        }
        return implode("\n", $headerParts) . "\n\n" . $this->getBody();
    }

    /**
     * PHP magic method that returns the string representation of this object.
     * @return string the string representation of this object.
     */
    public function __toString()
    {
        // __toString cannot throw exception
        // use trigger_error to bypass this limitation
        try {
            return $this->toString();
        } catch (\Exception $e) {
            ErrorHandler::convertExceptionToError($e);
            return '';
        }
    }
}