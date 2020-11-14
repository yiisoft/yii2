<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci;

use yii\db\Expression;
use yiiunit\data\ar\DefaultPk;
use yiiunit\data\ar\DefaultMultiplePk;
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
//        $this->assertSame(1337.42, $model->float_col);
//        $this->assertSame(42.1337, $model->float_col2);
//        $this->assertTrue($model->bool_col);
//        $this->assertFalse($model->bool_col2);
    }

    public function testDefaultValues()
    {
        $model = new Type();
        $model->loadDefaultValues();
        $this->assertEquals(1, $model->int_col2);
        $this->assertEquals('something', $model->char_col2);
        $this->assertEquals(1.23, $model->float_col2);
        $this->assertEquals(33.22, $model->numeric_col);
        $this->assertTrue($model->bool_col2);

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

    public function testFind()
    {
        /* @var $customerClass \yii\db\ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();
        /* @var $this TestCase|ActiveRecordTestTrait */
        // find one
        $result = $customerClass::find();
        $this->assertInstanceOf('\\yii\\db\\ActiveQueryInterface', $result);
        $customer = $result->one();
        $this->assertInstanceOf($customerClass, $customer);

        // find all
        $customers = $customerClass::find()->all();
        $this->assertCount(3, $customers);
        $this->assertInstanceOf($customerClass, $customers[0]);
        $this->assertInstanceOf($customerClass, $customers[1]);
        $this->assertInstanceOf($customerClass, $customers[2]);

        // find by a single primary key
        $customer = $customerClass::findOne(2);
        $this->assertInstanceOf($customerClass, $customer);
        $this->assertEquals('user2', $customer->name);
        $customer = $customerClass::findOne(5);
        $this->assertNull($customer);
        $customer = $customerClass::findOne(['id' => [5, 6, 1]]);
        $this->assertInstanceOf($customerClass, $customer);
        $customer = $customerClass::find()->where(['id' => [5, 6, 1]])->one();
        $this->assertNotNull($customer);

        // find by column values
        $customer = $customerClass::findOne(['id' => 2, 'name' => 'user2']);
        $this->assertInstanceOf($customerClass, $customer);
        $this->assertEquals('user2', $customer->name);
        $customer = $customerClass::findOne(['id' => 2, 'name' => 'user1']);
        $this->assertNull($customer);
        $customer = $customerClass::findOne(['id' => 5]);
        $this->assertNull($customer);
        $customer = $customerClass::findOne(['name' => 'user5']);
        $this->assertNull($customer);

        // find by attributes
        $customer = $customerClass::find()->where(['name' => 'user2'])->one();
        $this->assertInstanceOf($customerClass, $customer);
        $this->assertEquals(2, $customer->id);

        // find by expression
        $customer = $customerClass::findOne(new Expression('[[id]] = :id', [':id' => 2]));
        $this->assertInstanceOf($customerClass, $customer);
        $this->assertEquals('user2', $customer->name);
        $customer = $customerClass::findOne(
            new Expression('[[id]] = :id AND [[name]] = :name', [':id' => 2, ':name' => 'user1'])
        );
        $this->assertNull($customer);
        $customer = $customerClass::findOne(
            new Expression('[[id]] = :id', [':id' => 5])
        );
        $this->assertNull($customer);
        $customer = $customerClass::findOne(
            new Expression('[[name]] = :name', [':name' => 'user5'])
        );
        $this->assertNull($customer);

        // scope
        $this->assertCount(2, $customerClass::find()->active()->all());
        $this->assertEquals(2, $customerClass::find()->active()->count());
    }
}
