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
            // DateTime
            [ "DateTime", "Y-m-d", "2020-01-01"],
            [ "DateTime", null,    "boo"],
            // DateTimeImmutable
            [ "DateTimeImmutable", "Y-m-d", "2020-01-01"],
            [ "DateTimeImmutable", null,    "boo"],
        ];
    }

    /**
     * @dataProvider dateTimeProvider
     */
    public function testDateTimeBinder($typeName, $format, $value)
    {
        $binding = $this->dateTimeBinder->bindModel(TypeReflector::getBindingTarget($typeName, $value), $this->context);

        if ($format) {
            $this->assertNotNull($binding);
            $this->assertInstanceOf("yii\\bindings\\BindingResult", $binding);
            $this->assertSame($value, $binding->value->format($format));
        } else {
            $this->assertNull($binding);
        }
    }
}
