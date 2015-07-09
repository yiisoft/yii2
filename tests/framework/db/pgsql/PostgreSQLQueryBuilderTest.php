<?php

namespace yiiunit\framework\db\pgsql;

use yii\db\Schema;
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
            [Schema::TYPE_PK, Schema::primaryKey(), 'serial NOT NULL PRIMARY KEY'],
            [Schema::TYPE_PK . '(8)', Schema::primaryKey(8), 'serial NOT NULL PRIMARY KEY'],
            [Schema::TYPE_PK . ' CHECK (value > 5)', Schema::primaryKey()->check('value > 5'), 'serial NOT NULL PRIMARY KEY CHECK (value > 5)'],
            [Schema::TYPE_PK . '(8) CHECK (value > 5)', Schema::primaryKey(8)->check('value > 5'), 'serial NOT NULL PRIMARY KEY CHECK (value > 5)'],
            [Schema::TYPE_STRING, Schema::string(), 'varchar(255)'],
            [Schema::TYPE_STRING . '(32)', Schema::string(32), 'varchar(32)'],
            [Schema::TYPE_STRING . ' CHECK (value LIKE \'test%\')', Schema::string()->check('value LIKE \'test%\''), 'varchar(255) CHECK (value LIKE \'test%\')'],
            [Schema::TYPE_STRING . '(32) CHECK (value LIKE \'test%\')', Schema::string(32)->check('value LIKE \'test%\''), 'varchar(32) CHECK (value LIKE \'test%\')'],
            [Schema::TYPE_STRING . ' NOT NULL', Schema::string()->notNull(), 'varchar(255) NOT NULL'],
            [Schema::TYPE_TEXT, Schema::text(), 'text'],
            [Schema::TYPE_TEXT . '(255)', Schema::text(255), 'text'],
            [Schema::TYPE_TEXT . ' CHECK (value LIKE \'test%\')', Schema::text()->check('value LIKE \'test%\''), 'text CHECK (value LIKE \'test%\')'],
            [Schema::TYPE_TEXT . '(255) CHECK (value LIKE \'test%\')', Schema::text(255)->check('value LIKE \'test%\''), 'text CHECK (value LIKE \'test%\')'],
            [Schema::TYPE_TEXT . ' NOT NULL', Schema::text()->notNull(), 'text NOT NULL'],
            [Schema::TYPE_TEXT . '(255) NOT NULL', Schema::text(255)->notNull(), 'text NOT NULL'],
            [Schema::TYPE_SMALLINT, Schema::smallInteger(), 'smallint'],
            [Schema::TYPE_SMALLINT . '(8)', Schema::smallInteger(8), 'smallint'],
            [Schema::TYPE_INTEGER, Schema::integer(), 'integer'],
            [Schema::TYPE_INTEGER . '(8)', Schema::integer(8), 'integer'],
            [Schema::TYPE_INTEGER . ' CHECK (value > 5)', Schema::integer()->check('value > 5'), 'integer CHECK (value > 5)'],
            [Schema::TYPE_INTEGER . '(8) CHECK (value > 5)', Schema::integer(8)->check('value > 5'), 'integer CHECK (value > 5)'],
            [Schema::TYPE_INTEGER . ' NOT NULL', Schema::integer()->notNull(), 'integer NOT NULL'],
            [Schema::TYPE_BIGINT, Schema::bigInteger(), 'bigint'],
            [Schema::TYPE_BIGINT . '(8)', Schema::bigInteger(8), 'bigint'],
            [Schema::TYPE_BIGINT . ' CHECK (value > 5)', Schema::bigInteger()->check('value > 5'), 'bigint CHECK (value > 5)'],
            [Schema::TYPE_BIGINT . '(8) CHECK (value > 5)', Schema::bigInteger(8)->check('value > 5'), 'bigint CHECK (value > 5)'],
            [Schema::TYPE_BIGINT . ' NOT NULL', Schema::bigInteger()->notNull(), 'bigint NOT NULL'],
            [Schema::TYPE_FLOAT, Schema::float(), 'double precision'],
            [Schema::TYPE_FLOAT . ' CHECK (value > 5.6)', Schema::float()->check('value > 5.6'), 'double precision CHECK (value > 5.6)'],
            [Schema::TYPE_FLOAT . '(16,5) CHECK (value > 5.6)', Schema::float(16, 5)->check('value > 5.6'), 'double precision CHECK (value > 5.6)'],
            [Schema::TYPE_FLOAT . ' NOT NULL', Schema::float()->notNull(), 'double precision NOT NULL'],
            [Schema::TYPE_DECIMAL, Schema::decimal(), 'numeric(10,0)'],
            [Schema::TYPE_DECIMAL . '(12,4)', Schema::decimal(12, 4), 'numeric(12,4)'],
            [Schema::TYPE_DECIMAL . ' CHECK (value > 5.6)', Schema::decimal()->check('value > 5.6'), 'numeric(10,0) CHECK (value > 5.6)'],
            [Schema::TYPE_DECIMAL . '(12,4) CHECK (value > 5.6)', Schema::decimal(12, 4)->check('value > 5.6'), 'numeric(12,4) CHECK (value > 5.6)'],
            [Schema::TYPE_DECIMAL . ' NOT NULL', Schema::decimal()->notNull(), 'numeric(10,0) NOT NULL'], [Schema::TYPE_DATETIME, Schema::dateTime(), 'timestamp(0)'],
            [Schema::TYPE_DATETIME . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')", Schema::dateTime()->check("value BETWEEN '2011-01-01' AND '2013-01-01'"), "timestamp(0) CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_DATETIME . ' NOT NULL', Schema::dateTime()->notNull(), 'timestamp(0) NOT NULL'],
            [Schema::TYPE_TIMESTAMP, Schema::timestamp(), 'timestamp(0)'],
            [Schema::TYPE_TIMESTAMP . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')", Schema::timestamp()->check("value BETWEEN '2011-01-01' AND '2013-01-01'"), "timestamp(0) CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_TIMESTAMP . ' NOT NULL', Schema::timestamp()->notNull(), 'timestamp(0) NOT NULL'],
            [Schema::TYPE_TIMESTAMP . '(4)', Schema::timestamp(4), 'timestamp(4)'],
            [Schema::TYPE_TIME, Schema::time(), 'time(0)'],
            [Schema::TYPE_TIME . " CHECK (value BETWEEN '12:00:00' AND '13:01:01')", Schema::time()->check("value BETWEEN '12:00:00' AND '13:01:01'"), "time(0) CHECK (value BETWEEN '12:00:00' AND '13:01:01')"],
            [Schema::TYPE_TIME . ' NOT NULL', Schema::time()->notNull(), 'time(0) NOT NULL'],
            [Schema::TYPE_DATE, Schema::date(), 'date'],
            [Schema::TYPE_DATE . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')", Schema::date()->check("value BETWEEN '2011-01-01' AND '2013-01-01'"), "date CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_DATE . ' NOT NULL', Schema::date()->notNull(), 'date NOT NULL'],
            [Schema::TYPE_BINARY, Schema::binary(), 'bytea'],
            [Schema::TYPE_BOOLEAN, Schema::boolean(), 'boolean'],
            [Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT TRUE', Schema::boolean()->notNull()->default(true), 'boolean NOT NULL DEFAULT TRUE'],
            [Schema::TYPE_MONEY, Schema::money(), 'numeric(19,4)'],
            [Schema::TYPE_MONEY . '(16,2)', Schema::money(16, 2), 'numeric(16,2)'],
            [Schema::TYPE_MONEY . ' CHECK (value > 0.0)', Schema::money()->check('value > 0.0'), 'numeric(19,4) CHECK (value > 0.0)'],
            [Schema::TYPE_MONEY . '(16,2) CHECK (value > 0.0)', Schema::money(16, 2)->check('value > 0.0'), 'numeric(16,2) CHECK (value > 0.0)'],
            [Schema::TYPE_MONEY . ' NOT NULL', Schema::money()->notNull(), 'numeric(19,4) NOT NULL'],
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
