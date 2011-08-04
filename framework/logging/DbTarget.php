<?php
/**
 * CDbLogRoute class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


/**
 * CDbLogRoute stores log messages in a database table.
 *
 * To specify the database table for storing log messages, set {@link logTableName} as
 * the name of the table and specify {@link connectionID} to be the ID of a {@link CDbConnection}
 * application component. If they are not set, a SQLite3 database named 'log-YiiVersion.db' will be created
 * and used under the application runtime directory.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CDbLogRoute.php 3069 2011-03-14 00:28:38Z qiang.xue $
 * @package system.logging
 * @since 1.0
 */
class CDbLogRoute extends CLogRoute
{
	/**
	 * @var string the ID of CDbConnection application component. If not set, a SQLite database
	 * will be automatically created and used. The SQLite database file is
	 * <code>protected/runtime/log-YiiVersion.db</code>.
	 */
	public $connectionID;
	/**
	 * @var string the name of the DB table that stores log content. Defaults to 'YiiLog'.
	 * If {@link autoCreateLogTable} is false and you want to create the DB table manually by yourself,
	 * you need to make sure the DB table is of the following structure:
	 * <pre>
	 *  (
	 *		id       INTEGER NOT NULL PRIMARY KEY,
	 *		level    VARCHAR(128),
	 *		category VARCHAR(128),
	 *		logtime  INTEGER,
	 *		message  TEXT
	 *   )
	 * </pre>
	 * Note, the 'id' column must be created as an auto-incremental column.
	 * In MySQL, this means it should be <code>id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY</code>;
	 * In PostgreSQL, it is <code>id SERIAL PRIMARY KEY</code>.
	 * @see autoCreateLogTable
	 */
	public $logTableName = 'YiiLog';
	/**
	 * @var boolean whether the log DB table should be automatically created if not exists. Defaults to true.
	 * @see logTableName
	 */
	public $autoCreateLogTable = true;
	/**
	 * @var CDbConnection the DB connection instance
	 */
	private $_db;

	/**
	 * Initializes the route.
	 * This method is invoked after the route is created by the route manager.
	 */
	public function init()
	{
		parent::init();

		if ($this->autoCreateLogTable)
		{
			$db = $this->getDbConnection();
			$sql = "DELETE FROM  {$this->logTableName} WHERE 0=1";
			try
			{
				$db->createCommand($sql)->execute();
			}
			catch(Exception $e)
			{
				$this->createLogTable($db, $this->logTableName);
			}
		}
	}

	/**
	 * Creates the DB table for storing log messages.
	 * @param CDbConnection $db the database connection
	 * @param string $tableName the name of the table to be created
	 */
	protected function createLogTable($db, $tableName)
	{
		$driver = $db->getDriverName();
		if ($driver === 'mysql')
			$logID = 'id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY';
		elseif ($driver === 'pgsql')
			$logID = 'id SERIAL PRIMARY KEY';
		else
			$logID = 'id INTEGER NOT NULL PRIMARY KEY';

		$sql = "
CREATE TABLE $tableName
(
	$logID,
	level VARCHAR(128),
	category VARCHAR(128),
	logtime INTEGER,
	message TEXT
)";
		$db->createCommand($sql)->execute();
	}

	/**
	 * @return CDbConnection the DB connection instance
	 * @throws CException if {@link connectionID} does not point to a valid application component.
	 */
	protected function getDbConnection()
	{
		if ($this->_db !== null)
			return $this->_db;
		elseif (($id = $this->connectionID) !== null)
		{
			if (($this->_db = Yii::app()->getComponent($id)) instanceof CDbConnection)
				return $this->_db;
			else
				throw new CException(Yii::t('yii', 'CDbLogRoute.connectionID "{id}" does not point to a valid CDbConnection application component.',
					array('{id}' => $id)));
		}
		else
		{
			$dbFile = Yii::app()->getRuntimePath() . DIRECTORY_SEPARATOR . 'log-' . Yii::getVersion() . '.db';
			return $this->_db = new CDbConnection('sqlite:' . $dbFile);
		}
	}

	/**
	 * Stores log messages into database.
	 * @param array $logs list of log messages
	 */
	protected function processLogs($logs)
	{
		$sql = "
INSERT INTO  {$this->logTableName}
(level, category, logtime, message) VALUES
(:level, :category, :logtime, :message)
";
		$command = $this->getDbConnection()->createCommand($sql);
		foreach ($logs as $log)
		{
			$command->bindValue(':level', $log[1]);
			$command->bindValue(':category', $log[2]);
			$command->bindValue(':logtime', (int)$log[3]);
			$command->bindValue(':message', $log[0]);
			$command->execute();
		}
	}
}
