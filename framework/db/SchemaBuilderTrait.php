<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use Yii;
use yii\base\Object;
use yii\di\Instance;

trait SchemaBuilderTrait
{

    private static $dbName = 'db';

    private static $schemaBuilderMap = [
        'pgsql' => 'yii\db\pgsql\SchemaBuilder', // PostgreSQL
        'mysqli' => 'yii\db\mysql\SchemaBuilder', // MySQL
        'mysql' => 'yii\db\mysql\SchemaBuilder', // MySQL
        'sqlite' => 'yii\db\sqlite\SchemaBuilder', // sqlite 3
        'sqlite2' => 'yii\db\sqlite\SchemaBuilder', // sqlite 2
        'sqlsrv' => 'yii\db\mssql\SchemaBuilder', // newer MSSQL driver on MS Windows hosts
        'oci' => 'yii\db\oci\SchemaBuilder', // Oracle driver
        'mssql' => 'yii\db\mssql\SchemaBuilder', // older MSSQL driver on MS Windows hosts
        'dblib' => 'yii\db\mssql\SchemaBuilder', // dblib drivers on GNU/Linux (and maybe other OSes) hosts
        'cubrid' => 'yii\db\cubrid\SchemaBuilder', // CUBRID
    ];

    public static function setDb($dbName)
    {
        self::$dbName = $dbName;
    }

    public static function primaryKey($length = null)
    {
        return forward_static_call_array([self::getClass(), __FUNCTION__], func_get_args());
    }

    public static function bigPrimaryKey($length = null)
    {
        return forward_static_call_array([self::getClass(), __FUNCTION__], func_get_args());
    }

    public static function string($length = null)
    {
        return forward_static_call_array([self::getClass(), __FUNCTION__], func_get_args());
    }

    public static function text($length = null)
    {
        return forward_static_call_array([self::getClass(), __FUNCTION__], func_get_args());
    }

    public static function smallint($length = null)
    {
        return forward_static_call_array([self::getClass(), __FUNCTION__], func_get_args());
    }

    public static function integer($length = null)
    {
        return forward_static_call_array([self::getClass(), __FUNCTION__], func_get_args());
    }

    public static function bigint($length = null)
    {
        return forward_static_call_array([self::getClass(), __FUNCTION__], func_get_args());
    }

    public static function float($precision = null, $scale = null)
    {
        return forward_static_call_array([self::getClass(), __FUNCTION__], func_get_args());
    }

    public static function double($precision = null, $scale = null)
    {
        return forward_static_call_array([self::getClass(), __FUNCTION__], func_get_args());
    }

    public static function decimal($precision = null, $scale = null)
    {
        return forward_static_call_array([self::getClass(), __FUNCTION__], func_get_args());
    }

    public static function datetime()
    {
        return forward_static_call_array([self::getClass(), __FUNCTION__], func_get_args());
    }

    public static function timestamp()
    {
        return forward_static_call_array([self::getClass(), __FUNCTION__], func_get_args());
    }

    public static function time()
    {
        return forward_static_call_array([self::getClass(), __FUNCTION__], func_get_args());
    }

    public static function date()
    {
        return forward_static_call_array([self::getClass(), __FUNCTION__], func_get_args());
    }

    public static function binary($length = null)
    {
        return forward_static_call_array([self::getClass(), __FUNCTION__], func_get_args());
    }

    public static function boolean($length = null)
    {
        return forward_static_call_array([self::getClass(), __FUNCTION__], func_get_args());
    }

    public static function money($precision = null, $scale = null)
    {
        return forward_static_call_array([self::getClass(), __FUNCTION__], func_get_args());
    }

    private static function getClass()
    {
        $driverName = Instance::ensure(self::$dbName, Connection::className())->getDriverName();

        return self::$schemaBuilderMap[$driverName];
    }
}
