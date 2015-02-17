<?php
namespace yiiunit\framework\db;

use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\Category;
use yiiunit\data\ar\Customer;
use yiiunit\data\ar\NullValues;
use yiiunit\data\ar\OrderItem;
use yiiunit\data\ar\Order;
use yiiunit\data\ar\Item;
use yiiunit\data\ar\OrderItemWithNullFK;
use yiiunit\data\ar\OrderWithNullFK;
use yiiunit\data\ar\Profile;
use yiiunit\data\ar\Type;
use yiiunit\framework\ar\ActiveRecordTestTrait;
use yiiunit\framework\db\cubrid\CubridActiveRecordTest;
use yiiunit\data\ar\Animal;
use yiiunit\data\ar\Cat;
use yiiunit\data\ar\Dog;

/**
 * @group db
 * @group mysql
 */
class ActiveRecordTest extends DatabaseTestCase
{
    use ActiveRecordTestTrait;

    protected function setUp()
    {
        parent::setUp();
        ActiveRecord::$db = $this->getConnection();
    }

    public function getCustomerClass()
    {
        return Customer::className();
    }

    public function getItemClass()
    {
        return Item::className();
    }

    public function getOrderClass()
    {
        return Order::className();
    }

    public function getOrderItemClass()
    {
        return OrderItem::className();
    }

    public function getOrderWithNullFKClass()
    {
        return OrderWithNullFK::className();
    }
    public function getOrderItemWithNullFKmClass()
    {
        return OrderItemWithNullFK::className();
    }

    public function testCustomColumns()
    {
        // find custom column
        $customer = Customer::find()->select(['*', '(status*2) AS status2'])
            ->where(['name' => 'user3'])->one();
        $this->assertEquals(3, $customer->id);
        $this->assertEquals(4, $customer->status2);
    }

    public function testStatisticalFind()
    {
        // find count, sum, average, min, max, scalar
        $this->assertEquals(3, Customer::find()->count());
        $this->assertEquals(2, Customer::find()->where('id=1 OR id=2')->count());
        $this->assertEquals(6, Customer::find()->sum('id'));
        $this->assertEquals(2, Customer::find()->average('id'));
        $this->assertEquals(1, Customer::find()->min('id'));
        $this->assertEquals(3, Customer::find()->max('id'));
        $this->assertEquals(3, Customer::find()->select('COUNT(*)')->scalar());
    }

    public function testFindScalar()
    {
        // query scalar
        $customerName = Customer::find()->where(['id' => 2])->select('name')->scalar();
        $this->assertEquals('user2', $customerName);
    }

    public function testFindColumn()
    {
        /* @var $this TestCase|ActiveRecordTestTrait */
        $this->assertEquals(['user1', 'user2', 'user3'], Customer::find()->select('name')->column());
        $this->assertEquals(['user3', 'user2', 'user1'], Customer::find()->orderBy(['name' => SORT_DESC])->select('name')->column());
    }

    public function testFindBySql()
    {
        // find one
        $customer = Customer::findBySql('SELECT * FROM customer ORDER BY id DESC')->one();
        $this->assertTrue($customer instanceof Customer);
        $this->assertEquals('user3', $customer->name);

        // find all
        $customers = Customer::findBySql('SELECT * FROM customer')->all();
        $this->assertEquals(3, count($customers));

        // find with parameter binding
        $customer = Customer::findBySql('SELECT * FROM customer WHERE id=:id', [':id' => 2])->one();
        $this->assertTrue($customer instanceof Customer);
        $this->assertEquals('user2', $customer->name);
    }

    public function testFindLazyViaTable()
    {
        /* @var $order Order */
        $order = Order::findOne(1);
        $this->assertEquals(1, $order->id);
        $this->assertEquals(2, count($order->books));
        $this->assertEquals(1, $order->items[0]->id);
        $this->assertEquals(2, $order->items[1]->id);

        $order = Order::findOne(2);
        $this->assertEquals(2, $order->id);
        $this->assertEquals(0, count($order->books));

        $order = Order::find()->where(['id' => 1])->asArray()->one();
        $this->assertTrue(is_array($order));
    }

