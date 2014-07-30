<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\httpclient;

use yii\base\Exception;
use yii\base\Object;
use yii\web\HeaderCollection;

/**
 * Response represents HTTP request response.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Response extends Object implements DocumentInterface
{
    use DocumentTrait;

    /**
     * Returns status code.
     * @throws Exception on failure.
     * @return string status code.
     */
    public function getStatusCode()
    {
        $headers = $this->getHeaders();
        if ($headers->has('http-code')) {
            return $headers->get('http-code');
        } elseif ($headers->has('http_code')) {
            return $headers->get('http_code');
        }
        throw new Exception('Unable to get status code: referred header information is missing.');
    }

    /**
     * Checks if response status code is OK (200)
     * @return boolean whether response is OK.
     */
    public function isOk()
    {
        return strcmp('200', $this->getStatusCode()) === 0;
    }

    /**
     * Returns default format automatically detected from headers and content.
     * @return null|string format name, 'null' - if detection failed.
     */
    protected function defaultFormat()
    {
        $format = $this->detectFormatByHeaders($this->getHeaders());
        if ($format === null) {
            $format = $this->detectFormatByContent($this->getContent());
        }
        return $format;
    }

    /**
     * Detects format from headers.
     * @param HeaderCollection $headers source headers.
     * @return null|string format name, 'null' - if detection failed.
     */
    protected function detectFormatByHeaders(HeaderCollection $headers)
    {
        $contentType = $headers->get('content-type');
        if ($contentType === null) {
            $contentType = $headers->get('content_type');
        }

        if (!empty($contentType)) {
            if (stripos($contentType, 'json') !== false) {
                return self::FORMAT_JSON;
            }
            if (stripos($contentType, 'urlencoded') !== false) {
                return self::FORMAT_URLENCODED;
            }
            if (stripos($contentType, 'xml') !== false) {
                return self::FORMAT_XML;
            }
        }
        return null;
    }

    /**
     * Detects response format from raw content.
     * @param string $content raw response content.
     * @return null|string format name, 'null' - if detection failed.
     */
    protected function detectFormatByContent($content)
    {
        if (preg_match('/^\\{.*\\}$/is', $content)) {
            return self::FORMAT_JSON;
        }
        if (preg_match('/^[^=|^&]+=[^=|^&]+(&[^=|^&]+=[^=|^&]+)*$/is', $content)) {
            return self::FORMAT_URLENCODED;
        }
        if (preg_match('/^<.*>$/is', $content)) {
            return self::FORMAT_XML;
        }
        return null;
    }
}