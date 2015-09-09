<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
use yii\base\InvalidConfigException;
use yii\caching\DbCache;
use yii\db\Migration;

/**
 * Initializes Cache tables
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.0
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

    public function up()
    {
        $cache = $this->getCache();
        $this->db = $cache->db;

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        switch ($this->db->driverName) {
            case 'mysql':
                $blob = 'LONGBLOB';
                break;
            case 'pgsql':
                $blob = 'BYTEA';
                break;
            default:
                $blob = 'BLOB';
                break;
        }

        $this->createTable($cache->cacheTable, [
            'id' => $this->string(128)->notNull(),
            'expire' => $this->integer(),
            'data' => $blob,
            'PRIMARY KEY (id)',
            ], $tableOptions);
    }

    public function down()
    {
        $cache = $this->getCache();
        $this->db = $cache->db;

        $this->dropTable($cache->cacheTable);
    }
}
