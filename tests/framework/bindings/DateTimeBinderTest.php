<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\bindings;

use yii\bindings\binders\DateTimeBinder;
use yii\bindings\BindingContext;
use yii\bindings\ModelBinderInterface;
use yiiunit\TestCase;

class DateTimeBinderTest extends TestCase
{
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
        $this->modelBinder = new DateTimeBinder();

        $this->mockWebApplication([
            'components' => [
            ],
        ]);
    }

    public function dateTimeProvider()
    {
        return [
            ["Y-m-d", "2021-01-01", "2020-01-01"],
            ["Y-m-d H:m:s", "2021-01-01 10:30:45", "2020-01-01 10:30:45"],
            ["Y-m-d H:m:s", "2011-10-05 14:48:00", "2011-10-05T14:48:00.000Z"],
            [null,    null, "InvalidDate"],
        ];
    }

    /**
     * @dataProvider dateTimeProvider
     */
    public function testDateTimeBinder($format, $expected,  $value)
    {
        $binding = $this->dateTimeBinder->bindModel(TypeReflector::getBindingTarget("DateTime", $value), $this->context);

        if ($format) {
            $this->assertNotNull($binding);
            $this->assertInstanceOf("yii\\bindings\\BindingResult", $binding);
            $this->assertSame($expected, $binding->value->format($format));
        } else {
            $this->assertNull($binding);
        }

        $binding = $this->dateTimeBinder->bindModel(TypeReflector::getBindingTarget("DateTimeImmutable", $value), $this->context);

        if ($format) {
            $this->assertNotNull($binding);
            $this->assertInstanceOf("yii\\bindings\\BindingResult", $binding);
            $this->assertSame($expected, $binding->value->format($format));
        } else {
            $this->assertNull($binding);
        }
    }
}
