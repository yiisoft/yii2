<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mongodb;

use yii\base\InvalidConfigException;

/**
 * Session extends [[Session]] by using database as session data storage.
 *
 * By default, Session stores session data in a DB collection named 'session'. This collection
 * must be pre-created. The collection name can be changed by setting [[sessionCollection]].
 *
 * The following example shows how you can configure the application to use Session:
 * Add the following to your application config under `components`:
 *
 * ~~~
 * 'session' => [
 *     'class' => 'yii\mongodb\Session',
 *     'db' => 'my_mongodb', // Default mongodb
 *     'sessionCollection' => 'my_session', // Defaut session
 * ]
 * ~~~
 *
 * @property boolean $useCustomStorage Whether to use custom storage. This property is read-only.
 *
 * @author Igogo <skliar.ihor@gmail.com>
 * @since 2.0
 */
class Session extends \yii\web\Session {
	/**
	 * @var Connection|string the DB connection object or the application component ID of the DB connection.
	 * After the Session object is created, if you want to change this property, you should only assign it
	 * with a DB connection object.
	 */
	public $db = 'mongodb';
	/**
	 * @var string the name of the DB collection that stores the session data.
	 * The collection should be pre-created as follows:
	 *
	 * ~~~
	 * db.createCollection( "session", [ "_id", "id", "expire", "data" ] )
	 * ~~~
	 *
	 *
	 * When using Session in a production server, we recommend you create a DB index for the 'expire'
	 * column in the session collection to improve the performance.
	 */
	public $sessionCollection = 'session';

	/**
	 * Initializes the Session component.
	 * This method will initialize the [[db]] property to make sure it refers to a valid DB connection.
	 * @throws InvalidConfigException if [[db]] is invalid.
	 */
	public function init()
	{
		if (is_string($this->db)) {
			$this->db = \Yii::$app->getComponent($this->db);
		}

		if (!$this->db instanceof Connection) {
			throw new InvalidConfigException("Session::db must be either a MongoDB connection instance or the application component ID of a MongoDB connection.");
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
	 * Updates the current session ID with a newly generated one .
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

		
		$query = new Query;
		$query->select(['data'])
			  ->from($this->sessionCollection)
			  ->where( [ 'id' => $oldID ]);

		$row = $query->one();

		if ($row !== false) {
			if ($deleteOldSession) {
				$query = new Query;
				$query->from( $this->sessionCollection )->collection->update( [ 'id' => $newID ], [ 'id' => $oldID ] );
			} else {
				$row['id'] = $newID;
				$query = new Query;
				$query->from( $this->sessionCollection )->collection->insert( $row );
			}
		} else {
			// shouldn't reach here normally
			$query = new Query;
			$query->from( $this->sessionCollection )->collection->insert( [
				'id' => $newID,
				'expire' => time() + $this->getTimeout()
			] );
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
		$query = new Query;
		$query->select(['data'])
			  ->from($this->sessionCollection)
			  ->where( [
				  'expire' => [
					  '$gt' => time()
				  ], 
				  'id' => $id
			  ]);

		$data = $query->one();
		
		return $data === false || !isset($data['data']) ? '' : $data['data'];
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
			$query = new Query;
			$query->select(['data'])
				  ->from($this->sessionCollection)
				  ->where( [ 'id' => $id ]);

			$cnt = $query->count();

			if ( !$cnt ) {
				$query = new Query;
				$query->from( $this->sessionCollection )->collection->insert( [
					'id' => $id,
					'data' => $data,
					'expire' => $expire
				] );
			} else {
				$query = new Query;
				$query->from( $this->sessionCollection )->collection->update( [
					'data' => $data,
					'expire' => $expire
				], [ 'id' => $id ] );
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
		$query = new Query;
		$query->from( $this->sessionCollection )->collection->remove( [ 'id' => $id ] );
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
		$query = new Query;
		$query->from( $this->sessionCollection )->collection->remove( [ 'expire' => [ '$gt' => time() ] ] );
		return true;
	}
}
