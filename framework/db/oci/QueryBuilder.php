<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\oci;

use yii\base\InvalidParamException;

/**
 * QueryBuilder is the query builder for Oracle databases.
 *
 */
class QueryBuilder extends \yii\db\QueryBuilder
{

	private $sql;

	public function build($query)
	{
		$params = $query->params;
		$clauses = [
			$this->buildSelect($query->select, $query->distinct, $query->selectOption),
			$this->buildFrom($query->from),
			$this->buildJoin($query->join, $params),
			$this->buildWhere($query->where, $params),
			$this->buildGroupBy($query->groupBy),
			$this->buildHaving($query->having, $params),
			$this->buildUnion($query->union, $params),
			$this->buildOrderBy($query->orderBy),
		];
		$this->sql = implode($this->separator, array_filter($clauses));

		if ($query->limit !== null || $query->offset !== null) {
			$this->sql = $this->buildLimit($query->limit, $query->offset);
		}
		return [$this->sql, $params];
	}

	public function buildLimit($limit, $offset)
	{
		if (($limit < 0) && ($offset < 0)) {
			return $this->sql;
		}
		$filters = [];
		if ($offset > 0) {
			$filters[] = 'rowNumId > ' . (int)$offset;
		}

		if ($limit >= 0) {
			$filters[] = 'rownum <= ' . (int)$limit;
		}

		if (count($filters) > 0) {
			$filter = implode(' and ', $filters);
			$filter = " WHERE " . $filter;
		} else {
			$filter = '';
		}

		$sql = <<<EOD
WITH USER_SQL AS ({$this->sql}),
	PAGINATION AS (SELECT USER_SQL.*, rownum as rowNumId FROM USER_SQL)
SELECT *
FROM PAGINATION
{$filter}
EOD;
		return $sql;
	}


	/**
	 * Builds a SQL statement for renaming a DB table.
	 *
	 * @param string $table the table to be renamed. The name will be properly quoted by the method.
	 * @param string $newName the new table name. The name will be properly quoted by the method.
	 * @return string the SQL statement for renaming a DB table.
	 */
	public function renameTable($table, $newName)
	{
		return 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' RENAME TO ' . $this->db->quoteTableName($newName);
	}

	/**
	 * Builds a SQL statement for changing the definition of a column.
	 *
	 * @param string $table the table whose column is to be changed. The table name will be properly quoted by the method.
	 * @param string $column the name of the column to be changed. The name will be properly quoted by the method.
	 * @param string $type the new column type. The {@link getColumnType} method will be invoked to convert abstract column type (if any)
	 * into the physical one. Anything that is not recognized as abstract type will be kept in the generated SQL.
	 * For example, 'string' will be turned into 'varchar(255)', while 'string not null' will become 'varchar(255) not null'.
	 * @return string the SQL statement for changing the definition of a column.
	 */
	public function alterColumn($table, $column, $type)
	{
		$type = $this->getColumnType($type);
		return 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' MODIFY ' . $this->db->quoteColumnName($column) . ' ' . $this->getColumnType($type);
	}

	/**
	 * Builds a SQL statement for dropping an index.
	 *
	 * @param string $name the name of the index to be dropped. The name will be properly quoted by the method.
	 * @param string $table the table whose index is to be dropped. The name will be properly quoted by the method.
	 * @return string the SQL statement for dropping an index.
	 */
	public function dropIndex($name, $table)
	{
		return 'DROP INDEX ' . $this->db->quoteTableName($name);
	}

	/**
	 * @inheritdoc
	 */
	public function resetSequence($table, $value = null)
	{
		$tableSchema = $this->db->getTableSchema($table);
		if ($tableSchema === null) {
			throw new InvalidParamException("Unknown table: $table");
		}
		if ($tableSchema->sequenceName === null) {
			return '';
		}

		if ($value !== null) {
			$value = (int)$value;
		} else {
			$value = (int)$this->db->createCommand("SELECT MAX(\"{$tableSchema->primaryKey}\") FROM \"{$tableSchema->name}\"")->queryScalar();
			$value++;
		}
		return "DROP SEQUENCE \"{$tableSchema->name}_SEQ\";"
			. "CREATE SEQUENCE \"{$tableSchema->name}_SEQ\" START WITH {$value} INCREMENT BY 1 NOMAXVALUE NOCACHE";
	}
}
