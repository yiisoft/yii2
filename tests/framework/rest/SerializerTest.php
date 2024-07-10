<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\rest;

use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\rest\Serializer;
use yiiunit\TestCase;

/**
 * @group rest
 */
class SerializerTest extends TestCase
{
    protected function setUp(): void
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
        $serializer = new Serializer();
        $model = new TestModel();

        $model->addError('field1', 'Test error');
        $model->addError('field2', 'Multiple error 1');
        $model->addError('field2', 'Multiple error 2');

        $this->assertEquals([
            [
                'field' => 'field1',
                'message' => 'Test error',
            ],
            [
                'field' => 'field2',
                'message' => 'Multiple error 1',
            ],
        ], $serializer->serialize($model));
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

    public function testNestedExpand()
    {
        $serializer = new Serializer();
        $model = new TestModel();
        $model->extraField3 = new TestModel2();

        TestModel::$extraFields = ['extraField3'];
        TestModel2::$extraFields = ['extraField4'];

        \Yii::$app->request->setQueryParams(['expand' => 'extraField3.extraField4']);
        $this->assertSame([
            'field1' => 'test',
            'field2' => 2,
            'extraField3' => [
                'field3' => 'test2',
                'field4' => 8,
                'extraField4' => 'testExtra2',
            ],
        ], $serializer->serialize($model));
    }

