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
     * 
     * @param array $info column information
     * @return string the comment property for ColumnSchema column object
     */
    protected function getLoadColumnSchemaComment($info)
    {
        return '';
    }
    
    /**
     * 
     * @param string $columnsTableName column table name
     * @param type $whereSql where clause for $this->findColumns()
     * @return string sql for $this->findColumns()
     */
    protected function getFindColumnSQL($columnsTableName, $whereSql)
    {
        return <<<SQL
SELECT
    [t1].[column_name], [t1].[is_nullable], [t1].[data_type], [t1].[column_default],
    COLUMNPROPERTY(OBJECT_ID([t1].[table_schema] + '.' + [t1].[table_name]), [t1].[column_name], 'IsIdentity') AS is_identity
FROM {$columnsTableName} AS [t1]
WHERE {$whereSql}
SQL;
    }
}
