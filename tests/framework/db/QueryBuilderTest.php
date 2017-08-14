<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db;

use yii\db\cubrid\QueryBuilder as CubridQueryBuilder;
use yii\db\Expression;
use yii\db\mssql\QueryBuilder as MssqlQueryBuilder;
use yii\db\mysql\QueryBuilder as MysqlQueryBuilder;
use yii\db\oci\QueryBuilder as OracleQueryBuilder;
use yii\db\pgsql\QueryBuilder as PgsqlQueryBuilder;
use yii\db\Query;
use yii\db\QueryBuilder;
use yii\db\Schema;
use yii\db\SchemaBuilderTrait;
use yii\db\sqlite\QueryBuilder as SqliteQueryBuilder;
use yiiunit\data\base\TraversableObject;

abstract class QueryBuilderTest extends DatabaseTestCase
{
    use SchemaBuilderTrait;

    /**
     * @var string ` ESCAPE 'char'` part of a LIKE condition SQL.
     */
    protected $likeEscapeCharSql = '';
    /**
     * @var array map of values to their replacements in LIKE query params.
     */
    protected $likeParameterReplacements = [];

    public function getDb()
    {
        return $this->getConnection(false, false);
    }

    /**
     * @throws \Exception
     * @return QueryBuilder
     */
    protected function getQueryBuilder($reset = true, $open = false)
    {
        $connection = $this->getConnection($reset, $open);

        \Yii::$container->set('db', $connection);

        switch ($this->driverName) {
            case 'mysql':
                return new MysqlQueryBuilder($connection);
            case 'sqlite':
                return new SqliteQueryBuilder($connection);
            case 'sqlsrv':
                return new MssqlQueryBuilder($connection);
            case 'pgsql':
                return new PgsqlQueryBuilder($connection);
            case 'cubrid':
                return new CubridQueryBuilder($connection);
            case 'oci':
                return new OracleQueryBuilder($connection);
        }
        throw new \Exception('Test is not implemented for ' . $this->driverName);
    }

