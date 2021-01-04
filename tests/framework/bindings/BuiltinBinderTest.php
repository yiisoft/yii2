<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\bindings;


use yii\base\InlineAction;
use yii\bindings\ActionParameterBinder;
use yii\bindings\binders\BuiltinTypeBinder;
use yii\bindings\BindingContext;
use yii\bindings\ModelBinderInterface;
use yiiunit\TestCase;

class BuiltinBinderTest extends TestCase
{
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

    public function builtInBinderProvider()
    {
        return [
            [ "int", 100, "100"],
            [ "int", -100, "-100"],
            [ "int", null, "boo"],

            [ "float", 100.5, "100.5"],
            [ "float", -100.5, "-100.5"],
            [ "float", null, "boo"],

            [ "bool", true, "true"],
            [ "bool", true, "yes"],
            [ "bool", true, "on"],
            [ "bool", true, "1"],
            [ "bool", true, true],
            [ "bool", false, "false"],
            [ "bool", false, "no"],
            [ "bool", false, "off"],
            [ "bool", false, "0"],
            [ "bool", false, false],

            [ "bool", null, "boo"],

            [ "int", null, null],
            [ "float", null, null],
            //[ "bool", false, null],
        ];
    }

    /**
     * @dataProvider builtInBinderProvider
     */
    public function testBuiltInBinder($typeName, $expected, $value)
    {
        $binding = $this->modelBinder->bindModel(TypeReflector::getBindingTarget($typeName, $value), $this->context);

        if ($expected !== null) {
            $this->assertNotNull($binding);
            $this->assertInstanceOf("yii\\bindings\\BindingResult", $binding);
            $this->assertSame($expected, $binding->value);
        } else {
            $this->assertNull($binding);
        }
    }
}
