<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\web;

use yii\base\BaseObject;
use yii\web\JsExpression;
use yiiunit\TestCase;

/**
 * @group web
 */
class JsExpressionTest extends TestCase
{
    public function testExtendsBaseObject(): void
    {
        $expression = new JsExpression('var x = 1');

        $this->assertInstanceOf(BaseObject::class, $expression);
    }

    public function testConstructorSetsExpression(): void
    {
        $expression = new JsExpression('function() { return true; }');

        $this->assertSame('function() { return true; }', $expression->expression);
    }

    /**
     * @dataProvider provideToStringData
     */
    public function testToString($input, string $expected): void
    {
        $expression = new JsExpression($input);

        $this->assertSame($expected, (string) $expression);
    }

    public static function provideToStringData(): array
    {
        return [
            'simple expression' => ['var x = 1', 'var x = 1'],
            'function' => ['function() { return true; }', 'function() { return true; }'],
            'empty string' => ['', ''],
            'numeric string' => ['42', '42'],
        ];
    }

    public function testToStringWithNullExpression(): void
    {
        $expression = new JsExpression('placeholder');
        $expression->expression = null;

        $this->assertSame('', (string) $expression);
    }
}
