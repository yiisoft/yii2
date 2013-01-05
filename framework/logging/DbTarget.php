<?php
/**
 * DbTarget class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\logging;

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
	 * @var string the ID of [[\yii\db\Connection]] application component.
	 * Defaults to 'db'. Please make sure that your database contains a table
	 * whose name is as specified in [[tableName]] and has the required table structure.
	 * @see tableName
	 */
	public $connectionID = 'db';
	/**
	 * @var string the name of the DB table that stores log messages. Defaults to '{{log}}'.
	 * If you are using table prefix 'tbl_' (configured via [[\yii\db\Connection::tablePrefix]]),
	 * it means the DB table would be named as 'tbl_log'.
	 *
	 * The DB table must have the following structure:
	 *
	 * ~~~
	 * CREATE TABLE tbl_log (
	 *	   id       INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
	 *	   level    VARCHAR(32),
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
	 * to adjust it accordingly. For example, in PosgreSQL, it should be `id SERIAL PRIMARY KEY`.
	 *
	 * The indexes declared above are not required. They are mainly used to improve the performance
	 * of some queries about message levels and categories. Depending on your actual needs, you may
	 * want to create other indexes.
	 */
	public $tableName = '{{log}}';

	private $_db;

	/**
	 * Returns the DB connection used for saving log messages.
	 * @return \yii\db\Connection the DB connection instance
	 * @throws \yii\base\Exception if [[connectionID]] does not refer to a valid application component ID.
	 */
	public function getDbConnection()
	{
		if ($this->_db === null) {
			$this->_db = \Yii::$application->getComponent($this->connectionID);
			if (!$this->_db instanceof \yii\db\Connection) {
				throw new \yii\base\Exception('DbTarget.connectionID must refer to a valid application component ID');
			}
		}
		return $this->_db;
	}

	/**
	 * Stores log [[messages]] to DB.
	 * @param boolean $final whether this method is called at the end of the current application
	 */
	public function exportMessages($final)
	{
		$sql = "INSERT INTO {$this->tableName}
			(level, category, log_time, message) VALUES
			(:level, :category, :log_time, :message)";
		$command = $this->getDbConnection()->createCommand($sql);
		foreach ($this->messages as $message) {
			$command->bindValues(array(
				':level' => $message[1],
				':category' => $message[2],
				':log_time' => $message[3],
				':message' => $message[0],
			))->execute();
		}
	}
}