    public function testFindEagerViaTable()
    {
        $orders = Order::find()->with('books')->orderBy('id')->all();
        $this->assertEquals(3, count($orders));

        $order = $orders[0];
        $this->assertEquals(1, $order->id);
        $this->assertEquals(2, count($order->books));
        $this->assertEquals(1, $order->books[0]->id);
        $this->assertEquals(2, $order->books[1]->id);

        $order = $orders[1];
        $this->assertEquals(2, $order->id);
        $this->assertEquals(0, count($order->books));

        $order = $orders[2];
        $this->assertEquals(3, $order->id);
        $this->assertEquals(1, count($order->books));
        $this->assertEquals(2, $order->books[0]->id);

        // https://github.com/yiisoft/yii2/issues/1402
        $orders = Order::find()->with('books')->orderBy('id')->asArray()->all();
        $this->assertEquals(3, count($orders));
        $this->assertTrue(is_array($orders[0]['orderItems'][0]));

        $order = $orders[0];
        $this->assertTrue(is_array($order));
        $this->assertEquals(1, $order['id']);
        $this->assertEquals(2, count($order['books']));
        $this->assertEquals(1, $order['books'][0]['id']);
        $this->assertEquals(2, $order['books'][1]['id']);
    }

    // deeply nested table relation
    public function testDeeplyNestedTableRelation()
    {
        /* @var $customer Customer */
        $customer = Customer::findOne(1);
        $this->assertNotNull($customer);

        $items = $customer->orderItems;

        $this->assertEquals(2, count($items));
        $this->assertInstanceOf(Item::className(), $items[0]);
        $this->assertInstanceOf(Item::className(), $items[1]);
        $this->assertEquals(1, $items[0]->id);
        $this->assertEquals(2, $items[1]->id);
    }

    /**
     * https://github.com/yiisoft/yii2/issues/5341
     *
     * Issue:     Plan     1 -- * Account * -- * User
     * Our Tests: Category 1 -- * Item    * -- * Order
     */
    public function testDeeplyNestedTableRelation2()
    {
        /* @var $category Category */
        $category = Category::findOne(1);
        $this->assertNotNull($category);
        $orders = $category->orders;
        $this->assertEquals(2, count($orders));
        $this->assertInstanceOf(Order::className(), $orders[0]);
        $this->assertInstanceOf(Order::className(), $orders[1]);
        $ids = [$orders[0]->id, $orders[1]->id];
        sort($ids);
        $this->assertEquals([1, 3], $ids);

        $category = Category::findOne(2);
        $this->assertNotNull($category);
        $orders = $category->orders;
        $this->assertEquals(1, count($orders));
        $this->assertInstanceOf(Order::className(), $orders[0]);
        $this->assertEquals(2, $orders[0]->id);

    }

    public function testStoreNull()
    {
        $record = new NullValues();
        $this->assertNull($record->var1);
        $this->assertNull($record->var2);
        $this->assertNull($record->var3);
        $this->assertNull($record->stringcol);

        $record->id = 1;

        $record->var1 = 123;
        $record->var2 = 456;
        $record->var3 = 789;
        $record->stringcol = 'hello!';

        $record->save(false);
        $this->assertTrue($record->refresh());

        $this->assertEquals(123, $record->var1);
        $this->assertEquals(456, $record->var2);
        $this->assertEquals(789, $record->var3);
        $this->assertEquals('hello!', $record->stringcol);

        $record->var1 = null;
        $record->var2 = null;
        $record->var3 = null;
        $record->stringcol = null;

        $record->save(false);
        $this->assertTrue($record->refresh());

        $this->assertNull($record->var1);
        $this->assertNull($record->var2);
        $this->assertNull($record->var3);
        $this->assertNull($record->stringcol);

        $record->var1 = 0;
        $record->var2 = 0;
        $record->var3 = 0;
        $record->stringcol = '';

        $record->save(false);
        $this->assertTrue($record->refresh());

        $this->assertEquals(0, $record->var1);
        $this->assertEquals(0, $record->var2);
        $this->assertEquals(0, $record->var3);
        $this->assertEquals('', $record->stringcol);
    }

