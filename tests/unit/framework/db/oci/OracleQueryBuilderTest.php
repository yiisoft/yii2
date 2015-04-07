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
     * Removed PK and TIME columns
     * @return array
     */
    public function columnTypes()
    {
        return [
            [Schema::TYPE_STRING, 'VARCHAR2(255)'],
            [Schema::TYPE_STRING . '(32)', 'VARCHAR2(32)'],
            [Schema::TYPE_STRING . ' CHECK (value LIKE \'test%\')', 'VARCHAR2(255) CHECK (value LIKE \'test%\')'],
            [Schema::TYPE_STRING . '(32) CHECK (value LIKE \'test%\')', 'VARCHAR2(32) CHECK (value LIKE \'test%\')'],
            [Schema::TYPE_STRING . ' NOT NULL', 'VARCHAR2(255) NOT NULL'],
            [Schema::TYPE_TEXT, 'CLOB'],
            [Schema::TYPE_TEXT . '(255)', 'CLOB'],
            [Schema::TYPE_TEXT . ' CHECK (value LIKE \'test%\')', 'CLOB CHECK (value LIKE \'test%\')'],
            [Schema::TYPE_TEXT . '(255) CHECK (value LIKE \'test%\')', 'CLOB CHECK (value LIKE \'test%\')'],
            [Schema::TYPE_TEXT . ' NOT NULL', 'CLOB NOT NULL'],
            [Schema::TYPE_TEXT . '(255) NOT NULL', 'CLOB NOT NULL'],
            [Schema::TYPE_SMALLINT, 'NUMBER(5)'],
            [Schema::TYPE_SMALLINT . '(8)', 'NUMBER(8)'],
            [Schema::TYPE_INTEGER, 'NUMBER(10)'],
            [Schema::TYPE_INTEGER . '(8)', 'NUMBER(8)'],
            [Schema::TYPE_INTEGER . ' CHECK (value > 5)', 'NUMBER(10) CHECK (value > 5)'],
            [Schema::TYPE_INTEGER . '(8) CHECK (value > 5)', 'NUMBER(8) CHECK (value > 5)'],
            [Schema::TYPE_INTEGER . ' NOT NULL', 'NUMBER(10) NOT NULL'],
            [Schema::TYPE_BIGINT, 'NUMBER(20)'],
            [Schema::TYPE_BIGINT . '(8)', 'NUMBER(8)'],
            [Schema::TYPE_BIGINT . ' CHECK (value > 5)', 'NUMBER(20) CHECK (value > 5)'],
            [Schema::TYPE_BIGINT . '(8) CHECK (value > 5)', 'NUMBER(8) CHECK (value > 5)'],
            [Schema::TYPE_BIGINT . ' NOT NULL', 'NUMBER(20) NOT NULL'],
            [Schema::TYPE_FLOAT, 'NUMBER'],
            [Schema::TYPE_FLOAT . ' CHECK (value > 5.6)', 'NUMBER CHECK (value > 5.6)'],
            [Schema::TYPE_FLOAT . '(16,5) CHECK (value > 5.6)', 'NUMBER CHECK (value > 5.6)'],
            [Schema::TYPE_FLOAT . ' NOT NULL', 'NUMBER NOT NULL'],
            [Schema::TYPE_DECIMAL, 'NUMBER'],
            [Schema::TYPE_DECIMAL . '(12,4)', 'NUMBER'],
            [Schema::TYPE_DECIMAL . ' CHECK (value > 5.6)', 'NUMBER CHECK (value > 5.6)'],
            [Schema::TYPE_DECIMAL . '(12,4) CHECK (value > 5.6)', 'NUMBER CHECK (value > 5.6)'],
            [Schema::TYPE_DECIMAL . ' NOT NULL', 'NUMBER NOT NULL'],
            [Schema::TYPE_DATETIME, 'TIMESTAMP'],
            [
                Schema::TYPE_DATETIME . " CHECK (value BETWEEN to_timestamp('2011-01-01', 'yyyy-mm-dd') AND to_timestamp('2013-01-01', 'yyyy-mm-dd'))",
                "TIMESTAMP CHECK (value BETWEEN to_timestamp('2011-01-01', 'yyyy-mm-dd') AND to_timestamp('2013-01-01', 'yyyy-mm-dd'))",
            ],
            [Schema::TYPE_DATETIME . ' NOT NULL', 'TIMESTAMP NOT NULL'],
            [Schema::TYPE_TIMESTAMP, 'TIMESTAMP'],
            [
                Schema::TYPE_TIMESTAMP . " CHECK (value BETWEEN to_timestamp('2011-01-01', 'yyyy-mm-dd') AND to_timestamp('2013-01-01', 'yyyy-mm-dd'))",
                "TIMESTAMP CHECK (value BETWEEN to_timestamp('2011-01-01', 'yyyy-mm-dd') AND to_timestamp('2013-01-01', 'yyyy-mm-dd'))",
            ],
            [Schema::TYPE_TIMESTAMP . ' NOT NULL', 'TIMESTAMP NOT NULL'],
            [Schema::TYPE_TIMESTAMP.'(4)', 'TIMESTAMP'],
            [Schema::TYPE_DATE, 'DATE'],
            [
                Schema::TYPE_DATE . " CHECK (value BETWEEN to_date('2011-01-01', 'yyyy-mm-dd') AND to_date('2013-01-01', 'yyyy-mm-dd'))",
                "DATE CHECK (value BETWEEN to_date('2011-01-01', 'yyyy-mm-dd') AND to_date('2013-01-01', 'yyyy-mm-dd'))"],
            [Schema::TYPE_DATE . ' NOT NULL', 'DATE NOT NULL'],
            [Schema::TYPE_BINARY, 'BLOB'],
            [Schema::TYPE_BOOLEAN, 'NUMBER(1)'],
            [Schema::TYPE_BOOLEAN . ' DEFAULT 1 NOT NULL', 'NUMBER(1) DEFAULT 1 NOT NULL'],
            [Schema::TYPE_MONEY, 'NUMBER(19,4)'],
            [Schema::TYPE_MONEY . '(16,2)', 'NUMBER(16,2)'],
            [Schema::TYPE_MONEY . ' CHECK (value > 0.0)', 'NUMBER(19,4) CHECK (value > 0.0)'],
            [Schema::TYPE_MONEY . '(16,2) CHECK (value > 0.0)', 'NUMBER(16,2) CHECK (value > 0.0)'],
            [Schema::TYPE_MONEY . ' NOT NULL', 'NUMBER(19,4) NOT NULL'],
        ];
    }
}
