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

/**
 * SchemaBuilderTrait
 *
 * @author Vasenin Matvey <vaseninm@gmail.com>
 * @since 2.0.5
 */
trait SchemaBuilderTrait
{
    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection
     */
    private static $_dbName = 'db';

    /**
     * @var array mapping between PDO driver names and [[SchemaBuilder]] classes.
     * The keys of the array are PDO driver names while the values the corresponding
     * SchemaBuilder class name.
     */
    private static $_schemaBuilderMap = [
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
        self::$_dbName = $dbName;
    }

    public static function __callStatic($name, $arguments)
    {
        return forward_static_call_array([self::getClass(), $name], $arguments);
    }

    /**
     * Determines the SchemaBuilder for the $_dbName value.
     *
     * @return SchemaBuilder
     */
    private static function getClass()
    {
        $driverName = Instance::ensure(self::$_dbName, Connection::className())->getDriverName();

        return self::$_schemaBuilderMap[$driverName];
    }
}
