<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\log;

use Yii;
use yii\db\Connection;
use yii\base\InvalidConfigException;
use yii\di\Instance;

/**
 * DbTarget stores log messages in a database table.
 *
 * By default, DbTarget stores the log messages in a DB table named 'log'. This table
 * must be pre-created. The table name can be changed by setting the [[logTable]] property.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DbTarget extends Target
{
    /**
     * @var Connection|string the DB connection object or the application component ID of the DB connection.
     * After the DbTarget object is created, if you want to change this property, you should only assign it
     * with a DB connection object.
     */
    public $db = 'db';
    /**
     * @var string name of the DB table to store cache content.
     * The table should be pre-created as follows:
     *
     * ~~~
     * CREATE TABLE log (
     *     id       BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
     *     level    INTEGER,
     *     category VARCHAR(255),
     *     log_time INTEGER,
     *     prefix   TEXT,
     *     message  TEXT,
     *     INDEX idx_log_level (level),
     *     INDEX idx_log_category (category)
     * )
     * ~~~
     *
     * Note that the 'id' column must be created as an auto-incremental column.
     * The above SQL uses the MySQL syntax. If you are using other DBMS, you need
     * to adjust it accordingly. For example, in PostgreSQL, it should be `id SERIAL PRIMARY KEY`.
     *
     * The indexes declared above are not required. They are mainly used to improve the performance
     * of some queries about message levels and categories. Depending on your actual needs, you may
     * want to create additional indexes (e.g. index on `log_time`).
     */
    public $logTable = '{{%log}}';


    /**
     * Initializes the DbTarget component.
     * This method will initialize the [[db]] property to make sure it refers to a valid DB connection.
     * @throws InvalidConfigException if [[db]] is invalid.
     */
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::className());
    }

    /**
     * Stores log messages to DB.
     */
    public function export()
    {
        $tableName = $this->db->quoteTableName($this->logTable);
        $sql = "INSERT INTO $tableName ([[level]], [[category]], [[log_time]], [[prefix]], [[message]])
                VALUES (:level, :category, :log_time, :prefix, :message)";
        $command = $this->db->createCommand($sql);
        foreach ($this->messages as $message) {
            list($text, $level, $category, $timestamp) = $message;
            if (!is_string($text)) {
                $text = var_export($text, true);
            }
            $command->bindValues([
                ':level' => $level,
                ':category' => $category,
                ':log_time' => $timestamp,
                ':prefix' => $this->getMessagePrefix($message),
                ':message' => $text,
            ])->execute();
        }
    }
}
