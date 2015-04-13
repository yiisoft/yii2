<?php
namespace yiiunit\framework\db\oci;

use yiiunit\framework\db\ActiveRecordTest;
use yiiunit\data\ar\DefaultPk;
use yiiunit\data\ar\Type;

/**
 * @group db
 * @group oci
 */
class OracleActiveRecordTest extends ActiveRecordTest
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
//        $this->assertSame(true, $model->bool_col);
//        $this->assertSame(false, $model->bool_col2);
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
        $this->assertEquals(3, count($customers));
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
}
