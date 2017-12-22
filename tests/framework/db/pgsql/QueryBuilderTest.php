<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\pgsql;

use yii\base\DynamicModel;
use yii\db\Expression;
use yii\db\ArrayExpression;
use yii\db\JsonExpression;
use yii\db\Query;
use yii\db\Schema;
use yii\helpers\Json;
use yiiunit\data\base\TraversableObject;

/**
 * @group db
 * @group pgsql
 */
class QueryBuilderTest extends \yiiunit\framework\db\QueryBuilderTest
{
    public $driverName = 'pgsql';

    public function columnTypes()
    {
        return array_merge(parent::columnTypes(), [
            [
                Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT TRUE',
                $this->boolean()->notNull()->defaultValue(true),
                'boolean NOT NULL DEFAULT TRUE',
            ],
            [
                Schema::TYPE_CHAR . ' CHECK (value LIKE \'test%\')',
                $this->char()->check('value LIKE \'test%\''),
                'char(1) CHECK (value LIKE \'test%\')',
            ],
            [
                Schema::TYPE_CHAR . '(6) CHECK (value LIKE \'test%\')',
                $this->char(6)->check('value LIKE \'test%\''),
                'char(6) CHECK (value LIKE \'test%\')',
            ],
            [
                Schema::TYPE_CHAR . '(6)',
                $this->char(6)->unsigned(),
                'char(6)',
            ],
            [
                Schema::TYPE_INTEGER . '(8)',
                $this->integer(8)->unsigned(),
                'integer',
            ],
            [
                Schema::TYPE_TIMESTAMP . '(4)',
                $this->timestamp(4),
                'timestamp(4)',
            ],
        ]);
    }

