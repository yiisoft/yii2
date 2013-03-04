<?php
/**
 * DbSession class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
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
 * DbSession uses a DB application component to perform DB operations. The ID of the DB application
 * component is specified via [[connectionID]] which defaults to 'db'.
 *
 * By default, DbSession stores session data in a DB table named 'tbl_session'. This table
 * must be pre-created. The table name can be changed by setting [[sessionTableName]].
 * The table should have the following structure:
 *
 * ~~~
 * CREATE TABLE tbl_session
 * (
 *     id CHAR(32) PRIMARY KEY,
 *     expire INTEGER,
 *     data BLOB
 * )
 * ~~~
 *
 * where 'BLOB' refers to the BLOB-type of your preferred database. Below are the BLOB type
 * that can be used for some popular databases:
 *
 * - MySQL: LONGBLOB
 * - PostgreSQL: BYTEA
 * - MSSQL: BLOB
 *
 * When using DbSession in a production server, we recommend you create a DB index for the 'expire'
 * column in the session table to improve the performance.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DbSession extends Session
{
	/**
	 * @var string the ID of a {@link CDbConnection} application component. If not set, a SQLite database
	 * will be automatically created and used. The SQLite database file is
	 * is <code>protected/runtime/session-YiiVersion.db</code>.
	 */
	public $connectionID;
	/**
	 * @var string the name of the DB table to store session content.
	 * Note, if {@link autoCreateSessionTable} is false and you want to create the DB table manually by yourself,
	 * you need to make sure the DB table is of the following structure:
	 * <pre>
	 * (id CHAR(32) PRIMARY KEY, expire INTEGER, data BLOB)
	 * </pre>
	 * @see autoCreateSessionTable
	 */
	public $sessionTableName = 'tbl_session';
	/**
	 * @var Connection the DB connection instance
	 */
	private $_db;


	/**
	 * Returns a value indicating whether to use custom session storage.
	 * This method overrides the parent implementation and always returns true.
	 * @return boolean whether to use custom storage.
	 */
	public function getUseCustomStorage()
	{
		return true;
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
		$db = $this->getDb();

		$query = new Query;
		$row = $query->from($this->sessionTableName)
			->where(array('id' => $oldID))
			->createCommand($db)
			->queryRow();
		if ($row !== false) {
			if ($deleteOldSession) {
				$db->createCommand()->update($this->sessionTableName, array(
					'id' => $newID
				), array('id' => $oldID))->execute();
			} else {
				$row['id'] = $newID;
				$db->createCommand()->insert($this->sessionTableName, $row)->execute();
			}
		} else {
			// shouldn't reach here normally
			$db->createCommand()->insert($this->sessionTableName, array(
				'id' => $newID,
				'expire' => time() + $this->getTimeout(),
			))->execute();
		}
	}
	
	/**
	 * Returns the DB connection instance used for storing session data.
	 * @return Connection the DB connection instance
	 * @throws InvalidConfigException if [[connectionID]] does not point to a valid application component.
	 */
	public function getDb()
	{
		if ($this->_db === null) {
			$db = Yii::$app->getComponent($this->connectionID);
			if ($db instanceof Connection) {
				$this->_db = $db;
			} else {
				throw new InvalidConfigException("DbSession::connectionID must refer to the ID of a DB application component.");
			}
		}
		return $this->_db;
	}

	/**
	 * Sets the DB connection used by the session component.
	 * @param Connection $value the DB connection instance
	 */
	public function setDb($value)
	{
		$this->_db = $value;
	}

	/**
	 * Session read handler.
	 * Do not call this method directly.
	 * @param string $id session ID
	 * @return string the session data
	 */
	public function readSession($id)
	{
		$query = new Query;
		$data = $query->select(array('data'))
			->from($this->sessionTableName)
			->where('expire>:expire AND id=:id', array(':expire' => time(), ':id' => $id))
			->createCommand($this->getDb())
			->queryScalar();
		return $data === false ? '' : $data;
	}

	/**
	 * Session write handler.
	 * Do not call this method directly.
	 * @param string $id session ID
	 * @param string $data session data
	 * @return boolean whether session write is successful
	 */
	public function writeSession($id, $data)
	{
		// exception must be caught in session write handler
		// http://us.php.net/manual/en/function.session-set-save-handler.php
		try {
			$expire = time() + $this->getTimeout();
			$db = $this->getDb();
			$query = new Query;
			$exists = $query->select(array('id'))
				->from($this->sessionTableName)
				->where(array('id' => $id))
				->createCommand($db)
				->queryScalar();
			if ($exists === false) {
				$db->createCommand()->insert($this->sessionTableName, array(
					'id' => $id,
					'data' => $data,
					'expire' => $expire,
				))->execute();
			} else {
				$db->createCommand()->update($this->sessionTableName, array(
					'data' => $data,
					'expire' => $expire
				), array('id' => $id))->execute();
			}
		} catch (\Exception $e) {
			if (YII_DEBUG) {
				echo $e->getMessage();
			}
			// it is too late to log an error message here
			return false;
		}
		return true;
	}

	/**
	 * Session destroy handler.
	 * Do not call this method directly.
	 * @param string $id session ID
	 * @return boolean whether session is destroyed successfully
	 */
	public function destroySession($id)
	{
		$this->getDb()->createCommand()
			->delete($this->sessionTableName, array('id' => $id))
			->execute();
		return true;
	}

	/**
	 * Session GC (garbage collection) handler.
	 * Do not call this method directly.
	 * @param integer $maxLifetime the number of seconds after which data will be seen as 'garbage' and cleaned up.
	 * @return boolean whether session is GCed successfully
	 */
	public function gcSession($maxLifetime)
	{
		$this->getDb()->createCommand()
			->delete($this->sessionTableName, 'expire<:expire', array(':expire' => time()))
			->execute();
		return true;
	}
}
