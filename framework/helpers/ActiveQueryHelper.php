<?php

namespace yii\helpers;

use yii\db\ActiveQuery;
use yii\base\InvalidConfigException;

class ActiveQueryHelper
{
    /**
     * Table names calculate on from.
     * @param ActiveQuery $query
     * @return string[] table names
     */
    public static function getTableNames(ActiveQuery $query)
    {
        $tableNames = [];
        $from = $query->from;

        if (empty($from)) {
            $tableNames[] = self::getTableNameForModel($query);
        } elseif (is_array($from)) {
            $tableNames = array_values($from);
        } elseif (is_string($from)) {
            $tableNames = preg_split('/\s*,\s*/', trim($from), -1, PREG_SPLIT_NO_EMPTY);
        } else {
            self::generateNotSupportedTypeFrom($from);
        }

        // Clear table alias.
        foreach ($tableNames as &$tableName) {
            $tableName = preg_replace('/^(\w+)\s*.*$/', '$1', $tableName);
        }

        return $tableNames;
    }

    /**
     * Tables alias calculate on from.
     * @param ActiveQuery $query
     * @return string[] table alias
     */
    public static function getTablesAlias(ActiveQuery $query)
    {
        $tablesAlias = [];
        $from = $query->from;

        if (empty($from)) {
            return [self::getTableNameForModel($query)];
        }

        if (is_string($from)) {
           $tableNames = preg_split('/\s*,\s*/', trim($from), -1, PREG_SPLIT_NO_EMPTY);
        } elseif (is_array($from)) {
            $tableNames = $from;
        } else {
            self::generateNotSupportedTypeFrom($from);
        }

        foreach ($tableNames as $alias => $tableName) {
            if (is_string($alias)) {
                $tablesAlias[] = $alias;
            } else {
                $tablesAlias[] = self::getAliasForTableName($tableName);
            }
        }

        return $tablesAlias;
    }

    /**
     * @param string $tableName
     * @return string
     */
    private static function getAliasForTableName($tableName)
    {
        $cleanedTableName = preg_replace('/\'|\"|`|as/u', '', trim($tableName));

        $alias = preg_replace('/^.+\s+(\w+)$/', '$1', $cleanedTableName);

        return $alias;
    }

    /**
     * Table name get Model.
     * @param ActiveQuery $query
     * @return type
     */
    private static function getTableNameForModel(ActiveQuery $query)
    {
        /* @var $modelClass ActiveRecord */
        $modelClass = $query->modelClass;

        return $modelClass::tableName();
    }

    /**
     * @param mixed $from
     * @throws InvalidConfigException
     */
    private static function generateNotSupportedTypeFrom($from)
    {
        $error = sprintf('Not supported type "$s"', gettype($from));

        throw new InvalidConfigException($error);
    }
}
