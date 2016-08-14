<?php

namespace yiiunit\framework\rest;

use yii\base\Model;
use yii\rest\Serializer;
use yiiunit\TestCase;

/**
 * @group rest
 */
class SerializerTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication([
            'components' => [
                'request' => [
                    'scriptUrl' => '/index.php',
                ],
            ],

        ], 'yii\web\Application');

        TestModel::$fields = ['field1', 'field2'];
        TestModel::$extraFields = [];
    }

    public function testSerializeModelErrors()
    {
        // TODO
    }

    public function testSerializeModelData()
    {
        $serializer = new Serializer();
        $model = new TestModel();

        $this->assertSame([
            'field1' => 'test',
            'field2' => 2,
        ], $serializer->serialize($model));

        TestModel::$fields = ['field1'];
        TestModel::$extraFields = [];

        $this->assertSame([
            'field1' => 'test',
        ], $serializer->serialize($model));

        TestModel::$fields = ['field1'];
        TestModel::$extraFields = ['field2'];

        $this->assertSame([
            'field1' => 'test',
        ], $serializer->serialize($model));
    }

    public function testExpand()
    {
        $serializer = new Serializer();
        $model = new TestModel();

        TestModel::$fields = ['field1', 'field2'];
        TestModel::$extraFields = ['extraField1'];

        $this->assertSame([
            'field1' => 'test',
            'field2' => 2,
        ], $serializer->serialize($model));

        \Yii::$app->request->setQueryParams(['expand' => 'extraField1']);
        $this->assertSame([
            'field1' => 'test',
            'field2' => 2,
            'extraField1' => 'testExtra',
        ], $serializer->serialize($model));

        \Yii::$app->request->setQueryParams(['expand' => 'extraField1,extraField2']);
        $this->assertSame([
            'field1' => 'test',
            'field2' => 2,
            'extraField1' => 'testExtra',
        ], $serializer->serialize($model));

        \Yii::$app->request->setQueryParams(['expand' => 'field1,extraField2']);
        $this->assertSame([
            'field1' => 'test',
            'field2' => 2,
        ], $serializer->serialize($model));
    }

    /**
     * https://github.com/yiisoft/yii2/issues/12107
     */
    public function testExpandInvalidInput()
    {
        $serializer = new Serializer();
        $model = new TestModel();

        \Yii::$app->request->setQueryParams(['expand' => ['field1,extraField2']]);
        $this->assertSame([
            'field1' => 'test',
            'field2' => 2,
        ], $serializer->serialize($model));

        \Yii::$app->request->setQueryParams(['fields' => ['field1,extraField2']]);
        $this->assertSame([
            'field1' => 'test',
            'field2' => 2,
        ], $serializer->serialize($model));

        \Yii::$app->request->setQueryParams(['fields' => ['field1,extraField2'], 'expand' => ['field1,extraField2']]);
        $this->assertSame([
            'field1' => 'test',
            'field2' => 2,
        ], $serializer->serialize($model));
    }

    public function testSerializeDataProvider()
    {
        // TODO
    }


}

class TestModel extends Model
{
    public static $fields = ['field1', 'field2'];
    public static $extraFields = [];

    public $field1 = 'test';
    public $field2 = 2;
    public $extraField1 = 'testExtra';
    public $extraField2 = 42;

    public function fields()
    {
        return static::$fields;
    }

    public function extraFields()
    {
        return static::$extraFields;
    }
}