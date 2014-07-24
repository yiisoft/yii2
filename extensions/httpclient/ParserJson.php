<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\httpclient;

use yii\base\Object;
use yii\helpers\Json;

/**
 * Class ParserJson
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class ParserJson extends Object implements ParserInterface
{
    /**
     * @inheritdoc
     */
    public function parse(DocumentInterface $httpDocument)
    {
        return Json::decode($httpDocument->getContent());
    }
}