    public function testStoreEmpty()
    {
        $record = new NullValues();
        $record->id = 1;

        // this is to simulate empty html form submission
        $record->var1 = '';
        $record->var2 = '';
        $record->var3 = '';
        $record->stringcol = '';

        $record->save(false);
        $this->assertTrue($record->refresh());

        // https://github.com/yiisoft/yii2/commit/34945b0b69011bc7cab684c7f7095d837892a0d4#commitcomment-4458225
        $this->assertTrue($record->var1 === $record->var2);
        $this->assertTrue($record->var2 === $record->var3);
    }

    public function testIsPrimaryKey()
    {
        $this->assertFalse(Customer::isPrimaryKey([]));
        $this->assertTrue(Customer::isPrimaryKey(['id']));
        $this->assertFalse(Customer::isPrimaryKey(['id', 'name']));
        $this->assertFalse(Customer::isPrimaryKey(['name']));
        $this->assertFalse(Customer::isPrimaryKey(['name', 'email']));

        $this->assertFalse(OrderItem::isPrimaryKey([]));
        $this->assertFalse(OrderItem::isPrimaryKey(['order_id']));
        $this->assertFalse(OrderItem::isPrimaryKey(['item_id']));
        $this->assertFalse(OrderItem::isPrimaryKey(['quantity']));
        $this->assertFalse(OrderItem::isPrimaryKey(['quantity', 'subtotal']));
        $this->assertTrue(OrderItem::isPrimaryKey(['order_id', 'item_id']));
        $this->assertFalse(OrderItem::isPrimaryKey(['order_id', 'item_id', 'quantity']));
    }

