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
use yii\db\Query;
use yii\db\QueryBuilder;
use yii\helpers\ArrayHelper;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\Category;
use yiiunit\data\ar\Customer;
use yiiunit\data\ar\Item;
use yiiunit\data\ar\Order;
use yiiunit\data\ar\OrderItem;
use yiiunit\data\ar\Profile;

/**
 * Class ActiveQueryTest the base class for testing ActiveQuery.
 */
abstract class ActiveQueryTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        ActiveRecord::$db = $this->getConnection();
    }

    public function testConstructor()
    {
        $config = [
            'on' => ['a' => 'b'],
            'joinWith' => ['dummy relation'],
        ];
        $query = new ActiveQuery(Customer::className(), $config);
        $this->assertEquals($query->modelClass, Customer::className());
        $this->assertEquals($query->on, $config['on']);
        $this->assertEquals($query->joinWith, $config['joinWith']);
    }

    public function testTriggerInitEvent()
    {
        $where = '1==1';
        $callback = function (\yii\base\Event $event) use ($where) {
            $event->sender->where = $where;
        };
        Event::on(ActiveQuery::className(), ActiveQuery::EVENT_INIT, $callback);
        $result = new ActiveQuery(Customer::className());
        $this->assertEquals($where, $result->where);
        Event::off(ActiveQuery::className(), ActiveQuery::EVENT_INIT, $callback);
    }

    /**
     * @todo tests for internal logic of prepare()
     */
    public function testPrepare()
    {
        $query = new ActiveQuery(Customer::className());
        $builder = new QueryBuilder(new Connection());
        $result = $query->prepare($builder);
        $this->assertInstanceOf('yii\db\Query', $result);
    }

    public function testPopulate_EmptyRows()
    {
        $query = new ActiveQuery(Customer::className());
        $rows = [];
        $result = $query->populate([]);
        $this->assertEquals($rows, $result);
    }

    /**
     * @todo tests for internal logic of populate()
     */
    public function testPopulate_FilledRows()
    {
        $query = new ActiveQuery(Customer::className());
        $rows = $query->all();
        $result = $query->populate($rows);
        $this->assertEquals($rows, $result);
    }

    /**
     * @todo tests for internal logic of one()
     */
    public function testOne()
    {
        $query = new ActiveQuery(Customer::className());
        $result = $query->one();
        $this->assertInstanceOf('yiiunit\data\ar\Customer', $result);
    }

    /**
     * @todo test internal logic of createCommand()
     */
    public function testCreateCommand()
    {
        $query = new ActiveQuery(Customer::className());
        $result = $query->createCommand();
        $this->assertInstanceOf('yii\db\Command', $result);
    }

    /**
     * @todo tests for internal logic of queryScalar()
     */
    public function testQueryScalar()
    {
        $query = new ActiveQuery(Customer::className());
        $result = $this->invokeMethod($query, 'queryScalar', ['name', null]);
        $this->assertEquals('user1', $result);
    }

    /**
     * @todo tests for internal logic of joinWith()
     */
    public function testJoinWith()
    {
        $query = new ActiveQuery(Customer::className());
        $result = $query->joinWith('profile');
        $this->assertEquals([
            [['profile'], true, 'LEFT JOIN'],
        ], $result->joinWith);
    }

    /**
     * @todo tests for internal logic of innerJoinWith()
     */
    public function testInnerJoinWith()
    {
        $query = new ActiveQuery(Customer::className());
        $result = $query->innerJoinWith('profile');
        $this->assertEquals([
            [['profile'], true, 'INNER JOIN'],
        ], $result->joinWith);
    }

    public function testBuildJoinWithRemoveDuplicateJoinByTableName()
    {
        $query = new ActiveQuery(Customer::className());
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
    public function testGetQueryTableName_from_not_set()
    {
        $query = new ActiveQuery(Customer::className());
        $result = $this->invokeMethod($query, 'getTableNameAndAlias');
        $this->assertEquals(['customer', 'customer'], $result);
    }

    public function testGetQueryTableName_from_set()
    {
        $options = ['from' => ['alias' => 'customer']];
        $query = new ActiveQuery(Customer::className(), $options);
        $result = $this->invokeMethod($query, 'getTableNameAndAlias');
        $this->assertEquals(['customer', 'alias'], $result);
    }

    public function testOnCondition()
    {
        $query = new ActiveQuery(Customer::className());
        $on = ['active' => true];
        $params = ['a' => 'b'];
        $result = $query->onCondition($on, $params);
        $this->assertEquals($on, $result->on);
        $this->assertEquals($params, $result->params);
    }

    public function testAndOnCondition_on_not_set()
    {
        $query = new ActiveQuery(Customer::className());
        $on = ['active' => true];
        $params = ['a' => 'b'];
        $result = $query->andOnCondition($on, $params);
        $this->assertEquals($on, $result->on);
        $this->assertEquals($params, $result->params);
    }

    public function testAndOnCondition_on_set()
    {
        $onOld = ['active' => true];
        $query = new ActiveQuery(Customer::className());
        $query->on = $onOld;

        $on = ['active' => true];
        $params = ['a' => 'b'];
        $result = $query->andOnCondition($on, $params);
        $this->assertEquals(['and', $onOld, $on], $result->on);
        $this->assertEquals($params, $result->params);
    }

    public function testOrOnCondition_on_not_set()
    {
        $query = new ActiveQuery(Customer::className());
        $on = ['active' => true];
        $params = ['a' => 'b'];
        $result = $query->orOnCondition($on, $params);
        $this->assertEquals($on, $result->on);
        $this->assertEquals($params, $result->params);
    }

    public function testOrOnCondition_on_set()
    {
        $onOld = ['active' => true];
        $query = new ActiveQuery(Customer::className());
        $query->on = $onOld;

        $on = ['active' => true];
        $params = ['a' => 'b'];
        $result = $query->orOnCondition($on, $params);
        $this->assertEquals(['or', $onOld, $on], $result->on);
        $this->assertEquals($params, $result->params);
    }

    /**
     * @dataProvider viaTableProvider
     */
    public function testViaTable($viaByJoin)
    {
        $orderId = 2;
        $itemIdsForOrder = [3, 4, 5];

        $query = new ActiveQuery(Item::className(), [
            'primaryModel' => new Order(['id' => $orderId]),
            'link' => ['id' => 'item_id'],
            'multiple' => true
        ]);
        $query
            ->alias('i')
            ->orderBy('id');

        $viaTable = 'order_item oi'; // Note the alias "oi" here
        $viaLink = ['order_id' => 'id'];
        $query->viaTable($viaTable, $viaLink);
        $query->viaByJoin($viaByJoin);

        $via = $query->via;
        $this->assertInstanceOf('yii\db\ActiveQuery', $via);
        $this->assertEquals([$viaTable], $via->from);
        $this->assertEquals(Order::className(), $via->modelClass);
        $this->assertEquals($viaLink, $via->link);

        $preparedQuery = $query->prepare(new QueryBuilder(new Connection()));
        $this->assertInstanceOf('yii\db\Query', $preparedQuery);

        if ($viaByJoin) {
            $this->assertEquals(
                [
                    [
                        'INNER JOIN',
                        [
                            $viaTable
                        ],
                        [
                            'and',
                            '{{i}}.[[id]] = {{oi}}.[[item_id]]',
                            [
                                '{{oi}}.[[order_id]]' => $orderId
                            ]
                        ]
                    ]
                ],
                $query->join
            );

            $this->assertNull($preparedQuery->where);

        } else {
            // Check if "Item" ids are for the correct "Order"
            sort($preparedQuery->where[2]); // apply sort since some DBMS might return the ids in different order
            $this->assertEquals(['in', ['id'], $itemIdsForOrder], $preparedQuery->where);
            $this->assertNull($preparedQuery->join);
        }

        $result = $preparedQuery->all($this->getConnection());

        // Ensure we got the correct items and in the right order
        $this->assertEquals($itemIdsForOrder, ArrayHelper::getColumn($result, 'id'));

        // Ensure we got the right columns
        $this->assertEquals(['id', 'name', 'category_id'], array_keys($result[0]));
    }

    public function viaTableProvider()
    {
        return [
            [false],
            [true]
        ];
    }

    public function testViaJoinedTable()
    {
        $query = new ActiveQuery(Item::className());
        $query->viaJoinedTable('order_item',['order_id' => 'id']);
        $this->assertTrue($query->useJoinForVia());
        // For tests of actual implementation see `testViaTable`
    }

    public function testViaTables()
    {
        $customerId = 2;
        $itemIdsForCustomer = [2, 5, 4, 3]; // Customer 2 has Orders 2 and 3

        $query = new ActiveQuery(Item::className(), [
            'primaryModel' => new Customer(['id' => $customerId]),
            'link' => ['id' => 'item_id'],
            'multiple' => true
        ]);
        $query
            ->alias('i')
            ->select(['i.*', 'SUM(subtotal) AS sum_subtotal'])
            ->orderBy(['sum_subtotal' => SORT_DESC]);

        $viaTable1 = 'order_item oi'; // Note the alias "oi" here
        $viaLink1 = ['order_id' => 'id'];
        $viaTable2 = 'order o'; // Note the alias "o" here
        $viaLink2 = ['customer_id' => 'id'];
        $query->viaJoinedTables([
            $viaTable1 => $viaLink1,
            $viaTable2 => $viaLink2,
        ]);

        $via1 = $query->via;
        $this->assertInstanceOf('yii\db\ActiveQuery', $via1);
        $this->assertEquals([$viaTable1], $via1->from);
        $this->assertEquals(Customer::className(), $via1->modelClass);
        $this->assertEquals($viaLink1, $via1->link);

        $via2 = $via1->via;
        $this->assertInstanceOf('yii\db\ActiveQuery', $via2);
        $this->assertEquals([$viaTable2], $via2->from);
        $this->assertEquals(Customer::className(), $via2->modelClass);
        $this->assertEquals($viaLink2, $via2->link);

        $preparedQuery = $query->prepare(new QueryBuilder(new Connection()));
        $this->assertInstanceOf('yii\db\Query', $preparedQuery);

        $this->assertEquals(
            [
                [
                    'INNER JOIN',
                    [
                        'order_item oi'
                    ],
                    '{{i}}.[[id]] = {{oi}}.[[item_id]]'
                ],
                [
                    'INNER JOIN',
                    [
                        'order o'
                    ],
                    [
                        'and',
                        '{{oi}}.[[order_id]] = {{o}}.[[id]]',
                        [
                            '{{o}}.[[customer_id]]' => $customerId
                        ]
                    ]
                ]
            ],
            $query->join
        );

        $this->assertNull($preparedQuery->where);

        $result = $preparedQuery->all($this->getConnection());

        // Ensure we got the correct items and in the right order
        $this->assertEquals($itemIdsForCustomer, ArrayHelper::getColumn($result, 'id'));

        // Ensure we got the right columns
        $this->assertEquals(['id', 'name', 'category_id', 'sum_subtotal'], array_keys($result[0]));
    }

    public function testViaJoined()
    {
        $customerId = 2;
        $subtotalCondition = ['>=', 'subtotal', 10];
        $itemIdsForCustomer = [2, 5, 4]; // Customer 2 has Orders 2 and 3, items ids have a subtotal gte 10 and are ordered desc by subtotal

        $query = (new Customer(['id' => $customerId]))
            ->hasMany(Item::className(), ['id' => 'item_id'])
            ->viaJoined(
                'orderItems2', // Note `Customer` defines the 'via' version of "orderItems" as "orderItems2".
                function ($orderItemsQuery) use ($subtotalCondition) { // Test callable
                    /** @var Query $orderItemsQuery */
                    $orderItemsQuery->andWhere($subtotalCondition);
                }
            );

        $query
            ->select(['item.*', 'SUM(subtotal) AS sum_subtotal'])
            ->orderBy(['sum_subtotal' => SORT_DESC]);

        $this->assertTrue($query->useJoinForVia());

        $via1 = $query->via;
        $this->assertTrue(is_array($via1));
        $this->assertEquals('orderItems2', $via1[0]);
        $this->assertInstanceOf('yii\db\ActiveQuery', $via1[1]);
        $this->assertEquals(OrderItem::className(), $via1[1]->modelClass);
        $this->assertEquals(['order_id' => 'id'], $via1[1]->link);

        $via2 = $via1[1]->via;
        $this->assertTrue(is_array($via2));
        $this->assertEquals('orders', $via2[0]);
        $this->assertInstanceOf('yii\db\ActiveQuery', $via2[1]);
        $this->assertEquals(Order::className(), $via2[1]->modelClass);
        $this->assertEquals(['customer_id' => 'id'], $via2[1]->link);

        $preparedQuery = $query->prepare(new QueryBuilder(new Connection()));
        $this->assertInstanceOf('yii\db\Query', $preparedQuery);

        $this->assertEquals(
            [
                [
                    'INNER JOIN',
                    'order_item',
                    '{{item}}.[[id]] = {{order_item}}.[[item_id]]'
                ],
                [
                    'INNER JOIN',
                    'order',
                    [
                        'and',
                        '{{order_item}}.[[order_id]] = {{order}}.[[id]]',
                        [
                            '{{order}}.[[customer_id]]' => $customerId
                        ]
                    ]
                ]
            ],
            $query->join
        );

        $this->assertEquals(['sum_subtotal' => SORT_DESC, '[[id]]' => SORT_ASC], $preparedQuery->orderBy);

        // The subtotal condition should be copied over from the via
        $this->assertEquals($subtotalCondition, $preparedQuery->where);

        $result = $preparedQuery->all($this->getConnection());

        // Ensure we got the correct items and in the right order
        $this->assertEquals($itemIdsForCustomer, ArrayHelper::getColumn($result, 'id'));

        // Ensure we got the right columns
        $this->assertEquals(['id', 'name', 'category_id', 'sum_subtotal'], array_keys($result[0]));
    }

    public function testDisablingViaByJoinIsNotPossibleAfterPrepare()
    {
        $query = new ActiveQuery(Item::className(), [
            'primaryModel' => new Order(['id' => 1]),
            'link' => ['id' => 'item_id'],
            'multiple' => true
        ]);

        $query->viaJoinedTable('order_item', ['order_id' => 'id']);
        $query->prepare(new QueryBuilder(new Connection()));

        $this->expectException('yii\base\InvalidCallException');
        $this->expectExceptionMessage('`viaByJoin` can not be disabled after it has been applied.');
        $query->viaByJoin(false);
    }
    public function testDisablingViaByJoinIsNotPossibleWithMultipleViaTables()
    {
        $query = new ActiveQuery(Item::className());

        $query->viaJoinedTables([
            'order_item' => ['order_id' => 'id'],
            'order' => ['customer_id' => 'id'],
        ]);

        $this->expectException('yii\base\InvalidCallException');
        $this->expectExceptionMessage('`viaByJoin` can not be disabled when using multiple "via tables".');
        $query->viaByJoin(false);
    }

    public function testAlias_not_set()
    {
        $query = new ActiveQuery(Customer::className());
        $result = $query->alias('alias');
        $this->assertInstanceOf('yii\db\ActiveQuery', $result);
        $this->assertEquals(['alias' => 'customer'], $result->from);
    }

    public function testAlias_yet_set()
    {
        $aliasOld = ['old'];
        $query = new ActiveQuery(Customer::className());
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

    public function testGetTableNames_notFilledFrom()
    {
        $query = new ActiveQuery(Profile::className());

        $tables = $query->getTablesUsedInFrom();

        $this->assertEquals([
            '{{' . Profile::tableName() . '}}' => '{{' . Profile::tableName() . '}}',
        ], $tables);
    }

    public function testGetTableNames_wontFillFrom()
    {
        $query = new ActiveQuery(Profile::className());
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
    public function testDeeplyNestedTableRelationWith()
    {
        /* @var $category Category */
        $categories = Category::find()->with('orders')->indexBy('id')->all();

        $category = $categories[1];
        $this->assertNotNull($category);
        $orders = $category->orders;
        $this->assertEquals(2, count($orders));
        $this->assertInstanceOf(Order::className(), $orders[0]);
        $this->assertInstanceOf(Order::className(), $orders[1]);
        $ids = [$orders[0]->id, $orders[1]->id];
        sort($ids);
        $this->assertEquals([1, 3], $ids);

        $category = $categories[2];
        $this->assertNotNull($category);
        $orders = $category->orders;
        $this->assertEquals(1, count($orders));
        $this->assertInstanceOf(Order::className(), $orders[0]);
        $this->assertEquals(2, $orders[0]->id);
    }
}
