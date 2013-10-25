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
   * Builds a SQL statement for renaming a column.
   * @param string $table the table whose column is to be renamed. The name will be properly quoted by the method.
   * @param string $oldName the old name of the column. The name will be properly quoted by the method.
   * @param string $newName the new name of the column. The name will be properly quoted by the method.
   * @return string the SQL statement for renaming a DB column.
   * @throws Exception
   */
  public function renameColumn($table, $oldName, $newName)
  {
          $quotedTable = $this->db->quoteTableName($table);
          $row = $this->db->createCommand('SHOW CREATE TABLE ' . $quotedTable)->queryOne();
          if ($row === false) {
                  throw new Exception("Unable to find column '$oldName' in table '$table'.");
          }
          if (isset($row['Create Table'])) {
                  $sql = $row['Create Table'];
          } else {
                  $row = array_values($row);
                  $sql = $row[1];
          }
          if (preg_match_all('/^\s*`(.*?)`\s+(.*?),?$/m', $sql, $matches)) {
                  foreach ($matches[1] as $i => $c) {
                          if ($c === $oldName) {
                                  return "ALTER TABLE $quotedTable CHANGE "
                                          . $this->db->quoteColumnName($oldName) . ' '
                                          . $this->db->quoteColumnName($newName) . ' '
                                          . $matches[2][$i];
                          }
                  }
          }
          // try to give back a SQL anyway
          return "ALTER TABLE $quotedTable CHANGE "
                  . $this->db->quoteColumnName($oldName) . ' '
                  . $this->db->quoteColumnName($newName);
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
