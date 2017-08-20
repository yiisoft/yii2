<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\http;

use Psr\Http\Message\StreamInterface;
use yii\base\Object;

/**
 * ResourceStream
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
class ResourceStream extends Object implements StreamInterface
{
    /**
     * @var resource
     */
    public $resource;
}