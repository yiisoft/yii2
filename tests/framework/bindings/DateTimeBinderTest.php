<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\bindings;

use DateTime;
use DateTimeInterface;
use yii\bindings\binders\DateTimeBinder;
use yii\bindings\BindingContext;
use yii\bindings\ModelBinderInterface;
use yiiunit\TestCase;

/**
 * @group bindings
 * @requires PHP >= 7.1
 */
class DateTimeBinderTest extends BindingTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->modelBinder = new DateTimeBinder();
    }

    public function dateTimeProvider()
    {
        return [
            ["Y-m-d", "2021-01-01", "2021-01-01"],
            ["Y-m-d H:i:s", "2021-01-01 10:30:45", "2021-01-01 10:30:45"],
            ["Y-m-d H:i:s", "2021-01-03 02:45:31", "2021-01-03T02:45:31.523Z"],
            //["Y-m-d H:i:s", "2021-01-03 02:45:31", "2021-01-03T02:45:31+0000"],
            [null, null, "InvalidDate"],
        ];
    }

    /**
     * @dataProvider dateTimeProvider
     */
    public function testDateTimeBinder($format, $expected,  $value)
    {
        $target  = TypeReflector::getBindingParameter("DateTime", "value", $value);
        $binding = $this->modelBinder->bindModel($target, $this->context);

        if ($format) {
            $this->assertNotNull($binding);
            $this->assertInstanceOf("yii\\bindings\\BindingResult", $binding);
            $this->assertSame($expected, $binding->value->format($format));
        } else {
            $this->assertNull($binding);
        }

        $target  = TypeReflector::getBindingParameter("DateTimeImmutable", "value", $value);
        $binding = $this->modelBinder->bindModel($target, $this->context);

        if ($format) {
            $this->assertNotNull($binding);
            $this->assertInstanceOf("yii\\bindings\\BindingResult", $binding);
            $this->assertSame($expected, $binding->value->format($format));
        } else {
            $this->assertNull($binding);
        }
    }
}