    /**
     * this is not used as a dataprovider for testGetColumnType to speed up the test
     * when used as dataprovider every single line will cause a reconnect with the database which is not needed here
     */
    public function columnTypes()
    {
        $items = [
            [
                Schema::TYPE_BIGINT,
                $this->bigInteger(),
                [
                    'mysql' => 'bigint(20)',
                    'postgres' => 'bigint',
                    'sqlite' => 'bigint',
                    'oci' => 'NUMBER(20)',
                    'sqlsrv' => 'bigint',
                    'cubrid' => 'bigint',
                ],
            ],
            [
                Schema::TYPE_BIGINT . ' NOT NULL',
                $this->bigInteger()->notNull(),
                [
                    'mysql' => 'bigint(20) NOT NULL',
                    'postgres' => 'bigint NOT NULL',
                    'sqlite' => 'bigint NOT NULL',
                    'oci' => 'NUMBER(20) NOT NULL',
                    'sqlsrv' => 'bigint NOT NULL',
                    'cubrid' => 'bigint NOT NULL',
                ],
            ],
            [
                Schema::TYPE_BIGINT . ' CHECK (value > 5)',
                $this->bigInteger()->check('value > 5'),
                [
                    'mysql' => 'bigint(20) CHECK (value > 5)',
                    'postgres' => 'bigint CHECK (value > 5)',
                    'sqlite' => 'bigint CHECK (value > 5)',
                    'oci' => 'NUMBER(20) CHECK (value > 5)',
                    'sqlsrv' => 'bigint CHECK (value > 5)',
                    'cubrid' => 'bigint CHECK (value > 5)',
                ],
            ],
            [
                Schema::TYPE_BIGINT . '(8)',
                $this->bigInteger(8),
                [
                    'mysql' => 'bigint(8)',
                    'postgres' => 'bigint',
                    'sqlite' => 'bigint',
                    'oci' => 'NUMBER(8)',
                    'sqlsrv' => 'bigint',
                    'cubrid' => 'bigint',
                ],
            ],
            [
                Schema::TYPE_BIGINT . '(8) CHECK (value > 5)',
                $this->bigInteger(8)->check('value > 5'),
                [
                    'mysql' => 'bigint(8) CHECK (value > 5)',
                    'postgres' => 'bigint CHECK (value > 5)',
                    'sqlite' => 'bigint CHECK (value > 5)',
                    'oci' => 'NUMBER(8) CHECK (value > 5)',
                    'sqlsrv' => 'bigint CHECK (value > 5)',
                    'cubrid' => 'bigint CHECK (value > 5)',
                ],
            ],
            [
                Schema::TYPE_BIGPK,
                $this->bigPrimaryKey(),
                [
                    'mysql' => 'bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY',
                    'postgres' => 'bigserial NOT NULL PRIMARY KEY',
                    'sqlite' => 'integer PRIMARY KEY AUTOINCREMENT NOT NULL',
                ],
            ],
            [
                Schema::TYPE_BINARY,
                $this->binary(),
                [
                    'mysql' => 'blob',
                    'postgres' => 'bytea',
                    'sqlite' => 'blob',
                    'oci' => 'BLOB',
                    'sqlsrv' => 'varbinary(max)',
                    'cubrid' => 'blob',
                ],
            ],
            [
                Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
                $this->boolean()->notNull()->defaultValue(1),
                [
                    'mysql' => 'tinyint(1) NOT NULL DEFAULT 1',
                    'sqlite' => 'boolean NOT NULL DEFAULT 1',
                    'sqlsrv' => 'tinyint(1) NOT NULL DEFAULT 1',
                    'cubrid' => 'smallint NOT NULL DEFAULT 1',
                ],
            ],
            [
                Schema::TYPE_BOOLEAN,
                $this->boolean(),
                [
                    'mysql' => 'tinyint(1)',
                    'postgres' => 'boolean',
                    'sqlite' => 'boolean',
                    'oci' => 'NUMBER(1)',
                    'sqlsrv' => 'tinyint(1)',
                    'cubrid' => 'smallint',
                ],
            ],
            [
                Schema::TYPE_CHAR . ' CHECK (value LIKE "test%")',
                $this->char()->check('value LIKE "test%"'),
                [
                    'mysql' => 'char(1) CHECK (value LIKE "test%")',
                    'sqlite' => 'char(1) CHECK (value LIKE "test%")',
                    'cubrid' => 'char(1) CHECK (value LIKE "test%")',
                ],
            ],
            [
                Schema::TYPE_CHAR . ' NOT NULL',
                $this->char()->notNull(),
                [
                    'mysql' => 'char(1) NOT NULL',
                    'postgres' => 'char(1) NOT NULL',
                    'sqlite' => 'char(1) NOT NULL',
                    'oci' => 'CHAR(1) NOT NULL',
                    'cubrid' => 'char(1) NOT NULL',
                ],
            ],
            [
                Schema::TYPE_CHAR . '(6) CHECK (value LIKE "test%")',
                $this->char(6)->check('value LIKE "test%"'),
                [
                    'mysql' => 'char(6) CHECK (value LIKE "test%")',
                    'sqlite' => 'char(6) CHECK (value LIKE "test%")',
                    'cubrid' => 'char(6) CHECK (value LIKE "test%")',
                ],
            ],
            [
                Schema::TYPE_CHAR . '(6)',
                $this->char(6),
                [
                    'mysql' => 'char(6)',
                    'postgres' => 'char(6)',
                    'sqlite' => 'char(6)',
                    'oci' => 'CHAR(6)',
                    'cubrid' => 'char(6)',
                ],
            ],
            [
                Schema::TYPE_CHAR,
                $this->char(),
                [
                    'mysql' => 'char(1)',
                    'postgres' => 'char(1)',
                    'sqlite' => 'char(1)',
                    'oci' => 'CHAR(1)',
                    'cubrid' => 'char(1)',
                ],
            ],
            //[
            //    Schema::TYPE_DATE . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')",
            //    $this->date()->check("value BETWEEN '2011-01-01' AND '2013-01-01'"),
            //    [
            //        'mysql' => ,
            //        'postgres' => ,
            //        'sqlite' => ,
            //        'sqlsrv' => ,
            //        'cubrid' => ,
            //    ],
            //],
            [
                Schema::TYPE_DATE . ' NOT NULL',
                $this->date()->notNull(),
                [
                    'mysql' => 'date NOT NULL',
                    'postgres' => 'date NOT NULL',
                    'sqlite' => 'date NOT NULL',
                    'oci' => 'DATE NOT NULL',
                    'sqlsrv' => 'date NOT NULL',
                    'cubrid' => 'date NOT NULL',
                ],
            ],
            [
                Schema::TYPE_DATE,
                $this->date(),
                [
                    'mysql' => 'date',
                    'postgres' => 'date',
                    'sqlite' => 'date',
                    'oci' => 'DATE',
                    'sqlsrv' => 'date',
                    'cubrid' => 'date',
                ],
            ],
            //[
            //    Schema::TYPE_DATETIME . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')",
            //    $this->dateTime()->check("value BETWEEN '2011-01-01' AND '2013-01-01'"),
            //    [
            //        'mysql' => ,
            //        'postgres' => ,
            //        'sqlite' => ,
            //        'sqlsrv' => ,
            //        'cubrid' => ,
            //    ],
            //],
            [
                Schema::TYPE_DATETIME . ' NOT NULL',
                $this->dateTime()->notNull(),
                [
                    'mysql' => 'datetime NOT NULL',
                    'postgres' => 'timestamp(0) NOT NULL',
                    'sqlite' => 'datetime NOT NULL',
                    'oci' => 'TIMESTAMP NOT NULL',
                    'sqlsrv' => 'datetime NOT NULL',
                    'cubrid' => 'datetime NOT NULL',
                ],
            ],
            [
                Schema::TYPE_DATETIME,
                $this->dateTime(),
                [
                    'mysql' => 'datetime',
                    'postgres' => 'timestamp(0)',
                    'sqlite' => 'datetime',
                    'oci' => 'TIMESTAMP',
                    'sqlsrv' => 'datetime',
                    'cubrid' => 'datetime',
                ],
            ],
            [
                Schema::TYPE_DECIMAL . ' CHECK (value > 5.6)',
                $this->decimal()->check('value > 5.6'),
                [
                    'mysql' => 'decimal(10,0) CHECK (value > 5.6)',
                    'postgres' => 'numeric(10,0) CHECK (value > 5.6)',
                    'sqlite' => 'decimal(10,0) CHECK (value > 5.6)',
                    'oci' => 'NUMBER CHECK (value > 5.6)',
                    'sqlsrv' => 'decimal(18,0) CHECK (value > 5.6)',
                    'cubrid' => 'decimal(10,0) CHECK (value > 5.6)',
                ],
            ],
            [
                Schema::TYPE_DECIMAL . ' NOT NULL',
                $this->decimal()->notNull(),
                [
                    'mysql' => 'decimal(10,0) NOT NULL',
                    'postgres' => 'numeric(10,0) NOT NULL',
                    'sqlite' => 'decimal(10,0) NOT NULL',
                    'oci' => 'NUMBER NOT NULL',
                    'sqlsrv' => 'decimal(18,0) NOT NULL',
                    'cubrid' => 'decimal(10,0) NOT NULL',
                ],
            ],
            [
                Schema::TYPE_DECIMAL . '(12,4) CHECK (value > 5.6)',
                $this->decimal(12, 4)->check('value > 5.6'),
                [
                    'mysql' => 'decimal(12,4) CHECK (value > 5.6)',
                    'postgres' => 'numeric(12,4) CHECK (value > 5.6)',
                    'sqlite' => 'decimal(12,4) CHECK (value > 5.6)',
                    'oci' => 'NUMBER CHECK (value > 5.6)',
                    'sqlsrv' => 'decimal(12,4) CHECK (value > 5.6)',
                    'cubrid' => 'decimal(12,4) CHECK (value > 5.6)',
                ],
            ],
            [
                Schema::TYPE_DECIMAL . '(12,4)',
                $this->decimal(12, 4),
                [
                    'mysql' => 'decimal(12,4)',
                    'postgres' => 'numeric(12,4)',
                    'sqlite' => 'decimal(12,4)',
                    'oci' => 'NUMBER',
                    'sqlsrv' => 'decimal(12,4)',
                    'cubrid' => 'decimal(12,4)',
                ],
            ],
            [
                Schema::TYPE_DECIMAL,
                $this->decimal(),
                [
                    'mysql' => 'decimal(10,0)',
                    'postgres' => 'numeric(10,0)',
                    'sqlite' => 'decimal(10,0)',
                    'oci' => 'NUMBER',
                    'sqlsrv' => 'decimal(18,0)',
                    'cubrid' => 'decimal(10,0)',
                ],
            ],
            [
                Schema::TYPE_DOUBLE . ' CHECK (value > 5.6)',
                $this->double()->check('value > 5.6'),
                [
                    'mysql' => 'double CHECK (value > 5.6)',
                    'postgres' => 'double precision CHECK (value > 5.6)',
                    'sqlite' => 'double CHECK (value > 5.6)',
                    'oci' => 'NUMBER CHECK (value > 5.6)',
                    'sqlsrv' => 'float CHECK (value > 5.6)',
                    'cubrid' => 'double(15) CHECK (value > 5.6)',
                ],
            ],
            [
                Schema::TYPE_DOUBLE . ' NOT NULL',
                $this->double()->notNull(),
                [
                    'mysql' => 'double NOT NULL',
                    'postgres' => 'double precision NOT NULL',
                    'sqlite' => 'double NOT NULL',
                    'oci' => 'NUMBER NOT NULL',
                    'sqlsrv' => 'float NOT NULL',
                    'cubrid' => 'double(15) NOT NULL',
                ],
            ],
            [
                Schema::TYPE_DOUBLE . '(16) CHECK (value > 5.6)',
                $this->double(16)->check('value > 5.6'),
                [
                    'mysql' => 'double CHECK (value > 5.6)',
                    'postgres' => 'double precision CHECK (value > 5.6)',
                    'sqlite' => 'double CHECK (value > 5.6)',
                    'oci' => 'NUMBER CHECK (value > 5.6)',
                    'sqlsrv' => 'float CHECK (value > 5.6)',
                    'cubrid' => 'double(16) CHECK (value > 5.6)',
                ],
            ],
            [
                Schema::TYPE_DOUBLE . '(16)',
                $this->double(16),
                [
                    'mysql' => 'double',
                    'sqlite' => 'double',
                    'oci' => 'NUMBER',
                    'sqlsrv' => 'float',
                    'cubrid' => 'double(16)',
                ],
            ],
            [
                Schema::TYPE_DOUBLE,
                $this->double(),
                [
                    'mysql' => 'double',
                    'postgres' => 'double precision',
                    'sqlite' => 'double',
                    'oci' => 'NUMBER',
                    'sqlsrv' => 'float',
                    'cubrid' => 'double(15)',
                ],
            ],
            [
                Schema::TYPE_FLOAT . ' CHECK (value > 5.6)',
                $this->float()->check('value > 5.6'),
                [
                    'mysql' => 'float CHECK (value > 5.6)',
                    'postgres' => 'double precision CHECK (value > 5.6)',
                    'sqlite' => 'float CHECK (value > 5.6)',
                    'oci' => 'NUMBER CHECK (value > 5.6)',
                    'sqlsrv' => 'float CHECK (value > 5.6)',
                    'cubrid' => 'float(7) CHECK (value > 5.6)',
                ],
            ],
            [
                Schema::TYPE_FLOAT . ' NOT NULL',
                $this->float()->notNull(),
                [
                    'mysql' => 'float NOT NULL',
                    'postgres' => 'double precision NOT NULL',
                    'sqlite' => 'float NOT NULL',
                    'oci' => 'NUMBER NOT NULL',
                    'sqlsrv' => 'float NOT NULL',
                    'cubrid' => 'float(7) NOT NULL',
                ],
            ],
            [
                Schema::TYPE_FLOAT . '(16) CHECK (value > 5.6)',
                $this->float(16)->check('value > 5.6'),
                [
                    'mysql' => 'float CHECK (value > 5.6)',
                    'postgres' => 'double precision CHECK (value > 5.6)',
                    'sqlite' => 'float CHECK (value > 5.6)',
                    'oci' => 'NUMBER CHECK (value > 5.6)',
                    'sqlsrv' => 'float CHECK (value > 5.6)',
                    'cubrid' => 'float(16) CHECK (value > 5.6)',
                ],
            ],
            [
                Schema::TYPE_FLOAT . '(16)',
                $this->float(16),
                [
                    'mysql' => 'float',
                    'sqlite' => 'float',
                    'oci' => 'NUMBER',
                    'sqlsrv' => 'float',
                    'cubrid' => 'float(16)',
                ],
            ],
            [
                Schema::TYPE_FLOAT,
                $this->float(),
                [
                    'mysql' => 'float',
                    'postgres' => 'double precision',
                    'sqlite' => 'float',
                    'oci' => 'NUMBER',
                    'sqlsrv' => 'float',
                    'cubrid' => 'float(7)',
                ],
            ],
            [
                Schema::TYPE_INTEGER . ' CHECK (value > 5)',
                $this->integer()->check('value > 5'),
                [
                    'mysql' => 'int(11) CHECK (value > 5)',
                    'postgres' => 'integer CHECK (value > 5)',
                    'sqlite' => 'integer CHECK (value > 5)',
                    'oci' => 'NUMBER(10) CHECK (value > 5)',
                    'sqlsrv' => 'int CHECK (value > 5)',
                    'cubrid' => 'int CHECK (value > 5)',
                ],
            ],
            [
                Schema::TYPE_INTEGER . ' NOT NULL',
                $this->integer()->notNull(),
                [
                    'mysql' => 'int(11) NOT NULL',
                    'postgres' => 'integer NOT NULL',
                    'sqlite' => 'integer NOT NULL',
                    'oci' => 'NUMBER(10) NOT NULL',
                    'sqlsrv' => 'int NOT NULL',
                    'cubrid' => 'int NOT NULL',
                ],
            ],
            [
                Schema::TYPE_INTEGER . '(8) CHECK (value > 5)',
                $this->integer(8)->check('value > 5'),
                [
                    'mysql' => 'int(8) CHECK (value > 5)',
                    'postgres' => 'integer CHECK (value > 5)',
                    'sqlite' => 'integer CHECK (value > 5)',
                    'oci' => 'NUMBER(8) CHECK (value > 5)',
                    'sqlsrv' => 'int CHECK (value > 5)',
                    'cubrid' => 'int CHECK (value > 5)',
                ],
            ],
            [
                Schema::TYPE_INTEGER . '(8)',
                $this->integer(8),
                [
                    'mysql' => 'int(8)',
                    'postgres' => 'integer',
                    'sqlite' => 'integer',
                    'oci' => 'NUMBER(8)',
                    'sqlsrv' => 'int',
                    'cubrid' => 'int',
                ],
            ],
            [
                Schema::TYPE_INTEGER,
                $this->integer(),
                [
                    'mysql' => 'int(11)',
                    'postgres' => 'integer',
                    'sqlite' => 'integer',
                    'oci' => 'NUMBER(10)',
                    'sqlsrv' => 'int',
                    'cubrid' => 'int',
                ],
            ],
            [
                Schema::TYPE_MONEY . ' CHECK (value > 0.0)',
                $this->money()->check('value > 0.0'),
                [
                    'mysql' => 'decimal(19,4) CHECK (value > 0.0)',
                    'postgres' => 'numeric(19,4) CHECK (value > 0.0)',
                    'sqlite' => 'decimal(19,4) CHECK (value > 0.0)',
                    'oci' => 'NUMBER(19,4) CHECK (value > 0.0)',
                    'sqlsrv' => 'decimal(19,4) CHECK (value > 0.0)',
                    'cubrid' => 'decimal(19,4) CHECK (value > 0.0)',
                ],
            ],
            [
                Schema::TYPE_MONEY . ' NOT NULL',
                $this->money()->notNull(),
                [
                    'mysql' => 'decimal(19,4) NOT NULL',
                    'postgres' => 'numeric(19,4) NOT NULL',
                    'sqlite' => 'decimal(19,4) NOT NULL',
                    'oci' => 'NUMBER(19,4) NOT NULL',
                    'sqlsrv' => 'decimal(19,4) NOT NULL',
                    'cubrid' => 'decimal(19,4) NOT NULL',
                ],
            ],
            [
                Schema::TYPE_MONEY . '(16,2) CHECK (value > 0.0)',
                $this->money(16, 2)->check('value > 0.0'),
                [
                    'mysql' => 'decimal(16,2) CHECK (value > 0.0)',
                    'postgres' => 'numeric(16,2) CHECK (value > 0.0)',
                    'sqlite' => 'decimal(16,2) CHECK (value > 0.0)',
                    'oci' => 'NUMBER(16,2) CHECK (value > 0.0)',
                    'sqlsrv' => 'decimal(16,2) CHECK (value > 0.0)',
                    'cubrid' => 'decimal(16,2) CHECK (value > 0.0)',
                ],
            ],
            [
                Schema::TYPE_MONEY . '(16,2)',
                $this->money(16, 2),
                [
                    'mysql' => 'decimal(16,2)',
                    'postgres' => 'numeric(16,2)',
                    'sqlite' => 'decimal(16,2)',
                    'oci' => 'NUMBER(16,2)',
                    'sqlsrv' => 'decimal(16,2)',
                    'cubrid' => 'decimal(16,2)',
                ],
            ],
            [
                Schema::TYPE_MONEY,
                $this->money(),
                [
                    'mysql' => 'decimal(19,4)',
                    'postgres' => 'numeric(19,4)',
                    'sqlite' => 'decimal(19,4)',
                    'oci' => 'NUMBER(19,4)',
                    'sqlsrv' => 'decimal(19,4)',
                    'cubrid' => 'decimal(19,4)',
                ],
            ],
            [
                Schema::TYPE_PK . ' CHECK (value > 5)',
                $this->primaryKey()->check('value > 5'),
                [
                    'mysql' => 'int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY CHECK (value > 5)',
                    'postgres' => 'serial NOT NULL PRIMARY KEY CHECK (value > 5)',
                    'sqlite' => 'integer PRIMARY KEY AUTOINCREMENT NOT NULL CHECK (value > 5)',
                    'oci' => 'NUMBER(10) NOT NULL PRIMARY KEY CHECK (value > 5)',
                    'sqlsrv' => 'int IDENTITY PRIMARY KEY CHECK (value > 5)',
                    'cubrid' => 'int NOT NULL AUTO_INCREMENT PRIMARY KEY CHECK (value > 5)',
                ],
            ],
            [
                Schema::TYPE_PK . '(8) CHECK (value > 5)',
                $this->primaryKey(8)->check('value > 5'),
                [
                    'mysql' => 'int(8) NOT NULL AUTO_INCREMENT PRIMARY KEY CHECK (value > 5)',
                    'oci' => 'NUMBER(8) NOT NULL PRIMARY KEY CHECK (value > 5)',
                ],
            ],
            [
                Schema::TYPE_PK . '(8)',
                $this->primaryKey(8),
                [
                    'mysql' => 'int(8) NOT NULL AUTO_INCREMENT PRIMARY KEY',
                    'oci' => 'NUMBER(8) NOT NULL PRIMARY KEY',
                ],
            ],
            [
                Schema::TYPE_PK,
                $this->primaryKey(),
                [
                    'mysql' => 'int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY',
                    'postgres' => 'serial NOT NULL PRIMARY KEY',
                    'sqlite' => 'integer PRIMARY KEY AUTOINCREMENT NOT NULL',
                    'oci' => 'NUMBER(10) NOT NULL PRIMARY KEY',
                    'sqlsrv' => 'int IDENTITY PRIMARY KEY',
                    'cubrid' => 'int NOT NULL AUTO_INCREMENT PRIMARY KEY',
                ],
            ],
            [
                Schema::TYPE_SMALLINT . '(8)',
                $this->smallInteger(8),
                [
                    'mysql' => 'smallint(8)',
                    'postgres' => 'smallint',
                    'sqlite' => 'smallint',
                    'oci' => 'NUMBER(8)',
                    'sqlsrv' => 'smallint',
                    'cubrid' => 'smallint',
                ],
            ],
            [
                Schema::TYPE_SMALLINT,
                $this->smallInteger(),
                [
                    'mysql' => 'smallint(6)',
                    'postgres' => 'smallint',
                    'sqlite' => 'smallint',
                    'oci' => 'NUMBER(5)',
                    'sqlsrv' => 'smallint',
                    'cubrid' => 'smallint',
                ],
            ],
            [
                Schema::TYPE_STRING . ' CHECK (value LIKE "test%")',
                $this->string()->check('value LIKE "test%"'),
                [
                    'mysql' => 'varchar(255) CHECK (value LIKE "test%")',
                    'sqlite' => 'varchar(255) CHECK (value LIKE "test%")',
                    'sqlsrv' => 'varchar(255) CHECK (value LIKE "test%")',
                    'cubrid' => 'varchar(255) CHECK (value LIKE "test%")',
                ],
            ],
            [
                Schema::TYPE_STRING . ' CHECK (value LIKE \'test%\')',
                $this->string()->check('value LIKE \'test%\''),
                [
                    'postgres' => 'varchar(255) CHECK (value LIKE \'test%\')',
                    'oci' => 'VARCHAR2(255) CHECK (value LIKE \'test%\')',
                ],
            ],
            [
                Schema::TYPE_STRING . ' NOT NULL',
                $this->string()->notNull(),
                [
                    'mysql' => 'varchar(255) NOT NULL',
                    'postgres' => 'varchar(255) NOT NULL',
                    'sqlite' => 'varchar(255) NOT NULL',
                    'oci' => 'VARCHAR2(255) NOT NULL',
                    'sqlsrv' => 'varchar(255) NOT NULL',
                    'cubrid' => 'varchar(255) NOT NULL',
                ],
            ],
            [
                Schema::TYPE_STRING . '(32) CHECK (value LIKE "test%")',
                $this->string(32)->check('value LIKE "test%"'),
                [
                    'mysql' => 'varchar(32) CHECK (value LIKE "test%")',
                    'sqlite' => 'varchar(32) CHECK (value LIKE "test%")',
                    'sqlsrv' => 'varchar(32) CHECK (value LIKE "test%")',
                    'cubrid' => 'varchar(32) CHECK (value LIKE "test%")',
                ],
            ],
            [
                Schema::TYPE_STRING . '(32) CHECK (value LIKE \'test%\')',
                $this->string(32)->check('value LIKE \'test%\''),
                [
                    'postgres' => 'varchar(32) CHECK (value LIKE \'test%\')',
                    'oci' => 'VARCHAR2(32) CHECK (value LIKE \'test%\')',
                ],
            ],
            [
                Schema::TYPE_STRING . '(32)',
                $this->string(32),
                [
                    'mysql' => 'varchar(32)',
                    'postgres' => 'varchar(32)',
                    'sqlite' => 'varchar(32)',
                    'oci' => 'VARCHAR2(32)',
                    'sqlsrv' => 'varchar(32)',
                    'cubrid' => 'varchar(32)',
                ],
            ],
            [
                Schema::TYPE_STRING,
                $this->string(),
                [
                    'mysql' => 'varchar(255)',
                    'postgres' => 'varchar(255)',
                    'sqlite' => 'varchar(255)',
                    'oci' => 'VARCHAR2(255)',
                    'sqlsrv' => 'varchar(255)',
                    'cubrid' => 'varchar(255)',
                ],
            ],
            [
                Schema::TYPE_TEXT . ' CHECK (value LIKE "test%")',
                $this->text()->check('value LIKE "test%"'),
                [
                    'mysql' => 'text CHECK (value LIKE "test%")',
                    'sqlite' => 'text CHECK (value LIKE "test%")',
                    'sqlsrv' => 'text CHECK (value LIKE "test%")',
                    'cubrid' => 'varchar CHECK (value LIKE "test%")',
                ],
            ],
            [
                Schema::TYPE_TEXT . ' CHECK (value LIKE \'test%\')',
                $this->text()->check('value LIKE \'test%\''),
                [
                    'postgres' => 'text CHECK (value LIKE \'test%\')',
                    'oci' => 'CLOB CHECK (value LIKE \'test%\')',
                ],
            ],
            [
                Schema::TYPE_TEXT . ' NOT NULL',
                $this->text()->notNull(),
                [
                    'mysql' => 'text NOT NULL',
                    'postgres' => 'text NOT NULL',
                    'sqlite' => 'text NOT NULL',
                    'oci' => 'CLOB NOT NULL',
                    'sqlsrv' => 'text NOT NULL',
                    'cubrid' => 'varchar NOT NULL',
                ],
            ],
            [
                Schema::TYPE_TEXT . '(255) CHECK (value LIKE "test%")',
                $this->text(255)->check('value LIKE "test%"'),
                [
                    'mysql' => 'text CHECK (value LIKE "test%")',
                    'sqlite' => 'text CHECK (value LIKE "test%")',
                    'sqlsrv' => 'text CHECK (value LIKE "test%")',
                    'cubrid' => 'varchar CHECK (value LIKE "test%")',
                ],
                Schema::TYPE_TEXT . ' CHECK (value LIKE "test%")',
            ],
            [
                Schema::TYPE_TEXT . '(255) CHECK (value LIKE \'test%\')',
                $this->text(255)->check('value LIKE \'test%\''),
                [
                    'postgres' => 'text CHECK (value LIKE \'test%\')',
                    'oci' => 'CLOB CHECK (value LIKE \'test%\')',
                ],
                Schema::TYPE_TEXT . ' CHECK (value LIKE \'test%\')',
            ],
            [
                Schema::TYPE_TEXT . '(255) NOT NULL',
                $this->text(255)->notNull(),
                [
                    'mysql' => 'text NOT NULL',
                    'postgres' => 'text NOT NULL',
                    'sqlite' => 'text NOT NULL',
                    'oci' => 'CLOB NOT NULL',
                    'sqlsrv' => 'text NOT NULL',
                    'cubrid' => 'varchar NOT NULL',
                ],
                Schema::TYPE_TEXT . ' NOT NULL',
            ],
            [
                Schema::TYPE_TEXT . '(255)',
                $this->text(255),
                [
                    'mysql' => 'text',
                    'postgres' => 'text',
                    'sqlite' => 'text',
                    'oci' => 'CLOB',
                    'sqlsrv' => 'text',
                    'cubrid' => 'varchar',
                ],
                Schema::TYPE_TEXT,
            ],
            [
                Schema::TYPE_TEXT,
                $this->text(),
                [
                    'mysql' => 'text',
                    'postgres' => 'text',
                    'sqlite' => 'text',
                    'oci' => 'CLOB',
                    'sqlsrv' => 'text',
                    'cubrid' => 'varchar',
                ],
            ],
            //[
            //    Schema::TYPE_TIME . " CHECK (value BETWEEN '12:00:00' AND '13:01:01')",
            //    $this->time()->check("value BETWEEN '12:00:00' AND '13:01:01'"),
            //    [
            //        'mysql' => ,
            //        'postgres' => ,
            //        'sqlite' => ,
            //        'sqlsrv' => ,
            //        'cubrid' => ,
            //    ],
            //],
            [
                Schema::TYPE_TIME . ' NOT NULL',
                $this->time()->notNull(),
                [
                    'mysql' => 'time NOT NULL',
                    'postgres' => 'time(0) NOT NULL',
                    'sqlite' => 'time NOT NULL',
                    'oci' => 'TIMESTAMP NOT NULL',
                    'sqlsrv' => 'time NOT NULL',
                    'cubrid' => 'time NOT NULL',
                ],
            ],
            [
                Schema::TYPE_TIME,
                $this->time(),
                [
                    'mysql' => 'time',
                    'postgres' => 'time(0)',
                    'sqlite' => 'time',
                    'oci' => 'TIMESTAMP',
                    'sqlsrv' => 'time',
                    'cubrid' => 'time',
                ],
            ],
            //[
            //    Schema::TYPE_TIMESTAMP . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')",
            //    $this->timestamp()->check("value BETWEEN '2011-01-01' AND '2013-01-01'"),
            //    [
            //        'mysql' => ,
            //        'postgres' => ,
            //        'sqlite' => ,
            //        'sqlsrv' => ,
            //        'cubrid' => ,
            //    ],
            //],
            [
                Schema::TYPE_TIMESTAMP . ' NOT NULL',
                $this->timestamp()->notNull(),
                [
                    'mysql' => 'timestamp NOT NULL',
                    'postgres' => 'timestamp(0) NOT NULL',
                    'sqlite' => 'timestamp NOT NULL',
                    'oci' => 'TIMESTAMP NOT NULL',
                    'sqlsrv' => 'timestamp NOT NULL',
                    'cubrid' => 'timestamp NOT NULL',
                ],
            ],
            [
                Schema::TYPE_TIMESTAMP,
                $this->timestamp(),
                [
                    'mysql' => 'timestamp',
                    'postgres' => 'timestamp(0)',
                    'sqlite' => 'timestamp',
                    'oci' => 'TIMESTAMP',
                    'sqlsrv' => 'timestamp',
                    'cubrid' => 'timestamp',
                ],
            ],
            [
                Schema::TYPE_TIMESTAMP . ' NULL DEFAULT NULL',
                $this->timestamp()->defaultValue(null),
                [
                    'mysql' => 'timestamp NULL DEFAULT NULL',
                    'postgres' => 'timestamp(0) NULL DEFAULT NULL',
                    'sqlite' => 'timestamp NULL DEFAULT NULL',
                    'sqlsrv' => 'timestamp NULL DEFAULT NULL',
                    'cubrid' => 'timestamp NULL DEFAULT NULL',
                ],
            ],
            [
                Schema::TYPE_UPK,
                $this->primaryKey()->unsigned(),
                [
                    'mysql' => 'int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
                    'postgres' => 'serial NOT NULL PRIMARY KEY',
                    'sqlite' => 'integer UNSIGNED PRIMARY KEY AUTOINCREMENT NOT NULL',
                ],
            ],
            [
                Schema::TYPE_UBIGPK,
                $this->bigPrimaryKey()->unsigned(),
                [
                    'mysql' => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
                    'postgres' => 'bigserial NOT NULL PRIMARY KEY',
                    'sqlite' => 'integer UNSIGNED PRIMARY KEY AUTOINCREMENT NOT NULL',
                ],
            ],
            [
                Schema::TYPE_INTEGER . " COMMENT 'test comment'",
                $this->integer()->comment('test comment'),
                [
                    'mysql' => "int(11) COMMENT 'test comment'",
                    'postgres' => 'integer',
                    'sqlsrv' => 'int',
                    'cubrid' => "int COMMENT 'test comment'",
                ],
            ],
            [
                Schema::TYPE_PK . " COMMENT 'test comment'",
                $this->primaryKey()->comment('test comment'),
                [
                    'mysql' => "int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'test comment'",
                    'postgres' => 'serial NOT NULL PRIMARY KEY',
                    'sqlsrv' => 'int IDENTITY PRIMARY KEY',
                    'cubrid' => "int NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'test comment'",
                ],
            ],
            [
                Schema::TYPE_PK . ' FIRST',
                $this->primaryKey()->first(),
                [
                    'mysql' => 'int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST',
                    'postgres' => 'serial NOT NULL PRIMARY KEY',
                    'oci' => 'NUMBER(10) NOT NULL PRIMARY KEY',
                    'sqlsrv' => 'int IDENTITY PRIMARY KEY',
                    'cubrid' => 'int NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST',
                ],
            ],
            [
                Schema::TYPE_INTEGER . ' FIRST',
                $this->integer()->first(),
                [
                    'mysql' => 'int(11) FIRST',
                    'postgres' => 'integer',
                    'oci' => 'NUMBER(10)',
                    'sqlsrv' => 'int',
                    'cubrid' => 'int FIRST',
                ],
            ],
            [
                Schema::TYPE_STRING . ' FIRST',
                $this->string()->first(),
                [
                    'mysql' => 'varchar(255) FIRST',
                    'postgres' => 'varchar(255)',
                    'oci' => 'VARCHAR2(255)',
                    'sqlsrv' => 'varchar(255)',
                    'cubrid' => 'varchar(255) FIRST',
                ],
            ],
            [
                Schema::TYPE_INTEGER . ' NOT NULL FIRST',
                $this->integer()->append('NOT NULL')->first(),
                [
                    'mysql' => 'int(11) NOT NULL FIRST',
                    'postgres' => 'integer NOT NULL',
                    'oci' => 'NUMBER(10) NOT NULL',
                    'sqlsrv' => 'int NOT NULL',
                    'cubrid' => 'int NOT NULL FIRST',
                ],
            ],
            [
                Schema::TYPE_STRING . ' NOT NULL FIRST',
                $this->string()->append('NOT NULL')->first(),
                [
                    'mysql' => 'varchar(255) NOT NULL FIRST',
                    'postgres' => 'varchar(255) NOT NULL',
                    'oci' => 'VARCHAR2(255) NOT NULL',
                    'sqlsrv' => 'varchar(255) NOT NULL',
                    'cubrid' => 'varchar(255) NOT NULL FIRST',
                ],
            ],
        ];

        foreach ($items as $i => $item) {
            if (array_key_exists($this->driverName, $item[2])) {
                $item[2] = $item[2][$this->driverName];
                $items[$i] = $item;
            } else {
                unset($items[$i]);
            }
        }

        return array_values($items);
    }

