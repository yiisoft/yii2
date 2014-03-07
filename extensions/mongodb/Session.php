<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mongodb;

use Yii;
use yii\base\InvalidConfigException;

/**
 * Session extends [[\yii\web\Session]] by using MongoDB as session data storage.
 *
 * By default, Session stores session data in a collection named 'session' inside the default database.
 * This collection is better to be pre-created with fields 'id' and 'expire' indexed.
 * The collection name can be changed by setting [[sessionCollection]].
 *
 * The following example shows how you can configure the application to use Session:
 * Add the following to your application config under `components`:
 *
 * ~~~
 * 'session' => [
 *     'class' => 'yii\mongodb\Session',
 *     // 'db' => 'mymongodb',
 *     // 'sessionCollection' => 'my_session',
 * ]
 * ~~~
 *
 * @property boolean $useCustomStorage Whether to use custom storage. This property is read-only.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Session extends \yii\web\Session
{
	/**
	 * @var Connection|string the MongoDB connection object or the application component ID of the MongoDB connection.
	 * After the Session object is created, if you want to change this property, you should only assign it
	 * with a MongoDB connection object.
	 */
	public $db = 'mongodb';
	/**
	 * @var string|array the name of the MongoDB collection that stores the session data.
	 * Please refer to [[Connection::getCollection()]] on how to specify this parameter.
	 * This collection is better to be pre-created with fields 'id' and 'expire' indexed.
	 */
	public $sessionCollection = 'session';

	/**
	 * Initializes the Session component.
	 * This method will initialize the [[db]] property to make sure it refers to a valid MongoDB connection.
	 * @throws InvalidConfigException if [[db]] is invalid.
	 */
	public function init()
	{
		if (is_string($this->db)) {
			$this->db = Yii::$app->getComponent($this->db);
		}
		if (!$this->db instanceof Connection) {
			throw new InvalidConfigException($this->className() . "::db must be either a MongoDB connection instance or the application component ID of a MongoDB connection.");
		}
		parent::init();
	}

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
	 * Updates the current session ID with a newly generated one.
	 * Please refer to <http://php.net/session_regenerate_id> for more details.
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

		$collection = $this->db->getCollection($this->sessionCollection);
		$row = $collection->findOne(['id' => $oldID]);
		if ($row !== null) {
			if ($deleteOldSession) {
				$collection->update(['id' => $oldID], ['id' => $newID]);
			} else {
				unset($row['_id']);
				$row['id'] = $newID;
				$collection->insert($row);
			}
		} else {
			// shouldn't reach here normally
			$collection->insert([
				'id' => $newID,
				'expire' => time() + $this->getTimeout()
			]);
		}
	}

	/**
	 * Session read handler.
	 * Do not call this method directly.
	 * @param string $id session ID
	 * @return string the session data
	 */
	public function readSession($id)
	{
		$collection = $this->db->getCollection($this->sessionCollection);
		$doc = $collection->findOne(
			[
				'id' => $id,
				'expire' => ['$gt' => time()],
			],
			['data' => 1, '_id' => 0]
		);
		return isset($doc['data']) ? $doc['data'] : '';
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
			$this->db->getCollection($this->sessionCollection)->update(
				['id' => $id],
				[
					'id' => $id,
					'data' => $data,
					'expire' => time() + $this->getTimeout(),
				],
				['upsert' => true]
			);
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
		$this->db->getCollection($this->sessionCollection)->remove(
			['id' => $id],
			['justOne' => true]
		);
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
		$this->db->getCollection($this->sessionCollection)
			->remove(['expire' => ['$lt' => time()]]);
		return true;
	}
}
