<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * ConstraintFinderTrait provides methods for getting a table constraint information.
 *
 * @property CheckConstraint[][] $schemaChecks Check constraints for all tables in the database.
 * Each array element is an array of [[CheckConstraint]] or its child classes. This property is read-only.
 * @property DefaultValueConstraint[] $schemaDefaultValues Default value constraints for all tables in the database.
 * Each array element is an array of [[DefaultValueConstraint]] or its child classes. This property is read-only.
 * @property ForeignKeyConstraint[][] $schemaForeignKeys Foreign keys for all tables in the database. Each
 * array element is an array of [[ForeignKeyConstraint]] or its child classes. This property is read-only.
 * @property IndexConstraint[][] $schemaIndexes Indexes for all tables in the database. Each array element is
 * an array of [[IndexConstraint]] or its child classes. This property is read-only.
 * @property Constraint[] $schemaPrimaryKeys Primary keys for all tables in the database. Each array element
 * is an instance of [[Constraint]] or its child class. This property is read-only.
 * @property IndexConstraint[][] $schemaUniques Unique constraints for all tables in the database.
 * Each array element is an array of [[IndexConstraint]] or its child classes. This property is read-only.
 *
 * @author Sergey Makinen <sergey@makinen.ru>
 * @since 2.0.13
 */
trait ConstraintFinderTrait
{
    /**
     * Returns the metadata of the given type for the given table.
     * @param string $name table name. The table name may contain schema name if any. Do not quote the table name.
     * @param string $type metadata type.
     * @param bool $refresh whether to reload the table metadata even if it is found in the cache.
     * @return mixed metadata.
     */
    abstract protected function getTableMetadata($name, $type, $refresh);

    /**
     * Returns the metadata of the given type for all tables in the given schema.
     * @param string $schema the schema of the metadata. Defaults to empty string, meaning the current or default schema name.
     * @param string $type metadata type.
     * @param bool $refresh whether to fetch the latest available table metadata. If this is `false`,
     * cached data may be returned if available.
     * @return array array of metadata.
     */
    abstract protected function getSchemaMetadata($schema, $type, $refresh);

    /**
     * Loads a primary key for the given table.
     * @param string $tableName table name.
     * @return Constraint|null primary key for the given table, `null` if the table has no primary key.
     */
    abstract protected function loadTablePrimaryKey($tableName);

    /**
     * Loads all foreign keys for the given table.
     * @param string $tableName table name.
     * @return ForeignKeyConstraint[] foreign keys for the given table.
     */
    abstract protected function loadTableForeignKeys($tableName);

    /**
     * Loads all indexes for the given table.
     * @param string $tableName table name.
     * @return IndexConstraint[] indexes for the given table.
     */
    abstract protected function loadTableIndexes($tableName);

    /**
     * Loads all unique constraints for the given table.
     * @param string $tableName table name.
     * @return Constraint[] unique constraints for the given table.
     */
    abstract protected function loadTableUniques($tableName);

    /**
     * Loads all check constraints for the given table.
     * @param string $tableName table name.
     * @return CheckConstraint[] check constraints for the given table.
     */
    abstract protected function loadTableChecks($tableName);

    /**
     * Loads all default value constraints for the given table.
     *
     * @param string $tableName table name.
     * @return DefaultValueConstraint[] default value constraints for the given table.
     */
    abstract protected function loadTableDefaultValues($tableName);

    /**
     * Obtains the primary key for the named table.
     * @param string $name table name. The table name may contain schema name if any. Do not quote the table name.
     * @param bool $refresh whether to reload the information even if it is found in the cache.
     * @return Constraint|null table primary key, `null` if the table has no primary key.
     */
    public function getTablePrimaryKey($name, $refresh = false)
    {
        return $this->getTableMetadata($name, 'primaryKey', $refresh);
    }

    /**
     * Returns primary keys for all tables in the database.
     * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema name.
     * @param bool $refresh whether to fetch the latest available table schemas. If this is `false`,
     * cached data may be returned if available.
     * @return Constraint[] primary keys for all tables in the database.
     * Each array element is an instance of [[Constraint]] or its child class.
     */
    public function getSchemaPrimaryKeys($schema = '', $refresh = false)
    {
        return $this->getSchemaMetadata($schema, 'primaryKey', $refresh);
    }