    public function testJoinWith()
    {
        // left join and eager loading
        $orders = Order::find()->joinWith('customer')->orderBy('customer.id DESC, order.id')->all();
        $this->assertEquals(3, count($orders));
        $this->assertEquals(2, $orders[0]->id);
        $this->assertEquals(3, $orders[1]->id);
        $this->assertEquals(1, $orders[2]->id);
        $this->assertTrue($orders[0]->isRelationPopulated('customer'));
        $this->assertTrue($orders[1]->isRelationPopulated('customer'));
        $this->assertTrue($orders[2]->isRelationPopulated('customer'));

        // inner join filtering and eager loading
        $orders = Order::find()->innerJoinWith([
            'customer' => function ($query) {
                $query->where('customer.id=2');
            },
        ])->orderBy('order.id')->all();
        $this->assertEquals(2, count($orders));
        $this->assertEquals(2, $orders[0]->id);
        $this->assertEquals(3, $orders[1]->id);
        $this->assertTrue($orders[0]->isRelationPopulated('customer'));
        $this->assertTrue($orders[1]->isRelationPopulated('customer'));

        // inner join filtering, eager loading, conditions on both primary and relation
        $orders = Order::find()->innerJoinWith([
            'customer' => function ($query) {
                $query->where(['customer.id' => 2]);
            },
        ])->where(['order.id' => [1, 2]])->orderBy('order.id')->all();
        $this->assertEquals(1, count($orders));
        $this->assertEquals(2, $orders[0]->id);
        $this->assertTrue($orders[0]->isRelationPopulated('customer'));

        // inner join filtering without eager loading
        $orders = Order::find()->innerJoinWith([
            'customer' => function ($query) {
                $query->where('customer.id=2');
            },
        ], false)->orderBy('order.id')->all();
        $this->assertEquals(2, count($orders));
        $this->assertEquals(2, $orders[0]->id);
        $this->assertEquals(3, $orders[1]->id);
        $this->assertFalse($orders[0]->isRelationPopulated('customer'));
        $this->assertFalse($orders[1]->isRelationPopulated('customer'));

        // inner join filtering without eager loading, conditions on both primary and relation
        $orders = Order::find()->innerJoinWith([
            'customer' => function ($query) {
                    $query->where(['customer.id' => 2]);
            },
        ], false)->where(['order.id' => [1, 2]])->orderBy('order.id')->all();
        $this->assertEquals(1, count($orders));
        $this->assertEquals(2, $orders[0]->id);
        $this->assertFalse($orders[0]->isRelationPopulated('customer'));

        // join with via-relation
        $orders = Order::find()->innerJoinWith('books')->orderBy('order.id')->all();
        $this->assertEquals(2, count($orders));
        $this->assertEquals(1, $orders[0]->id);
        $this->assertEquals(3, $orders[1]->id);
        $this->assertTrue($orders[0]->isRelationPopulated('books'));
        $this->assertTrue($orders[1]->isRelationPopulated('books'));
        $this->assertEquals(2, count($orders[0]->books));
        $this->assertEquals(1, count($orders[1]->books));

        // join with sub-relation
        $orders = Order::find()->innerJoinWith([
            'items' => function ($q) {
                $q->orderBy('item.id');
            },
            'items.category' => function ($q) {
                $q->where('category.id = 2');
            },
        ])->orderBy('order.id')->all();
        $this->assertEquals(1, count($orders));
        $this->assertTrue($orders[0]->isRelationPopulated('items'));
        $this->assertEquals(2, $orders[0]->id);
        $this->assertEquals(3, count($orders[0]->items));
        $this->assertTrue($orders[0]->items[0]->isRelationPopulated('category'));
        $this->assertEquals(2, $orders[0]->items[0]->category->id);

        // join with table alias
        $orders = Order::find()->joinWith([
            'customer' => function ($q) {
                $q->from('customer c');
            }
        ])->orderBy('c.id DESC, order.id')->all();
        $this->assertEquals(3, count($orders));
        $this->assertEquals(2, $orders[0]->id);
        $this->assertEquals(3, $orders[1]->id);
        $this->assertEquals(1, $orders[2]->id);
        $this->assertTrue($orders[0]->isRelationPopulated('customer'));
        $this->assertTrue($orders[1]->isRelationPopulated('customer'));
        $this->assertTrue($orders[2]->isRelationPopulated('customer'));

        // join with ON condition
        $orders = Order::find()->joinWith('books2')->orderBy('order.id')->all();
        $this->assertEquals(3, count($orders));
        $this->assertEquals(1, $orders[0]->id);
        $this->assertEquals(2, $orders[1]->id);
        $this->assertEquals(3, $orders[2]->id);
        $this->assertTrue($orders[0]->isRelationPopulated('books2'));
        $this->assertTrue($orders[1]->isRelationPopulated('books2'));
        $this->assertTrue($orders[2]->isRelationPopulated('books2'));
        $this->assertEquals(2, count($orders[0]->books2));
        $this->assertEquals(0, count($orders[1]->books2));
        $this->assertEquals(1, count($orders[2]->books2));

        // lazy loading with ON condition
        $order = Order::findOne(1);
        $this->assertEquals(2, count($order->books2));
        $order = Order::findOne(2);
        $this->assertEquals(0, count($order->books2));
        $order = Order::findOne(3);
        $this->assertEquals(1, count($order->books2));

        // eager loading with ON condition
        $orders = Order::find()->with('books2')->all();
        $this->assertEquals(3, count($orders));
        $this->assertEquals(1, $orders[0]->id);
        $this->assertEquals(2, $orders[1]->id);
        $this->assertEquals(3, $orders[2]->id);
        $this->assertTrue($orders[0]->isRelationPopulated('books2'));
        $this->assertTrue($orders[1]->isRelationPopulated('books2'));
        $this->assertTrue($orders[2]->isRelationPopulated('books2'));
        $this->assertEquals(2, count($orders[0]->books2));
        $this->assertEquals(0, count($orders[1]->books2));
        $this->assertEquals(1, count($orders[2]->books2));

        // join with count and query
        $query = Order::find()->joinWith('customer');
        $count = $query->count();
        $this->assertEquals(3, $count);
        $orders = $query->all();
        $this->assertEquals(3, count($orders));

        // https://github.com/yiisoft/yii2/issues/2880
        $query = Order::findOne(1);
        $customer = $query->getCustomer()->joinWith([
            'orders' => function ($q) { $q->orderBy([]); }
        ])->one();
        $this->assertEquals(1, $customer->id);
        $order = Order::find()->joinWith([
            'items' => function ($q) {
                $q->from(['items' => 'item'])
                    ->orderBy('items.id');
            },
        ])->orderBy('order.id')->one();

        // join with sub-relation called inside Closure
        $orders = Order::find()->joinWith([
                'items' => function ($q) {
                    $q->orderBy('item.id');
                    $q->joinWith([
                            'category'=> function ($q) {
                                $q->where('category.id = 2');
                            }
                        ]);
                },
            ])->orderBy('order.id')->all();
        $this->assertEquals(1, count($orders));
        $this->assertTrue($orders[0]->isRelationPopulated('items'));
        $this->assertEquals(2, $orders[0]->id);
        $this->assertEquals(3, count($orders[0]->items));
        $this->assertTrue($orders[0]->items[0]->isRelationPopulated('category'));
        $this->assertEquals(2, $orders[0]->items[0]->category->id);
    }

