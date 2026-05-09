<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * ConstraintFinderInterface defines methods for getting a table constraint information.
 *
 * @author Sergey Makinen <sergey@makinen.ru>
 * @since 2.0.14
 */
interface ConstraintFinderInterface
{
    /**
     * Obtains the primary key for the named table.
     * @param string $name table name. The table name may contain schema name if any. Do not quote the table name.
     * @param bool $refresh whether to reload the information even if it is found in the cache.
     * @return Constraint|null table primary key, `null` if the table has no primary key.
     */
    public function getTablePrimaryKey($name, $refresh = false);

    /**
     * Returns primary keys for all tables in the database.
     * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema name.
     * @param bool $refresh whether to fetch the latest available table schemas. If this is `false`,
     * cached data may be returned if available.
     * @return Constraint[] primary keys for all tables in the database.
     * Each array element is an instance of [[Constraint]] or its child class.
     */
    public function getSchemaPrimaryKeys($schema = '', $refresh = false);

    /**
     * Obtains the foreign keys information for the named table.
     * @param string $name table name. The table name may contain schema name if any. Do not quote the table name.
     * @param bool $refresh whether to reload the information even if it is found in the cache.
     * @return ForeignKeyConstraint[] table foreign keys.
     */
    public function getTableForeignKeys($name, $refresh = false);

    /**
     * Returns foreign keys for all tables in the database.
     * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema name.
     * @param bool $refresh whether to fetch the latest available table schemas. If this is false,
     * cached data may be returned if available.
     * @return ForeignKeyConstraint[][] foreign keys for all tables in the database.
     * Each array element is an array of [[ForeignKeyConstraint]] or its child classes.
     */
    public function getSchemaForeignKeys($schema = '', $refresh = false);

    /**
     * Obtains the indexes information for the named table.
     * @param string $name table name. The table name may contain schema name if any. Do not quote the table name.
     * @param bool $refresh whether to reload the information even if it is found in the cache.
     * @return IndexConstraint[] table indexes.
     */
    public function getTableIndexes($name, $refresh = false);

    /**
     * Returns indexes for all tables in the database.
     * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema name.
     * @param bool $refresh whether to fetch the latest available table schemas. If this is false,
     * cached data may be returned if available.
     * @return IndexConstraint[][] indexes for all tables in the database.
     * Each array element is an array of [[IndexConstraint]] or its child classes.
     */
    public function getSchemaIndexes($schema = '', $refresh = false);

    /**
     * Obtains the unique constraints information for the named table.
     * @param string $name table name. The table name may contain schema name if any. Do not quote the table name.
     * @param bool $refresh whether to reload the information even if it is found in the cache.
     * @return Constraint[] table unique constraints.
     */
    public function getTableUniques($name, $refresh = false);

    /**
     * Returns unique constraints for all tables in the database.
     * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema name.
     * @param bool $refresh whether to fetch the latest available table schemas. If this is false,
     * cached data may be returned if available.
     * @return Constraint[][] unique constraints for all tables in the database.
     * Each array element is an array of [[Constraint]] or its child classes.
     */
    public function getSchemaUniques($schema = '', $refresh = false);

    /**
     * Obtains the check constraints information for the named table.
     * @param string $name table name. The table name may contain schema name if any. Do not quote the table name.
     * @param bool $refresh whether to reload the information even if it is found in the cache.
     * @return CheckConstraint[] table check constraints.
     */
    public function getTableChecks($name, $refresh = false);

    /**
     * Returns check constraints for all tables in the database.
     * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema name.
     * @param bool $refresh whether to fetch the latest available table schemas. If this is false,
     * cached data may be returned if available.
     * @return CheckConstraint[][] check constraints for all tables in the database.
     * Each array element is an array of [[CheckConstraint]] or its child classes.
     */
    public function getSchemaChecks($schema = '', $refresh = false);

    /**
     * Obtains the default value constraints information for the named table.
     * @param string $name table name. The table name may contain schema name if any. Do not quote the table name.
     * @param bool $refresh whether to reload the information even if it is found in the cache.
     * @return DefaultValueConstraint[] table default value constraints.
     */
    public function getTableDefaultValues($name, $refresh = false);

    /**
     * Returns default value constraints for all tables in the database.
     * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema name.
     * @param bool $refresh whether to fetch the latest available table schemas. If this is false,
     * cached data may be returned if available.
     * @return DefaultValueConstraint[] default value constraints for all tables in the database.
     * Each array element is an array of [[DefaultValueConstraint]] or its child classes.
     */
    public function getSchemaDefaultValues($schema = '', $refresh = false);
}
