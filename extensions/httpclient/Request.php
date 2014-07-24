<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\httpclient;

use yii\base\Object;

/**
 * Class Request
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Request extends Object implements DocumentInterface
{
    use DocumentTrait;

    /**
     * @var string request method.
     */
    public $method = 'get';

    public function send()
    {
        ;
    }
}