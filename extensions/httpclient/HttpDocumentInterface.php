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
    const FORMAT_JSON = 'json'; // JSON format
    const FORMAT_URLENCODED = 'urlencoded'; // urlencoded query string, like name1=value1&name2=value2
    const FORMAT_XML = 'xml'; // XML format

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
     * Sets the HTTP document raw content.
     * @param string $content raw content.
     * @return static self reference.
     */
    public function setContent($content);

    /**
     * Returns HTTP document raw content.
     * @return string raw body.
     */
    public function getContent();

    /**
     * Sets the data fields, which composes document content.
     * @param array $data content data fields.
     * @return static self reference.
     */
    public function setData(array $data);

    /**
     * Returns the data fields, parsed from raw content.
     * @return array content data fields.
     */
    public function getData();

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