    public function testJoinWithAndScope()
    {
        // hasOne inner join
        $customers = Customer::find()->active()->innerJoinWith('profile')->orderBy('customer.id')->all();
        $this->assertEquals(1, count($customers));
        $this->assertEquals(1, $customers[0]->id);
        $this->assertTrue($customers[0]->isRelationPopulated('profile'));

        // hasOne outer join
        $customers = Customer::find()->active()->joinWith('profile')->orderBy('customer.id')->all();
        $this->assertEquals(2, count($customers));
        $this->assertEquals(1, $customers[0]->id);
        $this->assertEquals(2, $customers[1]->id);
        $this->assertTrue($customers[0]->isRelationPopulated('profile'));
        $this->assertTrue($customers[1]->isRelationPopulated('profile'));
        $this->assertInstanceOf(Profile::className(), $customers[0]->profile);
        $this->assertNull($customers[1]->profile);

        // hasMany
        $customers = Customer::find()->active()->joinWith([
            'orders' => function ($q) {
                $q->orderBy('order.id');
            }
        ])->orderBy('customer.id DESC, order.id')->all();
        $this->assertEquals(2, count($customers));
        $this->assertEquals(2, $customers[0]->id);
        $this->assertEquals(1, $customers[1]->id);
        $this->assertTrue($customers[0]->isRelationPopulated('orders'));
        $this->assertTrue($customers[1]->isRelationPopulated('orders'));
    }

    /**
     * This query will do the same join twice, ensure duplicated JOIN gets removed
     * https://github.com/yiisoft/yii2/pull/2650
     */
    public function testJoinWithVia()
    {
        Order::getDb()->getQueryBuilder()->separator = "\n";
        Order::find()->joinWith('itemsInOrder1')->joinWith([
            'items' => function ($q) {
                $q->orderBy('item.id');
            },
        ])->all();
    }

