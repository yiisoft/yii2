<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

use yii\db\ColumnSchema;

/**
 * Simple Schema class for accessing Microsoft Azure hosted databases.
 *
 * @author Andrew Kehrig <me@andrewkehrig.com>
 */
class AzureSchema extends yii\db\mssql\Schema {
    
    /**
     * Loads the column information into a [[ColumnSchema]] object.
     * @param array $info column information
     * @return ColumnSchema the column schema object
     */
    protected function loadColumnSchema($info)
    {
        $column = $this->createColumnSchema();

        $column->name = $info['column_name'];
        $column->allowNull = $info['is_nullable'] == 'YES';
        $column->dbType = $info['data_type'];
        $column->enumValues = []; // mssql has only vague equivalents to enum
        $column->isPrimaryKey = null; // primary key will be determined in findColumns() method
        $column->autoIncrement = $info['is_identity'] == 1;
        $column->unsigned = stripos($column->dbType, 'unsigned') !== false;
        $column->comment = '';

        $column->type = self::TYPE_STRING;
        if (preg_match('/^(\w+)(?:\(([^\)]+)\))?/', $column->dbType, $matches)) {
            $type = $matches[1];
            if (isset($this->typeMap[$type])) {
                $column->type = $this->typeMap[$type];
            }
            if (!empty($matches[2])) {
                $values = explode(',', $matches[2]);
                $column->size = $column->precision = (int) $values[0];
                if (isset($values[1])) {
                    $column->scale = (int) $values[1];
                }
                if ($column->size === 1 && ($type === 'tinyint' || $type === 'bit')) {
                    $column->type = 'boolean';
                } elseif ($type === 'bit') {
                    if ($column->size > 32) {
                        $column->type = 'bigint';
                    } elseif ($column->size === 32) {
                        $column->type = 'integer';
                    }
                }
            }
        }

        $column->phpType = $this->getColumnPhpType($column);

        if ($info['column_default'] == '(NULL)') {
            $info['column_default'] = null;
        }
        if (!$column->isPrimaryKey && ($column->type !== 'timestamp' || $info['column_default'] !== 'CURRENT_TIMESTAMP')) {
            $column->defaultValue = $column->phpTypecast($info['column_default']);
        }

        return $column;
    }
    
    /**
     * Collects the metadata of table columns.
     * @param TableSchema $table the table metadata
     * @return boolean whether the table exists in the database
     */
    protected function findColumns($table)
    {
        $columnsTableName = 'INFORMATION_SCHEMA.COLUMNS';
        $whereSql = "[t1].[table_name] = '{$table->name}'";
        if ($table->catalogName !== null) {
            $columnsTableName = "{$table->catalogName}.{$columnsTableName}";
            $whereSql .= " AND [t1].[table_catalog] = '{$table->catalogName}'";
        }
        if ($table->schemaName !== null) {
            $whereSql .= " AND [t1].[table_schema] = '{$table->schemaName}'";
        }
        $columnsTableName = $this->quoteTableName($columnsTableName);
        $sql = <<<SQL
SELECT
    [t1].[column_name], [t1].[is_nullable], [t1].[data_type], [t1].[column_default],
    COLUMNPROPERTY(OBJECT_ID([t1].[table_schema] + '.' + [t1].[table_name]), [t1].[column_name], 'IsIdentity') AS is_identity
FROM {$columnsTableName} AS [t1]
WHERE {$whereSql}
SQL;
        try {
            $columns = $this->db->createCommand($sql)->queryAll();
            if (empty($columns)) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
        foreach ($columns as $column) {
            $column = $this->loadColumnSchema($column);
            foreach ($table->primaryKey as $primaryKey) {
                if (strcasecmp($column->name, $primaryKey) === 0) {
                    $column->isPrimaryKey = true;
                    break;
                }
            }
            if ($column->isPrimaryKey && $column->autoIncrement) {
                $table->sequenceName = '';
            }
            $table->columns[$column->name] = $column;
        }

        return true;
    }
}
