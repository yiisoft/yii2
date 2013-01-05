<?php
/**
 * DbDependency class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

use yii\base\Exception;
use yii\db\Connection;
use yii\db\Query;

/**
 * DbDependency represents a dependency based on the query result of a SQL statement.
 *
 * If the query result changes, the dependency is considered as changed.
 * The query is specified via the [[query]] property.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DbDependency extends Dependency
{
	/**
	 * @var string the ID of the [[Connection|DB connection]] application component. Defaults to 'db'.
	 */
	public $connectionID = 'db';
	/**
	 * @var Query the SQL query whose result is used to determine if the dependency has been changed.
	 * Only the first row of the query result will be used.
	 */
	public $query;
	/**
	 * @var Connection the DB connection instance
	 */
	private $_db;

	/**
	 * Constructor.
	 * @param Query $query the SQL query whose result is used to determine if the dependency has been changed.
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 */
	public function __construct($query = null, $config = array())
	{
		$this->query = $query;
		parent::__construct($config);
	}

	/**
	 * PHP sleep magic method.
	 * This method ensures that the database instance is set null because it contains resource handles.
	 * @return array
	 */
	public function __sleep()
	{
		$this->_db = null;
		return array_keys((array)$this);
	}

	/**
	 * Generates the data needed to determine if dependency has been changed.
	 * This method returns the value of the global state.
	 * @return mixed the data needed to determine if dependency has been changed.
	 */
	protected function generateDependencyData()
	{
		$db = $this->getDbConnection();
		$command = $this->query->createCommand($db);
		if ($db->enableQueryCache) {
			// temporarily disable and re-enable query caching
			$db->enableQueryCache = false;
			$result = $command->queryRow();
			$db->enableQueryCache = true;
		} else {
			$result = $command->queryRow();
		}
		return $result;
	}

	/**
	 * Returns the DB connection instance used for caching purpose.
	 * @return Connection the DB connection instance
	 * @throws Exception if [[connectionID]] does not point to a valid application component.
	 */
	public function getDbConnection()
	{
		if ($this->_db === null) {
			$db = \Yii::$application->getComponent($this->connectionID);
			if ($db instanceof Connection) {
				$this->_db = $db;
			} else {
				throw new Exception("DbDependency.connectionID must refer to the ID of a DB connection application component.");
			}
		}
		return $this->_db;
	}

	/**
	 * Sets the DB connection used by the cache component.
	 * @param Connection $value the DB connection instance
	 */
	public function setDbConnection($value)
	{
		$this->_db = $value;
	}
}
