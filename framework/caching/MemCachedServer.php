<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * MemCachedServer represents the configuration data for a single memcached server.
 *
 * See [PHP manual](http://php.net/manual/en/memcached.addserver.php) for detailed explanation
 * of each configuration property.
 *
 * For more details and usage information on Cache, see the [guide article on caching](guide:caching-overview).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MemCachedServer extends \yii\base\BaseObject
{
    /**
     * @var string memcached server hostname or IP address
     */
    public $host;
    /**
     * @var int memcached server port
     */
    public $port = 11211;
    /**
     * @var int probability of using this server among all servers.
     */
    public $weight = 1;
}
