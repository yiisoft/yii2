<?php

namespace yiiunit\framework\db\pgsql;

use yii\db\JsonExpression;
use yiiunit\data\ar\ActiveRecord;

class BaseActiveRecordTest extends \yiiunit\framework\db\BaseActiveRecordTest
{
    public $driverName = 'pgsql';

    /**
     * @see https://github.com/yiisoft/yii2/issues/19872
     *
     * @dataProvider provideArrayValueWithChange
     */
    public function testJsonDirtyAttributesWithDataChange($actual, $modified)
    {
        $createdStorage = new ArrayAndJsonType([
            'json_col' => new JsonExpression($actual),
        ]);

        $createdStorage->save();

        $foundStorage = ArrayAndJsonType::find()->limit(1)->one();

        $this->assertNotNull($foundStorage);

        $foundStorage->json_col = $modified;

        $this->assertSame(['json_col' => $modified], $foundStorage->getDirtyAttributes());
    }
}

/**
 * {@inheritdoc}
 * @property array id
 * @property array json_col
 */
class ArrayAndJsonType extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%array_and_json_types}}';
    }
}
