<?php

namespace yiiunit\framework\db\mysql;

use yiiunit\data\ar\Storage;

class BaseActiveRecordTest extends \yiiunit\framework\db\BaseActiveRecordTest
{
    public $driverName = 'mysql';

    /**
     * @see https://github.com/yiisoft/yii2/issues/19872
     *
     * @dataProvider provideArrayValueWithChange
     */
    public function testJsonDirtyAttributesWithDataChange($actual, $modified)
    {
        if (version_compare($this->getConnection()->getSchema()->getServerVersion(), '5.7', '<')) {
            $this->markTestSkipped('JSON columns are not supported in MySQL < 5.7');
        }
        if (version_compare(PHP_VERSION, '5.6', '<')) {
            $this->markTestSkipped('JSON columns are not supported in PDO for PHP < 5.6');
        }

        $createdStorage = new Storage(['data' => $actual]);

        $createdStorage->save();

        $foundStorage = Storage::find()->limit(1)->one();

        $this->assertNotNull($foundStorage);

        $foundStorage->data = $modified;

        $this->assertSame(['data' => $modified], $foundStorage->getDirtyAttributes());
    }
}