    public function testGetColumnType()
    {
        $qb = $this->getQueryBuilder();

        foreach ($this->columnTypes() as $item) {
            list($column, $builder, $expected) = $item;
            $expectedColumnSchemaBuilder = isset($item[3]) ? $item[3] : $column;

            $this->assertEquals($expected, $qb->getColumnType($column));
            $this->assertEquals($expected, $qb->getColumnType($builder));
            $this->assertEquals($expectedColumnSchemaBuilder, $builder->__toString());
        }
    }

    public function testCreateTableColumnTypes()
    {
        $qb = $this->getQueryBuilder();
        if ($qb->db->getTableSchema('column_type_table', true) !== null) {
            $this->getConnection(false)->createCommand($qb->dropTable('column_type_table'))->execute();
        }
        $columns = [];
        $i = 0;
        foreach ($this->columnTypes() as $item) {
            list($column, $builder, $expected) = $item;
            if (!(strncmp($column, Schema::TYPE_PK, 2) === 0 ||
                  strncmp($column, Schema::TYPE_UPK, 3) === 0 ||
                  strncmp($column, Schema::TYPE_BIGPK, 5) === 0 ||
                  strncmp($column, Schema::TYPE_UBIGPK, 6) === 0 ||
                  strncmp(substr($column, -5), 'FIRST', 5) === 0
                )) {
                $columns['col' . ++$i] = str_replace('CHECK (value', 'CHECK ([[col' . $i . ']]', $column);
            }
        }
        $this->getConnection(false)->createCommand($qb->createTable('column_type_table', $columns))->execute();
        $this->assertNotEmpty($qb->db->getTableSchema('column_type_table', true));
    }

