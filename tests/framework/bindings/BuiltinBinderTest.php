<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\bindings;


use yii\base\InlineAction;
use yii\bindings\ActionParameterBinder;
use yii\bindings\binders\ActiveRecordBinder;
use yii\bindings\binders\BuiltinTypeBinder;
use yii\bindings\binders\ContainerBinder;
use yii\bindings\binders\DateTimeBinder;
use yii\bindings\binders\DataFilterBinder;
use yii\bindings\BindingContext;
use yii\bindings\ModelBinderInterface;
use yiiunit\TestCase;

class BuiltinBinderTest extends TestCase
{
    private const DELTA = 0.0001;
    /**
     * @var ActionParameterBinder
     */
    private $parameterBinder;

    /**
     * @var ModelBinderInterface
     */
    private $modelBinder;

    /**
     * @var BindingContext
     */
    private $context = null;

    protected function setUp()
    {
        parent::setUp();
        $this->parameterBinder = new ActionParameterBinder();
        $this->modelBinder = new BuiltinTypeBinder();

        $this->mockWebApplication([
            'components' => [
            ],
        ]);
    }

    public function testActionBuiltinProvider()
    {
        return [
            [0, 0, 0, /**/ "0", "0", "0"],
            [0, 0, 0, /**/ "0", "0", "0"],
            [0, 0, 0, /**/ "0", "0", "0"],
            [0, 0, 0, /**/ "0", "0", "0"],
        ];
    }

    /**
     * @dataProvider testActionBuiltinProvider
     */
    public function testActionBuiltin($int, $float, $bool, $intParam, $floatParam, $boolParam)
    {
        $action = new InlineAction("action", $this->controller, "actionBuiltin");

        $result = $this->parameterBinder->bindActionParams($action, [
            "int" => $intParam,
            "float" => $floatParam,
            "bool" => $boolParam
        ]);

        $args = $result->arguments;

        $this->assertCount(3, $args);
        $this->assertSame($int, $args['int']);
        $this->assertSame($float, $args['float']);
        $this->assertSame($bool, $args['bool']);
    }

    public function builtInBinderProvider()
    {
        return [
            [ "int", 100, "100"],
            [ "int", -100, "-100"],
            [ "int", null, "boo"],

            [ "float", 100, "100"],
            [ "float", -100.5, "-100.5"],
            [ "float", null, "boo"],

            [ "bool", true, "true"],
            [ "bool", true, "yes"],
            [ "bool", true, "on"],
            [ "bool", false, "false"],
            [ "bool", false, "no"],
            [ "bool", false, "off"],
            [ "bool", null, null]
        ];
    }

    /**
     * @dataProvider builtInBinderProvider
     */
    public function testBuiltInBinder($typeName, $expected, $value)
    {
        $binding = $this->modelBinder->bindModel(TypeReflector::getBindingTarget($typeName, $value), $this->context);

        if ($expected) {
            $this->assertNotNull($binding);
            $this->assertInstanceOf("yii\\bindings\\BindingResult", $binding);
            $this->assertSame($expected, $binding->value);
        } else {
            $this->assertNull($binding);
        }
    }
}
