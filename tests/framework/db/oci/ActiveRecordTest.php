<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci;

use yii\db\Expression;
use yiiunit\data\ar\BitValues;
use yiiunit\data\ar\Customer;
use yiiunit\data\ar\DefaultPk;
use yiiunit\data\ar\DefaultMultiplePk;
use yiiunit\data\ar\Order;
use yiiunit\data\ar\Type;

/**
 * @group db
 * @group oci
 */
class ActiveRecordTest extends \yiiunit\framework\db\ActiveRecordTest
{
    protected $driverName = 'oci';

    public function testCastValues()
    {
        // pass, because boolean casting is not available
        return;
        $model = new Type();
        $model->int_col = 123;
        $model->int_col2 = 456;
        $model->smallint_col = 42;
        $model->char_col = '1337';
        $model->char_col2 = 'test';
        $model->char_col3 = 'test123';
        $model->float_col = 3.742;
        $model->float_col2 = 42.1337;
        $model->bool_col = 1;
        $model->bool_col2 = 0;
        $model->save(false);

        /* @var $model Type */
        $model = Type::find()->one();
        $this->assertSame(123, $model->int_col);
        $this->assertSame(456, $model->int_col2);
        $this->assertSame(42, $model->smallint_col);
        $this->assertSame('1337', trim($model->char_col));
        $this->assertSame('test', $model->char_col2);
        $this->assertSame('test123', $model->char_col3);
        $this->assertSame(1337.42, $model->float_col);
        $this->assertSame(42.1337, $model->float_col2);
        $this->assertEquals('1', $model->bool_col);
        $this->assertEquals('0', $model->bool_col2);
    }

    public function testDefaultValues()
    {
        $model = new Type();
        $model->loadDefaultValues();
        $this->assertEquals(1, $model->int_col2);
        $this->assertEquals('something', $model->char_col2);
        $this->assertEquals(1.23, $model->float_col2);
        $this->assertEquals(33.22, $model->numeric_col);
        $this->assertEquals('1', $model->bool_col2);

        // not testing $model->time, because oci\Schema can't read default value

        $model = new Type();
        $model->char_col2 = 'not something';

        $model->loadDefaultValues();
        $this->assertEquals('not something', $model->char_col2);

        $model = new Type();
        $model->char_col2 = 'not something';

        $model->loadDefaultValues(false);
        $this->assertEquals('something', $model->char_col2);
    }

