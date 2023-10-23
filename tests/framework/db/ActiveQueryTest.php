<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db;

use yii\base\Event;
use yii\db\ActiveQuery;
use yii\db\Connection;
use yii\db\QueryBuilder;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\Category;
use yiiunit\data\ar\Customer;
use yiiunit\data\ar\Order;
use yiiunit\data\ar\Profile;

/**
 * Class ActiveQueryTest the base class for testing ActiveQuery.
 */
abstract class ActiveQueryTest extends DatabaseTestCase
{
    public function setUp(): void
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
        $callback = function (\yii\base\Event $event) use ($where): void {
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

    public function testPopulate_EmptyRows(): void
    {
        $query = new ActiveQuery(Customer::class);
        $rows = [];
        $result = $query->populate([]);
        $this->assertEquals($rows, $result);
    }

    /**
     * @todo tests for internal logic of populate()
     */
    public function testPopulate_FilledRows(): void
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
    public function testGetQueryTableName_from_not_set(): void
    {
        $query = new ActiveQuery(Customer::class);
        $result = $this->invokeMethod($query, 'getTableNameAndAlias');
        $this->assertEquals(['customer', 'customer'], $result);
    }

    public function testGetQueryTableName_from_set(): void
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

    public function testAndOnCondition_on_not_set(): void
    {
        $query = new ActiveQuery(Customer::class);
        $on = ['active' => true];
        $params = ['a' => 'b'];
        $result = $query->andOnCondition($on, $params);
        $this->assertEquals($on, $result->on);
        $this->assertEquals($params, $result->params);
    }

    public function testAndOnCondition_on_set(): void
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

    public function testOrOnCondition_on_not_set(): void
    {
        $query = new ActiveQuery(Customer::class);
        $on = ['active' => true];
        $params = ['a' => 'b'];
        $result = $query->orOnCondition($on, $params);
        $this->assertEquals($on, $result->on);
        $this->assertEquals($params, $result->params);
    }

    public function testOrOnCondition_on_set(): void
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

    public function testAlias_not_set(): void
    {
        $query = new ActiveQuery(Customer::class);
        $result = $query->alias('alias');
        $this->assertInstanceOf('yii\db\ActiveQuery', $result);
        $this->assertEquals(['alias' => 'customer'], $result->from);
    }

    public function testAlias_yet_set(): void
    {
        $aliasOld = ['old'];
        $query = new ActiveQuery(Customer::class);
        $query->from = $aliasOld;
        $result = $query->alias('alias');
        $this->assertInstanceOf('yii\db\ActiveQuery', $result);
        $this->assertEquals(['alias' => 'old'], $result->from);
    }

    use GetTablesAliasTestTrait;
    protected function createQuery()
    {
        return new ActiveQuery(null);
    }

    public function testGetTableNames_notFilledFrom(): void
    {
        $query = new ActiveQuery(Profile::class);

        $tables = $query->getTablesUsedInFrom();

        $this->assertEquals([
            '{{' . Profile::tableName() . '}}' => '{{' . Profile::tableName() . '}}',
        ], $tables);
    }

    public function testGetTableNames_wontFillFrom(): void
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
        /* @var $category Category */
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
}
