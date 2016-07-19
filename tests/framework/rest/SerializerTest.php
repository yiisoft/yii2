<?php

namespace yiiunit\framework\rest;

use Yii;
use yii\data\ArrayDataProvider;
use yiiunit\TestCase;

/**
 * @group rest
 */
class SerializerTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockWebApplication();
    }

    /**
     * @dataProvider serializeDataProvider
     */
    public function testSerializeDataProvider($allModels, $page, $expectModels)
    {
        $dataProvider = new ArrayDataProvider([
            'allModels' => $allModels,
            'pagination' => [
                'route' => '/',
            ],
        ]);
        $serializer = Yii::createObject('yii\rest\Serializer');

        Yii::$app->getRequest()->setQueryParams(['page' => $page, 'per-page' => 1]);
        $result = $serializer->serialize($dataProvider);
        $this->assertEquals($expectModels, $result);
        $this->assertEquals(array_keys($expectModels), array_keys($result));
    }

    public function serializeDataProvider()
    {
        $model1 = ['id' => 1, 'username' => 'Bob'];
        $model2 = ['id' => 2, 'username' => 'Tom'];
        $models = [$model1, $model2];
        return [
            [
                $models,
                1,
                [$model1],
            ],
            [
                $models,
                2,
                [$model2],
            ],
        ];
    }
}
