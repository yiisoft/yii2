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
use yii\db\PdoValue;
use yii\db\Query;
use yii\di\Instance;

/**
 * DbCache 是使用数据库系统实现的缓存组件。
 *
 * 默认情况下，DbCache 把会话数据存入名为 'cache' 的数据库表。
 * 该表必须提前创建好。表名可以通过设置 [[cacheTable]] 来修改。
 *
 * 可以参考 [[Cache]] 查看 DbCache 支持的通用的缓存操作方法。
 *
 * 下面的例子展示了如何配置应用来使用 DbCache 组件：
 *
 * ```php
 * 'cache' => [
 *     'class' => 'yii\caching\DbCache',
 *     // 'db' => 'mydb',
 *     // 'cacheTable' => 'my_cache',
 * ]
 * ```
 *
 * 在 Cache 上更多的详情和详细的使用信息，请参考 [guide article on caching](guide:caching-overview)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DbCache extends Cache
{
    /**
     * @var Connection|array|string DB 连接对象或者表示 DB 连接的组件 ID。
     * 如果在 DbCache 对象创建后想要再修改这个属性，
     * 那么你只能设置为 DB 连接对象。
     * 从 2.0.2 版本开始，该属性也支持配置为一个数组来创建 DB 连接对象。
     */
    public $db = 'db';
    /**
     * @var string 存储缓存内容的数据库表名。
     * 表应该像下面这样提前创建好：
     *
     * ```php
     * CREATE TABLE cache (
     *     id char(128) NOT NULL PRIMARY KEY,
     *     expire int(11),
     *     data BLOB
     * );
     * ```
     *
     * 上面的 'BLOB' 表示数据库管理系统的 BLOB 类型。
     * 下面是主流数据库管理系统中可以使用的 BLOB 类型：
     *
     * - MySQL: LONGBLOB
     * - PostgreSQL: BYTEA
     * - MSSQL: BLOB
     *
     * 当在生产环境中使用 DbCache 时，
     * 我们建议为表中的 'expire' 字段增加索引来提高性能。
     */
    public $cacheTable = '{{%cache}}';
    /**
     * @var int 当往缓存中存入一块数据时，
     * 启动垃圾回收机制（GC）的可能性（百万分之一）。默认是 100，也就是 0.01% 的概率。
     * 这个数字的范围应该是 0 到 1000000。0 表示关闭 GC 功能。
     */
    public $gcProbability = 100;


    /**
     * 初始化 DbCache 组件。
     * 该方法将会把 [[db]] 属性初始化，确保它指向一个有效的 DB 连接。
     * @throws InvalidConfigException 如果 [[db]] 连接无效。
     */
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::className());
    }

    /**
     * 检测指定的键是否存在缓存中。
     * 如果缓存数据量大的话，这比从缓存中直接获取值稍快些。
     * 注意，如果缓存数据有缓存依赖，
     * 该方法不会检测缓存依赖是否发生变化。所以有可能调用 [[get]] 方法返回 false，
     * 而调用该方法返回 true。
     * @param mixed $key 指明缓存值的键。可以是一个简单的字符串，
     * 或者是一个包含着缓存键的复杂数据结构。
     * @return bool 如果缓存值存在返回 true，如果缓存值不存在或者已经过期则返回 false。
     */
    public function exists($key)
    {
        $key = $this->buildKey($key);

        $query = new Query();
        $query->select(['COUNT(*)'])
            ->from($this->cacheTable)
            ->where('[[id]] = :id AND ([[expire]] = 0 OR [[expire]] >' . time() . ')', [':id' => $key]);
        if ($this->db->enableQueryCache) {
            // temporarily disable and re-enable query caching
            $this->db->enableQueryCache = false;
            $result = $query->createCommand($this->db)->queryScalar();
            $this->db->enableQueryCache = true;
        } else {
            $result = $query->createCommand($this->db)->queryScalar();
        }

        return $result > 0;
    }

    /**
     * 根据指定的键从缓存中获取缓存数据。
     * 该方法从父类中声明，在子类这里实现。
     * @param string $key 指明缓存数据的唯一键。
     * @return string|false 缓存中的值，如果缓存值不存在或者已经过期则返回 false。
     */
    protected function getValue($key)
    {
        $query = new Query();
        $query->select(['data'])
            ->from($this->cacheTable)
            ->where('[[id]] = :id AND ([[expire]] = 0 OR [[expire]] >' . time() . ')', [':id' => $key]);
        if ($this->db->enableQueryCache) {
            // temporarily disable and re-enable query caching
            $this->db->enableQueryCache = false;
            $result = $query->createCommand($this->db)->queryScalar();
            $this->db->enableQueryCache = true;

            return $result;
        }

        return $query->createCommand($this->db)->queryScalar();
    }

    /**
     * 根据多个缓存键从缓存中一次获取多个缓存数据。
     * @param array $keys 指明缓存数据的缓存键列表。
     * @return array 由缓存键组成下标的缓存数据列表。
     */
    protected function getValues($keys)
    {
        if (empty($keys)) {
            return [];
        }
        $query = new Query();
        $query->select(['id', 'data'])
            ->from($this->cacheTable)
            ->where(['id' => $keys])
            ->andWhere('([[expire]] = 0 OR [[expire]] > ' . time() . ')');

        if ($this->db->enableQueryCache) {
            $this->db->enableQueryCache = false;
            $rows = $query->createCommand($this->db)->queryAll();
            $this->db->enableQueryCache = true;
        } else {
            $rows = $query->createCommand($this->db)->queryAll();
        }

        $results = [];
        foreach ($keys as $key) {
            $results[$key] = false;
        }
        foreach ($rows as $row) {
            if (is_resource($row['data']) && get_resource_type($row['data']) === 'stream') {
                $results[$row['id']] = stream_get_contents($row['data']);
            } else {
                $results[$row['id']] = $row['data'];
            }
        }

        return $results;
    }

    /**
     * 根据指定的键把数据存入缓存中。
     * 该方法从父类中声明，在子类这里实现。
     *
     * @param string $key 指明缓存值的键。
     * @param string $value 要缓存的值。其它的数据类型（如果禁用了 [[serializer]] 方法），不能保存。
     * @param int $duration 缓存值过期的秒数。0 表示永不过期。
     * @return bool 如果成功存入缓存返回 true，否则返回 false。
     */
    protected function setValue($key, $value, $duration)
    {
        try {
            $this->db->noCache(function (Connection $db) use ($key, $value, $duration) {
                $db->createCommand()->upsert($this->cacheTable, [
                    'id' => $key,
                    'expire' => $duration > 0 ? $duration + time() : 0,
                    'data' => new PdoValue($value, \PDO::PARAM_LOB),
                ])->execute();
            });

            $this->gc();

            return true;
        } catch (\Exception $e) {
            Yii::warning("Unable to update or insert cache data: {$e->getMessage()}", __METHOD__);

            return false;
        }
    }

    /**
     * 在指定的键不存在的情况下，才存入指定的缓存值。
     * 该方法从父类中声明，在子类里实现。
     *
     * @param string $key 指明缓存值的键。
     * @param string $value 要缓存的值。其它的数据类型（如果禁用了 [[serializer]] 方法），不能保存。
     * @param int $duration 缓存值过期的秒数。0 表示永不过期。
     * @return bool 如果成功存入缓存返回 true，否则返回 false。
     */
    protected function addValue($key, $value, $duration)
    {
        $this->gc();

        try {
            $this->db->noCache(function (Connection $db) use ($key, $value, $duration) {
                $db->createCommand()
                    ->insert($this->cacheTable, [
                        'id' => $key,
                        'expire' => $duration > 0 ? $duration + time() : 0,
                        'data' => new PdoValue($value, \PDO::PARAM_LOB),
                    ])->execute();
            });

            return true;
        } catch (\Exception $e) {
            Yii::warning("Unable to insert cache data: {$e->getMessage()}", __METHOD__);

            return false;
        }
    }

    /**
     * 根据指定的键把数据从缓存中删除。
     * 该方法从父类中声明，在子类这里实现。
     * @param string $key 指明要删除缓存的键。
     * @return bool 如果删除过程没有发生错误。
     */
    protected function deleteValue($key)
    {
        $this->db->noCache(function (Connection $db) use ($key) {
            $db->createCommand()
                ->delete($this->cacheTable, ['id' => $key])
                ->execute();
        });

        return true;
    }

    /**
     * 删除过期的缓存数据。
     * @param bool $force 是否强制执行垃圾回收，不论 [[gcProbability]] 概率。
     * 默认是 false，意味着是否发生垃圾回收还得参考由 [[gcProbability]] 指明的可能性概率。
     */
    public function gc($force = false)
    {
        if ($force || mt_rand(0, 1000000) < $this->gcProbability) {
            $this->db->createCommand()
                ->delete($this->cacheTable, '[[expire]] > 0 AND [[expire]] < ' . time())
                ->execute();
        }
    }

    /**
     * 从缓存中删除所有值。
     * 该方法从父类中声明，在子类这里实现。
     * @return bool 是否成功执行了删除操作。
     */
    protected function flushValues()
    {
        $this->db->createCommand()
            ->delete($this->cacheTable)
            ->execute();

        return true;
    }
}
