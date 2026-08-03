<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\base\db;

use yiiunit\framework\db\DatabaseTestCase;
use yiiunit\framework\db\GetTablesAliasTestTrait;
use yii\base\Event;
use yii\db\ActiveQuery;
use yii\db\Connection;
use yii\db\QueryBuilder;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\AlternativeConnectionRecord;
use yiiunit\data\ar\BaseOnlyRecord;
use yiiunit\data\ar\Category;
use yiiunit\data\ar\Customer;
use yiiunit\data\ar\Order;
use yiiunit\data\ar\Profile;

/**
 * Class ActiveQueryTest the base class for testing ActiveQuery.
 */
abstract class BaseActiveQuery extends DatabaseTestCase
{
    use GetTablesAliasTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        ActiveRecord::$db = $this->getConnection();
    }

    public function testConstructor(): void
    {
        $config = [
            'on' => ['a' => 'b'],
            'joinWith' => ['dummy relation'],
        ];
        $query = new ActiveQuery(Customer::class, $config);
        $this->assertEquals($query->modelClass, Customer::class);
        $this->assertEquals($query->on, $config['on']);
        $this->assertEquals($query->joinWith, $config['joinWith']);
    }

    public function testTriggerInitEvent(): void
    {
        $where = '1==1';
        $callback = function (Event $event) use ($where) {
            $event->sender->where = $where;
        };
        Event::on(ActiveQuery::class, ActiveQuery::EVENT_INIT, $callback);
        $result = new ActiveQuery(Customer::class);
        $this->assertEquals($where, $result->where);
        Event::off(ActiveQuery::class, ActiveQuery::EVENT_INIT, $callback);
    }

    /**
     * @todo tests for internal logic of prepare()
     */
    public function testPrepare(): void
    {
        $query = new ActiveQuery(Customer::class);
        $builder = new QueryBuilder(new Connection());
        $result = $query->prepare($builder);
        $this->assertInstanceOf('yii\db\Query', $result);
    }

    public function testPopulateEmptyRows(): void
    {
        $query = new ActiveQuery(Customer::class);
        $rows = [];
        $result = $query->populate([]);
        $this->assertEquals($rows, $result);
    }

    /**
     * @todo tests for internal logic of populate()
     */
    public function testPopulateFilledRows(): void
    {
        $query = new ActiveQuery(Customer::class);
        $rows = $query->all();
        $result = $query->populate($rows);
        $this->assertEquals($rows, $result);
    }

    /**
     * @todo tests for internal logic of one()
     */
    public function testOne(): void
    {
        $query = new ActiveQuery(Customer::class);
        $result = $query->one();
        $this->assertInstanceOf('yiiunit\data\ar\Customer', $result);
    }

    /**
     * @todo test internal logic of createCommand()
     */
    public function testCreateCommand(): void
    {
        $query = new ActiveQuery(Customer::class);
        $result = $query->createCommand();
        $this->assertInstanceOf('yii\db\Command', $result);
    }

    /**
     * @todo tests for internal logic of queryScalar()
     */
    public function testQueryScalar(): void
    {
        $query = new ActiveQuery(Customer::class);
        $result = $this->invokeMethod($query, 'queryScalar', ['name', null]);
        $this->assertEquals('user1', $result);
    }

    /**
     * @todo tests for internal logic of joinWith()
     */
    public function testJoinWith(): void
    {
        $query = new ActiveQuery(Customer::class);
        $result = $query->joinWith('profile');
        $this->assertEquals([
            [['profile'], true, 'LEFT JOIN'],
        ], $result->joinWith);
    }

    /**
     * @todo tests for internal logic of innerJoinWith()
     */
    public function testInnerJoinWith(): void
    {
        $query = new ActiveQuery(Customer::class);
        $result = $query->innerJoinWith('profile');
        $this->assertEquals([
            [['profile'], true, 'INNER JOIN'],
        ], $result->joinWith);
    }

    public function testBuildJoinWithRemoveDuplicateJoinByTableName(): void
    {
        $query = new ActiveQuery(Customer::class);
        $query->innerJoinWith('orders')
            ->joinWith('orders.orderItems');
        $this->invokeMethod($query, 'buildJoinWith');
        $this->assertEquals([
            [
                'INNER JOIN',
                'order',
                '{{customer}}.[[id]] = {{order}}.[[customer_id]]'
            ],
            [
                'LEFT JOIN',
                'order_item',
                '{{order}}.[[id]] = {{order_item}}.[[order_id]]'
            ],
        ], $query->join);
    }

    /**
     * @todo tests for the regex inside getQueryTableName
     */
    public function testGetQueryTableNameFromNotSet(): void
    {
        $query = new ActiveQuery(Customer::class);
        $result = $this->invokeMethod($query, 'getTableNameAndAlias');
        $this->assertEquals(['customer', 'customer'], $result);
    }

    public function testGetQueryTableNameFromSet(): void
    {
        $options = ['from' => ['alias' => 'customer']];
        $query = new ActiveQuery(Customer::class, $options);
        $result = $this->invokeMethod($query, 'getTableNameAndAlias');
        $this->assertEquals(['customer', 'alias'], $result);
    }

    public function testOnCondition(): void
    {
        $query = new ActiveQuery(Customer::class);
        $on = ['active' => true];
        $params = ['a' => 'b'];
        $result = $query->onCondition($on, $params);
        $this->assertEquals($on, $result->on);
        $this->assertEquals($params, $result->params);
    }

    public function testAndOnConditionOnNotSet(): void
    {
        $query = new ActiveQuery(Customer::class);
        $on = ['active' => true];
        $params = ['a' => 'b'];
        $result = $query->andOnCondition($on, $params);
        $this->assertEquals($on, $result->on);
        $this->assertEquals($params, $result->params);
    }

    public function testAndOnConditionOnSet(): void
    {
        $onOld = ['active' => true];
        $query = new ActiveQuery(Customer::class);
        $query->on = $onOld;

        $on = ['active' => true];
        $params = ['a' => 'b'];
        $result = $query->andOnCondition($on, $params);
        $this->assertEquals(['and', $onOld, $on], $result->on);
        $this->assertEquals($params, $result->params);
    }

    public function testOrOnConditionOnNotSet(): void
    {
        $query = new ActiveQuery(Customer::class);
        $on = ['active' => true];
        $params = ['a' => 'b'];
        $result = $query->orOnCondition($on, $params);
        $this->assertEquals($on, $result->on);
        $this->assertEquals($params, $result->params);
    }

    public function testOrOnConditionOnSet(): void
    {
        $onOld = ['active' => true];
        $query = new ActiveQuery(Customer::class);
        $query->on = $onOld;

        $on = ['active' => true];
        $params = ['a' => 'b'];
        $result = $query->orOnCondition($on, $params);
        $this->assertEquals(['or', $onOld, $on], $result->on);
        $this->assertEquals($params, $result->params);
    }

    /**
     * @todo tests for internal logic of viaTable()
     */
    public function testViaTable(): void
    {
        $query = new ActiveQuery(Customer::class, ['primaryModel' => new Order()]);
        $result = $query->viaTable(Profile::class, ['id' => 'item_id']);
        $this->assertInstanceOf('yii\db\ActiveQuery', $result);
        $this->assertInstanceOf('yii\db\ActiveQuery', $result->via);
    }

    public function testAliasNotSet(): void
    {
        $query = new ActiveQuery(Customer::class);
        $result = $query->alias('alias');
        $this->assertInstanceOf('yii\db\ActiveQuery', $result);
        $this->assertEquals(['alias' => 'customer'], $result->from);
    }

    public function testAliasYetSet(): void
    {
        $aliasOld = ['old'];
        $query = new ActiveQuery(Customer::class);
        $query->from = $aliasOld;
        $result = $query->alias('alias');
        $this->assertInstanceOf('yii\db\ActiveQuery', $result);
        $this->assertEquals(['alias' => 'old'], $result->from);
    }

    protected function createQuery()
    {
        return new ActiveQuery(null);
    }

    public function testGetTableNamesNotFilledFrom(): void
    {
        $query = new ActiveQuery(Profile::class);

        $tables = $query->getTablesUsedInFrom();

        $this->assertEquals([
            '{{' . Profile::tableName() . '}}' => '{{' . Profile::tableName() . '}}',
        ], $tables);
    }

    public function testGetTableNamesWontFillFrom(): void
    {
        $query = new ActiveQuery(Profile::class);
        $this->assertEquals($query->from, null);
        $query->getTablesUsedInFrom();
        $this->assertEquals($query->from, null);
    }

    /**
     * https://github.com/yiisoft/yii2/issues/5341
     *
     * Issue:     Plan     1 -- * Account * -- * User
     * Our Tests: Category 1 -- * Item    * -- * Order
     */
    public function testDeeplyNestedTableRelationWith(): void
    {
        $categories = Category::find()->with('orders')->indexBy('id')->all();
        $category = $categories[1];
        $this->assertNotNull($category);
        $orders = $category->orders;
        $this->assertEquals(2, count($orders));
        $this->assertInstanceOf(Order::class, $orders[0]);
        $this->assertInstanceOf(Order::class, $orders[1]);
        $ids = [$orders[0]->id, $orders[1]->id];
        sort($ids);
        $this->assertEquals([1, 3], $ids);

        $category = $categories[2];
        $this->assertNotNull($category);
        $orders = $category->orders;
        $this->assertEquals(1, count($orders));
        $this->assertInstanceOf(Order::class, $orders[0]);
        $this->assertEquals(2, $orders[0]->id);
    }

    public function testExplicitConnectionIsUsedToPopulateModels(): void
    {
        $db = $this->createAlternativeConnection();

        /** @var ActiveQuery<AlternativeConnectionRecord> $query */
        $query = new ActiveQuery(AlternativeConnectionRecord::class);

        $models = $query
            ->orderBy(['id' => SORT_ASC])
            ->all($db);

        self::assertCount(
            2,
            $models,
            'Record count mismatch.',
        );
        self::assertSame(
            1,
            $models[0]->id,
            "First record id must be '1'.",
        );
        self::assertSame(
            2,
            $models[1]->id,
            "Second record id must be '2'.",
        );
        self::assertSame(
            'first',
            $models[0]->name,
            'First record name must match.',
        );
        self::assertSame(
            'second',
            $models[1]->name,
            'Second record name must match.',
        );
        self::assertTrue(
            $models[0]->populated,
            "First record populated flag must be 'true'.",
        );
        self::assertTrue(
            $models[1]->populated,
            "Second record populated flag must be 'true'.",
        );
    }

    public function testExplicitConnectionIsUsedToPopulateOneModel(): void
    {
        $db = $this->createAlternativeConnection();

        /** @var ActiveQuery<AlternativeConnectionRecord> $query */
        $query = new ActiveQuery(AlternativeConnectionRecord::class);

        $model = $query
            ->where(['id' => 2])
            ->one($db);

        self::assertInstanceOf(
            AlternativeConnectionRecord::class,
            $model,
            'Model type mismatch.',
        );
        self::assertSame(
            2,
            $model->id,
            "Model id must be '2'.",
        );
        self::assertSame(
            'second',
            $model->name,
            'Model name must match.',
        );
        self::assertTrue(
            $model->populated,
            "Populated flag must be 'true'.",
        );
    }

    public function testExplicitConnectionIsUsedToPopulateBatches(): void
    {
        $db = $this->createAlternativeConnection();

        /** @var ActiveQuery<AlternativeConnectionRecord> $query */
        $query = new ActiveQuery(AlternativeConnectionRecord::class);

        $batches = iterator_to_array(
            $query
                ->orderBy(['id' => SORT_ASC])
                ->batch(1, $db),
            false,
        );

        self::assertCount(
            2,
            $batches,
            'Batch count mismatch.',
        );
        self::assertSame(
            1,
            $batches[0][0]->id,
            "First batch model id must be '1'.",
        );
        self::assertSame(
            2,
            $batches[1][0]->id,
            "Second batch model id must be '2'.",
        );
        self::assertTrue(
            $batches[0][0]->populated,
            "First batch populated flag must be 'true'.",
        );
        self::assertTrue(
            $batches[1][0]->populated,
            "Second batch populated flag must be 'true'.",
        );
    }

    public function testExplicitConnectionIsUsedToPopulateEachModel(): void
    {
        $db = $this->createAlternativeConnection();

        /** @var ActiveQuery<AlternativeConnectionRecord> $query */
        $query = new ActiveQuery(AlternativeConnectionRecord::class);

        $models = iterator_to_array(
            $query
                ->orderBy(['id' => SORT_ASC])
                ->each(1, $db),
            false,
        );

        self::assertCount(
            2,
            $models,
            'Model count mismatch.',
        );
        self::assertSame(
            1,
            $models[0]->id,
            "First model id must be '1'.",
        );
        self::assertSame(
            2,
            $models[1]->id,
            "Second model id must be '2'.",
        );
        self::assertTrue(
            $models[0]->populated,
            "First model populated flag must be 'true'.",
        );
        self::assertTrue(
            $models[1]->populated,
            "Second model populated flag must be 'true'.",
        );
    }

    public function testExplicitConnectionIsUsedToDeduplicateJoinedModels(): void
    {
        $db = $this->createAlternativeConnection();

        /** @var ActiveQuery<AlternativeConnectionRecord> $query */
        $query = new ActiveQuery(AlternativeConnectionRecord::class);

        $models = $query
            ->join(
                'LEFT JOIN',
                ['dup' => 'alternative_connection_record'],
                '{{dup}}.[[id]] >= {{alternative_connection_record}}.[[id]]',
            )
            ->orderBy(['alternative_connection_record.id' => SORT_ASC])
            ->all($db);

        self::assertCount(
            2,
            $models,
            'Join duplicates must be removed.',
        );
        self::assertSame(
            1,
            $models[0]->id,
            "First model id must be '1'.",
        );
        self::assertSame(
            2,
            $models[1]->id,
            "Second model id must be '2'.",
        );
    }

    public function testPopulateWithExplicitConnectionSupportsBaseActiveRecordModels(): void
    {
        $db = new Connection(['dsn' => 'sqlite::memory:']);

        $query = new ActiveQuery(BaseOnlyRecord::class);

        $models = $query->populate(
            [
                ['id' => 1, 'name' => 'first'],
            ],
            $db,
        );

        self::assertCount(
            1,
            $models,
            'Record count mismatch.',
        );

        $model = $models[0];

        self::assertInstanceOf(
            BaseOnlyRecord::class,
            $model,
            'Model type mismatch.',
        );
        self::assertSame(
            1,
            $model->id,
            "Model id must be '1'.",
        );
        self::assertSame(
            'first',
            $model->name,
            'Model name must match.',
        );
        self::assertTrue(
            $model->populated,
            "Populated flag must be 'true'.",
        );
    }

    private function createAlternativeConnection(): Connection
    {
        $db = new Connection(['dsn' => 'sqlite::memory:']);

        $db->open();
        $db->createCommand(
            <<<'SQL'
            CREATE TABLE alternative_connection_record (
                id INTEGER PRIMARY KEY,
                name TEXT NOT NULL
            )
            SQL,
        )->execute();
        $db->createCommand(
            <<<'SQL'
            INSERT INTO alternative_connection_record (id, name)
            VALUES (1, 'first'), (2, 'second')
            SQL,
        )->execute();

        return $db;
    }
}
