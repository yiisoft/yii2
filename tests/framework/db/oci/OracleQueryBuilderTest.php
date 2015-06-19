<?php

namespace yiiunit\framework\db\oci;

use yii\db\oci\Schema;
use yiiunit\framework\db\QueryBuilderTest;

/**
 * @group db
 * @group oci
 */
class OracleQueryBuilderTest extends QueryBuilderTest
{
    public $driverName = 'oci';

    /**
     * this is not used as a dataprovider for testGetColumnType to speed up the test
     * when used as dataprovider every single line will cause a reconnect with the database which is not needed here
     */
    public function columnTypes()
    {
        return [
            [Schema::TYPE_PK, Schema::primaryKey(), 'NUMBER(10) NOT NULL PRIMARY KEY'],
            [Schema::TYPE_PK . '(8)', Schema::primaryKey(8), 'NUMBER(8) NOT NULL PRIMARY KEY'],
            [Schema::TYPE_PK . ' CHECK (value > 5)', Schema::primaryKey()->check('value > 5'), 'NUMBER(10) NOT NULL PRIMARY KEY CHECK (value > 5)'],
            [Schema::TYPE_PK . '(8) CHECK (value > 5)', Schema::primaryKey(8)->check('value > 5'), 'NUMBER(8) NOT NULL PRIMARY KEY CHECK (value > 5)'],
            [Schema::TYPE_STRING, Schema::string(), 'VARCHAR2(255)'],
            [Schema::TYPE_STRING . '(32)', Schema::string(32), 'VARCHAR2(32)'],
            [Schema::TYPE_STRING . ' CHECK (value LIKE \'test%\')', Schema::string()->check('value LIKE \'test%\''), 'VARCHAR2(255) CHECK (value LIKE \'test%\')'],
            [Schema::TYPE_STRING . '(32) CHECK (value LIKE \'test%\')', Schema::string(32)->check('value LIKE \'test%\''), 'VARCHAR2(32) CHECK (value LIKE \'test%\')'],
            [Schema::TYPE_STRING . ' NOT NULL', Schema::string()->notNull(), 'VARCHAR2(255) NOT NULL'],
            [Schema::TYPE_TEXT, Schema::text(), 'CLOB'],
            [Schema::TYPE_TEXT . '(255)', Schema::text(255), 'CLOB'],
            [Schema::TYPE_TEXT . ' CHECK (value LIKE \'test%\')', Schema::text()->check('value LIKE \'test%\''), 'CLOB CHECK (value LIKE \'test%\')'],
            [Schema::TYPE_TEXT . '(255) CHECK (value LIKE \'test%\')', Schema::text(255)->check('value LIKE \'test%\''), 'CLOB CHECK (value LIKE \'test%\')'],
            [Schema::TYPE_TEXT . ' NOT NULL', Schema::text()->notNull(), 'CLOB NOT NULL'],
            [Schema::TYPE_TEXT . '(255) NOT NULL', Schema::text(255)->notNull(), 'CLOB NOT NULL'],
            [Schema::TYPE_SMALLINT, Schema::smallInteger(), 'NUMBER(5)'],
            [Schema::TYPE_SMALLINT . '(8)', Schema::smallInteger(8), 'NUMBER(8)'],
            [Schema::TYPE_INTEGER, Schema::integer(), 'NUMBER(10)'],
            [Schema::TYPE_INTEGER . '(8)', Schema::integer(8), 'NUMBER(8)'],
            [Schema::TYPE_INTEGER . ' CHECK (value > 5)', Schema::integer()->check('value > 5'), 'NUMBER(10) CHECK (value > 5)'],
            [Schema::TYPE_INTEGER . '(8) CHECK (value > 5)', Schema::integer(8)->check('value > 5'), 'NUMBER(8) CHECK (value > 5)'],
            [Schema::TYPE_INTEGER . ' NOT NULL', Schema::integer()->notNull(), 'NUMBER(10) NOT NULL'],
            [Schema::TYPE_BIGINT, Schema::bigInteger(), 'NUMBER(20)'],
            [Schema::TYPE_BIGINT . '(8)', Schema::bigInteger(8), 'NUMBER(8)'],
            [Schema::TYPE_BIGINT . ' CHECK (value > 5)', Schema::bigInteger()->check('value > 5'), 'NUMBER(20) CHECK (value > 5)'],
            [Schema::TYPE_BIGINT . '(8) CHECK (value > 5)', Schema::bigInteger(8)->check('value > 5'), 'NUMBER(8) CHECK (value > 5)'],
            [Schema::TYPE_BIGINT . ' NOT NULL', Schema::bigInteger()->notNull(), 'NUMBER(20) NOT NULL'],
            [Schema::TYPE_FLOAT, Schema::float(), 'NUMBER'],
            [Schema::TYPE_FLOAT . '(16,5)', Schema::float(16, 5), 'NUMBER'],
            [Schema::TYPE_FLOAT . ' CHECK (value > 5.6)', Schema::float()->check('value > 5.6'), 'NUMBER CHECK (value > 5.6)'],
            [Schema::TYPE_FLOAT . '(16,5) CHECK (value > 5.6)', Schema::float(16, 5)->check('value > 5.6'), 'NUMBER CHECK (value > 5.6)'],
            [Schema::TYPE_FLOAT . ' NOT NULL', Schema::float()->notNull(), 'NUMBER NOT NULL'],
            [Schema::TYPE_DOUBLE, Schema::double(), 'NUMBER'],
            [Schema::TYPE_DOUBLE . '(16,5)', Schema::double(16, 5), 'NUMBER'],
            [Schema::TYPE_DOUBLE . ' CHECK (value > 5.6)', Schema::double()->check('value > 5.6'), 'NUMBER CHECK (value > 5.6)'],
            [Schema::TYPE_DOUBLE . '(16,5) CHECK (value > 5.6)', Schema::double(16, 5)->check('value > 5.6'), 'NUMBER CHECK (value > 5.6)'],
            [Schema::TYPE_DOUBLE . ' NOT NULL', Schema::double()->notNull(), 'NUMBER NOT NULL'],
            [Schema::TYPE_DECIMAL, Schema::decimal(), 'NUMBER'],
            [Schema::TYPE_DECIMAL . '(12,4)', Schema::decimal(12, 4), 'NUMBER'],
            [Schema::TYPE_DECIMAL . ' CHECK (value > 5.6)', Schema::decimal()->check('value > 5.6'), 'NUMBER CHECK (value > 5.6)'],
            [Schema::TYPE_DECIMAL . '(12,4) CHECK (value > 5.6)', Schema::decimal(12, 4)->check('value > 5.6'), 'NUMBER CHECK (value > 5.6)'],
            [Schema::TYPE_DECIMAL . ' NOT NULL', Schema::decimal()->notNull(), 'NUMBER NOT NULL'],
            [Schema::TYPE_DATETIME, Schema::dateTime(), 'TIMESTAMP'],
            //[Schema::TYPE_DATETIME . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')", "TIMESTAMP CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_DATETIME . ' NOT NULL', Schema::dateTime()->notNull(), 'TIMESTAMP NOT NULL'],
            [Schema::TYPE_TIMESTAMP, Schema::timestamp(), 'TIMESTAMP'],
            //[Schema::TYPE_TIMESTAMP . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')", "TIMESTAMP CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_TIMESTAMP . ' NOT NULL', Schema::timestamp()->notNull(), 'TIMESTAMP NOT NULL'],
            [Schema::TYPE_TIME, Schema::time(), 'TIMESTAMP'],
            //[Schema::TYPE_TIME . " CHECK (value BETWEEN '12:00:00' AND '13:01:01')", "TIMESTAMP CHECK (value BETWEEN '12:00:00' AND '13:01:01')"],
            [Schema::TYPE_TIME . ' NOT NULL', Schema::time()->notNull(), 'TIMESTAMP NOT NULL'],
            [Schema::TYPE_DATE, Schema::date(), 'DATE'],
            //[Schema::TYPE_DATE . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')", "DATE CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_DATE . ' NOT NULL', Schema::date()->notNull(), 'DATE NOT NULL'],
            [Schema::TYPE_BINARY, Schema::binary(), 'BLOB'],
            [Schema::TYPE_BOOLEAN, Schema::boolean(), 'NUMBER(1)'],
            [Schema::TYPE_BOOLEAN . ' DEFAULT 1 NOT NULL', Schema::boolean()->notNull()->default(1), 'NUMBER(1) DEFAULT 1 NOT NULL'],
            [Schema::TYPE_MONEY, Schema::money(), 'NUMBER(19,4)'],
            [Schema::TYPE_MONEY . '(16,2)', Schema::money(16, 2), 'NUMBER(16,2)'],
            [Schema::TYPE_MONEY . ' CHECK (value > 0.0)', Schema::money()->check('value > 0.0'), 'NUMBER(19,4) CHECK (value > 0.0)'],
            [Schema::TYPE_MONEY . '(16,2) CHECK (value > 0.0)', Schema::money(16, 2)->check('value > 0.0'), 'NUMBER(16,2) CHECK (value > 0.0)'],
            [Schema::TYPE_MONEY . ' NOT NULL', Schema::money()->notNull(), 'NUMBER(19,4) NOT NULL'],
        ];
    }
}
