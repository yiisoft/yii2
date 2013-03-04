<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\base\InvalidParamException;

/**
 * TableSchema represents the metadata of a database table.
 *
 * @property array $columnNames list of column names
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class TableSchema extends \yii\base\Object
{
	/**
	 * @var string name of the catalog (database) that this table belongs to.
	 * Defaults to null, meaning no catalog (or the current database).
	 * This property is only meaningful for MSSQL.
	 */
	public $catalogName;
	/**
	 * @var string name of the schema that this table belongs to.
	 */
	public $schemaName;
	/**
	 * @var string name of this table.
	 */
	public $name;
	/**
	 * @var string[] primary keys of this table.
	 */
	public $primaryKey = array();
	/**
	 * @var string sequence name for the primary key. Null if no sequence.
	 */
	public $sequenceName;
	/**
	 * @var array foreign keys of this table. Each array element is of the following structure:
	 *
	 * ~~~
	 * array(
	 *	 'ForeignTableName',
	 *	 'fk1' => 'pk1',  // pk1 is in foreign table
	 *	 'fk2' => 'pk2',  // if composite foreign key
	 * )
	 * ~~~
	 */
	public $foreignKeys = array();
	/**
	 * @var ColumnSchema[] column metadata of this table. Each array element is a [[ColumnSchema]] object, indexed by column names.
	 */
	public $columns = array();

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
			$keys = array($keys);
		}
		$this->primaryKey = $keys;
		foreach ($this->columns as $column) {
			$column->isPrimaryKey = false;
		}
		foreach ($keys as $key) {
			if (isset($this->columns[$key])) {
				$this->columns[$key]->isPrimaryKey = true;
			} else {
				throw new InvalidParamException("Primary key '$key' cannot be found in table '{$this->name}'.");
			}
		}
	}
}
