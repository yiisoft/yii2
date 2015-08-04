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
            [Schema::TYPE_PK, $this->primaryKey(), 'NUMBER(10) NOT NULL PRIMARY KEY'],
            [Schema::TYPE_PK . '(8)', $this->primaryKey(8), 'NUMBER(8) NOT NULL PRIMARY KEY'],
            [Schema::TYPE_PK . ' CHECK (value > 5)', $this->primaryKey()->check('value > 5'), 'NUMBER(10) NOT NULL PRIMARY KEY CHECK (value > 5)'],
            [Schema::TYPE_PK . '(8) CHECK (value > 5)', $this->primaryKey(8)->check('value > 5'), 'NUMBER(8) NOT NULL PRIMARY KEY CHECK (value > 5)'],
            [Schema::TYPE_STRING, $this->string(), 'VARCHAR2(255)'],
            [Schema::TYPE_STRING . '(32)', $this->string(32), 'VARCHAR2(32)'],
            [Schema::TYPE_STRING . ' CHECK (value LIKE \'test%\')', $this->string()->check('value LIKE \'test%\''), 'VARCHAR2(255) CHECK (value LIKE \'test%\')'],
            [Schema::TYPE_STRING . '(32) CHECK (value LIKE \'test%\')', $this->string(32)->check('value LIKE \'test%\''), 'VARCHAR2(32) CHECK (value LIKE \'test%\')'],
            [Schema::TYPE_STRING . ' NOT NULL', $this->string()->notNull(), 'VARCHAR2(255) NOT NULL'],
            [Schema::TYPE_TEXT, $this->text(), 'CLOB'],
            [Schema::TYPE_TEXT . '(255)', $this->text(255), 'CLOB'],
            [Schema::TYPE_TEXT . ' CHECK (value LIKE \'test%\')', $this->text()->check('value LIKE \'test%\''), 'CLOB CHECK (value LIKE \'test%\')'],
            [Schema::TYPE_TEXT . '(255) CHECK (value LIKE \'test%\')', $this->text(255)->check('value LIKE \'test%\''), 'CLOB CHECK (value LIKE \'test%\')'],
            [Schema::TYPE_TEXT . ' NOT NULL', $this->text()->notNull(), 'CLOB NOT NULL'],
            [Schema::TYPE_TEXT . '(255) NOT NULL', $this->text(255)->notNull(), 'CLOB NOT NULL'],
            [Schema::TYPE_SMALLINT, $this->smallInteger(), 'NUMBER(5)'],
            [Schema::TYPE_SMALLINT . '(8)', $this->smallInteger(8), 'NUMBER(8)'],
            [Schema::TYPE_INTEGER, $this->integer(), 'NUMBER(10)'],
            [Schema::TYPE_INTEGER . '(8)', $this->integer(8), 'NUMBER(8)'],
            [Schema::TYPE_INTEGER . ' CHECK (value > 5)', $this->integer()->check('value > 5'), 'NUMBER(10) CHECK (value > 5)'],
            [Schema::TYPE_INTEGER . '(8) CHECK (value > 5)', $this->integer(8)->check('value > 5'), 'NUMBER(8) CHECK (value > 5)'],
            [Schema::TYPE_INTEGER . ' NOT NULL', $this->integer()->notNull(), 'NUMBER(10) NOT NULL'],
            [Schema::TYPE_BIGINT, $this->bigInteger(), 'NUMBER(20)'],
            [Schema::TYPE_BIGINT . '(8)', $this->bigInteger(8), 'NUMBER(8)'],
            [Schema::TYPE_BIGINT . ' CHECK (value > 5)', $this->bigInteger()->check('value > 5'), 'NUMBER(20) CHECK (value > 5)'],
            [Schema::TYPE_BIGINT . '(8) CHECK (value > 5)', $this->bigInteger(8)->check('value > 5'), 'NUMBER(8) CHECK (value > 5)'],
            [Schema::TYPE_BIGINT . ' NOT NULL', $this->bigInteger()->notNull(), 'NUMBER(20) NOT NULL'],
            [Schema::TYPE_FLOAT, $this->float(), 'NUMBER'],
            [Schema::TYPE_FLOAT . '(16)', $this->float(16), 'NUMBER'],
            [Schema::TYPE_FLOAT . ' CHECK (value > 5.6)', $this->float()->check('value > 5.6'), 'NUMBER CHECK (value > 5.6)'],
            [Schema::TYPE_FLOAT . '(16) CHECK (value > 5.6)', $this->float(16)->check('value > 5.6'), 'NUMBER CHECK (value > 5.6)'],
            [Schema::TYPE_FLOAT . ' NOT NULL', $this->float()->notNull(), 'NUMBER NOT NULL'],
            [Schema::TYPE_DOUBLE, $this->double(), 'NUMBER'],
            [Schema::TYPE_DOUBLE . '(16)', $this->double(16), 'NUMBER'],
            [Schema::TYPE_DOUBLE . ' CHECK (value > 5.6)', $this->double()->check('value > 5.6'), 'NUMBER CHECK (value > 5.6)'],
            [Schema::TYPE_DOUBLE . '(16) CHECK (value > 5.6)', $this->double(16)->check('value > 5.6'), 'NUMBER CHECK (value > 5.6)'],
            [Schema::TYPE_DOUBLE . ' NOT NULL', $this->double()->notNull(), 'NUMBER NOT NULL'],
            [Schema::TYPE_DECIMAL, $this->decimal(), 'NUMBER'],
            [Schema::TYPE_DECIMAL . '(12,4)', $this->decimal(12, 4), 'NUMBER'],
            [Schema::TYPE_DECIMAL . ' CHECK (value > 5.6)', $this->decimal()->check('value > 5.6'), 'NUMBER CHECK (value > 5.6)'],
            [Schema::TYPE_DECIMAL . '(12,4) CHECK (value > 5.6)', $this->decimal(12, 4)->check('value > 5.6'), 'NUMBER CHECK (value > 5.6)'],
            [Schema::TYPE_DECIMAL . ' NOT NULL', $this->decimal()->notNull(), 'NUMBER NOT NULL'],
            [Schema::TYPE_DATETIME, $this->dateTime(), 'TIMESTAMP'],
            //[Schema::TYPE_DATETIME . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')", "TIMESTAMP CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_DATETIME . ' NOT NULL', $this->dateTime()->notNull(), 'TIMESTAMP NOT NULL'],
            [Schema::TYPE_TIMESTAMP, $this->timestamp(), 'TIMESTAMP'],
            //[Schema::TYPE_TIMESTAMP . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')", "TIMESTAMP CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_TIMESTAMP . ' NOT NULL', $this->timestamp()->notNull(), 'TIMESTAMP NOT NULL'],
            [Schema::TYPE_TIME, $this->time(), 'TIMESTAMP'],
            //[Schema::TYPE_TIME . " CHECK (value BETWEEN '12:00:00' AND '13:01:01')", "TIMESTAMP CHECK (value BETWEEN '12:00:00' AND '13:01:01')"],
            [Schema::TYPE_TIME . ' NOT NULL', $this->time()->notNull(), 'TIMESTAMP NOT NULL'],
            [Schema::TYPE_DATE, $this->date(), 'DATE'],
            //[Schema::TYPE_DATE . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')", "DATE CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_DATE . ' NOT NULL', $this->date()->notNull(), 'DATE NOT NULL'],
            [Schema::TYPE_BINARY, $this->binary(), 'BLOB'],
            [Schema::TYPE_BOOLEAN, $this->boolean(), 'NUMBER(1)'],
            [Schema::TYPE_BOOLEAN . ' DEFAULT 1 NOT NULL', $this->boolean()->notNull()->defaultValue(1), 'NUMBER(1) DEFAULT 1 NOT NULL'],
            [Schema::TYPE_MONEY, $this->money(), 'NUMBER(19,4)'],
            [Schema::TYPE_MONEY . '(16,2)', $this->money(16, 2), 'NUMBER(16,2)'],
            [Schema::TYPE_MONEY . ' CHECK (value > 0.0)', $this->money()->check('value > 0.0'), 'NUMBER(19,4) CHECK (value > 0.0)'],
            [Schema::TYPE_MONEY . '(16,2) CHECK (value > 0.0)', $this->money(16, 2)->check('value > 0.0'), 'NUMBER(16,2) CHECK (value > 0.0)'],
            [Schema::TYPE_MONEY . ' NOT NULL', $this->money()->notNull(), 'NUMBER(19,4) NOT NULL'],
        ];
    }
}