    public function testInverseOf()
    {
        // eager loading: find one and all
        $customer = Customer::find()->with('orders2')->where(['id' => 1])->one();
        $this->assertTrue($customer->orders2[0]->customer2 === $customer);
        $customers = Customer::find()->with('orders2')->where(['id' => [1, 3]])->all();
        $this->assertTrue($customers[0]->orders2[0]->customer2 === $customers[0]);
        $this->assertTrue(empty($customers[1]->orders2));
        // lazy loading
        $customer = Customer::findOne(2);
        $orders = $customer->orders2;
        $this->assertTrue(count($orders) === 2);
        $this->assertTrue($customer->orders2[0]->customer2 === $customer);
        $this->assertTrue($customer->orders2[1]->customer2 === $customer);
        // ad-hoc lazy loading
        $customer = Customer::findOne(2);
        $orders = $customer->getOrders2()->all();
        $this->assertTrue(count($orders) === 2);
        $this->assertTrue($customer->orders2[0]->customer2 === $customer);
        $this->assertTrue($customer->orders2[1]->customer2 === $customer);

        // the other way around
        $customer = Customer::find()->with('orders2')->where(['id' => 1])->asArray()->one();
        $this->assertTrue($customer['orders2'][0]['customer2']['id'] === $customer['id']);
        $customers = Customer::find()->with('orders2')->where(['id' => [1, 3]])->asArray()->all();
        $this->assertTrue($customer['orders2'][0]['customer2']['id'] === $customers[0]['id']);
        $this->assertTrue(empty($customers[1]['orders2']));

        $orders = Order::find()->with('customer2')->where(['id' => 1])->all();
        $this->assertTrue($orders[0]->customer2->orders2 === [$orders[0]]);
        $order = Order::find()->with('customer2')->where(['id' => 1])->one();
        $this->assertTrue($order->customer2->orders2 === [$order]);

        $orders = Order::find()->with('customer2')->where(['id' => 1])->asArray()->all();
        $this->assertTrue($orders[0]['customer2']['orders2'][0]['id'] === $orders[0]['id']);
        $order = Order::find()->with('customer2')->where(['id' => 1])->asArray()->one();
        $this->assertTrue($order['customer2']['orders2'][0]['id'] === $orders[0]['id']);

        $orders = Order::find()->with('customer2')->where(['id' => [1, 3]])->all();
        $this->assertTrue($orders[0]->customer2->orders2 === [$orders[0]]);
        $this->assertTrue($orders[1]->customer2->orders2 === [$orders[1]]);

        $orders = Order::find()->with('customer2')->where(['id' => [2, 3]])->orderBy('id')->all();
        $this->assertTrue($orders[0]->customer2->orders2 === $orders);
        $this->assertTrue($orders[1]->customer2->orders2 === $orders);

        $orders = Order::find()->with('customer2')->where(['id' => [2, 3]])->orderBy('id')->asArray()->all();
        $this->assertTrue($orders[0]['customer2']['orders2'][0]['id'] === $orders[0]['id']);
        $this->assertTrue($orders[0]['customer2']['orders2'][1]['id'] === $orders[1]['id']);
        $this->assertTrue($orders[1]['customer2']['orders2'][0]['id'] === $orders[0]['id']);
        $this->assertTrue($orders[1]['customer2']['orders2'][1]['id'] === $orders[1]['id']);
    }

    public function testDefaultValues()
    {
        $model = new Type();
        $model->loadDefaultValues();
        $this->assertEquals(1, $model->int_col2);
        $this->assertEquals('something', $model->char_col2);
        $this->assertEquals(1.23, $model->float_col2);
        $this->assertEquals(33.22, $model->numeric_col);
        $this->assertEquals(true, $model->bool_col2);

        if ($this instanceof CubridActiveRecordTest) {
            // cubrid has non-standard timestamp representation
            $this->assertEquals('12:00:00 AM 01/01/2002', $model->time);
        } else {
            $this->assertEquals('2002-01-01 00:00:00', $model->time);
        }

        $model = new Type();
        $model->char_col2 = 'not something';

        $model->loadDefaultValues();
        $this->assertEquals('not something', $model->char_col2);

        $model = new Type();
        $model->char_col2 = 'not something';

        $model->loadDefaultValues(false);
        $this->assertEquals('something', $model->char_col2);
    }

