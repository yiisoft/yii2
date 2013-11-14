<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\sphinx;

/**
 * Class Connection
 *
 * @property Schema $schema The schema information for this Sphinx connection. This property is read-only.
 * @property \yii\sphinx\QueryBuilder $queryBuilder The query builder for this Sphinx connection. This property is
 * read-only.
 * @method Schema getSchema() The schema information for this Sphinx connection
 * @method \yii\sphinx\QueryBuilder getQueryBuilder() the query builder for this Sphinx connection
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Connection extends \yii\db\Connection
{
	/**
	 * @inheritdoc
	 */
	public $schemaMap = [
		'mysqli' => 'yii\sphinx\Schema',   // MySQL
		'mysql' => 'yii\sphinx\Schema',    // MySQL
	];

	/**
	 * Obtains the schema information for the named index.
	 * @param string $name index name.
	 * @param boolean $refresh whether to reload the table schema even if it is found in the cache.
	 * @return IndexSchema index schema information. Null if the named index does not exist.
	 */
	public function getIndexSchema($name, $refresh = false)
	{
		return $this->getSchema()->getIndexSchema($name, $refresh);
	}

	/**
	 * Quotes a index name for use in a query.
	 * If the index name contains schema prefix, the prefix will also be properly quoted.
	 * If the index name is already quoted or contains special characters including '(', '[[' and '{{',
	 * then this method will do nothing.
	 * @param string $name index name
	 * @return string the properly quoted index name
	 */
	public function quoteIndexName($name)
	{
		return $this->getSchema()->quoteIndexName($name);
	}

	/**
	 * Alias of [[quoteIndexName()]].
	 * @param string $name table name
	 * @return string the properly quoted table name
	 */
	public function quoteTableName($name)
	{
		return $this->quoteIndexName($name);
	}

	/**
	 * Creates a command for execution.
	 * @param string $sql the SQL statement to be executed
	 * @param array $params the parameters to be bound to the SQL statement
	 * @return Command the Sphinx command
	 */
	public function createCommand($sql = null, $params = [])
	{
		$this->open();
		$command = new Command([
			'db' => $this,
			'sql' => $sql,
		]);
		return $command->bindValues($params);
	}
}