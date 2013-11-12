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
 * @method Schema getSchema() The schema information for this Sphinx connection
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
}