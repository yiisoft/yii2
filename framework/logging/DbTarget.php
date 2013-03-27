<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\logging;

use Yii;
use yii\db\Connection;
use yii\base\InvalidConfigException;

/**
 * DbTarget stores log messages in a database table.
 *
 * By default, DbTarget stores the log messages in a DB table named 'tbl_log'. This table
 * must be pre-created. The table name can be changed by setting [[logTable]].
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
	 * CREATE TABLE tbl_log (
	 *	   id       BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	 *	   level    INTEGER,
	 *	   category VARCHAR(255),
	 *	   log_time INTEGER,
	 *	   message  TEXT,
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
	public $logTable = 'tbl_log';

	/**
	 * Initializes the DbTarget component.
	 * This method will initialize the [[db]] property to make sure it refers to a valid DB connection.
	 * @throws InvalidConfigException if [[db]] is invalid.
	 */
	public function init()
	{
		parent::init();
		if (is_string($this->db)) {
			$this->db = Yii::$app->getComponent($this->db);
		}
		if (!$this->db instanceof Connection) {
			throw new InvalidConfigException("DbTarget::db must be either a DB connection instance or the application component ID of a DB connection.");
		}
	}

	/**
	 * Stores log messages to DB.
	 * @param array $messages the messages to be exported. See [[Logger::messages]] for the structure
	 * of each message.
	 */
	public function export($messages)
	{
		$tableName = $this->db->quoteTableName($this->logTable);
		$sql = "INSERT INTO $tableName (level, category, log_time, message) VALUES (:level, :category, :log_time, :message)";
		$command = $this->db->createCommand($sql);
		foreach ($messages as $message) {
			$command->bindValues(array(
				':level' => $message[1],
				':category' => $message[2],
				':log_time' => $message[3],
				':message' => $message[0],
			))->execute();
		}
	}
}
