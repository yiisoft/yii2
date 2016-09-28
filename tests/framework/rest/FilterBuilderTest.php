<?php

namespace yiiunit\framework\rest;

use yii\base\DynamicModel;
use yii\rest\FilterBuilder;
use yiiunit\data\base\SearchModel;
use yiiunit\TestCase;

/**
 * @group rest
 */
class FilterBuilderTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->mockApplication();
    }

    // Tests :

    public function testSetupModel()
    {
        $builder = new FilterBuilder();

        $model = new SearchModel();
        $builder->setModel($model);
        $this->assertSame($model, $builder->getModel());

        $builder->setModel(SearchModel::className());
        $model = $builder->getModel();
        $this->assertTrue($model instanceof SearchModel);

        $builder->setModel([
            'class' => SearchModel::className(),
            'scenario' => 'search',
        ]);
        $model = $builder->getModel();
        $this->assertTrue($model instanceof SearchModel);
        $this->assertEquals('search', $model->getScenario());

        $builder->setModel(function () {
            return (new DynamicModel(['name' => null, 'price' => null]))
                ->addRule(['name'], 'string', ['max' => 128])
                ->addRule(['price'], 'number');
        });
        $model = $builder->getModel();
        $this->assertTrue($model instanceof DynamicModel);

        $this->setExpectedException('yii\base\InvalidConfigException');
        $builder->setModel(new \stdClass());
    }

    /**
     * Data provider for [[testValidate()]].
     * @return array test data.
     */
    public function dataProviderValidate()
    {
        return [
            [
                [
                    'name' => 'foo',
                    'number' => '10',
                ],
                true,
                []
            ],
            [
                [
                    'fake' => 'foo',
                    'number' => '10',
                ],
                false,
                [
                    'Unknown filter attribute fake'
                ]
            ],
            [
                [
                    '$and' => [
                        'name' => [
                            '$eq' => 'foo',
                        ],
                        'number' => [
                            '$in' => [1, 5, 8]
                        ],
                    ],
                ],
                true,
                []
            ],
            [
                [
                    '$and' => [
                        '$not' => [
                            'name' => 'foo',
                        ],
                        'number' => [
                            '$in' => [1, 5, 8]
                        ],
                    ],
                ],
                true,
                []
            ],
            [
                [
                    'name' => ['foo'],
                ],
                false,
                [
                    'Name must be a string.'
                ]
            ],
        ];
    }

    /**
     * @depends testSetupModel
     *
     * @dataProvider dataProviderValidate
     *
     * @param array $filter
     * @param boolean $expectedResult
     * @param array $expectedErrors
     */
    public function testValidate($filter, $expectedResult, $expectedErrors)
    {
        $builder = new FilterBuilder();

        $builder->setModel(new SearchModel());

        $builder->filter = $filter;
        $this->assertEquals($expectedResult, $builder->validate());
        $this->assertEquals($expectedErrors, $builder->errors);
    }
}