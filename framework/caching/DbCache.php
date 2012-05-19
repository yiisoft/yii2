<?php
/**
 * CDbCache class file
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDbCache implements a cache application component by storing cached data in a database.
 *
 * CDbCache stores cache data in a DB table named {@link cacheTableName}.
 * If the table does not exist, it will be automatically created.
 * By setting {@link autoCreateCacheTable} to false, you can also manually create the DB table.
 *
 * CDbCache relies on {@link http://www.php.net/manual/en/ref.pdo.php PDO} to access database.
 * By default, it will use a SQLite3 database under the application runtime directory.
 * You can also specify {@link connectionID} so that it makes use of
 * a DB application component to access database.
 *
 * See {@link CCache} manual for common cache operations that are supported by CDbCache.
 *
 * @property integer $gCProbability The probability (parts per million) that garbage collection (GC) should be performed
 * when storing a piece of data in the cache. Defaults to 100, meaning 0.01% chance.
 * @property CDbConnection $dbConnection The DB connection instance.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.caching
 * @since 1.0
 */
class CDbCache extends CCache
{
	/**
	 * @var string the ID of the {@link CDbConnection} application component. If not set,
	 * a SQLite3 database will be automatically created and used. The SQLite database file
	 * is <code>protected/runtime/cache-YiiVersion.db</code>.
	 */
	public $connectionID;
	/**
	 * @var string name of the DB table to store cache content. Defaults to 'YiiCache'.
	 * Note, if {@link autoCreateCacheTable} is false and you want to create the DB table
	 * manually by yourself, you need to make sure the DB table is of the following structure:
	 * <pre>
	 * (id CHAR(128) PRIMARY KEY, expire INTEGER, value BLOB)
	 * </pre>
	 * Note, some DBMS might not support BLOB type. In this case, replace 'BLOB' with a suitable
	 * binary data type (e.g. LONGBLOB in MySQL, BYTEA in PostgreSQL.)
	 * @see autoCreateCacheTable
	 */
	public $cacheTableName='YiiCache';
	/**
	 * @var boolean whether the cache DB table should be created automatically if it does not exist. Defaults to true.
	 * If you already have the table created, it is recommended you set this property to be false to improve performance.
	 * @see cacheTableName
	 */
	public $autoCreateCacheTable=true;
	/**
	 * @var CDbConnection the DB connection instance
	 */
	private $_db;
	private $_gcProbability=100;
	private $_gced=false;

	/**
	 * Initializes this application component.
	 *
	 * This method is required by the {@link IApplicationComponent} interface.
	 * It ensures the existence of the cache DB table.
	 * It also removes expired data items from the cache.
	 */
	public function init()
	{
		parent::init();

		$db=$this->getDbConnection();
		$db->setActive(true);
		if($this->autoCreateCacheTable)
		{
			$sql="DELETE FROM {$this->cacheTableName} WHERE expire>0 AND expire<".time();
			try
			{
				$db->createCommand($sql)->execute();
			}
			catch(Exception $e)
			{
				$this->createCacheTable($db,$this->cacheTableName);
			}
		}
	}

	/**
	 * @return integer the probability (parts per million) that garbage collection (GC) should be performed
	 * when storing a piece of data in the cache. Defaults to 100, meaning 0.01% chance.
	 */
	public function getGCProbability()
	{
		return $this->_gcProbability;
	}

	/**
	 * @param integer $value the probability (parts per million) that garbage collection (GC) should be performed
	 * when storing a piece of data in the cache. Defaults to 100, meaning 0.01% chance.
	 * This number should be between 0 and 1000000. A value 0 meaning no GC will be performed at all.
	 */
	public function setGCProbability($value)
	{
		$value=(int)$value;
		if($value<0)
			$value=0;
		if($value>1000000)
			$value=1000000;
		$this->_gcProbability=$value;
	}

	/**
	 * Creates the cache DB table.
	 * @param CDbConnection $db the database connection
	 * @param string $tableName the name of the table to be created
	 */
	protected function createCacheTable($db,$tableName)
	{
		$driver=$db->getDriverName();
		if($driver==='mysql')
			$blob='LONGBLOB';
		else if($driver==='pgsql')
			$blob='BYTEA';
		else
			$blob='BLOB';
		$sql=<<<EOD
CREATE TABLE $tableName
(
	id CHAR(128) PRIMARY KEY,
	expire INTEGER,
	value $blob
)
EOD;
		$db->createCommand($sql)->execute();
	}

