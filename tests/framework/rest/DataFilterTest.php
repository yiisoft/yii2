<?php

namespace yiiunit\framework\rest;

use yii\base\DynamicModel;
use yii\rest\DataFilter;
use yiiunit\data\base\Singer;
use yiiunit\TestCase;

/**
 * @group rest
 */
class DataFilterTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->mockApplication();
    }

    // Tests :

    public function testSetupSearchModel()
    {
        $builder = new DataFilter();

        $model = new Singer();
        $builder->setSearchModel($model);
        $this->assertSame($model, $builder->getSearchModel());

        $builder->setSearchModel(Singer::className());
        $model = $builder->getSearchModel();
        $this->assertTrue($model instanceof Singer);

        $builder->setSearchModel([
            'class' => Singer::className(),
            'scenario' => 'search',
        ]);
        $model = $builder->getSearchModel();
        $this->assertTrue($model instanceof Singer);
        $this->assertEquals('search', $model->getScenario());

        $builder->setSearchModel(function () {
            return (new DynamicModel(['name' => null, 'price' => null]))
                ->addRule(['name'], 'string', ['max' => 128])
                ->addRule(['price'], 'number');
        });
        $model = $builder->getSearchModel();
        $this->assertTrue($model instanceof DynamicModel);

        $this->setExpectedException('yii\base\InvalidConfigException');
        $builder->setSearchModel(new \stdClass());
    }

    public function testLoad()
    {
        $filterValue = [
            'name' => 'value'
        ];

        $builder = new DataFilter();

        $this->assertTrue($builder->load(['filter' => $filterValue]));
        $this->assertEquals($filterValue, $builder->getFilter());

        $this->assertFalse($builder->load([]));

        $builder = new DataFilter();
        $builder->filterAttributeName = 'search';

        $builder->load(['filter' => $filterValue]);
        $this->assertNull($builder->getFilter());

        $builder->load(['search' => $filterValue]);
        $this->assertEquals($filterValue, $builder->getFilter());
    }

    /**
     * Data provider for [[testValidate()]].
     * @return array test data.
     */
    public function dataProviderValidate()
    {
        return [
            [
                [],
                true,
                []
            ],
            [
                null,
                true,
                []
            ],
            [
                '',
                false,
                [
                    'The format of Filter is invalid.'
                ]
            ],
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
                    "Unknown filter attribute 'fake'"
                ]
            ],
            [
                [
                    'and' => [
                        [
                            'name' => ['eq' => 'foo']
                        ],
                        [
                            'number' => ['in' => [1, 5, 8]],
                        ],
                    ],
                ],
                true,
                []
            ],
            [
                [
                    'and' => [
                        'name' => ['eq' => 'foo'],
                        'number' => ['in' => [1, 5, 8]],
                    ],
                ],
                false,
                [
                    "Operator 'and' requires multiple operands."
                ]
            ],
            [
                [
                    'not' => ['name' => 'foo']
                ],
                true,
                []
            ],
            [
                [
                    'and' => [
                        [
                            'not' => ['name' => 'foo']
                        ],
                        [
                            'number' => ['in' => [1, 5, 8]],
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
                    "Name must be a string."
                ]
            ],
            [
                [
                    'number' => [
                        'gt' => 10,
                        'lt' => 20,
                    ],
                ],
                true,
                []
            ],
            [
                [
                    'gt' => 10,
                ],
                false,
                [
                    "Operator 'gt' must be used with a search attribute."
                ]
            ],
        ];
    }

    /**
     * @depends testSetupSearchModel
     *
     * @dataProvider dataProviderValidate
     *
     * @param array $filter
     * @param boolean $expectedResult
     * @param array $expectedErrors
     */
    public function testValidate($filter, $expectedResult, $expectedErrors)
    {
        $builder = new DataFilter();
        $searchModel = (new DynamicModel(['name' => null, 'number' => null, 'price' => null, 'tags' => null]))
            ->addRule('name', 'string')
            ->addRule('number', 'integer', ['min' => 0, 'max' => 100])
            ->addRule('price', 'number')
            ->addRule('tags', 'each', ['rule' => ['string']]);

        $builder->setSearchModel($searchModel);

        $builder->filter = $filter;
        $this->assertEquals($expectedResult, $builder->validate());
        $this->assertEquals($expectedErrors, $builder->getErrors('filter'));
    }

    /**
     * Data provider for [[testNormalize()]].
     * @return array test data.
     */
    public function dataProviderNormalize()
    {
        return [
            [
                [],
                [],
            ],
            [
                null,
                [],
            ],
            [
                '',
                [],
            ],
            [
                [
                    'name' => 'foo',
                    'number' => '10',
                ],
                [
                    'name' => 'foo',
                    'number' => '10',
                ],
            ],
            [
                [
                    'number' => [
                        'gt' => 10,
                        'lt' => 20,
                    ],
                ],
                [
                    'number' => [
                        '>' => 10,
                        '<' => 20,
                    ],
                ],
            ],
            [
                [
                    'and' => [
                        [
                            'name' => ['eq' => 'foo']
                        ],
                        [
                            'number' => ['gte' => 15]
                        ],
                    ],
                ],
                [
                    'AND' => [
                        [
                            'name' => ['=' => 'foo']
                        ],
                        [
                            'number' => ['>=' => 15]
                        ],
                    ],
                ],
            ],
            [
                [
                    'authorName' => 'John',
                    'number' => '10',
                ],
                [
                    '{{author}}.[[name]]' => 'John',
                    'number' => '10',
                ],
            ],
        ];
    }

    /**
     * @depends testValidate
     *
     * @dataProvider dataProviderNormalize
     *
     * @param array $filter
     * @param array $expectedResult
     */
    public function testNormalize($filter, $expectedResult)
    {
        $builder = new DataFilter();
        $searchModel = (new DynamicModel(['name' => null, 'number' => null, 'price' => null, 'tags' => null]))
            ->addRule('name', 'string')
            ->addRule('number', 'integer', ['min' => 0, 'max' => 100])
            ->addRule('price', 'number')
            ->addRule('tags', 'each', ['rule' => ['string']]);

        $builder->setSearchModel($searchModel);
        $builder->attributeMap = [
            'authorName' => '{{author}}.[[name]]'
        ];

        $builder->filter = $filter;
        $this->assertEquals($expectedResult, $builder->normalize(false));
    }

    public function testSetupErrorMessages()
    {
        $builder = new DataFilter();
        $builder->setErrorMessages([
            'unsupportedOperatorType' => 'Test message'
        ]);

        $errorMessages = $builder->getErrorMessages();
        $this->assertEquals('Test message', $errorMessages['unsupportedOperatorType']);
        $this->assertTrue(isset($errorMessages['unknownAttribute']));

        $builder->setErrorMessages(function () {
            return [
                'unsupportedOperatorType' => 'Test message callback'
            ];
        });
        $errorMessages = $builder->getErrorMessages();
        $this->assertEquals('Test message callback', $errorMessages['unsupportedOperatorType']);
        $this->assertTrue(isset($errorMessages['unknownAttribute']));
    }
}