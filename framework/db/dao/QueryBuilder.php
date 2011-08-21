<?php
/**
 * This file contains the Command class.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\dao;

/**
 * QueryBuilder builds a SQL statement based on the specification given as a [[Query]] object.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class QueryBuilder extends \yii\base\Component
{
	private $_connection;

	public function __construct($connection)
	{
		$this->_connection = $connection;
	}

	/**
	 * @return CDbConnection the connection associated with this command
	 */
	public function getConnection()
	{
		return $this->_connection;
	}

	public function build($query)
	{
		$clauses = array(
			$this->buildSelect($query),
			$this->buildFrom($query),
			$this->buildJoin($query),
			$this->buildWhere($query),
			$this->buildGroupBy($query),
			$this->buildHaving($query),
			$this->buildUnion($query),
			$this->buildOrderBy($query),
			$this->buildLimit($query),
		);

		return implode("\n", array_filter($clauses));
	}

	/**
	 * Builds a SQL statement for creating a new DB table.
	 *
	 * The columns in the new  table should be specified as name-definition pairs (e.g. 'name'=>'string'),
	 * where name stands for a column name which will be properly quoted by the method, and definition
	 * stands for the column type which can contain an abstract DB type.
	 * The {@link getColumnType} method will be invoked to convert any abstract type into a physical one.
	 *
	 * If a column is specified with definition only (e.g. 'PRIMARY KEY (name, type)'), it will be directly
	 * inserted into the generated SQL.
	 *
	 * @param string $table the name of the table to be created. The name will be properly quoted by the method.
	 * @param array $columns the columns (name=>definition) in the new table.
	 * @param string $options additional SQL fragment that will be appended to the generated SQL.
	 * @return string the SQL statement for creating a new DB table.
	 */
	public function createTable($table, $columns, $options = null)
	{
		$cols = array();
		foreach ($columns as $name => $type)
		{
			if (is_string($name))
				$cols[] = "\t" . $this->quoteColumnName($name) . ' ' . $this->getColumnType($type);
			else
				$cols[] = "\t" . $type;
		}
		$sql = "CREATE TABLE " . $this->quoteTableName($table) . " (\n" . implode(",\n", $cols) . "\n)";
		return $options === null ? $sql : $sql . ' ' . $options;
	}

	/**
	 * Builds a SQL statement for renaming a DB table.
	 * @param string $table the table to be renamed. The name will be properly quoted by the method.
	 * @param string $newName the new table name. The name will be properly quoted by the method.
	 * @return string the SQL statement for renaming a DB table.
	 */
	public function renameTable($table, $newName)
	{
		return 'RENAME TABLE ' . $this->quoteTableName($table) . ' TO ' . $this->quoteTableName($newName);
	}

	/**
	 * Builds a SQL statement for dropping a DB table.
	 * @param string $table the table to be dropped. The name will be properly quoted by the method.
	 * @return string the SQL statement for dropping a DB table.
	 */
	public function dropTable($table)
	{
		return "DROP TABLE " . $this->quoteTableName($table);
	}

	/**
	 * Builds a SQL statement for truncating a DB table.
	 * @param string $table the table to be truncated. The name will be properly quoted by the method.
	 * @return string the SQL statement for truncating a DB table.
	 */
	public function truncateTable($table)
	{
		return "TRUNCATE TABLE " . $this->quoteTableName($table);
	}

	/**
	 * Builds a SQL statement for adding a new DB column.
	 * @param string $table the table that the new column will be added to. The table name will be properly quoted by the method.
	 * @param string $column the name of the new column. The name will be properly quoted by the method.
	 * @param string $type the column type. The {@link getColumnType} method will be invoked to convert abstract column type (if any)
	 * into the physical one. Anything that is not recognized as abstract type will be kept in the generated SQL.
	 * For example, 'string' will be turned into 'varchar(255)', while 'string not null' will become 'varchar(255) not null'.
	 * @return string the SQL statement for adding a new column.
	 * @since 1.1.6
	 */
	public function addColumn($table, $column, $type)
	{
		return 'ALTER TABLE ' . $this->quoteTableName($table)
			. ' ADD ' . $this->quoteColumnName($column) . ' '
			. $this->getColumnType($type);
	}

	/**
	 * Builds a SQL statement for dropping a DB column.
	 * @param string $table the table whose column is to be dropped. The name will be properly quoted by the method.
	 * @param string $column the name of the column to be dropped. The name will be properly quoted by the method.
	 * @return string the SQL statement for dropping a DB column.
	 * @since 1.1.6
	 */
	public function dropColumn($table, $column)
	{
		return "ALTER TABLE " . $this->quoteTableName($table)
			. " DROP COLUMN " . $this->quoteColumnName($column);
	}

	/**
	 * Builds a SQL statement for renaming a column.
	 * @param string $table the table whose column is to be renamed. The name will be properly quoted by the method.
	 * @param string $name the old name of the column. The name will be properly quoted by the method.
	 * @param string $newName the new name of the column. The name will be properly quoted by the method.
	 * @return string the SQL statement for renaming a DB column.
	 * @since 1.1.6
	 */
	public function renameColumn($table, $name, $newName)
	{
		return "ALTER TABLE " . $this->quoteTableName($table)
			. " RENAME COLUMN " . $this->quoteColumnName($name)
			. " TO " . $this->quoteColumnName($newName);
	}

	/**
	 * Builds a SQL statement for changing the definition of a column.
	 * @param string $table the table whose column is to be changed. The table name will be properly quoted by the method.
	 * @param string $column the name of the column to be changed. The name will be properly quoted by the method.
	 * @param string $type the new column type. The {@link getColumnType} method will be invoked to convert abstract column type (if any)
	 * into the physical one. Anything that is not recognized as abstract type will be kept in the generated SQL.
	 * For example, 'string' will be turned into 'varchar(255)', while 'string not null' will become 'varchar(255) not null'.
	 * @return string the SQL statement for changing the definition of a column.
	 */
	public function alterColumn($table, $column, $type)
	{
		return 'ALTER TABLE ' . $this->quoteTableName($table) . ' CHANGE '
			. $this->quoteColumnName($column) . ' '
			. $this->quoteColumnName($column) . ' '
			. $this->getColumnType($type);
	}

	/**
	 * Builds a SQL statement for adding a foreign key constraint to an existing table.
	 * The method will properly quote the table and column names.
	 * @param string $name the name of the foreign key constraint.
	 * @param string $table the table that the foreign key constraint will be added to.
	 * @param string $columns the name of the column to that the constraint will be added on. If there are multiple columns, separate them with commas.
	 * @param string $refTable the table that the foreign key references to.
	 * @param string $refColumns the name of the column that the foreign key references to. If there are multiple columns, separate them with commas.
	 * @param string $delete the ON DELETE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
	 * @param string $update the ON UPDATE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
	 * @return string the SQL statement for adding a foreign key constraint to an existing table.
	 */
	public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
	{
		$columns = preg_split('/\s*,\s*/', $columns, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($columns as $i => $col)
			$columns[$i] = $this->quoteColumnName($col);
		$refColumns = preg_split('/\s*,\s*/', $refColumns, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($refColumns as $i => $col)
			$refColumns[$i] = $this->quoteColumnName($col);
		$sql = 'ALTER TABLE ' . $this->quoteTableName($table)
			. ' ADD CONSTRAINT ' . $this->quoteColumnName($name)
			. ' FOREIGN KEY (' . implode(', ', $columns) . ')'
			. ' REFERENCES ' . $this->quoteTableName($refTable)
			. ' (' . implode(', ', $refColumns) . ')';
		if ($delete !== null)
			$sql .= ' ON DELETE ' . $delete;
		if ($update !== null)
			$sql .= ' ON UPDATE ' . $update;
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
		return 'ALTER TABLE ' . $this->quoteTableName($table)
			. ' DROP CONSTRAINT ' . $this->quoteColumnName($name);
	}

	/**
	 * Builds a SQL statement for creating a new index.
	 * @param string $name the name of the index. The name will be properly quoted by the method.
	 * @param string $table the table that the new index will be created for. The table name will be properly quoted by the method.
	 * @param string $column the column(s) that should be included in the index. If there are multiple columns, please separate them
	 * by commas. Each column name will be properly quoted by the method, unless a parenthesis is found in the name.
	 * @param boolean $unique whether to add UNIQUE constraint on the created index.
	 * @return string the SQL statement for creating a new index.
	 */
	public function createIndex($name, $table, $column, $unique = false)
	{
		$cols = array();
		$columns = preg_split('/\s*,\s*/', $column, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($columns as $col)
		{
			if (strpos($col, '(') !== false)
				$cols[] = $col;
			else
				$cols[] = $this->quoteColumnName($col);
		}
		return ($unique ? 'CREATE UNIQUE INDEX ' : 'CREATE INDEX ')
			. $this->quoteTableName($name) . ' ON '
			. $this->quoteTableName($table) . ' (' . implode(', ', $cols) . ')';
	}

	/**
	 * Builds a SQL statement for dropping an index.
	 * @param string $name the name of the index to be dropped. The name will be properly quoted by the method.
	 * @param string $table the table whose index is to be dropped. The name will be properly quoted by the method.
	 * @return string the SQL statement for dropping an index.
	 */
	public function dropIndex($name, $table)
	{
		return 'DROP INDEX ' . $this->quoteTableName($name) . ' ON ' . $this->quoteTableName($table);
	}

	protected function buildSelect($query)
	{
		$select = $query->distinct ? 'SELECT DISTINCT' : 'SELECT';

		$columns = $query->select;
		if (empty($columns)) {
			return $select . ' *';
		}

		if (is_string($columns)) {
			if (strpos($columns, '(') !== false) {
				return $select . ' ' . $columns;
			}
			$columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
		}

		foreach ($columns as $i => $column) {
			if (is_object($column)) {
				$columns[$i] = (string)$column;
			}
			elseif (strpos($column, '(') === false) {
				if (preg_match('/^(.*?)(?i:\s+as\s+|\s+)([\w\-\.])$/', $column, $matches)) {
					$columns[$i] = $this->_connection->quoteColumnName($matches[1]) . ' AS ' . $this->_connection->quoteSimpleColumnName($matches[2]);
				}
				else {
					$columns[$i] = $this->_connection->quoteColumnName($column);
				}
			}
		}

		return $select . ' ' . implode(', ', $columns);
	}

	protected function buildFrom($query)
	{
		$tables = $query->from;
		if (is_string($tables) && strpos($tables, '(') !== false) {
			return 'FROM ' . $tables;
		}

		if (!is_array($tables)) {
			$tables = preg_split('/\s*,\s*/', trim($tables), -1, PREG_SPLIT_NO_EMPTY);
		}
		foreach ($tables as $i => $table) {
			if (strpos($table, '(') === false) {
				if (preg_match('/^(.*?)(?i:\s+as\s+|\s+)(.*)$/i', $table, $matches)) { // with alias
					$tables[$i] = $this->_connection->quoteTableName($matches[1]) . ' ' . $this->_connection->quoteTableName($matches[2]);
				}
				else {
					$tables[$i] = $this->_connection->quoteTableName($table);
				}
			}
		}

		return 'FROM ' . implode(', ', $tables);
	}

	protected function buildJoin($query)
	{
		$joins = $query->join;
		if (empty($joins)) {
			return '';
		}
		if (is_string($joins)) {
			return $joins;
		}

		foreach ($joins as $i => $join) {
			if (is_array($join)) {  // join type, table name, on-condition
				if (isset($join[0], $join[1])) {
					$table = $join[1];
					if (strpos($table,'(')===false) {
						if (preg_match('/^(.*?)(?i:\s+as\s+|\s+)(.*)$/', $table, $matches)) {  // with alias
							$table = $this->_connection->quoteTableName($matches[1]).' '.$this->_connection->quoteTableName($matches[2]);
						}
						else {
							$table = $this->_connection->quoteTableName($table);
						}
					}
					$joins[$i] = strtoupper($join[0]) . ' ' . $table;
					if (isset($join[2])) {  // join condition
						$condition = $this->processCondition($join[2]);
						$joins[$i] .= ' ON ' . $condition;
					}
				}
				else {
					throw new Exception('The join clause may be specified as an array of at least two elements.');
				}
			}
		}

		return implode("\n", $joins);
	}

	protected function buildWhere($query)
	{
		$where = $this->processConditions($query->where);
		return empty($where) ? '' : 'WHERE ' . $where;
	}

	protected function buildGroupBy($query)
	{
		$columns = $query->groupBy;
		if (empty($columns)) {
			return '';
		}
		if (is_string($columns) && strpos($columns, '(') !== false) {
			return 'GROUP BY ' . $columns;
		}

		if (!is_array($columns)) {
			$columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
		}
		foreach ($columns as $i => $column) {
			if (is_object($column)) {
				$columns[$i] = (string)$column;
			}
			elseif (strpos($column, '(') === false) {
				$columns[$i] = $this->_connection->quoteColumnName($column);
			}
		}
		return 'GROUP BY ' . implode(', ', $columns);
	}

	protected function buildHaving($query)
	{
		$having = $this->processConditions($query->having);
		return empty($having) ? '' : 'HAVING ' . $having;
	}

	protected function buildOrderBy($query)
	{
		$columns = $query->orderBy;
		if (empty($columns)) {
			return '';
		}
		if (is_string($columns) && strpos($columns, '(') !== false) {
			return 'ORDER BY ' . $columns;
		}

		if (!is_array($columns)) {
			$columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
		}
		foreach ($columns as $i => $column) {
			if (is_object($column)) {
				$columns[$i] = (string)$column;
			}
			elseif (strpos($column, '(') === false) {
				if (preg_match('/^(.*?)\s+(asc|desc)$/i', $column, $matches)) {
					$columns[$i] = $this->_connection->quoteColumnName($matches[1]) . ' ' . strtoupper($matches[2]);
				}
				else {
					$columns[$i] = $this->_connection->quoteColumnName($column);
				}
			}
		}
		return 'ORDER BY ' . implode(', ', $columns);
	}

	protected function buildLimit($query)
	{
		$sql = '';
		if ($query->limit !== null && $query->limit >= 0) {
			$sql = 'LIMIT ' . (int)$query->limit;
		}
		if ($query->offset>0) {
			$sql .= ' OFFSET '.(int)$query->offset;
		}
		return ltrim($sql);
	}

	protected function buildUnion($query)
	{
		$unions = $query->union;
		if (empty($unions)) {
			return '';
		}
		if (!is_array($unions)) {
			$unions = array($unions);
		}
		foreach ($unions as $i => $union) {
			if ($union instanceof Query) {
				$unions[$i] = $union->getSql($this->_connection);
			}
		}
		return "UNION (\n" . implode("\n) UNION (\n", $unions) . "\n)";
	}

	protected function processCondition($conditions)
	{
		if (!is_array($conditions)) {
			return $conditions;
		}
		elseif ($conditions === array()) {
			return '';
		}

		$n = count($conditions);
		$operator = strtoupper($conditions[0]);
		if ($operator === 'OR' || $operator === 'AND') {
			$parts = array();
			for ($i = 1; $i < $n; ++$i) {
				$condition = $this->processCondition($conditions[$i]);
				if ($condition !== '') {
					$parts[] = '(' . $condition . ')';
				}
			}
			return $parts === array() ? '' : implode(' ' . $operator . ' ', $parts);
		}

		if (!isset($conditions[1], $conditions[2])) {
			return '';
		}

		$column = $conditions[1];
		if (strpos($column, '(') === false) {
			$column = $this->_connection->quoteColumnName($column);
		}

		$values = $conditions[2];
		if (!is_array($values)) {
			$values = array($values);
		}

		if ($operator === 'IN' || $operator === 'NOT IN') {
			if ($values === array()) {
				return $operator === 'IN' ? '0=1' : '';
			}
			foreach ($values as $i => $value) {
				if (is_string($value)) {
					$values[$i] = $this->_connection->quoteValue($value);
				}
				else {
					$values[$i] = (string)$value;
				}
			}
			return $column . ' ' . $operator . ' (' . implode(', ', $values) . ')';
		}

		if ($operator === 'LIKE' || $operator === 'NOT LIKE' || $operator === 'OR LIKE' || $operator === 'OR NOT LIKE') {
			if ($values === array()) {
				return $operator === 'LIKE' || $operator === 'OR LIKE' ? '0=1' : '';
			}

			if ($operator === 'LIKE' || $operator === 'NOT LIKE') {
				$andor = ' AND ';
			}
			else {
				$andor = ' OR ';
				$operator = $operator === 'OR LIKE' ? 'LIKE' : 'NOT LIKE';
			}
			$expressions = array();
			foreach ($values as $value) {
				$expressions[] = $column . ' ' . $operator . ' ' . $this->_connection->quoteValue($value);
			}
			return implode($andor, $expressions);
		}

		throw new Exception('Unknown operator: ' . $operator);
	}
}