    /**
     * Obtains the foreign keys information for the named table.
     * @param string $name table name. The table name may contain schema name if any. Do not quote the table name.
     * @param bool $refresh whether to reload the information even if it is found in the cache.
     * @return ForeignKeyConstraint[] table foreign keys.
     */
    public function getTableForeignKeys($name, $refresh = false)
    {
        return $this->getTableMetadata($name, 'foreignKeys', $refresh);
    }

    /**
     * Returns foreign keys for all tables in the database.
     * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema name.
     * @param bool $refresh whether to fetch the latest available table schemas. If this is false,
     * cached data may be returned if available.
     * @return ForeignKeyConstraint[][] foreign keys for all tables in the database.
     * Each array element is an array of [[ForeignKeyConstraint]] or its child classes.
     */
    public function getSchemaForeignKeys($schema = '', $refresh = false)
    {
        return $this->getSchemaMetadata($schema, 'foreignKeys', $refresh);
    }

    /**
     * Obtains the indexes information for the named table.
     * @param string $name table name. The table name may contain schema name if any. Do not quote the table name.
     * @param bool $refresh whether to reload the information even if it is found in the cache.
     * @return IndexConstraint[] table indexes.
     */
    public function getTableIndexes($name, $refresh = false)
    {
        return $this->getTableMetadata($name, 'indexes', $refresh);
    }

    /**
     * Returns indexes for all tables in the database.
     * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema name.
     * @param bool $refresh whether to fetch the latest available table schemas. If this is false,
     * cached data may be returned if available.
     * @return IndexConstraint[][] indexes for all tables in the database.
     * Each array element is an array of [[IndexConstraint]] or its child classes.
     */
    public function getSchemaIndexes($schema = '', $refresh = false)
    {
        return $this->getSchemaMetadata($schema, 'indexes', $refresh);
    }

    /**
     * Obtains the unique constraints information for the named table.
     * @param string $name table name. The table name may contain schema name if any. Do not quote the table name.
     * @param bool $refresh whether to reload the information even if it is found in the cache.
     * @return Constraint[] table unique constraints.
     */
    public function getTableUniques($name, $refresh = false)
    {
        return $this->getTableMetadata($name, 'uniques', $refresh);
    }

    /**
     * Returns unique constraints for all tables in the database.
     * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema name.
     * @param bool $refresh whether to fetch the latest available table schemas. If this is false,
     * cached data may be returned if available.
     * @return Constraint[][] unique constraints for all tables in the database.
     * Each array element is an array of [[Constraint]] or its child classes.
     */
    public function getSchemaUniques($schema = '', $refresh = false)
    {
        return $this->getSchemaMetadata($schema, 'uniques', $refresh);
    }

    /**
     * Obtains the check constraints information for the named table.
     * @param string $name table name. The table name may contain schema name if any. Do not quote the table name.
     * @param bool $refresh whether to reload the information even if it is found in the cache.
     * @return CheckConstraint[] table check constraints.
     */
    public function getTableChecks($name, $refresh = false)
    {
        return $this->getTableMetadata($name, 'checks', $refresh);
    }

    /**
     * Returns check constraints for all tables in the database.
     * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema name.
     * @param bool $refresh whether to fetch the latest available table schemas. If this is false,
     * cached data may be returned if available.
     * @return CheckConstraint[][] check constraints for all tables in the database.
     * Each array element is an array of [[CheckConstraint]] or its child classes.
     */
    public function getSchemaChecks($schema = '', $refresh = false)
    {
        return $this->getSchemaMetadata($schema, 'checks', $refresh);
    }

    /**
     * Obtains the default value constraints information for the named table.
     * @param string $name table name. The table name may contain schema name if any. Do not quote the table name.
     * @param bool $refresh whether to reload the information even if it is found in the cache.
     * @return DefaultValueConstraint[] table default value constraints.
     */
    public function getTableDefaultValues($name, $refresh = false)
    {
        return $this->getTableMetadata($name, 'defaultValues', $refresh);
    }

    /**
     * Returns default value constraints for all tables in the database.
     * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema name.
     * @param bool $refresh whether to fetch the latest available table schemas. If this is false,
     * cached data may be returned if available.
     * @return DefaultValueConstraint[] default value constraints for all tables in the database.
     * Each array element is an array of [[DefaultValueConstraint]] or its child classes.
     */
    public function getSchemaDefaultValues($schema = '', $refresh = false)
    {
        return $this->getSchemaMetadata($schema, 'defaultValues', $refresh);
    }
}