    public function conditionProvider()
    {
        $conditions = [
            // empty values
            [['like', 'name', []], '0=1', []],
            [['not like', 'name', []], '', []],
            [['or like', 'name', []], '0=1', []],
            [['or not like', 'name', []], '', []],

            // not
            [['not', 'name'], 'NOT (name)', []],

            // and
            [['and', 'id=1', 'id=2'], '(id=1) AND (id=2)', []],
            [['and', 'type=1', ['or', 'id=1', 'id=2']], '(type=1) AND ((id=1) OR (id=2))', []],
            [['and', 'id=1', new Expression('id=:qp0', [':qp0' => 2])], '(id=1) AND (id=:qp0)', [':qp0' => 2]],

            // or
            [['or', 'id=1', 'id=2'], '(id=1) OR (id=2)', []],
            [['or', 'type=1', ['or', 'id=1', 'id=2']], '(type=1) OR ((id=1) OR (id=2))', []],
            [['or', 'type=1', new Expression('id=:qp0', [':qp0' => 1])], '(type=1) OR (id=:qp0)', [':qp0' => 1]],


            // between
            [['between', 'id', 1, 10], '[[id]] BETWEEN :qp0 AND :qp1', [':qp0' => 1, ':qp1' => 10]],
            [['not between', 'id', 1, 10], '[[id]] NOT BETWEEN :qp0 AND :qp1', [':qp0' => 1, ':qp1' => 10]],
            [['between', 'date', new Expression('(NOW() - INTERVAL 1 MONTH)'), new Expression('NOW()')], '[[date]] BETWEEN (NOW() - INTERVAL 1 MONTH) AND NOW()', []],
            [['between', 'date', new Expression('(NOW() - INTERVAL 1 MONTH)'), 123], '[[date]] BETWEEN (NOW() - INTERVAL 1 MONTH) AND :qp0', [':qp0' => 123]],
            [['not between', 'date', new Expression('(NOW() - INTERVAL 1 MONTH)'), new Expression('NOW()')], '[[date]] NOT BETWEEN (NOW() - INTERVAL 1 MONTH) AND NOW()', []],
            [['not between', 'date', new Expression('(NOW() - INTERVAL 1 MONTH)'), 123], '[[date]] NOT BETWEEN (NOW() - INTERVAL 1 MONTH) AND :qp0', [':qp0' => 123]],

            // in
            [['in', 'id', [1, 2, 3]], '[[id]] IN (:qp0, :qp1, :qp2)', [':qp0' => 1, ':qp1' => 2, ':qp2' => 3]],
            [['not in', 'id', [1, 2, 3]], '[[id]] NOT IN (:qp0, :qp1, :qp2)', [':qp0' => 1, ':qp1' => 2, ':qp2' => 3]],
            [['in', 'id', (new Query())->select('id')->from('users')->where(['active' => 1])], '[[id]] IN (SELECT [[id]] FROM [[users]] WHERE [[active]]=:qp0)', [':qp0' => 1]],
            [['not in', 'id', (new Query())->select('id')->from('users')->where(['active' => 1])], '[[id]] NOT IN (SELECT [[id]] FROM [[users]] WHERE [[active]]=:qp0)', [':qp0' => 1]],

            [['in', 'id', 1],   '[[id]]=:qp0', [':qp0' => 1]],
            [['in', 'id', [1]], '[[id]]=:qp0', [':qp0' => 1]],
            [['in', 'id', new TraversableObject([1])], '[[id]]=:qp0', [':qp0' => 1]],
            'composite in' => [
                ['in', ['id', 'name'], [['id' => 1, 'name' => 'oy']]],
                '([[id]], [[name]]) IN ((:qp0, :qp1))',
                [':qp0' => 1, ':qp1' => 'oy'],
            ],

            // in using array objects.
            [['id' => new TraversableObject([1, 2])], '[[id]] IN (:qp0, :qp1)', [':qp0' => 1, ':qp1' => 2]],

            [['in', 'id', new TraversableObject([1, 2, 3])], '[[id]] IN (:qp0, :qp1, :qp2)', [':qp0' => 1, ':qp1' => 2, ':qp2' => 3]],

            'composite in using array objects' => [
                ['in', new TraversableObject(['id', 'name']), new TraversableObject([
                    ['id' => 1, 'name' => 'oy'],
                    ['id' => 2, 'name' => 'yo'],
                ])],
                '([[id]], [[name]]) IN ((:qp0, :qp1), (:qp2, :qp3))',
                [':qp0' => 1, ':qp1' => 'oy', ':qp2' => 2, ':qp3' => 'yo'],
            ],
            // exists
            [['exists', (new Query())->select('id')->from('users')->where(['active' => 1])], 'EXISTS (SELECT [[id]] FROM [[users]] WHERE [[active]]=:qp0)', [':qp0' => 1]],
            [['not exists', (new Query())->select('id')->from('users')->where(['active' => 1])], 'NOT EXISTS (SELECT [[id]] FROM [[users]] WHERE [[active]]=:qp0)', [':qp0' => 1]],

            // simple conditions
            [['=', 'a', 'b'], '[[a]] = :qp0', [':qp0' => 'b']],
            [['>', 'a', 1], '[[a]] > :qp0', [':qp0' => 1]],
            [['>=', 'a', 'b'], '[[a]] >= :qp0', [':qp0' => 'b']],
            [['<', 'a', 2], '[[a]] < :qp0', [':qp0' => 2]],
            [['<=', 'a', 'b'], '[[a]] <= :qp0', [':qp0' => 'b']],
            [['<>', 'a', 3], '[[a]] <> :qp0', [':qp0' => 3]],
            [['!=', 'a', 'b'], '[[a]] != :qp0', [':qp0' => 'b']],
            [['>=', 'date', new Expression('DATE_SUB(NOW(), INTERVAL 1 MONTH)')], '[[date]] >= DATE_SUB(NOW(), INTERVAL 1 MONTH)', []],
            [['>=', 'date', new Expression('DATE_SUB(NOW(), INTERVAL :month MONTH)', [':month' => 2])], '[[date]] >= DATE_SUB(NOW(), INTERVAL :month MONTH)', [':month' => 2]],
            [['=', 'date', (new Query())->select('max(date)')->from('test')->where(['id' => 5])], '[[date]] = (SELECT max(date) FROM [[test]] WHERE [[id]]=:qp0)', [':qp0' => 5]],

            // hash condition
            [['a' => 1, 'b' => 2], '([[a]]=:qp0) AND ([[b]]=:qp1)', [':qp0' => 1, ':qp1' => 2]],
            [['a' => new Expression('CONCAT(col1, col2)'), 'b' => 2], '([[a]]=CONCAT(col1, col2)) AND ([[b]]=:qp0)', [':qp0' => 2]],

            // direct conditions
            ['a = CONCAT(col1, col2)', 'a = CONCAT(col1, col2)', []],
            [new Expression('a = CONCAT(col1, :param1)', ['param1' => 'value1']), 'a = CONCAT(col1, :param1)', ['param1' => 'value1']],

            // Expression with params as operand of 'not'
            [['not', new Expression('any_expression(:a)', [':a' => 1])], 'NOT (any_expression(:a))', [':a' => 1]],
            [new Expression('NOT (any_expression(:a))', [':a' => 1]), 'NOT (any_expression(:a))', [':a' => 1]],
        ];
        switch ($this->driverName) {
            case 'sqlsrv':
            case 'sqlite':
                $conditions = array_merge($conditions, [
                    [['in', ['id', 'name'], [['id' => 1, 'name' => 'foo'], ['id' => 2, 'name' => 'bar']]], '(([[id]] = :qp0 AND [[name]] = :qp1) OR ([[id]] = :qp2 AND [[name]] = :qp3))', [':qp0' => 1, ':qp1' => 'foo', ':qp2' => 2, ':qp3' => 'bar']],
                    [['not in', ['id', 'name'], [['id' => 1, 'name' => 'foo'], ['id' => 2, 'name' => 'bar']]], '(([[id]] != :qp0 OR [[name]] != :qp1) AND ([[id]] != :qp2 OR [[name]] != :qp3))', [':qp0' => 1, ':qp1' => 'foo', ':qp2' => 2, ':qp3' => 'bar']],
                    //[ ['in', ['id', 'name'], (new Query())->select(['id', 'name'])->from('users')->where(['active' => 1])], 'EXISTS (SELECT 1 FROM (SELECT [[id]], [[name]] FROM [[users]] WHERE [[active]]=:qp0) AS a WHERE a.[[id]] = [[id AND a.]]name[[ = ]]name`)', [':qp0' => 1] ],
                    //[ ['not in', ['id', 'name'], (new Query())->select(['id', 'name'])->from('users')->where(['active' => 1])], 'NOT EXISTS (SELECT 1 FROM (SELECT [[id]], [[name]] FROM [[users]] WHERE [[active]]=:qp0) AS a WHERE a.[[id]] = [[id]] AND a.[[name = ]]name`)', [':qp0' => 1] ],
                ]);
                break;
            default:
                $conditions = array_merge($conditions, [
                    [['in', ['id', 'name'], [['id' => 1, 'name' => 'foo'], ['id' => 2, 'name' => 'bar']]], '([[id]], [[name]]) IN ((:qp0, :qp1), (:qp2, :qp3))', [':qp0' => 1, ':qp1' => 'foo', ':qp2' => 2, ':qp3' => 'bar']],
                    [['not in', ['id', 'name'], [['id' => 1, 'name' => 'foo'], ['id' => 2, 'name' => 'bar']]], '([[id]], [[name]]) NOT IN ((:qp0, :qp1), (:qp2, :qp3))', [':qp0' => 1, ':qp1' => 'foo', ':qp2' => 2, ':qp3' => 'bar']],
                    [['in', ['id', 'name'], (new Query())->select(['id', 'name'])->from('users')->where(['active' => 1])], '([[id]], [[name]]) IN (SELECT [[id]], [[name]] FROM [[users]] WHERE [[active]]=:qp0)', [':qp0' => 1]],
                    [['not in', ['id', 'name'], (new Query())->select(['id', 'name'])->from('users')->where(['active' => 1])], '([[id]], [[name]]) NOT IN (SELECT [[id]], [[name]] FROM [[users]] WHERE [[active]]=:qp0)', [':qp0' => 1]],
                ]);
                break;
        }

        // adjust dbms specific escaping
        foreach ($conditions as $i => $condition) {
            $conditions[$i][1] = $this->replaceQuotes($condition[1]);
        }
        return $conditions;
    }

