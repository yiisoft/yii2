<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\httpclient;

use yii\base\Object;

/**
 * Class ParserUrlEncoded
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class ParserUrlEncoded extends Object implements ParserInterface
{
    /**
     * @inheritdoc
     */
    public function parse(DocumentInterface $httpDocument)
    {
        $data = [];
        parse_str($httpDocument->getContent(), $data);
        return $data;
    }
} 