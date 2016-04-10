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
        return array_merge(parent::columnTypes(), [
            [
                Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT TRUE',
                $this->boolean()->notNull()->defaultValue(true),
                'boolean NOT NULL DEFAULT TRUE'
            ],
            [
                Schema::TYPE_CHAR . ' CHECK (value LIKE \'test%\')',
                $this->char()->check('value LIKE \'test%\''),
                'char(1) CHECK (value LIKE \'test%\')'
            ],
            [
                Schema::TYPE_CHAR . '(6) CHECK (value LIKE \'test%\')',
                $this->char(6)->check('value LIKE \'test%\''),
                'char(6) CHECK (value LIKE \'test%\')'
            ],
            [
                Schema::TYPE_CHAR . '(6)',
                $this->char(6)->unsigned(),
                'char(6)'
            ],
            [
                Schema::TYPE_INTEGER . '(8)',
                $this->integer(8)->unsigned(),
                'integer'
            ],
            [
                Schema::TYPE_TIMESTAMP . '(4)',
                $this->timestamp(4),
                'timestamp(4)'
            ],
        ]);
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
