<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\log;

use yii\base\InvalidConfigException;
use yii\db\Connection;
use yii\db\Exception;
use yii\di\Instance;

use function array_chunk;
use function count;

/**
 * DbTarget stores log messages in a database table.
 *
 * The database connection is specified by [[db]]. Database schema could be initialized by applying migration:
 *
 * ```
 * yii migrate --migrationPath=@yii/log/migrations/
 * ```
 *
 * If you don't want to use migration and need SQL instead, files for all databases are in migrations directory.
 *
 * You may change the name of the table used to store the data by setting [[logTable]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DbTarget extends Target
{
    /**
     * The maximum number of log messages inserted per multi-row INSERT statement.
     *
     * SQL Server rejects `INSERT ... VALUES` statements with more than 1000 rows and very large statements may exceed
     * `max_allowed_packet` on MySQL, so messages are exported in chunks.
     */
    private const int EXPORT_CHUNK_SIZE = 100;

    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     * After the DbTarget object is created, if you want to change this property, you should only assign it
     * with a DB connection object.
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     */
    public $db = 'db';
    /**
     * @var string name of the DB table to store cache content. Defaults to "log".
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
        $this->db = Instance::ensure($this->db, Connection::class);
    }

    /**
     * Stores log messages to DB.
     * Starting from version 2.0.14, this method throws LogRuntimeException in case the log can not be exported.
     * Starting from version 22.0, messages are inserted with chunked multi-row INSERT statements, except for
     * Oracle where rows are inserted individually with bound parameters.
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

        if ($this->db->driverName === 'oci') {
            $this->exportByRow();

            return;
        }

        $rows = [];

        foreach ($this->messages as $message) {
            $rows[] = [
                $message[1],
                $message[2],
                $message[3],
                $this->getMessagePrefix($message),
                $this->formatMessageText($message[0]),
            ];
        }

        $inserted = 0;

        $chunks = array_chunk($rows, self::EXPORT_CHUNK_SIZE);

        foreach ($chunks as $chunk) {
            $inserted += $this->db->createCommand()
                ->batchInsert($this->logTable, ['level', 'category', 'log_time', 'prefix', 'message'], $chunk)
                ->execute();
        }

        if ($inserted < count($rows)) {
            throw new LogRuntimeException(
                'Unable to export log through database!',
            );
        }
    }

    /**
     * Stores log messages to DB one row at a time using bound parameters.
     *
     * Oracle limits inlined SQL string literals to 4000 bytes, so the multi-row INSERT statements built by
     * [[\yii\db\QueryBuilder::batchInsert()]] cannot carry long log messages there.
     *
     * @throws Exception
     * @throws LogRuntimeException
     */
    private function exportByRow(): void
    {
        $tableName = $this->db->quoteTableName($this->logTable);

        // bind variable names must avoid Oracle reserved words such as LEVEL (ORA-01745)
        $sql = <<<SQL
        INSERT INTO {$tableName} ([[level]], [[category]], [[log_time]], [[prefix]], [[message]]) VALUES (:log_level, :log_category, :log_time, :log_prefix, :log_message)
        SQL;

        $command = $this->db->createCommand($sql);

        foreach ($this->messages as $message) {
            [$text, $level, $category, $timestamp] = $message;

            if (
                $command->bindValues(
                    [
                        ':log_level' => $level,
                        ':log_category' => $category,
                        ':log_time' => $timestamp,
                        ':log_prefix' => $this->getMessagePrefix($message),
                        ':log_message' => $this->formatMessageText($text),
                    ],
                )->execute() > 0
            ) {
                continue;
            }

            throw new LogRuntimeException(
                'Unable to export log through database!',
            );
        }
    }
}
