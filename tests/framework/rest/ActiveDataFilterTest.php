<?php

namespace yiiunit\framework\rest;

use yii\base\DynamicModel;
use yii\rest\ActiveDataFilter;
use yiiunit\TestCase;

class ActiveDataFilterTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->mockApplication();
    }

    // Tests :

    public function dataProviderBuild()
    {
        return [
            [
                [],
                [],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderBuild
     *
     * @param array $filter
     * @param array $expectedResult
     */
    public function testBuild($filter, $expectedResult)
    {
        $builder = new ActiveDataFilter();
        $searchModel = (new DynamicModel(['name' => null, 'number' => null, 'price' => null, 'tags' => null]))
            ->addRule('name', 'string')
            ->addRule('number', 'integer', ['min' => 0, 'max' => 100])
            ->addRule('price', 'number')
            ->addRule('tags', 'each', ['rule' => ['string']]);

        $builder->setSearchModel($searchModel);

        $builder->filter = $filter;
        $this->assertEquals($expectedResult, $builder->build());
    }
}