<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\bindings;

use yii\bindings\binders\DataFilterBinder;

/**
 * @group bindings
 * @requires PHP >= 7.1
 */
class DataFilterBinderTest extends BindingTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->modelBinder = new DataFilterBinder();
    }

    public function dataProvider() {
        return [
            ['actionDataFilter',  "yii\data\DataFilter"],
            ['actionActiveDataFilter',  "yii\data\ActiveDataFilter"],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testDataFilter($actionName, $typeName)
    {
        //TODO: Use parameter name instead of "filter" property
        $values = [
            "filter" => [
                "name" => "value"
            ],
        ];

        $this->setBodyParams($values);

        $action = $this->getControllerAction($actionName);
        $result = $this->parameterBinder->bindActionParams($action, []);
        $args   = $result->arguments;

        $instance = $args["model"];

        $this->assertNotNull($instance);
        $this->assertInstanceOf($typeName, $instance);
        $this->assertSame($values["filter"], $instance->getFilter());
    }
}
