<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\sphinx;

use yii\base\Object;
use yii\caching\Cache;
use Yii;
use yii\caching\GroupDependency;

/**
 * Schema represents the Sphinx schema information.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Schema extends Object
{
	/**
	 * The followings are the supported abstract column data types.
	 */
	const TYPE_PK = 'pk';
	const TYPE_STRING = 'string';
	const TYPE_INTEGER = 'integer';
	const TYPE_BIGINT = 'bigint';
	const TYPE_FLOAT = 'float';
	const TYPE_TIMESTAMP = 'timestamp';
	const TYPE_BOOLEAN = 'boolean';

	/**
	 * @var Connection the Sphinx connection
	 */
	public $db;
	/**
	 * @var array list of ALL index names in the Sphinx
	 */
	private $_indexNames = [];
	/**
	 * @var array list of loaded index metadata (index name => IndexSchema)
	 */
	private $_indexes = [];
	/**
	 * @var QueryBuilder the query builder for this Sphinx connection
	 */
	private $_builder;

	/**
	 * @var array mapping from physical column types (keys) to abstract column types (values)
	 */
	public $typeMap = [
		'field' => self::TYPE_STRING,
		'string' => self::TYPE_STRING,
		'ordinal' => self::TYPE_STRING,
		'integer' => self::TYPE_INTEGER,
		'int' => self::TYPE_INTEGER,
		'uint' => self::TYPE_INTEGER,
		'bigint' => self::TYPE_BIGINT,
		'timestamp' => self::TYPE_TIMESTAMP,
		'bool' => self::TYPE_BOOLEAN,
		'float' => self::TYPE_FLOAT,
		'mva' => self::TYPE_INTEGER,
	];

	/**
	 * Loads the metadata for the specified index.
	 * @param string $name index name
	 * @return IndexSchema driver dependent index metadata. Null if the index does not exist.
	 */
	protected function loadIndexSchema($name)
	{
		$index = new IndexSchema;
		$this->resolveIndexNames($index, $name);

		if ($this->findColumns($index)) {
			return $index;
		} else {
			return null;
		}
	}

	/**
	 * Resolves the index name and schema name (if any).
	 * @param IndexSchema $index the index metadata object
	 * @param string $name the index name
	 */
	protected function resolveIndexNames($index, $name)
	{
		$parts = explode('.', str_replace('`', '', $name));
		if (isset($parts[1])) {
			$index->schemaName = $parts[0];
			$index->name = $parts[1];
		} else {
			$index->name = $parts[0];
		}
	}

	/**
	 * Obtains the metadata for the named index.
	 * @param string $name index name. The index name may contain schema name if any. Do not quote the index name.
	 * @param boolean $refresh whether to reload the index schema even if it is found in the cache.
	 * @return IndexSchema index metadata. Null if the named index does not exist.
	 */
	public function getIndexSchema($name, $refresh = false)
	{
		if (isset($this->_indexes[$name]) && !$refresh) {
			return $this->_indexes[$name];
		}

		$db = $this->db;
		$realName = $this->getRawIndexName($name);

		if ($db->enableSchemaCache && !in_array($name, $db->schemaCacheExclude, true)) {
			/** @var $cache Cache */
			$cache = is_string($db->schemaCache) ? Yii::$app->getComponent($db->schemaCache) : $db->schemaCache;
			if ($cache instanceof Cache) {
				$key = $this->getCacheKey($name);
				if ($refresh || ($index = $cache->get($key)) === false) {
					$index = $this->loadIndexSchema($realName);
					if ($index !== null) {
						$cache->set($key, $index, $db->schemaCacheDuration, new GroupDependency($this->getCacheGroup()));
					}
				}
				return $this->_indexes[$name] = $index;
			}
		}
		return $this->_indexes[$name] = $index = $this->loadIndexSchema($realName);
	}

	/**
	 * Returns the cache key for the specified index name.
	 * @param string $name the index name
	 * @return mixed the cache key
	 */
	protected function getCacheKey($name)
	{
		return [
			__CLASS__,
			$this->db->dsn,
			$this->db->username,
			$name,
		];
	}

	/**
	 * Returns the cache group name.
	 * This allows [[refresh()]] to invalidate all cached index schemas.
	 * @return string the cache group name
	 */
	protected function getCacheGroup()
	{
		return md5(serialize([
			__CLASS__,
			$this->db->dsn,
			$this->db->username,
		]));
	}

	/**
	 * Returns the metadata for all indexes in the database.
	 * @param string $schema the schema of the indexes. Defaults to empty string, meaning the current or default schema name.
	 * @param boolean $refresh whether to fetch the latest available index schemas. If this is false,
	 * cached data may be returned if available.
	 * @return IndexSchema[] the metadata for all indexes in the Sphinx.
	 * Each array element is an instance of [[IndexSchema]] or its child class.
	 */
	public function getTableSchemas($schema = '', $refresh = false)
	{
		$indexes = [];
		foreach ($this->getIndexNames($schema, $refresh) as $name) {
			if ($schema !== '') {
				$name = $schema . '.' . $name;
			}
			if (($index = $this->getIndexSchema($name, $refresh)) !== null) {
				$indexes[] = $index;
			}
		}
		return $indexes;
	}

	/**
	 * Returns all index names in the database.
	 * @param string $schema the schema of the indexes. Defaults to empty string, meaning the current or default schema name.
	 * If not empty, the returned index names will be prefixed with the schema name.
	 * @param boolean $refresh whether to fetch the latest available index names. If this is false,
	 * index names fetched previously (if available) will be returned.
	 * @return string[] all index names in the database.
	 */
	public function getIndexNames($schema = '', $refresh = false)
	{
		if (!isset($this->_indexNames[$schema]) || $refresh) {
			$this->_indexNames[$schema] = $this->findIndexNames($schema);
		}
		return $this->_indexNames[$schema];
	}

	/**
	 * Returns all index names in the database.
	 * @param string $schema the schema of the indexes. Defaults to empty string, meaning the current or default schema.
	 * @return array all index names in the database. The names have NO schema name prefix.
	 */
	protected function findIndexNames($schema = '')
	{
		$sql = 'SHOW TABLES';
		if ($schema !== '') {
			$sql .= ' FROM ' . $this->quoteSimpleIndexName($schema);
		}
		return $this->db->createCommand($sql)->queryColumn();
	}

	/**
	 * @return QueryBuilder the query builder for this connection.
	 */
	public function getQueryBuilder()
	{
		if ($this->_builder === null) {
			$this->_builder = $this->createQueryBuilder();
		}
		return $this->_builder;
	}

	/**
	 * Determines the PDO type for the given PHP data value.
	 * @param mixed $data the data whose PDO type is to be determined
	 * @return integer the PDO type
	 * @see http://www.php.net/manual/en/pdo.constants.php
	 */
	public function getPdoType($data)
	{
		static $typeMap = [
			// php type => PDO type
			'boolean' => \PDO::PARAM_BOOL,
			'integer' => \PDO::PARAM_INT,
			'string' => \PDO::PARAM_STR,
			'resource' => \PDO::PARAM_LOB,
			'NULL' => \PDO::PARAM_NULL,
		];
		$type = gettype($data);
		return isset($typeMap[$type]) ? $typeMap[$type] : \PDO::PARAM_STR;
	}

	/**
	 * Refreshes the schema.
	 * This method cleans up all cached index schemas so that they can be re-created later
	 * to reflect the Sphinx schema change.
	 */
	public function refresh()
	{
		/** @var $cache Cache */
		$cache = is_string($this->db->schemaCache) ? Yii::$app->getComponent($this->db->schemaCache) : $this->db->schemaCache;
		if ($this->db->enableSchemaCache && $cache instanceof Cache) {
			GroupDependency::invalidate($cache, $this->getCacheGroup());
		}
		$this->_indexNames = [];
		$this->_indexes = [];
	}

	/**
	 * Creates a query builder for the Sphinx.
	 * @return QueryBuilder query builder instance
	 */
	public function createQueryBuilder()
	{
		return new QueryBuilder($this->db);
	}

	/**
	 * Quotes a string value for use in a query.
	 * Note that if the parameter is not a string, it will be returned without change.
	 * @param string $str string to be quoted
	 * @return string the properly quoted string
	 * @see http://www.php.net/manual/en/function.PDO-quote.php
	 */
	public function quoteValue($str)
	{
		if (!is_string($str)) {
			return $str;
		}

		$this->db->open();
		if (($value = $this->db->pdo->quote($str)) !== false) {
			return $value;
		} else { // the driver doesn't support quote (e.g. oci)
			return "'" . addcslashes(str_replace("'", "''", $str), "\000\n\r\\\032") . "'";
		}
	}

	/**
	 * Quotes a index name for use in a query.
	 * If the index name contains schema prefix, the prefix will also be properly quoted.
	 * If the index name is already quoted or contains '(' or '{{',
	 * then this method will do nothing.
	 * @param string $name index name
	 * @return string the properly quoted index name
	 * @see quoteSimpleTableName
	 */
	public function quoteIndexName($name)
	{
		if (strpos($name, '(') !== false || strpos($name, '{{') !== false) {
			return $name;
		}
		if (strpos($name, '.') === false) {
			return $this->quoteSimpleIndexName($name);
		}
		$parts = explode('.', $name);
		foreach ($parts as $i => $part) {
			$parts[$i] = $this->quoteSimpleIndexName($part);
		}
		return implode('.', $parts);
	}

	/**
	 * Quotes a column name for use in a query.
	 * If the column name contains prefix, the prefix will also be properly quoted.
	 * If the column name is already quoted or contains '(', '[[' or '{{',
	 * then this method will do nothing.
	 * @param string $name column name
	 * @return string the properly quoted column name
	 * @see quoteSimpleColumnName
	 */
	public function quoteColumnName($name)
	{
		if (strpos($name, '(') !== false || strpos($name, '[[') !== false || strpos($name, '{{') !== false) {
			return $name;
		}
		if (($pos = strrpos($name, '.')) !== false) {
			$prefix = $this->quoteIndexName(substr($name, 0, $pos)) . '.';
			$name = substr($name, $pos + 1);
		} else {
			$prefix = '';
		}
		return $prefix . $this->quoteSimpleColumnName($name);
	}

	/**
	 * Quotes a index name for use in a query.
	 * A simple index name has no schema prefix.
	 * @param string $name index name
	 * @return string the properly quoted index name
	 */
	public function quoteSimpleIndexName($name)
	{
		return strpos($name, "`") !== false ? $name : "`" . $name . "`";
	}

	/**
	 * Quotes a column name for use in a query.
	 * A simple column name has no prefix.
	 * @param string $name column name
	 * @return string the properly quoted column name
	 */
	public function quoteSimpleColumnName($name)
	{
		return strpos($name, '`') !== false || $name === '*' ? $name : '`' . $name . '`';
	}

	/**
	 * Returns the actual name of a given index name.
	 * This method will strip off curly brackets from the given index name
	 * and replace the percentage character '%' with [[Connection::indexPrefix]].
	 * @param string $name the index name to be converted
	 * @return string the real name of the given index name
	 */
	public function getRawIndexName($name)
	{
		if (strpos($name, '{{') !== false) {
			$name = preg_replace('/\\{\\{(.*?)\\}\\}/', '\1', $name);
			return str_replace('%', $this->db->tablePrefix, $name);
		} else {
			return $name;
		}
	}

	/**
	 * Extracts the PHP type from abstract DB type.
	 * @param ColumnSchema $column the column schema information
	 * @return string PHP type name
	 */
	protected function getColumnPhpType($column)
	{
		static $typeMap = [ // abstract type => php type
			'smallint' => 'integer',
			'integer' => 'integer',
			'bigint' => 'integer',
			'boolean' => 'boolean',
			'float' => 'double',
		];
		if (isset($typeMap[$column->type])) {
			if ($column->type === 'bigint') {
				return PHP_INT_SIZE == 8 ? 'integer' : 'string';
			} elseif ($column->type === 'integer') {
				return PHP_INT_SIZE == 4 ? 'string' : 'integer';
			} else {
				return $typeMap[$column->type];
			}
		} else {
			return 'string';
		}
	}

	/**
	 * Collects the metadata of index columns.
	 * @param IndexSchema $index the index metadata
	 * @return boolean whether the index exists in the database
	 * @throws \Exception if DB query fails
	 */
	protected function findColumns($index)
	{
		$sql = 'DESCRIBE ' . $this->quoteSimpleIndexName($index->name);
		try {
			$columns = $this->db->createCommand($sql)->queryAll();
		} catch (\Exception $e) {
			$previous = $e->getPrevious();
			if ($previous instanceof \PDOException && $previous->getCode() == '42S02') {
				// index does not exist
				return false;
			}
			throw $e;
		}
		foreach ($columns as $info) {
			$column = $this->loadColumnSchema($info);
			$index->columns[$column->name] = $column;
			if ($column->isPrimaryKey) {
				$index->primaryKey = $column->name;
			}
		}
		return true;
	}

	/**
	 * Loads the column information into a [[ColumnSchema]] object.
	 * @param array $info column information
	 * @return ColumnSchema the column schema object
	 */
	protected function loadColumnSchema($info)
	{
		$column = new ColumnSchema;

		$column->name = $info['Field'];
		$column->dbType = $info['Type'];

		$column->isPrimaryKey = ($column->name == 'id');

		$type = $info['Type'];
		if (isset($this->typeMap[$type])) {
			$column->type = $this->typeMap[$type];
		} else {
			$column->type = self::TYPE_STRING;
		}

		$column->isField = ($type == 'field');
		$column->isAttribute = !$column->isField;

		$column->isMva = ($type == 'mva');

		$column->phpType = $this->getColumnPhpType($column);

		return $column;
	}
}