<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\bindings;

use Yii;
use yii\bindings\ActionParameterBinder;
use yiiunit\framework\bindings\mocks\Post;

/**
 * @group bindings
 * @requires PHP >= 7.1
 */
class ActiveRecordBinderTest extends BindingTestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function testActiveRecordBinderFindOne()
    {
        $action = $this->getControllerAction("actionActiveRecord");
        $result = $this->parameterBinder->bindActionParams($action, ["id" => 100]);

        /**
         * @var Post
         */
        $instance = $result->arguments["model"];

        $this->assertNotNull($instance);
        $this->assertInstanceOf("yiiunit\\framework\\bindings\\mocks\\Post", $instance);
        $this->assertSame(true, $instance->findOneCalled);
        $this->assertSame(100, $instance->arguments['findOne']["condition"]);
    }

    public function testActiveRecordBinderSetAttributes()
    {
        $action = $this->getControllerAction("actionActiveRecord");

        $id = 100;

        $condition = [
            "condition" => $id
        ];

        $values = [
            "values" => [
                "title" => "title",
                "content" => "some content"
            ],
            "safeOnly" => true
        ];

        $this->setBodyParams($values["values"]);
        $result = $this->parameterBinder->bindActionParams($action, ["id" => $id]);

        /**
         * @var Post
         */
        $instance =  $result->arguments["model"];

        $this->assertNotNull($instance);
        $this->assertInstanceOf("yiiunit\\framework\bindings\mocks\Post", $instance);
        $this->assertSame(true, $instance->findOneCalled);
        $this->assertSame(true, $instance->setAttributesCalled);
        $this->assertSame($values, $instance->arguments["setAttributes"]);
        $this->assertSame($condition, $instance->arguments["findOne"]);
    }
}