    public function testFields()
    {
        $serializer = new Serializer();
        $model = new TestModel();
        $model->extraField3 = new TestModel2();

        TestModel::$extraFields = ['extraField3'];

        \Yii::$app->request->setQueryParams([]);
        $this->assertSame([
            'field1' => 'test',
            'field2' => 2,
        ], $serializer->serialize($model));

        \Yii::$app->request->setQueryParams(['fields' => '*']);
        $this->assertSame([
            'field1' => 'test',
            'field2' => 2,
        ], $serializer->serialize($model));

        \Yii::$app->request->setQueryParams(
            [
                'fields' => 'field1,extraField3.field3',
                'expand' => 'extraField3.extraField4'
            ]
        );
        $this->assertSame([
            'field1' => 'test',
            'extraField3' => [
                'field3' => 'test2',
                'extraField4' => 'testExtra2',
            ],
        ], $serializer->serialize($model));

        \Yii::$app->request->setQueryParams(
            [
                'fields' => 'extraField3.*',
                'expand' => 'extraField3',
            ]
        );
        $this->assertSame([
            'extraField3' => [
                'field3' => 'test2',
                'field4' => 8,
            ],
        ], $serializer->serialize($model));

        \Yii::$app->request->setQueryParams(
            [
                'fields' => 'extraField3.*',
                'expand' => 'extraField3.extraField4'
            ]
        );
        $this->assertSame([
            'extraField3' => [
                'field3' => 'test2',
                'field4' => 8,
                'extraField4' => 'testExtra2',
            ],
        ], $serializer->serialize($model));

        $model->extraField3 = [
            new TestModel2(),
            new TestModel2(),
        ];

        \Yii::$app->request->setQueryParams(
            [
                'fields' => 'extraField3.*',
                'expand' => 'extraField3',
            ]
        );
        $this->assertSame([
            'extraField3' => [
                [
                    'field3' => 'test2',
                    'field4' => 8,
                ],
                [
                    'field3' => 'test2',
                    'field4' => 8,
                ],
            ],
        ], $serializer->serialize($model));

        \Yii::$app->request->setQueryParams(
            [
                'fields' => '*,extraField3.*',
                'expand' => 'extraField3',
            ]
        );
        $this->assertSame([
            'field1' => 'test',
            'field2' => 2,
            'extraField3' => [
                [
                    'field3' => 'test2',
                    'field4' => 8,
                ],
                [
                    'field3' => 'test2',
                    'field4' => 8,
                ],
            ],
        ], $serializer->serialize($model));

        \Yii::$app->request->setQueryParams(
            [
                'fields' => 'extraField3.field3',
                'expand' => 'extraField3',
            ]
        );
        $this->assertSame([
            'extraField3' => [
                ['field3' => 'test2'],
                ['field3' => 'test2'],
            ],
        ], $serializer->serialize($model));
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/12107
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

    public function dataProviderSerializeDataProvider()
    {
        return [
            [
                new ArrayDataProvider([
                    'allModels' => [
                        ['id' => 1, 'username' => 'Bob'],
                        ['id' => 2, 'username' => 'Tom'],
                    ],
                    'pagination' => [
                        'route' => '/',
                    ],
                ]),
                [
                    ['id' => 1, 'username' => 'Bob'],
                    ['id' => 2, 'username' => 'Tom'],
                ],
            ],
            [
                new ArrayDataProvider([
                    'allModels' => [
                        ['id' => 1, 'username' => 'Bob'],
                        ['id' => 2, 'username' => 'Tom'],
                    ],
                    'pagination' => [
                        'route' => '/',
                        'pageSize' => 1,
                        'page' => 0,
                    ],
                ]),
                [
                    ['id' => 1, 'username' => 'Bob'],
                ],
            ],
            [
                new ArrayDataProvider([
                    'allModels' => [
                        ['id' => 1, 'username' => 'Bob'],
                        ['id' => 2, 'username' => 'Tom'],
                    ],
                    'pagination' => [
                        'route' => '/',
                        'pageSize' => 1,
                        'page' => 1,
                    ],
                ]),
                [
                    ['id' => 2, 'username' => 'Tom'],
                ],
            ],
            [
                new ArrayDataProvider([
                    'allModels' => [
                        'Bob' => ['id' => 1, 'username' => 'Bob'],
                        'Tom' => ['id' => 2, 'username' => 'Tom'],
                    ],
                    'pagination' => [
                        'route' => '/',
                        'pageSize' => 1,
                        'page' => 1,
                    ],
                ]),
                [
                    ['id' => 2, 'username' => 'Tom'],
                ],
            ],
            [
                new ArrayDataProvider([
                    'allModels' => [
                        ['id' => 1, 'username' => 'Bob'],
                        ['id' => 2, 'username' => 'Tom'],
                    ],
                    'pagination' => [
                        'route' => '/',
                        'pageSize' => 1,
                        'page' => 1,
                    ],
                ]),
                [
                    1 => ['id' => 2, 'username' => 'Tom'],
                ],
                true,
            ],
            [
                new ArrayDataProvider([
                    'allModels' => [
                        'Bob' => ['id' => 1, 'username' => 'Bob'],
                        'Tom' => ['id' => 2, 'username' => 'Tom'],
                    ],
                    'pagination' => [
                        'route' => '/',
                        'pageSize' => 1,
                        'page' => 1,
                    ],
                ]),
                [
                    'Tom' => ['id' => 2, 'username' => 'Tom'],
                ],
                true,
            ],
            /*[
                new ArrayDataProvider([
                    'allModels' => [
                        new \DateTime('2000-01-01'),
                    ],
                    'pagination' => [
                        'route' => '/',
                    ],
                ]),
                [
                    [
                        'date' => '2000-01-01 00:00:00.000000',
                        'timezone_type' => 3,
                        'timezone' => 'UTC',
                    ],
                ]
            ],*/
        ];
    }

    /**
     * @dataProvider dataProviderSerializeDataProvider
     *
     * @param \yii\data\DataProviderInterface $dataProvider
     * @param array $expectedResult
     * @param bool $saveKeys
     */
    public function testSerializeDataProvider($dataProvider, $expectedResult, $saveKeys = false)
    {
        $serializer = new Serializer();
        $serializer->preserveKeys = $saveKeys;

        $this->assertEquals($expectedResult, $serializer->serialize($dataProvider));
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/16334
     */
    public function testSerializeJsonSerializable()
    {
        $serializer = new Serializer();
        $model3 = new TestModel3();
        $model4 = new TestModel4();

        $this->assertEquals(['customField' => 'test3/test4'], $serializer->serialize($model3));
        $this->assertEquals(['customField2' => 'test5/test6'], $serializer->serialize($model4));
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/16334
     */
    public function testSerializeArrayableWithJsonSerializableAttribute()
    {
        $serializer = new Serializer();
        $model = new TestModel5();

        $this->assertEquals(
            [
                'field7' => 'test7',
                'field8' => 'test8',
                'testModel3' => ['customField' => 'test3/test4'],
                'testModel4' => ['customField2' => 'test5/test6'],
                'testModelArray' => [['customField' => 'test3/test4'], ['customField2' => 'test5/test6']],
            ],
            $serializer->serialize($model)
        );
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/17886
     */
    public function testSerializeArray()
    {
        $serializer = new Serializer();
        $model1 = new TestModel();
        $model2 = new TestModel();
        $model3 = new TestModel();

        $this->assertSame([
            [
                'field1' => 'test',
                'field2' => 2,
            ],
            [
                'field1' => 'test',
                'field2' => 2,
            ],
            'testKey' => [
                'field1' => 'test',
                'field2' => 2,
            ],
        ], $serializer->serialize([$model1, $model2, 'testKey' => $model3]));
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
    public $extraField3;

    public function fields()
    {
        return static::$fields;
    }

    public function extraFields()
    {
        return static::$extraFields;
    }
}

class TestModel2 extends Model
{
    public static $fields = ['field3', 'field4'];
    public static $extraFields = [];

    public $field3 = 'test2';
    public $field4 = 8;
    public $extraField4 = 'testExtra2';

    public function fields()
    {
        return static::$fields;
    }

    public function extraFields()
    {
        return static::$extraFields;
    }
}

class TestModel3 extends Model implements \JsonSerializable
{
    public static $fields = ['field3', 'field4'];
    public static $extraFields = [];

    public $field3 = 'test3';
    public $field4 = 'test4';
    public $extraField4 = 'testExtra2';

    public function fields()
    {
        return [
            'customField' => function() {
                return $this->field3.'/'.$this->field4;
            },
        ];
    }

    public function extraFields()
    {
        return static::$extraFields;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->getAttributes();
    }
}
class TestModel4 implements \JsonSerializable
{
    public $field5 = 'test5';
    public $field6 = 'test6';

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'customField2' => $this->field5.'/'.$this->field6,
        ];
    }
}

class TestModel5 extends Model
{
    public static $fields = ['field7', 'field8'];
    public static $extraFields = [];

    public $field7 = 'test7';
    public $field8 = 'test8';
    public $extraField4 = 'testExtra4';

    public function fields()
    {
        $fields = static::$fields;
        $fields['testModel3'] = function() {
            return $this->getTestModel3();
        };
        $fields['testModel4'] = function() {
            return $this->getTestModel4();
        };
        $fields['testModelArray'] = function() {
            return [$this->getTestModel3(), $this->getTestModel4()];
        };
        return $fields;
    }

    public function extraFields()
    {
        return static::$extraFields;
    }

    public function getTestModel3()
    {
        return new TestModel3();
    }

    public function getTestModel4()
    {
        return new TestModel4();
    }
}
