<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\log;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\Connection;
use yii\db\Exception;
use yii\di\Instance;
use yii\helpers\VarDumper;

/**
 * DbTarget 将日志消息存储在数据库中。
 *
 * 数据库连接由 [[db] 指定。可以通过应用迁移来初始化数据库模式：
 *
 * ```
 * yii migrate --migrationPath=@yii/log/migrations/
 * ```
 *
 * 如果您不想使用迁移并且需要 SQL，则所有数据库的文件都位于迁移目录中。
 *
 * 您可以通过设置 [[logTable]] 来更改用于存储数据的表的名称。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DbTarget extends Target
{
    /**
     * @var Connection|array|string 数据库连接对象或数据库连接的应用程序组件 ID
     * 创建 DbTarget
     * 对象后，只有当它是数据库连接对象时，才可以更改属性。
     * 从 2.0.2 版开始，这也可以是用于创建对象的配置数组。
     */
    public $db = 'db';
    /**
     * @var string 用于存储缓存内容的表的名称。默认为“log”。
     */
    public $logTable = '{{%log}}';


    /**
     * 初始化 DbTarget 组件。
     * 此方法将初始化 [[db]] 属性以确保它引用有效的数据库连接。
     * @throws InvalidConfigException 如果 [[db]] 无效。
     */
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::className());
    }

    /**
     * 将日志消息存储到数据库。
     * 从版本 2.0.14 开始，如果无法导出日志，此方法将抛出 LogRuntimeException。
     * @throws Exception
     * @throws LogRuntimeException
     */
    public function export()
    {
        if ($this->db->getTransaction()) {
            // create new database connection, if there is an open transaction
            // to ensure insert statement is not affected by a rollback
            $this->db = clone $this->db;
        }

        $tableName = $this->db->quoteTableName($this->logTable);
        $sql = "INSERT INTO $tableName ([[level]], [[category]], [[log_time]], [[prefix]], [[message]])
                VALUES (:level, :category, :log_time, :prefix, :message)";
        $command = $this->db->createCommand($sql);
        foreach ($this->messages as $message) {
            list($text, $level, $category, $timestamp) = $message;
            if (!is_string($text)) {
                // exceptions may not be serializable if in the call stack somewhere is a Closure
                if ($text instanceof \Throwable || $text instanceof \Exception) {
                    $text = (string) $text;
                } else {
                    $text = VarDumper::export($text);
                }
            }
            if ($command->bindValues([
                    ':level' => $level,
                    ':category' => $category,
                    ':log_time' => $timestamp,
                    ':prefix' => $this->getMessagePrefix($message),
                    ':message' => $text,
                ])->execute() > 0) {
                continue;
            }
            throw new LogRuntimeException('Unable to export log through database!');
        }
    }
}