    public function testUnlinkAllViaTable()
    {
        /* @var $orderClass \yii\db\ActiveRecordInterface */
        $orderClass = $this->getOrderClass();
        /* @var $orderItemClass \yii\db\ActiveRecordInterface */
        $orderItemClass = $this->getOrderItemClass();
        /* @var $itemClass \yii\db\ActiveRecordInterface */
        $itemClass = $this->getItemClass();
        /* @var $orderItemsWithNullFKClass \yii\db\ActiveRecordInterface */
        $orderItemsWithNullFKClass = $this->getOrderItemWithNullFKmClass();

        // via table with delete
        /* @var $order  Order */
        $order = $orderClass::findOne(1);
        $this->assertEquals(2, count($order->booksViaTable));
        $orderItemCount = $orderItemClass::find()->count();
        $this->assertEquals(5, $itemClass::find()->count());
        $order->unlinkAll('booksViaTable', true);
        $this->afterSave();
        $this->assertEquals(5, $itemClass::find()->count());
        $this->assertEquals($orderItemCount - 2, $orderItemClass::find()->count());
        $this->assertEquals(0, count($order->booksViaTable));

        // via table without delete
        $this->assertEquals(2, count($order->booksWithNullFKViaTable));
        $orderItemCount = $orderItemsWithNullFKClass::find()->count();
        $this->assertEquals(5, $itemClass::find()->count());
        $order->unlinkAll('booksWithNullFKViaTable',false);
        $this->assertEquals(0, count($order->booksWithNullFKViaTable));
        $this->assertEquals(2,$orderItemsWithNullFKClass::find()->where(['AND', ['item_id' => [1, 2]], ['order_id' => null]])->count());
        $this->assertEquals($orderItemCount, $orderItemsWithNullFKClass::find()->count());
        $this->assertEquals(5, $itemClass::find()->count());
    }

    public function testCastValues()
    {
        $model = new Type();
        $model->int_col = 123;
        $model->int_col2 = 456;
        $model->smallint_col = 42;
        $model->char_col = '1337';
        $model->char_col2 = 'test';
        $model->char_col3 = 'test123';
        $model->float_col = 3.742;
        $model->float_col2 = 42.1337;
        $model->bool_col = true;
        $model->bool_col2 = false;
        $model->save(false);

        /* @var $model Type */
        $model = Type::find()->one();
        $this->assertSame(123, $model->int_col);
        $this->assertSame(456, $model->int_col2);
        $this->assertSame(42, $model->smallint_col);
        $this->assertSame('1337', trim($model->char_col));
        $this->assertSame('test', $model->char_col2);
        $this->assertSame('test123', $model->char_col3);
//        $this->assertSame(1337.42, $model->float_col);
//        $this->assertSame(42.1337, $model->float_col2);
//        $this->assertSame(true, $model->bool_col);
//        $this->assertSame(false, $model->bool_col2);
    }

    public function testIssues()
    {
        // https://github.com/yiisoft/yii2/issues/4938
        $category = Category::findOne(2);
        $this->assertTrue($category instanceof Category);
        $this->assertEquals(3, $category->getItems()->count());
        $this->assertEquals(1, $category->getLimitedItems()->count());
        $this->assertEquals(1, $category->getLimitedItems()->distinct(true)->count());

        // https://github.com/yiisoft/yii2/issues/3197
        $orders = Order::find()->with('orderItems')->orderBy('id')->all();
        $this->assertEquals(3, count($orders));
        $this->assertEquals(2, count($orders[0]->orderItems));
        $this->assertEquals(3, count($orders[1]->orderItems));
        $this->assertEquals(1, count($orders[2]->orderItems));
        $orders = Order::find()->with(['orderItems' => function ($q) { $q->indexBy('item_id'); }])->orderBy('id')->all();
        $this->assertEquals(3, count($orders));
        $this->assertEquals(2, count($orders[0]->orderItems));
        $this->assertEquals(3, count($orders[1]->orderItems));
        $this->assertEquals(1, count($orders[2]->orderItems));
    }
    
    public function testPopulateRecordCallWhenQueryingOnParentClass() 
    {
        (new Cat())->save(false);
        (new Dog())->save(false);

        $animal = Animal::find()->where(['type' => Dog::className()])->one();
        $this->assertEquals('bark', $animal->getDoes());

        $animal = Animal::find()->where(['type' => Cat::className()])->one();
        $this->assertEquals('meow', $animal->getDoes());
    }
}