    public function filterConditionProvider()
    {
        $conditions = [
            // like
            [['like', 'name', []], '', []],
            [['not like', 'name', []], '', []],
            [['or like', 'name', []], '', []],
            [['or not like', 'name', []], '', []],

            // not
            [['not', ''], '', []],

            // and
            [['and', '', ''], '', []],
            [['and', '', 'id=2'], '(id=2)', []],
            [['and', 'id=1', ''], '(id=1)', []],
            [['and', 'type=1', ['or', '', 'id=2']], '(type=1) AND ((id=2))', []],

            // or
            [['or', 'id=1', ''], '(id=1)', []],
            [['or', 'type=1', ['or', '', 'id=2']], '(type=1) OR ((id=2))', []],


            // between
            [['between', 'id', 1, null], '', []],
            [['not between', 'id', null, 10], '', []],

            // in
            [['in', 'id', []], '', []],
            [['not in', 'id', []], '', []],

            // simple conditions
            [['=', 'a', ''], '', []],
            [['>', 'a', ''], '', []],
            [['>=', 'a', ''], '', []],
            [['<', 'a', ''], '', []],
            [['<=', 'a', ''], '', []],
            [['<>', 'a', ''], '', []],
            [['!=', 'a', ''], '', []],
        ];

        // adjust dbms specific escaping
        foreach ($conditions as $i => $condition) {
            $conditions[$i][1] = $this->replaceQuotes($condition[1]);
        }
        return $conditions;
    }

    /**
     * @dataProvider conditionProvider
     */
    public function testBuildCondition($condition, $expected, $expectedParams)
    {
        $query = (new Query())->where($condition);
        list($sql, $params) = $this->getQueryBuilder()->build($query);
        $this->assertEquals('SELECT *' . (empty($expected) ? '' : ' WHERE ' . $this->replaceQuotes($expected)), $sql);
        $this->assertEquals($expectedParams, $params);
    }

    /**
     * @dataProvider filterConditionProvider
     */
    public function testBuildFilterCondition($condition, $expected, $expectedParams)
    {
        $query = (new Query())->filterWhere($condition);
        list($sql, $params) = $this->getQueryBuilder()->build($query);
        $this->assertEquals('SELECT *' . (empty($expected) ? '' : ' WHERE ' . $this->replaceQuotes($expected)), $sql);
        $this->assertEquals($expectedParams, $params);
    }

