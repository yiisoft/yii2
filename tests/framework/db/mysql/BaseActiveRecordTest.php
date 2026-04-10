<?php

namespace yiiunit\framework\db\mysql;

use yiiunit\data\ar\Storage;
use yiiunit\base\db\BaseActiveRecordTemplate;

/**
 * @group db
 * @group mysql
 */
class BaseActiveRecordTest extends BaseActiveRecordTemplate
{
    public $driverName = 'mysql';

    /**
     * @see https://github.com/yiisoft/yii2/issues/19872
     *
     * @dataProvider provideArrayValueWithChange
     */
    public function testJsonDirtyAttributesWithDataChange($actual, $modified): void
    {
        if (version_compare($this->getConnection()->getSchema()->getServerVersion(), '5.7', '<')) {
            $this->markTestSkipped('JSON columns are not supported in MySQL < 5.7');
        }
        $createdStorage = new Storage(['data' => $actual]);

        $createdStorage->save();

        $foundStorage = Storage::find()->limit(1)->one();

        $this->assertNotNull($foundStorage);

        $foundStorage->data = $modified;

        $this->assertSame(['data' => $modified], $foundStorage->getDirtyAttributes());
    }
}
