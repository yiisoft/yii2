<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
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