    public function primaryKeysProvider()
    {
        $tableName = 'T_constraints_1';
        $name = 'CN_pk';
        return [
            'drop' => [
                "ALTER TABLE {{{$tableName}}} DROP CONSTRAINT [[$name]]",
                function (QueryBuilder $qb) use ($tableName, $name) {
                    return $qb->dropPrimaryKey($name, $tableName);
                },
            ],
            'add' => [
                "ALTER TABLE {{{$tableName}}} ADD CONSTRAINT [[$name]] PRIMARY KEY ([[C_id_1]])",
                function (QueryBuilder $qb) use ($tableName, $name) {
                    return $qb->addPrimaryKey($name, $tableName, 'C_id_1');
                },
            ],
            'add (2 columns)' => [
                "ALTER TABLE {{{$tableName}}} ADD CONSTRAINT [[$name]] PRIMARY KEY ([[C_id_1]], [[C_id_2]])",
                function (QueryBuilder $qb) use ($tableName, $name) {
                    return $qb->addPrimaryKey($name, $tableName, 'C_id_1, C_id_2');
                },
            ],
        ];
    }

    /**
     * @dataProvider primaryKeysProvider
     */
    public function testAddDropPrimaryKey($sql, \Closure $builder)
    {
        $this->assertSame($this->getConnection(false)->quoteSql($sql), $builder($this->getQueryBuilder(false)));
    }

    public function foreignKeysProvider()
    {
        $tableName = 'T_constraints_3';
        $name = 'CN_constraints_3';
        $pkTableName = 'T_constraints_2';
        return [
            'drop' => [
                "ALTER TABLE {{{$tableName}}} DROP CONSTRAINT [[$name]]",
                function (QueryBuilder $qb) use ($tableName, $name) {
                    return $qb->dropForeignKey($name, $tableName);
                },
            ],
            'add' => [
                "ALTER TABLE {{{$tableName}}} ADD CONSTRAINT [[$name]] FOREIGN KEY ([[C_fk_id_1]]) REFERENCES {{{$pkTableName}}} ([[C_id_1]]) ON DELETE CASCADE ON UPDATE CASCADE",
                function (QueryBuilder $qb) use ($tableName, $name, $pkTableName) {
                    return $qb->addForeignKey($name, $tableName, 'C_fk_id_1', $pkTableName, 'C_id_1', 'CASCADE', 'CASCADE');
                },
            ],
            'add (2 columns)' => [
                "ALTER TABLE {{{$tableName}}} ADD CONSTRAINT [[$name]] FOREIGN KEY ([[C_fk_id_1]], [[C_fk_id_2]]) REFERENCES {{{$pkTableName}}} ([[C_id_1]], [[C_id_2]]) ON DELETE CASCADE ON UPDATE CASCADE",
                function (QueryBuilder $qb) use ($tableName, $name, $pkTableName) {
                    return $qb->addForeignKey($name, $tableName, 'C_fk_id_1, C_fk_id_2', $pkTableName, 'C_id_1, C_id_2', 'CASCADE', 'CASCADE');
                },
            ],
        ];
    }

    /**
     * @dataProvider foreignKeysProvider
     */
    public function testAddDropForeignKey($sql, \Closure $builder)
    {
        $this->assertSame($this->getConnection(false)->quoteSql($sql), $builder($this->getQueryBuilder(false)));
    }

    public function indexesProvider()
    {
        $tableName = 'T_constraints_2';
        $name1 = 'CN_constraints_2_single';
        $name2 = 'CN_constraints_2_multi';
        return [
            'drop' => [
                "DROP INDEX [[$name1]] ON {{{$tableName}}}",
                function (QueryBuilder $qb) use ($tableName, $name1) {
                    return $qb->dropIndex($name1, $tableName);
                },
            ],
            'create' => [
                "CREATE INDEX [[$name1]] ON {{{$tableName}}} ([[C_index_1]])",
                function (QueryBuilder $qb) use ($tableName, $name1) {
                    return $qb->createIndex($name1, $tableName, 'C_index_1');
                },
            ],
            'create (2 columns)' => [
                "CREATE INDEX [[$name2]] ON {{{$tableName}}} ([[C_index_2_1]], [[C_index_2_2]])",
                function (QueryBuilder $qb) use ($tableName, $name2) {
                    return $qb->createIndex($name2, $tableName, 'C_index_2_1, C_index_2_2');
                },
            ],
            'create unique' => [
                "CREATE UNIQUE INDEX [[$name1]] ON {{{$tableName}}} ([[C_index_1]])",
                function (QueryBuilder $qb) use ($tableName, $name1) {
                    return $qb->createIndex($name1, $tableName, 'C_index_1', true);
                },
            ],
            'create unique (2 columns)' => [
                "CREATE UNIQUE INDEX [[$name2]] ON {{{$tableName}}} ([[C_index_2_1]], [[C_index_2_2]])",
                function (QueryBuilder $qb) use ($tableName, $name2) {
                    return $qb->createIndex($name2, $tableName, 'C_index_2_1, C_index_2_2', true);
                },
            ],
        ];
    }

    /**
     * @dataProvider indexesProvider
     */
    public function testCreateDropIndex($sql, \Closure $builder)
    {
        $this->assertSame($this->getConnection(false)->quoteSql($sql), $builder($this->getQueryBuilder(false)));
    }

    public function uniquesProvider()
    {
        $tableName1 = 'T_constraints_1';
        $name1 = 'CN_unique';
        $tableName2 = 'T_constraints_2';
        $name2 = 'CN_constraints_2_multi';
        return [
            'drop' => [
                "ALTER TABLE {{{$tableName1}}} DROP CONSTRAINT [[$name1]]",
                function (QueryBuilder $qb) use ($tableName1, $name1) {
                    return $qb->dropUnique($name1, $tableName1);
                },
            ],
            'add' => [
                "ALTER TABLE {{{$tableName1}}} ADD CONSTRAINT [[$name1]] UNIQUE ([[C_unique]])",
                function (QueryBuilder $qb) use ($tableName1, $name1) {
                    return $qb->addUnique($name1, $tableName1, 'C_unique');
                },
            ],
            'add (2 columns)' => [
                "ALTER TABLE {{{$tableName2}}} ADD CONSTRAINT [[$name2]] UNIQUE ([[C_index_2_1]], [[C_index_2_2]])",
                function (QueryBuilder $qb) use ($tableName2, $name2) {
                    return $qb->addUnique($name2, $tableName2, 'C_index_2_1, C_index_2_2');
                },
            ],
        ];
    }

    /**
     * @dataProvider uniquesProvider
     */
    public function testAddDropUnique($sql, \Closure $builder)
    {
        $this->assertSame($this->getConnection(false)->quoteSql($sql), $builder($this->getQueryBuilder(false)));
    }

    public function checksProvider()
    {
        $tableName = 'T_constraints_1';
        $name = 'CN_check';
        return [
            'drop' => [
                "ALTER TABLE {{{$tableName}}} DROP CONSTRAINT [[$name]]",
                function (QueryBuilder $qb) use ($tableName, $name) {
                    return $qb->dropCheck($name, $tableName);
                },
            ],
            'add' => [
                "ALTER TABLE {{{$tableName}}} ADD CONSTRAINT [[$name]] CHECK ([[C_not_null]] > 100)",
                function (QueryBuilder $qb) use ($tableName, $name) {
                    return $qb->addCheck($name, $tableName, '[[C_not_null]] > 100');
                },
            ],
        ];
    }

    /**
     * @dataProvider checksProvider
     */
    public function testAddDropCheck($sql, \Closure $builder)
    {
        $this->assertSame($this->getConnection(false)->quoteSql($sql), $builder($this->getQueryBuilder(false)));
    }

    public function defaultValuesProvider()
    {
        $tableName = 'T_constraints_1';
        $name = 'CN_default';
        return [
            'drop' => [
                "ALTER TABLE {{{$tableName}}} DROP CONSTRAINT [[$name]]",
                function (QueryBuilder $qb) use ($tableName, $name) {
                    return $qb->dropDefaultValue($name, $tableName);
                },
            ],
            'add' => [
                "ALTER TABLE {{{$tableName}}} ADD CONSTRAINT [[$name]] DEFAULT 0 FOR [[C_default]]",
                function (QueryBuilder $qb) use ($tableName, $name) {
                    return $qb->addDefaultValue($name, $tableName, 'C_default', 0);
                },
            ],
        ];
    }

    /**
     * @dataProvider defaultValuesProvider
     */
    public function testAddDropDefaultValue($sql, \Closure $builder)
    {
        $this->assertSame($this->getConnection(false)->quoteSql($sql), $builder($this->getQueryBuilder(false)));
    }

    public function existsParamsProvider()
    {
        return [
            ['exists', $this->replaceQuotes('SELECT [[id]] FROM [[TotalExample]] [[t]] WHERE EXISTS (SELECT [[1]] FROM [[Website]] [[w]])')],
            ['not exists', $this->replaceQuotes('SELECT [[id]] FROM [[TotalExample]] [[t]] WHERE NOT EXISTS (SELECT [[1]] FROM [[Website]] [[w]])')],
        ];
    }

    /**
     * @dataProvider existsParamsProvider
     */
    public function testBuildWhereExists($cond, $expectedQuerySql)
    {
        $expectedQueryParams = [];

        $subQuery = new Query();
        $subQuery->select('1')
            ->from('Website w');

        $query = new Query();
        $query->select('id')
            ->from('TotalExample t')
            ->where([$cond, $subQuery]);

        list($actualQuerySql, $actualQueryParams) = $this->getQueryBuilder()->build($query);
        $this->assertEquals($expectedQuerySql, $actualQuerySql);
        $this->assertEquals($expectedQueryParams, $actualQueryParams);
    }


    public function testBuildWhereExistsWithParameters()
    {
        $expectedQuerySql = $this->replaceQuotes(
            'SELECT [[id]] FROM [[TotalExample]] [[t]] WHERE (EXISTS (SELECT [[1]] FROM [[Website]] [[w]] WHERE (w.id = t.website_id) AND (w.merchant_id = :merchant_id))) AND (t.some_column = :some_value)'
        );
        $expectedQueryParams = [':some_value' => 'asd', ':merchant_id' => 6];

        $subQuery = new Query();
        $subQuery->select('1')
            ->from('Website w')
            ->where('w.id = t.website_id')
            ->andWhere('w.merchant_id = :merchant_id', [':merchant_id' => 6]);

        $query = new Query();
        $query->select('id')
            ->from('TotalExample t')
            ->where(['exists', $subQuery])
            ->andWhere('t.some_column = :some_value', [':some_value' => 'asd']);

        list($actualQuerySql, $queryParams) = $this->getQueryBuilder()->build($query);
        $this->assertEquals($expectedQuerySql, $actualQuerySql);
        $this->assertEquals($expectedQueryParams, $queryParams);
    }

