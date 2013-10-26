<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

use yii\base\InvalidParamException;

/**
 * QueryBuilder is the query builder for MS SQL Server databases (version 2008 and above).
 *
 * @author Timur Ruziev <resurtm@gmail.com>
 * @since 2.0
 */
class QueryBuilder extends \yii\db\QueryBuilder
{
	/**
	 * @var array mapping from abstract column types (keys) to physical column types (values).
	 */
	public $typeMap = array(
		Schema::TYPE_PK => 'int IDENTITY PRIMARY KEY',
		Schema::TYPE_STRING => 'varchar(255)',
		Schema::TYPE_TEXT => 'text',
		Schema::TYPE_SMALLINT => 'smallint(6)',
		Schema::TYPE_INTEGER => 'int(11)',
		Schema::TYPE_BIGINT => 'bigint(20)',
		Schema::TYPE_FLOAT => 'float',
		Schema::TYPE_DECIMAL => 'decimal(10,0)',
		Schema::TYPE_DATETIME => 'datetime',
		Schema::TYPE_TIMESTAMP => 'timestamp',
		Schema::TYPE_TIME => 'time',
		Schema::TYPE_DATE => 'date',
		Schema::TYPE_BINARY => 'binary',
		Schema::TYPE_BOOLEAN => 'tinyint(1)',
		Schema::TYPE_MONEY => 'decimal(19,4)',
	);

  /**
   * Builds a SQL statement for renaming a DB table.
   * @param string $table the table to be renamed. The name will be properly quoted by the method.
   * @param string $newName the new table name. The name will be properly quoted by the method.
   * @return string the SQL statement for renaming a DB table.
   * @since 1.1.6
   */
  public function renameTable($table, $newName)
  {
    $quotedTable = $this->db->quoteTableName($table);
    return "sp_rename '$quotedTable', '$newName'";
  }

  /**
   * Builds a SQL statement for renaming a column.
   * @param string $table the table whose column is to be renamed. The name will be properly quoted by the method.
   * @param string $name the old name of the column. The name will be properly quoted by the method.
   * @param string $newName the new name of the column. The name will be properly quoted by the method.
   * @return string the SQL statement for renaming a DB column by build-in stored procedure defined by MS SQL.
   * @since 1.1.6
   */
  public function renameColumn($table, $oldName, $newName)
  {
    $quotedTable = $this->db->quoteTableName($table);
    return "sp_rename '$quotedTable.$oldName', '$newName', 'COLUMN'";
  }

  /**
   * Builds a SQL statement for changing the definition of a column.
   * @param string $table the table whose column is to be changed. The table name will be properly quoted by the method.
   * @param string $column the name of the column to be changed. The name will be properly quoted by the method.
   * @param string $type the new column type. The [[getColumnType()]] method will be invoked to convert abstract
   * column type (if any) into the physical one. Anything that is not recognized as abstract type will be kept
   * in the generated SQL. For example, 'string' will be turned into 'varchar(255)', while 'string not null'
   * will become 'varchar(255) not null'.
   * @return string the SQL statement for changing the definition of a column.
   */
  public function alterColumn($table, $column, $type)
  {
    $sql='ALTER TABLE ' . $this->db->quoteTableName($table) . ' ALTER COLUMN '
      . $this->db->quoteColumnName($column) . ' '
      . $this->db->quoteColumnName($column) . ' '
      . $this->getColumnType($type);
    return $sql;
  }

  /**
   * Builds a SQL statement for dropping a foreign key constraint.
   * @param string $name the name of the foreign key constraint to be dropped. The name will be properly quoted by the method.
   * @param string $table the table whose foreign is to be dropped. The name will be properly quoted by the method.
   * @return string the SQL statement for dropping a foreign key constraint.
   */
  public function dropForeignKey($name, $table)
  {
          return 'ALTER TABLE ' . $this->db->quoteTableName($table)
                  . ' DROP CONSTRAINT ' . $this->db->quoteColumnName($name);
  }

  /**
   * Builds a SQL statement for removing a primary key constraint to an existing table.
   * @param string $name the name of the primary key constraint to be removed.
   * @param string $table the table that the primary key constraint will be removed from.
   * @return string the SQL statement for removing a primary key constraint from an existing table.
   */
  public function dropPrimaryKey($name, $table)
  {
          return 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' DROP CONSTRAINT ' . $this->db->quoteColumnName($name);
  }

  /**
	 * @param integer $limit
	 * @param integer $offset
	 * @return string the LIMIT and OFFSET clauses built from [[query]].
	 */
	public function buildLimit($limit, $offset)
	{
		$sql = '';
		if ($limit !== null && $limit >= 0) {
			$sql = 'LIMIT ' . (int)$limit;
		}
		if ($offset > 0) {
			$sql .= ' OFFSET ' . (int)$offset;
		}
		return ltrim($sql);
	}

	public function applyLimit($sql, $limit, $offset)
  {
    $limit = $limit!==null ? (int)$limit : -1;
    $offset = $offset!==null ? (int)$offset : -1;
    if ($limit > 0 && $offset <= 0) //just limit
            $sql = preg_replace('/^([\s(])*SELECT( DISTINCT)?(?!\s*TOP\s*\()/i',"\\1SELECT\\2 TOP $limit", $sql);
    elseif($limit > 0 && $offset > 0)
            $sql = $this->rewriteLimitOffsetSql($sql, $limit,$offset);
    return $sql;
  }

//	public function update($table, $columns, $condition, &$params)
//	{
//		return '';
//	}

//	public function delete($table, $condition, &$params)
//	{
//		return '';
//	}

//	public function buildLimit($limit, $offset)
//	{
//		return '';
//	}

//	public function resetSequence($table, $value = null)
//	{
//		return '';
//	}

	/**
	 * Builds a SQL statement for enabling or disabling integrity check.
	 * @param boolean $check whether to turn on or off the integrity check.
	 * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
	 * @param string $table the table name. Defaults to empty string, meaning that no table will be changed.
	 * @return string the SQL statement for checking integrity
	 * @throws InvalidParamException if the table does not exist or there is no sequence associated with the table.
	 */
	public function checkIntegrity($check = true, $schema = '', $table = '')
	{
		if ($schema !== '') {
			$table = "{$schema}.{$table}";
		}
		$table = $this->db->quoteTableName($table);
		if ($this->db->getTableSchema($table) === null) {
			throw new InvalidParamException("Table not found: $table");
		}
		$enable = $check ? 'CHECK' : 'NOCHECK';
		return "ALTER TABLE {$table} {$enable} CONSTRAINT ALL";
	}
}