    public function conditionProvider()
    {
        return array_merge(parent::conditionProvider(), [
            // adding conditions for ILIKE i.e. case insensitive LIKE
            // http://www.postgresql.org/docs/8.3/static/functions-matching.html#FUNCTIONS-LIKE

            // empty values
            [['ilike', 'name', []], '0=1', []],
            [['not ilike', 'name', []], '', []],
            [['or ilike', 'name', []], '0=1', []],
            [['or not ilike', 'name', []], '', []],

            // simple ilike
            [['ilike', 'name', 'heyho'], '"name" ILIKE :qp0', [':qp0' => '%heyho%']],
            [['not ilike', 'name', 'heyho'], '"name" NOT ILIKE :qp0', [':qp0' => '%heyho%']],
            [['or ilike', 'name', 'heyho'], '"name" ILIKE :qp0', [':qp0' => '%heyho%']],
            [['or not ilike', 'name', 'heyho'], '"name" NOT ILIKE :qp0', [':qp0' => '%heyho%']],

            // ilike for many values
            [['ilike', 'name', ['heyho', 'abc']], '"name" ILIKE :qp0 AND "name" ILIKE :qp1', [':qp0' => '%heyho%', ':qp1' => '%abc%']],
            [['not ilike', 'name', ['heyho', 'abc']], '"name" NOT ILIKE :qp0 AND "name" NOT ILIKE :qp1', [':qp0' => '%heyho%', ':qp1' => '%abc%']],
            [['or ilike', 'name', ['heyho', 'abc']], '"name" ILIKE :qp0 OR "name" ILIKE :qp1', [':qp0' => '%heyho%', ':qp1' => '%abc%']],
            [['or not ilike', 'name', ['heyho', 'abc']], '"name" NOT ILIKE :qp0 OR "name" NOT ILIKE :qp1', [':qp0' => '%heyho%', ':qp1' => '%abc%']],

            // array condition corner cases
            [['@>', 'id', new ArrayExpression([1])], '"id" @> ARRAY[:qp0]', [':qp0' => 1]],
            'scalar can not be converted to array #1' => [['@>', 'id', new ArrayExpression(1)], '"id" @> \'{}\'', []],
            ['scalar can not be converted to array #2' => ['@>', 'id', new ArrayExpression(false)], '"id" @> \'{}\'', []],
            [['&&', 'price', new ArrayExpression([12, 14], 'float')], '"price" && ARRAY[:qp0, :qp1]::float[]', [':qp0' => 12, ':qp1' => 14]],
            [['@>', 'id', new ArrayExpression([2, 3])], '"id" @> ARRAY[:qp0, :qp1]', [':qp0' => 2, ':qp1' => 3]],
            'array of arrays' => [['@>', 'id', new ArrayExpression([[1,2], [3,4]], 'float', 2)], '"id" @> ARRAY[ARRAY[:qp0, :qp1]::float[], ARRAY[:qp2, :qp3]::float[]\\]::float[][]', [':qp0' => 1, ':qp1' => 2, ':qp2' => 3, ':qp3' => 4]],
            [['@>', 'id', new ArrayExpression([])], '"id" @> \'{}\'', []],
            'array can contain nulls' => [['@>', 'id', new ArrayExpression([null])], '"id" @> ARRAY[:qp0]', [':qp0' => null]],
            'traversable objects are supported' => [['@>', 'id', new ArrayExpression(new TraversableObject([1, 2, 3]))], '[[id]] @> ARRAY[:qp0, :qp1, :qp2]', [':qp0' => 1, ':qp1' => 2, ':qp2' => 3]],
            [['@>', 'time', new ArrayExpression([new Expression('now()')])], '[[time]] @> ARRAY[now()]', []],
            [['@>', 'id', new ArrayExpression((new Query())->select('id')->from('users')->where(['active' => 1]))], '[[id]] @> ARRAY(SELECT [[id]] FROM [[users]] WHERE [[active]]=:qp0)', [':qp0' => 1]],
            [['@>', 'id', new ArrayExpression([(new Query())->select('id')->from('users')->where(['active' => 1])], 'integer')], '[[id]] @> ARRAY[ARRAY(SELECT [[id]] FROM [[users]] WHERE [[active]]=:qp0)::integer[]]::integer[]', [':qp0' => 1]],

            // json conditions
            [['=', 'jsoncol', new JsonExpression(['lang' => 'uk', 'country' => 'UA'])], '[[jsoncol]] = :qp0', [':qp0' => '{"lang":"uk","country":"UA"}']],
            [['=', 'jsoncol', new JsonExpression([false])], '[[jsoncol]] = :qp0', [':qp0' => '[false]']],
            [['=', 'prices', new JsonExpression(['seeds' => 15, 'apples' => 25], 'jsonb')], '[[prices]] = :qp0::jsonb', [':qp0' => '{"seeds":15,"apples":25}']],
            'nested json' => [
                ['=', 'data', new JsonExpression(['user' => ['login' => 'silverfire', 'password' => 'c4ny0ur34d17?'], 'props' => ['mood' => 'good']])],
                '"data" = :qp0', [':qp0' => '{"user":{"login":"silverfire","password":"c4ny0ur34d17?"},"props":{"mood":"good"}}']
            ],
            'null value' => [['=', 'jsoncol', new JsonExpression(null)], '"jsoncol" = :qp0', [':qp0' => 'null']],
            'null as array value' => [['=', 'jsoncol', new JsonExpression([null])], '"jsoncol" = :qp0', [':qp0' => '[null]']],
            'null as object value' => [['=', 'jsoncol', new JsonExpression(['nil' => null])], '"jsoncol" = :qp0', [':qp0' => '{"nil":null}']],

            [['=', 'jsoncol', new JsonExpression(new DynamicModel(['a' => 1, 'b' => 2]))], '[[jsoncol]] = :qp0', [':qp0' => '{"a":1,"b":2}']],
            'query' => [['=', 'jsoncol', new JsonExpression((new Query())->select('params')->from('user')->where(['id' => 1]))], '[[jsoncol]] = (SELECT [[params]] FROM [[user]] WHERE [[id]]=:qp0)', [':qp0' => 1]],
            'query with type' => [['=', 'jsoncol', new JsonExpression((new Query())->select('params')->from('user')->where(['id' => 1]), 'jsonb')], '[[jsoncol]] = (SELECT [[params]] FROM [[user]] WHERE [[id]]=:qp0)::jsonb', [':qp0' => 1]],

            // Checks to verity that operators work correctly
            [['@>', 'id', new ArrayExpression([1])], '"id" @> ARRAY[:qp0]', [':qp0' => 1]],
            [['<@', 'id', new ArrayExpression([1])], '"id" <@ ARRAY[:qp0]', [':qp0' => 1]],
            [['=', 'id',  new ArrayExpression([1])], '"id" = ARRAY[:qp0]', [':qp0' => 1]],
            [['<>', 'id', new ArrayExpression([1])], '"id" <> ARRAY[:qp0]', [':qp0' => 1]],
            [['>', 'id',  new ArrayExpression([1])], '"id" > ARRAY[:qp0]', [':qp0' => 1]],
            [['<', 'id',  new ArrayExpression([1])], '"id" < ARRAY[:qp0]', [':qp0' => 1]],
            [['>=', 'id', new ArrayExpression([1])], '"id" >= ARRAY[:qp0]', [':qp0' => 1]],
            [['<=', 'id', new ArrayExpression([1])], '"id" <= ARRAY[:qp0]', [':qp0' => 1]],
            [['&&', 'id', new ArrayExpression([1])], '"id" && ARRAY[:qp0]', [':qp0' => 1]],
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

    public function indexesProvider()
    {
        $result = parent::indexesProvider();
        $result['drop'][0] = 'DROP INDEX [[CN_constraints_2_single]]';
        return $result;
    }

    public function defaultValuesProvider()
    {
        $this->markTestSkipped('Adding/dropping default constraints is not supported in PostgreSQL.');
    }

    public function testCommentColumn()
    {
        $qb = $this->getQueryBuilder();

        $expected = "COMMENT ON COLUMN [[comment]].[[text]] IS 'This is my column.'";
        $sql = $qb->addCommentOnColumn('comment', 'text', 'This is my column.');
        $this->assertEquals($this->replaceQuotes($expected), $sql);

        $expected = 'COMMENT ON COLUMN [[comment]].[[text]] IS NULL';
        $sql = $qb->dropCommentFromColumn('comment', 'text');
        $this->assertEquals($this->replaceQuotes($expected), $sql);
    }

    public function testCommentTable()
    {
        $qb = $this->getQueryBuilder();

        $expected = "COMMENT ON TABLE [[comment]] IS 'This is my table.'";
        $sql = $qb->addCommentOnTable('comment', 'This is my table.');
        $this->assertEquals($this->replaceQuotes($expected), $sql);

        $expected = 'COMMENT ON TABLE [[comment]] IS NULL';
        $sql = $qb->dropCommentFromTable('comment');
        $this->assertEquals($this->replaceQuotes($expected), $sql);
    }

    public function batchInsertProvider()
    {
        $data = parent::batchInsertProvider();

        $data['escape-danger-chars']['expected'] = "INSERT INTO \"customer\" (\"address\") VALUES ('SQL-danger chars are escaped: ''); --')";
        $data['bool-false, bool2-null']['expected'] = 'INSERT INTO "type" ("bool_col", "bool_col2") VALUES (FALSE, NULL)';
        $data['bool-false, time-now()']['expected'] = 'INSERT INTO {{%type}} ({{%type}}.[[bool_col]], [[time]]) VALUES (FALSE, now())';

        return $data;
    }

    public function testResetSequence()
    {
        $qb = $this->getQueryBuilder();

        $expected = "SELECT SETVAL('\"item_id_seq\"',(SELECT COALESCE(MAX(\"id\"),0) FROM \"item\")+1,false)";
        $sql = $qb->resetSequence('item');
        $this->assertEquals($expected, $sql);

        $expected = "SELECT SETVAL('\"item_id_seq\"',4,false)";
        $sql = $qb->resetSequence('item', 4);
        $this->assertEquals($expected, $sql);
    }
}