    public function testBuildWhereExistsWithArrayParameters()
    {
        $expectedQuerySql = $this->replaceQuotes(
            'SELECT [[id]] FROM [[TotalExample]] [[t]] WHERE (EXISTS (SELECT [[1]] FROM [[Website]] [[w]] WHERE (w.id = t.website_id) AND (([[w]].[[merchant_id]]=:qp0) AND ([[w]].[[user_id]]=:qp1)))) AND ([[t]].[[some_column]]=:qp2)'
        );
        $expectedQueryParams = [':qp0' => 6, ':qp1' => 210, ':qp2' => 'asd'];

        $subQuery = new Query();
        $subQuery->select('1')
            ->from('Website w')
            ->where('w.id = t.website_id')
            ->andWhere(['w.merchant_id' => 6, 'w.user_id' => '210']);

        $query = new Query();
        $query->select('id')
            ->from('TotalExample t')
            ->where(['exists', $subQuery])
            ->andWhere(['t.some_column' => 'asd']);

        list($actualQuerySql, $queryParams) = $this->getQueryBuilder()->build($query);
        $this->assertEquals($expectedQuerySql, $actualQuerySql);
        $this->assertEquals($expectedQueryParams, $queryParams);
    }

    /**
     * This test contains three select queries connected with UNION and UNION ALL constructions.
     * It could be useful to use "phpunit --group=db --filter testBuildUnion" command for run it.
     */
    public function testBuildUnion()
    {
        $expectedQuerySql = $this->replaceQuotes(
            '(SELECT [[id]] FROM [[TotalExample]] [[t1]] WHERE (w > 0) AND (x < 2)) UNION ( SELECT [[id]] FROM [[TotalTotalExample]] [[t2]] WHERE w > 5 ) UNION ALL ( SELECT [[id]] FROM [[TotalTotalExample]] [[t3]] WHERE w = 3 )'
        );
        $query = new Query();
        $secondQuery = new Query();
        $secondQuery->select('id')
              ->from('TotalTotalExample t2')
              ->where('w > 5');
        $thirdQuery = new Query();
        $thirdQuery->select('id')
              ->from('TotalTotalExample t3')
              ->where('w = 3');
        $query->select('id')
              ->from('TotalExample t1')
              ->where(['and', 'w > 0', 'x < 2'])
              ->union($secondQuery)
              ->union($thirdQuery, true);
        list($actualQuerySql, $queryParams) = $this->getQueryBuilder()->build($query);
        $this->assertEquals($expectedQuerySql, $actualQuerySql);
        $this->assertEquals([], $queryParams);
    }

    public function testSelectSubquery()
    {
        $subquery = (new Query())
            ->select('COUNT(*)')
            ->from('operations')
            ->where('account_id = accounts.id');
        $query = (new Query())
            ->select('*')
            ->from('accounts')
            ->addSelect(['operations_count' => $subquery]);
        list($sql, $params) = $this->getQueryBuilder()->build($query);
        $expected = $this->replaceQuotes('SELECT *, (SELECT COUNT(*) FROM [[operations]] WHERE account_id = accounts.id) AS [[operations_count]] FROM [[accounts]]');
        $this->assertEquals($expected, $sql);
        $this->assertEmpty($params);
    }

    public function testComplexSelect()
    {
        $query = (new Query())
            ->select([
                'ID' => 't.id',
                'gsm.username as GSM',
                'part.Part',
                'Part Cost' => 't.Part_Cost',
                'st_x(location::geometry) as lon',
                new Expression($this->replaceQuotes("case t.Status_Id when 1 then 'Acknowledge' when 2 then 'No Action' else 'Unknown Action' END as [[Next Action]]")),
            ])
            ->from('tablename');
        list($sql, $params) = $this->getQueryBuilder()->build($query);
        $expected = $this->replaceQuotes(
            'SELECT [[t]].[[id]] AS [[ID]], [[gsm]].[[username]] AS [[GSM]], [[part]].[[Part]], [[t]].[[Part_Cost]] AS [[Part Cost]], st_x(location::geometry) as lon,'
            . ' case t.Status_Id when 1 then \'Acknowledge\' when 2 then \'No Action\' else \'Unknown Action\' END as [[Next Action]] FROM [[tablename]]');
        $this->assertEquals($expected, $sql);
        $this->assertEmpty($params);
    }

    public function testSelectExpression()
    {
        $query = (new Query())
            ->select(new Expression('1 AS ab'))
            ->from('tablename');
        list($sql, $params) = $this->getQueryBuilder()->build($query);
        $expected = $this->replaceQuotes('SELECT 1 AS ab FROM [[tablename]]');
        $this->assertEquals($expected, $sql);
        $this->assertEmpty($params);

        $query = (new Query())
            ->select(new Expression('1 AS ab'))
            ->addSelect(new Expression('2 AS cd'))
            ->addSelect(['ef' => new Expression('3')])
            ->from('tablename');
        list($sql, $params) = $this->getQueryBuilder()->build($query);
        $expected = $this->replaceQuotes('SELECT 1 AS ab, 2 AS cd, 3 AS [[ef]] FROM [[tablename]]');
        $this->assertEquals($expected, $sql);
        $this->assertEmpty($params);

        $query = (new Query())
            ->select(new Expression('SUBSTR(name, 0, :len)', [':len' => 4]))
            ->from('tablename');
        list($sql, $params) = $this->getQueryBuilder()->build($query);
        $expected = $this->replaceQuotes('SELECT SUBSTR(name, 0, :len) FROM [[tablename]]');
        $this->assertEquals($expected, $sql);
        $this->assertEquals([':len' => 4], $params);
    }

    /**
     * https://github.com/yiisoft/yii2/issues/10869
     */
    public function testFromIndexHint()
    {
        $query = (new Query())->from([new Expression('{{%user}} USE INDEX (primary)')]);
        list($sql, $params) = $this->getQueryBuilder()->build($query);
        $expected = $this->replaceQuotes('SELECT * FROM {{%user}} USE INDEX (primary)');
        $this->assertEquals($expected, $sql);
        $this->assertEmpty($params);

        $query = (new Query())
            ->from([new Expression('{{user}} {{t}} FORCE INDEX (primary) IGNORE INDEX FOR ORDER BY (i1)')])
            ->leftJoin(['p' => 'profile'], 'user.id = profile.user_id USE INDEX (i2)');
        list($sql, $params) = $this->getQueryBuilder()->build($query);
        $expected = $this->replaceQuotes('SELECT * FROM {{user}} {{t}} FORCE INDEX (primary) IGNORE INDEX FOR ORDER BY (i1) LEFT JOIN [[profile]] [[p]] ON user.id = profile.user_id USE INDEX (i2)');
        $this->assertEquals($expected, $sql);
        $this->assertEmpty($params);
    }

    public function testFromSubquery()
    {
        // query subquery
        $subquery = (new Query())->from('user')->where('account_id = accounts.id');
        $query = (new Query())->from(['activeusers' => $subquery]);
        // SELECT * FROM (SELECT * FROM [[user]] WHERE [[active]] = 1) [[activeusers]];
        list($sql, $params) = $this->getQueryBuilder()->build($query);
        $expected = $this->replaceQuotes('SELECT * FROM (SELECT * FROM [[user]] WHERE account_id = accounts.id) [[activeusers]]');
        $this->assertEquals($expected, $sql);
        $this->assertEmpty($params);

        // query subquery with params
        $subquery = (new Query())->from('user')->where('account_id = :id', ['id' => 1]);
        $query = (new Query())->from(['activeusers' => $subquery])->where('abc = :abc', ['abc' => 'abc']);
        // SELECT * FROM (SELECT * FROM [[user]] WHERE [[active]] = 1) [[activeusers]];
        list($sql, $params) = $this->getQueryBuilder()->build($query);
        $expected = $this->replaceQuotes('SELECT * FROM (SELECT * FROM [[user]] WHERE account_id = :id) [[activeusers]] WHERE abc = :abc');
        $this->assertEquals($expected, $sql);
        $this->assertEquals([
            'id' => 1,
            'abc' => 'abc',
        ], $params);

        // simple subquery
        $subquery = '(SELECT * FROM user WHERE account_id = accounts.id)';
        $query = (new Query())->from(['activeusers' => $subquery]);
        // SELECT * FROM (SELECT * FROM [[user]] WHERE [[active]] = 1) [[activeusers]];
        list($sql, $params) = $this->getQueryBuilder()->build($query);
        $expected = $this->replaceQuotes('SELECT * FROM (SELECT * FROM user WHERE account_id = accounts.id) [[activeusers]]');
        $this->assertEquals($expected, $sql);
        $this->assertEmpty($params);
    }

    public function testOrderBy()
    {
        // simple string
        $query = (new Query())
            ->select('*')
            ->from('operations')
            ->orderBy('name ASC, date DESC');
        list($sql, $params) = $this->getQueryBuilder()->build($query);
        $expected = $this->replaceQuotes('SELECT * FROM [[operations]] ORDER BY [[name]], [[date]] DESC');
        $this->assertEquals($expected, $sql);
        $this->assertEmpty($params);

        // array syntax
        $query = (new Query())
            ->select('*')
            ->from('operations')
            ->orderBy(['name' => SORT_ASC, 'date' => SORT_DESC]);
        list($sql, $params) = $this->getQueryBuilder()->build($query);
        $expected = $this->replaceQuotes('SELECT * FROM [[operations]] ORDER BY [[name]], [[date]] DESC');
        $this->assertEquals($expected, $sql);
        $this->assertEmpty($params);

        // expression
        $query = (new Query())
            ->select('*')
            ->from('operations')
            ->where('account_id = accounts.id')
            ->orderBy(new Expression('SUBSTR(name, 3, 4) DESC, x ASC'));
        list($sql, $params) = $this->getQueryBuilder()->build($query);
        $expected = $this->replaceQuotes('SELECT * FROM [[operations]] WHERE account_id = accounts.id ORDER BY SUBSTR(name, 3, 4) DESC, x ASC');
        $this->assertEquals($expected, $sql);
        $this->assertEmpty($params);

        // expression with params
        $query = (new Query())
            ->select('*')
            ->from('operations')
            ->orderBy(new Expression('SUBSTR(name, 3, :to) DESC, x ASC', [':to' => 4]));
        list($sql, $params) = $this->getQueryBuilder()->build($query);
        $expected = $this->replaceQuotes('SELECT * FROM [[operations]] ORDER BY SUBSTR(name, 3, :to) DESC, x ASC');
        $this->assertEquals($expected, $sql);
        $this->assertEquals([':to' => 4], $params);
    }

