<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql;

use yiiunit\data\ar\Storage;

/**
 * @group db
 * @group mysql
 */
class CommandTest extends \yiiunit\framework\db\CommandTest
{
    public $driverName = 'mysql';

    protected $upsertTestCharCast = 'CONVERT([[address]], CHAR)';

    public function testAddDropCheck()
    {
        $this->markTestSkipped('MySQL does not support adding/dropping check constraints.');
    }

    public function testJsonInsert()
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

        $this->getConnection()->createCommand()->insert(Storage::tableName(), [
            'id' => 123456,
            'data' => $data
        ])->execute();

        $retrievedStorage = Storage::findOne(123456);
        $this->assertSame($data, $retrievedStorage->data, 'Properties are restored from JSON to array without changes');
    }
}