    public function testFindAsArray()
    {
        /* @var $customerClass \yii\db\ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();

        // asArray
        $customer = $customerClass::find()->where(['id' => 2])->asArray()->one();
        $this->assertEquals([
            'id' => 2,
            'email' => 'user2@example.com',
            'name' => 'user2',
            'address' => 'address2',
            'status' => 1,
            'profile_id' => null,
            'bool_status' => true,
        ], $customer);

        // find all asArray
        $customers = $customerClass::find()->asArray()->all();
        $this->assertCount(3, $customers);
        $this->assertArrayHasKey('id', $customers[0]);
        $this->assertArrayHasKey('name', $customers[0]);
        $this->assertArrayHasKey('email', $customers[0]);
        $this->assertArrayHasKey('address', $customers[0]);
        $this->assertArrayHasKey('status', $customers[0]);
        $this->assertArrayHasKey('bool_status', $customers[0]);
        $this->assertArrayHasKey('id', $customers[1]);
        $this->assertArrayHasKey('name', $customers[1]);
        $this->assertArrayHasKey('email', $customers[1]);
        $this->assertArrayHasKey('address', $customers[1]);
        $this->assertArrayHasKey('status', $customers[1]);
        $this->assertArrayHasKey('bool_status', $customers[1]);
        $this->assertArrayHasKey('id', $customers[2]);
        $this->assertArrayHasKey('name', $customers[2]);
        $this->assertArrayHasKey('email', $customers[2]);
        $this->assertArrayHasKey('address', $customers[2]);
        $this->assertArrayHasKey('status', $customers[2]);
        $this->assertArrayHasKey('bool_status', $customers[2]);
    }

    public function testPrimaryKeyAfterSave()
    {
        $record = new DefaultPk();
        $record->type = 'type';
        $record->save(false);
        $this->assertEquals(5, $record->primaryKey);
    }

    public function testMultiplePrimaryKeyAfterSave()
    {
        $record = new DefaultMultiplePk();
        $record->id = 5;
        $record->second_key_column = 'secondKey';
        $record->type = 'type';
        $record->save(false);
        $this->assertEquals(5, $record->id);
        $this->assertEquals('secondKey', $record->second_key_column);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/9006
     */
    public function testBit()
    {
        $falseBit = BitValues::findOne(1);
        $this->assertEquals('0', $falseBit->val);

        $trueBit = BitValues::findOne(2);
        $this->assertEquals('1', $trueBit->val);
    }

    /**
     * Some PDO implementations(e.g. cubrid) do not support boolean values.
     * Make sure this does not affect AR layer.
     */
    public function testBooleanAttribute()
    {
        /* @var $customerClass \yii\db\ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();
        /* @var $this TestCase|ActiveRecordTestTrait */
        $customer = new $customerClass();
        $customer->name = 'boolean customer';
        $customer->email = 'mail@example.com';
        $customer->status = '1';
        $customer->save(false);

        $customer->refresh();
        $this->assertEquals('1', $customer->status);

        $customer->status = '0';
        $customer->save(false);

        $customer->refresh();
        $this->assertEquals('0', $customer->status);

        $customers = $customerClass::find()->where(['[[status]]' => '1'])->all();
        $this->assertCount(2, $customers);

        $customers = $customerClass::find()->where(['[[status]]' => '0'])->all();
        $this->assertCount(1, $customers);
    }

    /**
     * Tests the alias syntax for joinWith: 'alias' => 'relation'.
     * @dataProvider aliasMethodProvider
     * @param string $aliasMethod whether alias is specified explicitly or using the query syntax {{@tablename}}
     */
    public function testJoinWithAlias($aliasMethod)
    {
        // left join and eager loading
        /** @var ActiveQuery $query */
        $query = Order::find()->joinWith(['customer c']);
        if ($aliasMethod === 'explicit') {
            $orders = $query->orderBy('c.id DESC, order.id')->all();
        } elseif ($aliasMethod === 'querysyntax') {
            $orders = $query->orderBy('{{@customer}}.id DESC, {{@order}}.id')->all();
        } elseif ($aliasMethod === 'applyAlias') {
            $orders = $query->orderBy($query->applyAlias('customer', 'id') . ' DESC,' . $query->applyAlias('order', 'id'))->all();
        }
        $this->assertCount(3, $orders);
        $this->assertEquals(2, $orders[0]->id);
        $this->assertEquals(3, $orders[1]->id);
        $this->assertEquals(1, $orders[2]->id);
        $this->assertTrue($orders[0]->isRelationPopulated('customer'));
        $this->assertTrue($orders[1]->isRelationPopulated('customer'));
        $this->assertTrue($orders[2]->isRelationPopulated('customer'));

        // inner join filtering and eager loading
        $query = Order::find()->innerJoinWith(['customer c']);
        if ($aliasMethod === 'explicit') {
            $orders = $query->where('{{c}}.[[id]]=2')->orderBy('order.id')->all();
        } elseif ($aliasMethod === 'querysyntax') {
            $orders = $query->where('{{@customer}}.[[id]]=2')->orderBy('{{@order}}.id')->all();
        } elseif ($aliasMethod === 'applyAlias') {
            $orders = $query->where([$query->applyAlias('customer', 'id') => 2])->orderBy($query->applyAlias('order', 'id'))->all();
        }
        $this->assertCount(2, $orders);
        $this->assertEquals(2, $orders[0]->id);
        $this->assertEquals(3, $orders[1]->id);
        $this->assertTrue($orders[0]->isRelationPopulated('customer'));
        $this->assertTrue($orders[1]->isRelationPopulated('customer'));

        // inner join filtering without eager loading
        $query = Order::find()->innerJoinWith(['customer c'], false);
        if ($aliasMethod === 'explicit') {
            $orders = $query->where('{{c}}.[[id]]=2')->orderBy('order.id')->all();
        } elseif ($aliasMethod === 'querysyntax') {
            $orders = $query->where('{{@customer}}.[[id]]=2')->orderBy('{{@order}}.id')->all();
        } elseif ($aliasMethod === 'applyAlias') {
            $orders = $query->where([$query->applyAlias('customer', 'id') => 2])->orderBy($query->applyAlias('order', 'id'))->all();
        }
        $this->assertCount(2, $orders);
        $this->assertEquals(2, $orders[0]->id);
        $this->assertEquals(3, $orders[1]->id);
        $this->assertFalse($orders[0]->isRelationPopulated('customer'));
        $this->assertFalse($orders[1]->isRelationPopulated('customer'));

        // join with via-relation
        $query = Order::find()->innerJoinWith(['books b']);
        if ($aliasMethod === 'explicit') {
            $orders = $query->where(['b.name' => 'Yii 1.1 Application Development Cookbook'])->orderBy('order.id')->all();
        } elseif ($aliasMethod === 'querysyntax') {
            $orders = $query->where(['{{@item}}.name' => 'Yii 1.1 Application Development Cookbook'])->orderBy('{{@order}}.id')->all();
        } elseif ($aliasMethod === 'applyAlias') {
            $orders = $query->where([$query->applyAlias('book', 'name') => 'Yii 1.1 Application Development Cookbook'])->orderBy($query->applyAlias('order', 'id'))->all();
        }
        $this->assertCount(2, $orders);
        $this->assertEquals(1, $orders[0]->id);
        $this->assertEquals(3, $orders[1]->id);
        $this->assertTrue($orders[0]->isRelationPopulated('books'));
        $this->assertTrue($orders[1]->isRelationPopulated('books'));
        $this->assertCount(2, $orders[0]->books);
        $this->assertCount(1, $orders[1]->books);

        // joining sub relations
        $query = Order::find()->innerJoinWith([
            'items i' => function ($q) use ($aliasMethod) {
                /* @var $q ActiveQuery */
                if ($aliasMethod === 'explicit') {
                    $q->orderBy('{{i}}.id');
                } elseif ($aliasMethod === 'querysyntax') {
                    $q->orderBy('{{@item}}.id');
                } elseif ($aliasMethod === 'applyAlias') {
                    $q->orderBy($q->applyAlias('item', 'id'));
                }
            },
            'items.category c' => function ($q) use ($aliasMethod) {
                /* @var $q ActiveQuery */
                if ($aliasMethod === 'explicit') {
                    $q->where('{{c}}.[[id]] = 2');
                } elseif ($aliasMethod === 'querysyntax') {
                    $q->where('{{@category}}.[[id]] = 2');
                } elseif ($aliasMethod === 'applyAlias') {
                    $q->where([$q->applyAlias('category', 'id') => 2]);
                }
            },
        ]);
        if ($aliasMethod === 'explicit') {
            $orders = $query->orderBy('{{i}}.id')->all();
        } elseif ($aliasMethod === 'querysyntax') {
            $orders = $query->orderBy('{{@item}}.id')->all();
        } elseif ($aliasMethod === 'applyAlias') {
            $orders = $query->orderBy($query->applyAlias('item', 'id'))->all();
        }
        $this->assertCount(1, $orders);
        $this->assertTrue($orders[0]->isRelationPopulated('items'));
        $this->assertEquals(2, $orders[0]->id);
        $this->assertCount(3, $orders[0]->items);
        $this->assertTrue($orders[0]->items[0]->isRelationPopulated('category'));
        $this->assertEquals(2, $orders[0]->items[0]->category->id);

        // join with ON condition
        if ($aliasMethod === 'explicit' || $aliasMethod === 'querysyntax') {
            $relationName = 'books' . ucfirst($aliasMethod);
            $orders = Order::find()->joinWith(["$relationName b"])->orderBy('order.id')->all();
            $this->assertCount(3, $orders);
            $this->assertEquals(1, $orders[0]->id);
            $this->assertEquals(2, $orders[1]->id);
            $this->assertEquals(3, $orders[2]->id);
            $this->assertTrue($orders[0]->isRelationPopulated($relationName));
            $this->assertTrue($orders[1]->isRelationPopulated($relationName));
            $this->assertTrue($orders[2]->isRelationPopulated($relationName));
            $this->assertCount(2, $orders[0]->$relationName);
            $this->assertCount(0, $orders[1]->$relationName);
            $this->assertCount(1, $orders[2]->$relationName);
        }

        // join with ON condition and alias in relation definition
        if ($aliasMethod === 'explicit' || $aliasMethod === 'querysyntax') {
            $relationName = 'books' . ucfirst($aliasMethod) . 'A';
            $orders = Order::find()->joinWith([(string)$relationName])->orderBy('order.id')->all();
            $this->assertCount(3, $orders);
            $this->assertEquals(1, $orders[0]->id);
            $this->assertEquals(2, $orders[1]->id);
            $this->assertEquals(3, $orders[2]->id);
            $this->assertTrue($orders[0]->isRelationPopulated($relationName));
            $this->assertTrue($orders[1]->isRelationPopulated($relationName));
            $this->assertTrue($orders[2]->isRelationPopulated($relationName));
            $this->assertCount(2, $orders[0]->$relationName);
            $this->assertCount(0, $orders[1]->$relationName);
            $this->assertCount(1, $orders[2]->$relationName);
        }
    }
}
