<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\bindings;

use Yii;
use yii\bindings\binders\DataFilterBinder;

class DataFilterBinderTest extends BindingTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->modelBinder = new DataFilterBinder();
    }

    public function testDataFilter()
    {
        $action = $this->getControllerAction("actionDataFilter");

        $values = [
            "filter" => [
                "name" => "value"
            ],
        ];

        $this->setBodyParams($values);

        $result = $this->parameterBinder->bindActionParams($action, []);
        $args   = $result->arguments;

        /**
         * @var \yii\data\DataFilter
         */
        $instance = $args["model"];

        $this->assertNotNull($instance);
        $this->assertInstanceOf(\yii\data\DataFilter::class, $instance);
        $this->assertSame($values["filter"], $instance->getFilter());
    }

    public function testActiveDataFilter()
    {
        $action = $this->getControllerAction("actionActiveDataFilter");

        $values = [
            "filter" => [
                "name" => "value"
            ],
        ];

        $this->setBodyParams($values);

        $result = $this->parameterBinder->bindActionParams($action, []);
        $args   = $result->arguments;

        /**
         * @var \yii\data\ActiveDataFilter
         */
        $instance = $args["model"];

        $this->assertNotNull($instance);
        $this->assertInstanceOf(\yii\data\ActiveDataFilter::class, $instance);
        $this->assertSame($values["filter"], $instance->getFilter());
    }
}
