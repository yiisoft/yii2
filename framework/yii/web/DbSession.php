<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\db\Connection;
use yii\db\Query;
use yii\base\InvalidConfigException;

/**
 * DbSession extends [[Session]] by using database as session data storage.
 *
 * By default, DbSession stores session data in a DB table named 'tbl_session'. This table
 * must be pre-created. The table name can be changed by setting [[sessionTable]].
 *
 * The following example shows how you can configure the application to use DbSession:
 * Add the following to your application config under `components`:
 *
 * ~~~
 * 'session' => [
 *     'class' => 'yii\web\DbSession',
 *     // 'db' => 'mydb',
 *     // 'sessionTable' => 'my_session',
 * ]
 * ~~~
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DbSession extends Session
{
	/**
	 * @var string|SessionHandlerInterface the name of class or an object implementing the session handler
	 */
	public $handler = 'yii\web\DbSessionHandler';
	/**
	 * @var Connection|string the DB connection object or the application component ID of the DB connection.
	 * After the DbSession object is created, if you want to change this property, you should only assign it
	 * with a DB connection object.
	 */
	public $db = 'db';
	/**
	 * @var string the name of the DB table that stores the session data.
	 * The table should be pre-created as follows:
	 *
	 * ~~~
	 * CREATE TABLE tbl_session
	 * (
	 *     id CHAR(40) NOT NULL PRIMARY KEY,
	 *     expire INTEGER,
	 *     data BLOB
	 * )
	 * ~~~
	 *
	 * where 'BLOB' refers to the BLOB-type of your preferred DBMS. Below are the BLOB type
	 * that can be used for some popular DBMS:
	 *
	 * - MySQL: LONGBLOB
	 * - PostgreSQL: BYTEA
	 * - MSSQL: BLOB
	 *
	 * When using DbSession in a production server, we recommend you create a DB index for the 'expire'
	 * column in the session table to improve the performance.
	 */
	public $sessionTable = 'tbl_session';

	/**
	 * Initializes the DbSession component.
	 * This method will initialize the [[db]] property to make sure it refers to a valid DB connection.
	 * @throws InvalidConfigException if [[db]] is invalid.
	 */
	public function init()
	{
		if (is_string($this->db)) {
			$this->db = Yii::$app->getComponent($this->db);
		}
		if (!$this->db instanceof Connection) {
			throw new InvalidConfigException("DbSession::db must be either a DB connection instance or the application component ID of a DB connection.");
		}
		parent::init();
	}

	/**
	 * Updates the current session ID with a newly generated one .
	 * Please refer to [[http://php.net/session_regenerate_id]] for more details.
	 * @param boolean $deleteOldSession Whether to delete the old associated session file or not.
	 */
	public function regenerateID($deleteOldSession = false)
	{
		$oldID = session_id();

		// if no session is started, there is nothing to regenerate
		if (empty($oldID)) {
			return;
		}

		parent::regenerateID(false);
		$newID = session_id();

		$query = new Query;
		$row = $query->from($this->sessionTable)
			->where(['id' => $oldID])
			->createCommand($this->db)
			->queryOne();
		if ($row !== false) {
			if ($deleteOldSession) {
				$this->db->createCommand()
					->update($this->sessionTable, ['id' => $newID], ['id' => $oldID])
					->execute();
			} else {
				$row['id'] = $newID;
				$this->db->createCommand()
					->insert($this->sessionTable, $row)
					->execute();
			}
		} else {
			// shouldn't reach here normally
			$this->db->createCommand()
				->insert($this->sessionTable, [
					'id' => $newID,
					'expire' => time() + $this->getTimeout(),
				])->execute();
		}
	}
}
