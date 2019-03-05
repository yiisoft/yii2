<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

use yii\base\InvalidConfigException;
use yii\db\QueryInterface;
use yii\di\Instance;

/**
 * DbQueryDependency 是基于一个 [[QueryInterface]] 实例的查询结果实现的依赖类。
 *
 * 如果查询结果有变化，那么就认为依赖发生了变化。
 * 查询语句由 [[query]] 属性指定。
 *
 * 任何实现了 [[QueryInterface]] 接口的对象都能用，所以这个依赖不仅可以在普通的关系数据库中使用，
 * 在 MongoDB，Redis 这些系统中也能使用。
 *
 * 在 Cache 上更多的详情和详细的使用信息，请参考 [guide article on caching](guide:caching-overview)。
 *
 * @see QueryInterface
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.12
 */
class DbQueryDependency extends Dependency
{
    /**
     * @var string|array|object 数据库连接的应用组件 ID，
     * 也可以是连接对象或者数组格式的配置。
     * 该属性也可以留空，这允许根据查询来自动决定连接对象。
     */
    public $db;
    /**
     * @var QueryInterface 实现接口的查询对象，它的结果决定依赖是否发生了变化。
     * 实际的查询方法有 [[method]] 属性决定并调用。
     */
    public $query;
    /**
     * @var string|callable 使用 [[query]] 对象作为参数调用的方法。
     *
     * 如果它是字符串，这表示一个自有的查询方法名，那么传递 [[db]] 属性作为第一个参数直接调用。
     * 比如 `exists`，`all` 方法名。
     *
     * 该属性也可以是如下签名的 PHP 回调函数：
     *
     * ```php
     * function (QueryInterface $query, mixed $db) {
     *     //return mixed;
     * }
     * ```
     *
     * 如果没有设置 - 那么会使用 [[QueryInterface::one()]] 方法。
     */
    public $method;


    /**
     * 生成在判断依赖是否发生变化时用到的依赖数据。
     *
     * 该方法返回查询的结果。
     * @param CacheInterface $cache 正在计算缓存依赖的缓存组件。
     * @return mixed 判断依赖是否发生变化时用到的依赖数据。
     * @throws InvalidConfigException 如果配置是无效的时候。
     */
    protected function generateDependencyData($cache)
    {
        $db = $this->db;
        if ($db !== null) {
            $db = Instance::ensure($db);
        }

        if (!$this->query instanceof QueryInterface) {
            throw new InvalidConfigException('"' . get_class($this) . '::$query" should be an instance of "yii\db\QueryInterface".');
        }

        if (!empty($db->enableQueryCache)) {
            // temporarily disable and re-enable query caching
            $originEnableQueryCache = $db->enableQueryCache;
            $db->enableQueryCache = false;
            $result = $this->executeQuery($this->query, $db);
            $db->enableQueryCache = $originEnableQueryCache;
        } else {
            $result = $this->executeQuery($this->query, $db);
        }

        return $result;
    }

    /**
     * 根据 [[method]] 指定的方法执行查询。
     * @param QueryInterface $query 待执行的查询对象。
     * @param mixed $db 连接。
     * @return mixed 查询结果。
     */
    private function executeQuery($query, $db)
    {
        if ($this->method === null) {
            return $query->one($db);
        }
        if (is_string($this->method)) {
            return call_user_func([$query, $this->method], $db);
        }

        return call_user_func($this->method, $query, $db);
    }
}
