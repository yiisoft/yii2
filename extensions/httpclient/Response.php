<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\httpclient;

use yii\base\Object;
use yii\web\HeaderCollection;

/**
 * Class Response
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Response extends Object implements DocumentInterface
{
    use DocumentTrait;

    /**
     * @inheritdoc
     */
    public function getFormat()
    {
        if ($this->_format === null) {
            $this->_format = $this->detectFormat();
        }
        return $this->_format;
    }

    /**
     * Automatically detects response format
     * @return null|string format name, 'null' - if detection failed.
     */
    protected function detectFormat()
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