<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\sphinx;

use yii\base\Object;
use yii\base\InvalidParamException;

/**
 * IndexSchema represents the metadata of a Sphinx index.
 *
 * @property array $columnNames List of column names. This property is read-only.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class IndexSchema extends Object
{
	/**
	 * @var string name of the schema that this index belongs to.
	 */
	public $schemaName;
	/**
	 * @var string name of this index.
	 */
	public $name;
	/**
	 * @var string[] primary keys of this index.
	 */
	public $primaryKey = [];
	/**
	 * @var ColumnSchema[] column metadata of this index. Each array element is a [[ColumnSchema]] object, indexed by column names.
	 */
	public $columns = [];

	/**
	 * Gets the named column metadata.
	 * This is a convenient method for retrieving a named column even if it does not exist.
	 * @param string $name column name
	 * @return ColumnSchema metadata of the named column. Null if the named column does not exist.
	 */
	public function getColumn($name)
	{
		return isset($this->columns[$name]) ? $this->columns[$name] : null;
	}

	/**
	 * Returns the names of all columns in this table.
	 * @return array list of column names
	 */
	public function getColumnNames()
	{
		return array_keys($this->columns);
	}

	/**
	 * Manually specifies the primary key for this table.
	 * @param string|array $keys the primary key (can be composite)
	 * @throws InvalidParamException if the specified key cannot be found in the table.
	 */
	public function fixPrimaryKey($keys)
	{
		if (!is_array($keys)) {
			$keys = [$keys];
		}
		$this->primaryKey = $keys;
		foreach ($this->columns as $column) {
			$column->isPrimaryKey = false;
		}
		foreach ($keys as $key) {
			if (isset($this->columns[$key])) {
				$this->columns[$key]->isPrimaryKey = true;
			} else {
				throw new InvalidParamException("Primary key '$key' cannot be found in index '{$this->name}'.");
			}
		}
	}
}