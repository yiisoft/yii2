<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\Connection;
use yii\di\Instance;

/**
 * DbDependency 是基于 SQL 语句的查询结果实现的依赖类。
 *
 * 如果查询结果有变化，那么就认为依赖发生了变化。
 * 查询语句由 [[sql]] 属性指定。
 *
 * 在 Cache 上更多的详情和详细的使用信息，请参考 [guide article on caching](guide:caching-overview)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DbDependency extends Dependency
{
    /**
     * @var string 表示 DB 连接的应用组件 ID。
     */
    public $db = 'db';
    /**
     * @var string SQL 查询语句，它的查询结果决定了依赖是否发生了变化。
     * 只使用查询结果的第一行。
     */
    public $sql;
    /**
     * @var array (name => value) 格式的参数，用在 [[sql]] 属性指定的 SQL 语句中。
     */
    public $params = [];


    /**
     * 生成在判断依赖是否发生变化时用到的依赖数据。
     * 该方法返回全局状态的值。
     * @param CacheInterface $cache 正在计算缓存依赖的缓存组件。
     * @return mixed 判断依赖是否发生变化时的依赖数据。
     * @throws InvalidConfigException 如果 [[db]] 不是一个有效的应用组件 ID。
     */
    protected function generateDependencyData($cache)
    {
        /* @var $db Connection */
        $db = Instance::ensure($this->db, Connection::className());
        if ($this->sql === null) {
            throw new InvalidConfigException('DbDependency::sql must be set.');
        }

        if ($db->enableQueryCache) {
            // temporarily disable and re-enable query caching
            $db->enableQueryCache = false;
            $result = $db->createCommand($this->sql, $this->params)->queryOne();
            $db->enableQueryCache = true;
        } else {
            $result = $db->createCommand($this->sql, $this->params)->queryOne();
        }

        return $result;
    }
}
