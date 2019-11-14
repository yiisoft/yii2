<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * MemCacheServer 是一个 memcache 或者 memcached 服务器的配置选项。
 *
 * 可以参考 [PHP manual](http://php.net/manual/en/memcache.addserver.php) 
 * 查看每个属性的详细说明。
 *
 * 在 Cache 上更多的详情和详细的使用信息，请参考 [guide article on caching](guide:caching-overview)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MemCacheServer extends \yii\base\BaseObject
{
    /**
     * @var string memcache 服务器主机名或者 IP 地址。
     */
    public $host;
    /**
     * @var int memcache 服务器端口。
     */
    public $port = 11211;
    /**
     * @var int 在多个服务器中使用该服务器的可能性。
     */
    public $weight = 1;
    /**
     * @var bool 是否使用持续连接。它只在 memcache 上有效。
     */
    public $persistent = true;
    /**
     * @var int timeout 在连接服务器时的超时毫秒数。
     * 它只在 memcache 上有效。对于只支持超时时间以秒为单位的旧版本，
     * 这个值将四舍五入到整秒。
     */
    public $timeout = 1000;
    /**
     * @var int 一个连接失败的服务器每隔多长时间进行重试连接（以秒为单位）。它只在 memcache 上有效。
     */
    public $retryInterval = 15;
    /**
     * @var bool 是否在服务器故障时标记为仍然在线。它只在 memcache 上有效。
     */
    public $status = true;
    /**
     * @var \Closure 一旦遭遇错误时将会调用这个回调函数。
     * 该回调会在故障转移之前被调用。函数需要两个参数，
     * 它们是故障主机的 [[host]] 和 [[port]]。
     * 它只在 memcache 上有效。
     */
    public $failureCallback;
}
