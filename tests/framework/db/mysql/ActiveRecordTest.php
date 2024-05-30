<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql;

use yiiunit\data\ar\Storage;
use yiiunit\data\ar\Type;

/**
 * @group db
 * @group mysql
 */
class ActiveRecordTest extends \yiiunit\framework\db\ActiveRecordTest
{
    public $driverName = 'mysql';

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
        $this->assertSame('1337', trim((string) $model->char_col));
        $this->assertSame('test', $model->char_col2);
        $this->assertSame('test123', $model->char_col3);
        $this->assertSame(3.742, $model->float_col);
        $this->assertSame(42.1337, $model->float_col2);
        //$this->assertSame(true, $model->bool_col);
        //$this->assertSame(false, $model->bool_col2);
    }

    public function testJsonColumn()
    {
        if (version_compare($this->getConnection()->getSchema()->getServerVersion(), '5.7', '<')) {
            $this->markTestSkipped('JSON columns are not supported in MySQL < 5.7');
        }
        if (version_compare(PHP_VERSION, '5.6', '<')) {
            $this->markTestSkipped('JSON columns are not supported in PDO for PHP < 5.6');
        }

        $data = [
            'obj' => ['a' => ['b' => ['c' => 2.7418]]],
            'array' => [1,2,null,3],
            'null_field' => null,
            'boolean_field' => true,
            'last_update_time' => '2018-02-21',
        ];

        $storage = new Storage(['data' => $data]);
        $this->assertTrue($storage->save(), 'Storage can be saved');
        $this->assertNotNull($storage->id);

        $retrievedStorage = Storage::findOne($storage->id);
        $this->assertSame($data, $retrievedStorage->data, 'Properties are restored from JSON to array without changes');

        $retrievedStorage->data = ['updatedData' => $data];
        $this->assertSame(1, $retrievedStorage->update(), 'Storage can be updated');

        $retrievedStorage->refresh();
        $this->assertSame(['updatedData' => $data], $retrievedStorage->data, 'Properties have been changed during update');
    }
}
