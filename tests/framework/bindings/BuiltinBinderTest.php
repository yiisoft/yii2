<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\bindings;

use yii\bindings\binders\BuiltinTypeBinder;

/**
 * @group bindings
 * @requires PHP >= 7.1
 */
class BuiltinBinderTest extends BindingTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->modelBinder = new BuiltinTypeBinder();
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
            // Does not work the same for php5.4 and php5.6+ where null is converted to false
            //[ "bool", false, null],
        ];
    }

    /**
     * @requires PHP >= 7.1
     * @dataProvider builtInBinderProvider
     */
    public function testBuiltInBinder($typeName, $expected, $value)
    {
        $target  = TypeReflector::getBindingParameter($typeName, "value", $value);

        $binding = $this->modelBinder->bindModel($target, $this->context);

        if ($expected !== null) {
            $this->assertNotNull($binding);
            $this->assertInstanceOf("yii\\bindings\\BindingResult", $binding);
            $this->assertSame($expected, $binding->value);
        } else {
            $this->assertNull($binding);
        }
    }
}