    public function testGroupBy()
    {
        // simple string
        $query = (new Query())
            ->select('*')
            ->from('operations')
            ->groupBy('name, date');
        list($sql, $params) = $this->getQueryBuilder()->build($query);
        $expected = $this->replaceQuotes('SELECT * FROM [[operations]] GROUP BY [[name]], [[date]]');
        $this->assertEquals($expected, $sql);
        $this->assertEmpty($params);

        // array syntax
        $query = (new Query())
            ->select('*')
            ->from('operations')
            ->groupBy(['name', 'date']);
        list($sql, $params) = $this->getQueryBuilder()->build($query);
        $expected = $this->replaceQuotes('SELECT * FROM [[operations]] GROUP BY [[name]], [[date]]');
        $this->assertEquals($expected, $sql);
        $this->assertEmpty($params);

        // expression
        $query = (new Query())
            ->select('*')
            ->from('operations')
            ->where('account_id = accounts.id')
            ->groupBy(new Expression('SUBSTR(name, 0, 1), x'));
        list($sql, $params) = $this->getQueryBuilder()->build($query);
        $expected = $this->replaceQuotes('SELECT * FROM [[operations]] WHERE account_id = accounts.id GROUP BY SUBSTR(name, 0, 1), x');
        $this->assertEquals($expected, $sql);
        $this->assertEmpty($params);

        // expression with params
        $query = (new Query())
            ->select('*')
            ->from('operations')
            ->groupBy(new Expression('SUBSTR(name, 0, :to), x', [':to' => 4]));
        list($sql, $params) = $this->getQueryBuilder()->build($query);
        $expected = $this->replaceQuotes('SELECT * FROM [[operations]] GROUP BY SUBSTR(name, 0, :to), x');
        $this->assertEquals($expected, $sql);
        $this->assertEquals([':to' => 4], $params);
    }

//    public function testInsert()
//    {
//        // TODO implement
//    }
//

    public function batchInsertProvider()
    {
        return [
            [
                'customer',
                ['email', 'name', 'address'],
                [['test@example.com', 'silverfire', 'Kyiv {{city}}, Ukraine']],
                $this->replaceQuotes("INSERT INTO [[customer]] ([[email]], [[name]], [[address]]) VALUES ('test@example.com', 'silverfire', 'Kyiv {{city}}, Ukraine')"),
            ],
            'escape-danger-chars' => [
                'customer',
                ['address'],
                [["SQL-danger chars are escaped: '); --"]],
                'expected' => $this->replaceQuotes("INSERT INTO [[customer]] ([[address]]) VALUES ('SQL-danger chars are escaped: \'); --')"),
            ],
            [
                'customer',
                ['address'],
                [],
                '',
            ],
            [
                'customer',
                [],
                [['no columns passed']],
                $this->replaceQuotes("INSERT INTO [[customer]] () VALUES ('no columns passed')"),
            ],
            'bool-false, bool2-null' => [
                'type',
                ['bool_col', 'bool_col2'],
                [[false, null]],
                'expected' => $this->replaceQuotes('INSERT INTO [[type]] ([[bool_col]], [[bool_col2]]) VALUES (0, NULL)'),
            ],
            [
                '{{%type}}',
                ['{{%type}}.[[float_col]]', '[[time]]'],
                [[null, new Expression('now()')]],
                'INSERT INTO {{%type}} ({{%type}}.[[float_col]], [[time]]) VALUES (NULL, now())',
            ],
            'bool-false, time-now()' => [
                '{{%type}}',
                ['{{%type}}.[[bool_col]]', '[[time]]'],
                [[false, new Expression('now()')]],
                'expected' => 'INSERT INTO {{%type}} ({{%type}}.[[bool_col]], [[time]]) VALUES (0, now())',
            ],
        ];
    }

    /**
     * @dataProvider batchInsertProvider
     */
    public function testBatchInsert($table, $columns, $value, $expected)
    {
        $queryBuilder = $this->getQueryBuilder();

        $sql = $queryBuilder->batchInsert($table, $columns, $value);
        $this->assertEquals($expected, $sql);
    }
//
//    public function testUpdate()
//    {
//        // TODO implement
//    }
//
//    public function testDelete()
//    {
//        // TODO implement
//    }


    public function testCommentColumn()
    {
        $qb = $this->getQueryBuilder();

        $expected = "ALTER TABLE [[comment]] CHANGE [[add_comment]] [[add_comment]] varchar(255) NOT NULL COMMENT 'This is my column.'";
        $sql = $qb->addCommentOnColumn('comment', 'add_comment', 'This is my column.');
        $this->assertEquals($this->replaceQuotes($expected), $sql);

        $expected = "ALTER TABLE [[comment]] CHANGE [[replace_comment]] [[replace_comment]] varchar(255) DEFAULT NULL COMMENT 'This is my column.'";
        $sql = $qb->addCommentOnColumn('comment', 'replace_comment', 'This is my column.');
        $this->assertEquals($this->replaceQuotes($expected), $sql);

        $expected = "ALTER TABLE [[comment]] CHANGE [[delete_comment]] [[delete_comment]] varchar(128) NOT NULL COMMENT ''";
        $sql = $qb->dropCommentFromColumn('comment', 'delete_comment');
        $this->assertEquals($this->replaceQuotes($expected), $sql);
    }

    public function testCommentTable()
    {
        $qb = $this->getQueryBuilder();

        $expected = "ALTER TABLE [[comment]] COMMENT 'This is my table.'";
        $sql = $qb->addCommentOnTable('comment', 'This is my table.');
        $this->assertEquals($this->replaceQuotes($expected), $sql);

        $expected = "ALTER TABLE [[comment]] COMMENT ''";
        $sql = $qb->dropCommentFromTable('comment');
        $this->assertEquals($this->replaceQuotes($expected), $sql);
    }

    public function likeConditionProvider()
    {
        $conditions = [
            // simple like
            [['like', 'name', 'foo%'], '[[name]] LIKE :qp0', [':qp0' => '%foo\%%']],
            [['not like', 'name', 'foo%'], '[[name]] NOT LIKE :qp0', [':qp0' => '%foo\%%']],
            [['or like', 'name', 'foo%'], '[[name]] LIKE :qp0', [':qp0' => '%foo\%%']],
            [['or not like', 'name', 'foo%'], '[[name]] NOT LIKE :qp0', [':qp0' => '%foo\%%']],

            // like for many values
            [['like', 'name', ['foo%', '[abc]']], '[[name]] LIKE :qp0 AND [[name]] LIKE :qp1', [':qp0' => '%foo\%%', ':qp1' => '%[abc]%']],
            [['not like', 'name', ['foo%', '[abc]']], '[[name]] NOT LIKE :qp0 AND [[name]] NOT LIKE :qp1', [':qp0' => '%foo\%%', ':qp1' => '%[abc]%']],
            [['or like', 'name', ['foo%', '[abc]']], '[[name]] LIKE :qp0 OR [[name]] LIKE :qp1', [':qp0' => '%foo\%%', ':qp1' => '%[abc]%']],
            [['or not like', 'name', ['foo%', '[abc]']], '[[name]] NOT LIKE :qp0 OR [[name]] NOT LIKE :qp1', [':qp0' => '%foo\%%', ':qp1' => '%[abc]%']],

            // like with Expression
            [['like', 'name', new Expression('CONCAT("test", name, "%")')], '[[name]] LIKE CONCAT("test", name, "%")', []],
            [['not like', 'name', new Expression('CONCAT("test", name, "%")')], '[[name]] NOT LIKE CONCAT("test", name, "%")', []],
            [['or like', 'name', new Expression('CONCAT("test", name, "%")')], '[[name]] LIKE CONCAT("test", name, "%")', []],
            [['or not like', 'name', new Expression('CONCAT("test", name, "%")')], '[[name]] NOT LIKE CONCAT("test", name, "%")', []],
            [['like', 'name', [new Expression('CONCAT("test", name, "%")'), '\ab_c']], '[[name]] LIKE CONCAT("test", name, "%") AND [[name]] LIKE :qp0', [':qp0' => '%\\\ab\_c%']],
            [['not like', 'name', [new Expression('CONCAT("test", name, "%")'), '\ab_c']], '[[name]] NOT LIKE CONCAT("test", name, "%") AND [[name]] NOT LIKE :qp0', [':qp0' => '%\\\ab\_c%']],
            [['or like', 'name', [new Expression('CONCAT("test", name, "%")'), '\ab_c']], '[[name]] LIKE CONCAT("test", name, "%") OR [[name]] LIKE :qp0', [':qp0' => '%\\\ab\_c%']],
            [['or not like', 'name', [new Expression('CONCAT("test", name, "%")'), '\ab_c']], '[[name]] NOT LIKE CONCAT("test", name, "%") OR [[name]] NOT LIKE :qp0', [':qp0' => '%\\\ab\_c%']],
        ];

        // adjust dbms specific escaping
        foreach ($conditions as $i => $condition) {
            $conditions[$i][1] = $this->replaceQuotes($condition[1]);
            if (!empty($this->likeEscapeCharSql)) {
                preg_match_all('/(?P<condition>LIKE.+?)( AND| OR|$)/', $conditions[$i][1], $matches, PREG_SET_ORDER);
                foreach ($matches as $match) {
                    $conditions[$i][1] = str_replace($match['condition'], $match['condition'] . $this->likeEscapeCharSql, $conditions[$i][1]);
                }
            }
            foreach ($conditions[$i][2] as $name => $value) {
                $conditions[$i][2][$name] = strtr($conditions[$i][2][$name], $this->likeParameterReplacements);
            }
        }
        return $conditions;
    }

    /**
     * @dataProvider likeConditionProvider
     */
    public function testBuildLikeCondition($condition, $expected, $expectedParams)
    {
        $query = (new Query())->where($condition);
        list($sql, $params) = $this->getQueryBuilder()->build($query);
        $this->assertEquals('SELECT *' . (empty($expected) ? '' : ' WHERE ' . $this->replaceQuotes($expected)), $sql);
        $this->assertEquals($expectedParams, $params);
    }
}
