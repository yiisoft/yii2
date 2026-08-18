<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\caching;

use PHPUnit\Framework\TestCase;
use yii\caching\ArrayCache;
use yii\caching\ExpressionDependency;

class ExpressionDependencyTest extends TestCase
{
    /**
     * @var ArrayCache
     */
    private $cache;

    protected function setUp(): void
    {
        $this->cache = new ArrayCache();
    }

    public function testDefaultExpressionIsTrue(): void
    {
        $dependency = new ExpressionDependency();
        $this->assertSame('true', $dependency->expression);
    }

    public function testDefaultParamsIsNull(): void
    {
        $dependency = new ExpressionDependency();
        $this->assertNull($dependency->params);
    }

    public function testNotChangedWhenExpressionReturnsSameValue(): void
    {
        $dependency = new ExpressionDependency([
            'expression' => '1 + 1',
        ]);

        $dependency->evaluateDependency($this->cache);
        $this->assertSame(2, $dependency->data);
        $this->assertFalse($dependency->isChanged($this->cache));
    }

    public function testDefaultExpressionNotChanged(): void
    {
        $dependency = new ExpressionDependency();

        $dependency->evaluateDependency($this->cache);
        $this->assertTrue($dependency->data);
        $this->assertFalse($dependency->isChanged($this->cache));
    }

    public function testStringExpression(): void
    {
        $dependency = new ExpressionDependency([
            'expression' => '"hello" . " " . "world"',
        ]);

        $dependency->evaluateDependency($this->cache);
        $this->assertSame('hello world', $dependency->data);
        $this->assertFalse($dependency->isChanged($this->cache));
    }

    public function testExpressionWithParams(): void
    {
        $dependency = new ExpressionDependency([
            'expression' => '$this->params["multiplier"] * 10',
            'params' => ['multiplier' => 5],
        ]);

        $dependency->evaluateDependency($this->cache);
        $this->assertSame(50, $dependency->data);
        $this->assertFalse($dependency->isChanged($this->cache));
    }

    public function testChangedWhenParamsChange(): void
    {
        $dependency = new ExpressionDependency([
            'expression' => '$this->params["value"]',
            'params' => ['value' => 'original'],
        ]);

        $dependency->evaluateDependency($this->cache);
        $this->assertSame('original', $dependency->data);

        $dependency->params = ['value' => 'modified'];
        $this->assertTrue($dependency->isChanged($this->cache));
    }

    public function testExpressionWithArrayResult(): void
    {
        $dependency = new ExpressionDependency([
            'expression' => '[1, 2, 3]',
        ]);

        $dependency->evaluateDependency($this->cache);
        $this->assertSame([1, 2, 3], $dependency->data);
        $this->assertFalse($dependency->isChanged($this->cache));
    }

    public function testExpressionWithNullResult(): void
    {
        $dependency = new ExpressionDependency([
            'expression' => 'null',
        ]);

        $dependency->evaluateDependency($this->cache);
        $this->assertNull($dependency->data);
        $this->assertFalse($dependency->isChanged($this->cache));
    }

    public function testExpressionWithPhpFunction(): void
    {
        $dependency = new ExpressionDependency([
            'expression' => 'strlen("test")',
        ]);

        $dependency->evaluateDependency($this->cache);
        $this->assertSame(4, $dependency->data);
        $this->assertFalse($dependency->isChanged($this->cache));
    }

    public function testExpressionWithTernary(): void
    {
        $dependency = new ExpressionDependency([
            'expression' => '$this->params["flag"] ? "yes" : "no"',
            'params' => ['flag' => true],
        ]);

        $dependency->evaluateDependency($this->cache);
        $this->assertSame('yes', $dependency->data);

        $dependency->params = ['flag' => false];
        $this->assertTrue($dependency->isChanged($this->cache));
    }

    public function testParamsAccessibleViaThisParams(): void
    {
        $dependency = new ExpressionDependency([
            'expression' => 'array_sum($this->params)',
            'params' => [10, 20, 30],
        ]);

        $dependency->evaluateDependency($this->cache);
        $this->assertSame(60, $dependency->data);
    }

    public function testExpressionWithBooleanResult(): void
    {
        $dependency = new ExpressionDependency([
            'expression' => '5 > 3',
        ]);

        $dependency->evaluateDependency($this->cache);
        $this->assertTrue($dependency->data);
        $this->assertFalse($dependency->isChanged($this->cache));
    }
}
