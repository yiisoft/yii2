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
 * TableSchema is the base class for representing the metadata of a database table.
 *
 * It may be extended by different DBMS driver to provide DBMS-specific table metadata.
 *
 * TableSchema provides the following information about a table:
 * <ul>
 * <li>{@link name}</li>
 * <li>{@link rawName}</li>
 * <li>{@link columns}</li>
 * <li>{@link primaryKey}</li>
 * <li>{@link foreignKeys}</li>
 * <li>{@link sequenceName}</li>
 * </ul>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class TableSchema extends \yii\base\Object
{
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
	 *     'ForeignTableName',
	 *     'fk1' => 'pk1',  // pk1 is in foreign table
	 *     'fk2' => 'pk2',  // if composite foreign key
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
	 * @return CDbColumnSchema metadata of the named column. Null if the named column does not exist.
	 */
	public function getColumn($name)
	{
		return isset($this->columns[$name]) ? $this->columns[$name] : null;
	}

	/**
	 * @return array list of column names
	 */
	public function getColumnNames()
	{
		return array_keys($this->columns);
	}
}
