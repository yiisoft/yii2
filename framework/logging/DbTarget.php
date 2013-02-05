<?php
/**
 * DbTarget class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\logging;

use yii\db\Connection;
use yii\base\InvalidConfigException;

/**
 * DbTarget stores log messages in a database table.
 *
 * By default, DbTarget will use the database specified by [[connectionID]] and save
 * messages into a table named by [[tableName]]. Please refer to [[tableName]] for the required
 * table structure. Note that this table must be created beforehand. Otherwise an exception
 * will be thrown when DbTarget is saving messages into DB.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DbTarget extends Target
{
	/**
	 * @var string the ID of [[Connection]] application component.
	 * Defaults to 'db'. Please make sure that your database contains a table
	 * whose name is as specified in [[tableName]] and has the required table structure.
	 * @see tableName
	 */
	public $connectionID = 'db';
	/**
	 * @var string the name of the DB table that stores log messages. Defaults to 'tbl_log'.
	 *
	 * The DB table should have the following structure:
	 *
	 * ~~~
	 * CREATE TABLE tbl_log (
	 *	   id       INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
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
	 * The above SQL shows the syntax of MySQL. If you are using other DBMS, you need
	 * to adjust it accordingly. For example, in PostgreSQL, it should be `id SERIAL PRIMARY KEY`.
	 *
	 * The indexes declared above are not required. They are mainly used to improve the performance
	 * of some queries about message levels and categories. Depending on your actual needs, you may
	 * want to create additional indexes (e.g. index on log_time).
	 */
	public $tableName = 'tbl_log';

	private $_db;

	/**
	 * Returns the DB connection used for saving log messages.
	 * @return Connection the DB connection instance
	 * @throws InvalidConfigException if [[connectionID]] does not point to a valid application component.
	 */
	public function getDb()
	{
		if ($this->_db === null) {
			$db = \Yii::$app->getComponent($this->connectionID);
			if ($db instanceof Connection) {
				$this->_db = $db;
			} else {
				throw new InvalidConfigException("DbTarget::connectionID must refer to the ID of a DB application component.");
			}
		}
		return $this->_db;
	}

	/**
	 * Sets the DB connection used by the cache component.
	 * @param Connection $value the DB connection instance
	 */
	public function setDb($value)
	{
		$this->_db = $value;
	}

	/**
	 * Stores log messages to DB.
	 * @param array $messages the messages to be exported. See [[Logger::messages]] for the structure
	 * of each message.
	 */
	public function export($messages)
	{
		$db = $this->getDb();
		$tableName = $db->quoteTableName($this->tableName);
		$sql = "INSERT INTO $tableName (level, category, log_time, message) VALUES (:level, :category, :log_time, :message)";
		$command = $db->createCommand($sql);
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
