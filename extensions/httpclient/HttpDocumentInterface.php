<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\httpclient;

use yii\web\HeaderCollection;

/**
 * HttpDocumentInterface represents HTTP document.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
interface HttpDocumentInterface
{
    /**
     * Sets the HTTP headers associated with HTTP document.
     * @param array|HeaderCollection $headers headers collection or headers list in format: [headerName => headerValue]
     * @return static self reference.
     */
    public function setHeaders($headers);

    /**
     * Returns the header collection.
     * The header collection contains the HTTP headers associated with HTTP document.
     * @return HeaderCollection the header collection
     */
    public function getHeaders();

    /**
     * Adds HTTP headers to the headers collection.
     * @param array $headers headers list in format: [headerName => headerValue]
     * @return static self reference.
     */
    public function addHeaders(array $headers);

    /**
     * Sets the HTTP document raw body.
     * @param string $body raw body.
     * @return static self reference.
     */
    public function setBody($body);

    /**
     * Returns HTTP document raw body.
     * @return string raw body.
     */
    public function getBody();

    /**
     * Sets the fields, which composes document body.
     * @param array $fields body fields.
     * @return static self reference.
     */
    public function setBodyFields(array $fields);

    /**
     * Returns the fields, parsed from raw body.
     * @return array body fields.
     */
    public function getBodyFields();

    /**
     * Sets body format.
     * @param string $format body format name.
     * @return static self reference.
     */
    public function setFormat($format);

    /**
     * Returns body format.
     * @return string body format name.
     */
    public function getFormat();

    /**
     * Returns string representation of this HTTP document.
     * @return string the string representation of this HTTP document.
     */
    public function toString();
}