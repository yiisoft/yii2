<?php

namespace yiiunit\framework\db\pgsql;

use yii\db\pgsql\Schema;
use yiiunit\framework\db\QueryBuilderTest;

/**
 * @group db
 * @group pgsql
 */
class PostgreSQLQueryBuilderTest extends QueryBuilderTest
{
    public $driverName = 'pgsql';

    public function columnTypes()
    {
        return [
            [Schema::TYPE_PK, 'serial NOT NULL PRIMARY KEY'],
            [Schema::TYPE_PK . '(8)', 'serial NOT NULL PRIMARY KEY'],
            [Schema::TYPE_PK . ' CHECK (value > 5)', 'serial NOT NULL PRIMARY KEY CHECK (value > 5)'],
            [Schema::TYPE_PK . '(8) CHECK (value > 5)', 'serial NOT NULL PRIMARY KEY CHECK (value > 5)'],
            [Schema::TYPE_STRING, 'varchar(255)'],
            [Schema::TYPE_STRING . '(32)', 'varchar(32)'],
            [Schema::TYPE_STRING . ' CHECK (value LIKE \'test%\')', 'varchar(255) CHECK (value LIKE \'test%\')'],
            [Schema::TYPE_STRING . '(32) CHECK (value LIKE \'test%\')', 'varchar(32) CHECK (value LIKE \'test%\')'],
            [Schema::TYPE_STRING . ' NOT NULL', 'varchar(255) NOT NULL'],
            [Schema::TYPE_TEXT, 'text'],
            [Schema::TYPE_TEXT . '(255)', 'text'],
            [Schema::TYPE_TEXT . ' CHECK (value LIKE \'test%\')', 'text CHECK (value LIKE \'test%\')'],
            [Schema::TYPE_TEXT . '(255) CHECK (value LIKE \'test%\')', 'text CHECK (value LIKE \'test%\')'],
            [Schema::TYPE_TEXT . ' NOT NULL', 'text NOT NULL'],
            [Schema::TYPE_TEXT . '(255) NOT NULL', 'text NOT NULL'],
            [Schema::TYPE_SMALLINT, 'smallint'],
            [Schema::TYPE_SMALLINT . '(8)', 'smallint'],
            [Schema::TYPE_INTEGER, 'integer'],
            [Schema::TYPE_INTEGER . '(8)', 'integer'],
            [Schema::TYPE_INTEGER . ' CHECK (value > 5)', 'integer CHECK (value > 5)'],
            [Schema::TYPE_INTEGER . '(8) CHECK (value > 5)', 'integer CHECK (value > 5)'],
            [Schema::TYPE_INTEGER . ' NOT NULL', 'integer NOT NULL'],
            [Schema::TYPE_BIGINT, 'bigint'],
            [Schema::TYPE_BIGINT . '(8)', 'bigint'],
            [Schema::TYPE_BIGINT . ' CHECK (value > 5)', 'bigint CHECK (value > 5)'],
            [Schema::TYPE_BIGINT . '(8) CHECK (value > 5)', 'bigint CHECK (value > 5)'],
            [Schema::TYPE_BIGINT . ' NOT NULL', 'bigint NOT NULL'],
            [Schema::TYPE_FLOAT, 'double precision'],
            [Schema::TYPE_FLOAT . ' CHECK (value > 5.6)', 'double precision CHECK (value > 5.6)'],
            [Schema::TYPE_FLOAT . '(16,5) CHECK (value > 5.6)', 'double precision CHECK (value > 5.6)'],
            [Schema::TYPE_FLOAT . ' NOT NULL', 'double precision NOT NULL'],
            [Schema::TYPE_DECIMAL, 'numeric(10,0)'],
            [Schema::TYPE_DECIMAL . '(12,4)', 'numeric(12,4)'],
            [Schema::TYPE_DECIMAL . ' CHECK (value > 5.6)', 'numeric(10,0) CHECK (value > 5.6)'],
            [Schema::TYPE_DECIMAL . '(12,4) CHECK (value > 5.6)', 'numeric(12,4) CHECK (value > 5.6)'],
            [Schema::TYPE_DECIMAL . ' NOT NULL', 'numeric(10,0) NOT NULL'],
            [Schema::TYPE_DATETIME, 'timestamp(0)'],
            [Schema::TYPE_DATETIME . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')", "timestamp(0) CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_DATETIME . ' NOT NULL', 'timestamp(0) NOT NULL'],
            [Schema::TYPE_TIMESTAMP, 'timestamp(0)'],
            [Schema::TYPE_TIMESTAMP . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')", "timestamp(0) CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_TIMESTAMP . ' NOT NULL', 'timestamp(0) NOT NULL'],
            [Schema::TYPE_TIMESTAMP.'(4)', 'timestamp(4)'],
            [Schema::TYPE_TIME, 'time(0)'],
            [Schema::TYPE_TIME . " CHECK (value BETWEEN '12:00:00' AND '13:01:01')", "time(0) CHECK (value BETWEEN '12:00:00' AND '13:01:01')"],
            [Schema::TYPE_TIME . ' NOT NULL', 'time(0) NOT NULL'],
            [Schema::TYPE_DATE, 'date'],
            [Schema::TYPE_DATE . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')", "date CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_DATE . ' NOT NULL', 'date NOT NULL'],
            [Schema::TYPE_BINARY, 'bytea'],
            [Schema::TYPE_BOOLEAN, 'boolean'],
            [Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT TRUE', 'boolean NOT NULL DEFAULT TRUE'],
            [Schema::TYPE_MONEY, 'numeric(19,4)'],
            [Schema::TYPE_MONEY . '(16,2)', 'numeric(16,2)'],
            [Schema::TYPE_MONEY . ' CHECK (value > 0.0)', 'numeric(19,4) CHECK (value > 0.0)'],
            [Schema::TYPE_MONEY . '(16,2) CHECK (value > 0.0)', 'numeric(16,2) CHECK (value > 0.0)'],
            [Schema::TYPE_MONEY . ' NOT NULL', 'numeric(19,4) NOT NULL'],
        ];
    }

    public function conditionProvider()
    {
        return array_merge(parent::conditionProvider(), [
            // adding conditions for ILIKE i.e. case insensitive LIKE
            // http://www.postgresql.org/docs/8.3/static/functions-matching.html#FUNCTIONS-LIKE

            // empty values
            [ ['ilike', 'name', []], '0=1', [] ],
            [ ['not ilike', 'name', []], '', [] ],
            [ ['or ilike', 'name', []], '0=1', [] ],
            [ ['or not ilike', 'name', []], '', [] ],

            // simple ilike
            [ ['ilike', 'name', 'heyho'], '"name" ILIKE :qp0', [':qp0' => '%heyho%'] ],
            [ ['not ilike', 'name', 'heyho'], '"name" NOT ILIKE :qp0', [':qp0' => '%heyho%'] ],
            [ ['or ilike', 'name', 'heyho'], '"name" ILIKE :qp0', [':qp0' => '%heyho%'] ],
            [ ['or not ilike', 'name', 'heyho'], '"name" NOT ILIKE :qp0', [':qp0' => '%heyho%'] ],

            // ilike for many values
            [ ['ilike', 'name', ['heyho', 'abc']], '"name" ILIKE :qp0 AND "name" ILIKE :qp1', [':qp0' => '%heyho%', ':qp1' => '%abc%'] ],
            [ ['not ilike', 'name', ['heyho', 'abc']], '"name" NOT ILIKE :qp0 AND "name" NOT ILIKE :qp1', [':qp0' => '%heyho%', ':qp1' => '%abc%'] ],
            [ ['or ilike', 'name', ['heyho', 'abc']], '"name" ILIKE :qp0 OR "name" ILIKE :qp1', [':qp0' => '%heyho%', ':qp1' => '%abc%'] ],
            [ ['or not ilike', 'name', ['heyho', 'abc']], '"name" NOT ILIKE :qp0 OR "name" NOT ILIKE :qp1', [':qp0' => '%heyho%', ':qp1' => '%abc%'] ],
        ]);
    }

    public function testAlterColumn()
    {
        $qb = $this->getQueryBuilder();

        $expected = 'ALTER TABLE "foo1" ALTER COLUMN "bar" TYPE varchar(255)';
        $sql = $qb->alterColumn('foo1', 'bar', 'varchar(255)');
        $this->assertEquals($expected, $sql);

        $expected = 'ALTER TABLE "foo1" ALTER COLUMN "bar" SET NOT null';
        $sql = $qb->alterColumn('foo1', 'bar', 'SET NOT null');
        $this->assertEquals($expected, $sql);

        $expected = 'ALTER TABLE "foo1" ALTER COLUMN "bar" drop default';
        $sql = $qb->alterColumn('foo1', 'bar', 'drop default');
        $this->assertEquals($expected, $sql);

        $expected = 'ALTER TABLE "foo1" ALTER COLUMN "bar" reset xyz';
        $sql = $qb->alterColumn('foo1', 'bar', 'reset xyz');
        $this->assertEquals($expected, $sql);
    }
}
