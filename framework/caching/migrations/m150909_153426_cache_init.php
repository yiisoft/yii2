<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

use yii\base\InvalidConfigException;
use yii\caching\DbCache;
use yii\db\Migration;

/**
 * Initializes Cache tables.
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.0.7
 */
class m150909_153426_cache_init extends Migration
{
    /**
     * @throws yii\base\InvalidConfigException
     * @return DbCache
     */
    protected function getCache()
    {
        $cache = Yii::$app->getCache();
        if (!$cache instanceof DbCache) {
            throw new InvalidConfigException('You should configure "cache" component to use database before executing this migration.');
        }

        return $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $cache = $this->getCache();
        $this->db = $cache->db;

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = sprintf('CHARACTER SET %s ENGINE=InnoDB', $this->db->effectiveCharset);
        }

        $this->createTable($cache->cacheTable, [
            'id' => $this->string(128)->notNull(),
            'expire' => $this->integer(),
            'data' => $this->binary(),
            'PRIMARY KEY ([[id]])',
            ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $cache = $this->getCache();
        $this->db = $cache->db;

        $this->dropTable($cache->cacheTable);
    }
}
