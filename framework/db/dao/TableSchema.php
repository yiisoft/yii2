<?php
/**
 * TableSchema class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\dao;

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
	 * @var string quoted name of this table. This will include [[schemaName]] if it is not empty.
	 */
	public $quotedName;
	/**
	 * @var string|array primary key name of this table. If composite key, an array of key names is returned.
	 */
	public $primaryKey;
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
	 * @var array column metadata of this table. Each array element is a [[ColumnSchema]] object, indexed by column names.
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
}
