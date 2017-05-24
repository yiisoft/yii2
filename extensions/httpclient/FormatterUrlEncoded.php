<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\httpclient;

use yii\base\Object;

/**
 * Class FormatterUrlEncoded
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class FormatterUrlEncoded extends Object implements FormatterInterface
{
    /**
     * @var integer URL encoding type.
     * possible values:
     *  - PHP_QUERY_RFC1738
     *  - PHP_QUERY_RFC3986
     */
    public $encodingType = PHP_QUERY_RFC3986;

    /**
     * @inheritdoc
     */
    public function format(DocumentInterface $httpDocument)
    {
        $httpDocument->getHeaders()->set('Content-Type', 'application/x-www-form-urlencoded');
        $data = $httpDocument->getData();
        return http_build_query($data, '', '&', $this->encodingType);
    }
}