	/**
	 * @return CDbConnection the DB connection instance
	 * @throws CException if {@link connectionID} does not point to a valid application component.
	 */
	public function getDbConnection()
	{
		if($this->_db!==null)
			return $this->_db;
		else if(($id=$this->connectionID)!==null)
		{
			if(($this->_db=Yii::app()->getComponent($id)) instanceof CDbConnection)
				return $this->_db;
			else
				throw new CException(Yii::t('yii','CDbCache.connectionID "{id}" is invalid. Please make sure it refers to the ID of a CDbConnection application component.',
					array('{id}'=>$id)));
		}
		else
		{
			$dbFile=Yii::app()->getRuntimePath().DIRECTORY_SEPARATOR.'cache-'.Yii::getVersion().'.db';
			return $this->_db=new CDbConnection('sqlite:'.$dbFile);
		}
	}

	/**
	 * Sets the DB connection used by the cache component.
	 * @param CDbConnection $value the DB connection instance
	 * @since 1.1.5
	 */
	public function setDbConnection($value)
	{
		$this->_db=$value;
	}

	/**
	 * Retrieves a value from cache with a specified key.
	 * This is the implementation of the method declared in the parent class.
	 * @param string $key a unique key identifying the cached value
	 * @return string the value stored in cache, false if the value is not in the cache or expired.
	 */
	protected function getValue($key)
	{
		$time=time();
		$sql="SELECT value FROM {$this->cacheTableName} WHERE id='$key' AND (expire=0 OR expire>$time)";
		$db=$this->getDbConnection();
		if($db->queryCachingDuration>0)
		{
			$duration=$db->queryCachingDuration;
			$db->queryCachingDuration=0;
			$result=$db->createCommand($sql)->queryScalar();
			$db->queryCachingDuration=$duration;
			return $result;
		}
		else
			return $db->createCommand($sql)->queryScalar();
	}

	/**
	 * Retrieves multiple values from cache with the specified keys.
	 * @param array $keys a list of keys identifying the cached values
	 * @return array a list of cached values indexed by the keys
	 */
	protected function getValues($keys)
	{
		if(empty($keys))
			return array();

		$ids=implode("','",$keys);
		$time=time();
		$sql="SELECT id, value FROM {$this->cacheTableName} WHERE id IN ('$ids') AND (expire=0 OR expire>$time)";

		$db=$this->getDbConnection();
		if($db->queryCachingDuration>0)
		{
			$duration=$db->queryCachingDuration;
			$db->queryCachingDuration=0;
			$rows=$db->createCommand($sql)->queryAll();
			$db->queryCachingDuration=$duration;
		}
		else
			$rows=$db->createCommand($sql)->queryAll();

		$results=array();
		foreach($keys as $key)
			$results[$key]=false;
		foreach($rows as $row)
			$results[$row['id']]=$row['value'];
		return $results;
	}

	/**
	 * Stores a value identified by a key in cache.
	 * This is the implementation of the method declared in the parent class.
	 *
	 * @param string $key the key identifying the value to be cached
	 * @param string $value the value to be cached
	 * @param integer $expire the number of seconds in which the cached value will expire. 0 means never expire.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	protected function setValue($key,$value,$expire)
	{
		$this->deleteValue($key);
		return $this->addValue($key,$value,$expire);
	}

	/**
	 * Stores a value identified by a key into cache if the cache does not contain this key.
	 * This is the implementation of the method declared in the parent class.
	 *
	 * @param string $key the key identifying the value to be cached
	 * @param string $value the value to be cached
	 * @param integer $expire the number of seconds in which the cached value will expire. 0 means never expire.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	protected function addValue($key,$value,$expire)
	{
		if(!$this->_gced && mt_rand(0,1000000)<$this->_gcProbability)
		{
			$this->gc();
			$this->_gced=true;
		}

		if($expire>0)
			$expire+=time();
		else
			$expire=0;
		$sql="INSERT INTO {$this->cacheTableName} (id,expire,value) VALUES ('$key',$expire,:value)";
		try
		{
			$command=$this->getDbConnection()->createCommand($sql);
			$command->bindValue(':value',$value,PDO::PARAM_LOB);
			$command->execute();
			return true;
		}
		catch(Exception $e)
		{
			return false;
		}
	}

	/**
	 * Deletes a value with the specified key from cache
	 * This is the implementation of the method declared in the parent class.
	 * @param string $key the key of the value to be deleted
	 * @return boolean if no error happens during deletion
	 */
	protected function deleteValue($key)
	{
		$sql="DELETE FROM {$this->cacheTableName} WHERE id='$key'";
		$this->getDbConnection()->createCommand($sql)->execute();
		return true;
	}

	/**
	 * Removes the expired data values.
	 */
	protected function gc()
	{
		$this->getDbConnection()->createCommand("DELETE FROM {$this->cacheTableName} WHERE expire>0 AND expire<".time())->execute();
	}

	/**
	 * Deletes all values from cache.
	 * This is the implementation of the method declared in the parent class.
	 * @return boolean whether the flush operation was successful.
	 * @since 1.1.5
	 */
	protected function flushValues()
	{
		$this->getDbConnection()->createCommand("DELETE FROM {$this->cacheTableName}")->execute();
		return true;
	}
}
