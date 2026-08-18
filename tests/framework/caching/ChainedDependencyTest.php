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
use yii\caching\CallbackDependency;
use yii\caching\ChainedDependency;

class ChainedDependencyTest extends TestCase
{
    /**
     * @var ArrayCache
     */
    private $cache;

    protected function setUp(): void
    {
        $this->cache = new ArrayCache();
    }

    public function testDependOnAllTrueNoneChanged(): void
    {
        $dep1 = new CallbackDependency(['callback' => function () {
            return 1;
        }]);
        $dep2 = new CallbackDependency(['callback' => function () {
            return 2;
        }]);

        $chained = new ChainedDependency([
            'dependencies' => [$dep1, $dep2],
            'dependOnAll' => true,
        ]);

        $chained->evaluateDependency($this->cache);
        $this->assertFalse($chained->isChanged($this->cache));
    }

    public function testDependOnAllTrueOneChanged(): void
    {
        $value = 1;
        $dep1 = new CallbackDependency(['callback' => function () use (&$value) {
            return $value;
        }]);
        $dep2 = new CallbackDependency(['callback' => function () {
            return 2;
        }]);

        $chained = new ChainedDependency([
            'dependencies' => [$dep1, $dep2],
            'dependOnAll' => true,
        ]);

        $chained->evaluateDependency($this->cache);
        $value = 99;
        $this->assertTrue($chained->isChanged($this->cache));
    }

    public function testDependOnAllTrueAllChanged(): void
    {
        $v1 = 1;
        $v2 = 2;
        $dep1 = new CallbackDependency(['callback' => function () use (&$v1) {
            return $v1;
        }]);
        $dep2 = new CallbackDependency(['callback' => function () use (&$v2) {
            return $v2;
        }]);

        $chained = new ChainedDependency([
            'dependencies' => [$dep1, $dep2],
            'dependOnAll' => true,
        ]);

        $chained->evaluateDependency($this->cache);
        $v1 = 10;
        $v2 = 20;
        $this->assertTrue($chained->isChanged($this->cache));
    }

    public function testDependOnAllFalseNoneChanged(): void
    {
        $dep1 = new CallbackDependency(['callback' => function () {
            return 1;
        }]);
        $dep2 = new CallbackDependency(['callback' => function () {
            return 2;
        }]);

        $chained = new ChainedDependency([
            'dependencies' => [$dep1, $dep2],
            'dependOnAll' => false,
        ]);

        $chained->evaluateDependency($this->cache);
        $this->assertFalse($chained->isChanged($this->cache));
    }

    public function testDependOnAllFalseOneChanged(): void
    {
        $value = 1;
        $dep1 = new CallbackDependency(['callback' => function () use (&$value) {
            return $value;
        }]);
        $dep2 = new CallbackDependency(['callback' => function () {
            return 2;
        }]);

        $chained = new ChainedDependency([
            'dependencies' => [$dep1, $dep2],
            'dependOnAll' => false,
        ]);

        $chained->evaluateDependency($this->cache);
        $value = 99;
        $this->assertFalse($chained->isChanged($this->cache));
    }

    public function testDependOnAllFalseAllChanged(): void
    {
        $v1 = 1;
        $v2 = 2;
        $dep1 = new CallbackDependency(['callback' => function () use (&$v1) {
            return $v1;
        }]);
        $dep2 = new CallbackDependency(['callback' => function () use (&$v2) {
            return $v2;
        }]);

        $chained = new ChainedDependency([
            'dependencies' => [$dep1, $dep2],
            'dependOnAll' => false,
        ]);

        $chained->evaluateDependency($this->cache);
        $v1 = 10;
        $v2 = 20;
        $this->assertTrue($chained->isChanged($this->cache));
    }

    public function testEmptyDependenciesDependOnAllTrue(): void
    {
        $chained = new ChainedDependency([
            'dependencies' => [],
            'dependOnAll' => true,
        ]);

        $chained->evaluateDependency($this->cache);
        $this->assertFalse($chained->isChanged($this->cache));
    }

    public function testEmptyDependenciesDependOnAllFalse(): void
    {
        $chained = new ChainedDependency([
            'dependencies' => [],
            'dependOnAll' => false,
        ]);

        $chained->evaluateDependency($this->cache);
        $this->assertTrue($chained->isChanged($this->cache));
    }

    public function testEvaluateDependencyCallsAllSubDependencies(): void
    {
        $evaluated = [];

        $dep1 = new CallbackDependency(['callback' => function () use (&$evaluated) {
            $evaluated[] = 'dep1';
            return 1;
        }]);
        $dep2 = new CallbackDependency(['callback' => function () use (&$evaluated) {
            $evaluated[] = 'dep2';
            return 2;
        }]);

        $chained = new ChainedDependency([
            'dependencies' => [$dep1, $dep2],
        ]);

        $chained->evaluateDependency($this->cache);
        $this->assertSame(['dep1', 'dep2'], $evaluated);
    }

    public function testDefaultDependOnAllIsTrue(): void
    {
        $chained = new ChainedDependency();
        $this->assertTrue($chained->dependOnAll);
    }

    public function testDefaultDependenciesIsEmptyArray(): void
    {
        $chained = new ChainedDependency();
        $this->assertSame([], $chained->dependencies);
    }

    public function testSingleDependencyChanged(): void
    {
        $value = 'a';
        $dep = new CallbackDependency(['callback' => function () use (&$value) {
            return $value;
        }]);

        $chained = new ChainedDependency([
            'dependencies' => [$dep],
            'dependOnAll' => true,
        ]);

        $chained->evaluateDependency($this->cache);
        $this->assertFalse($chained->isChanged($this->cache));

        $value = 'b';
        $this->assertTrue($chained->isChanged($this->cache));
    }

    public function testSingleDependencyDependOnAllFalse(): void
    {
        $value = 'a';
        $dep = new CallbackDependency(['callback' => function () use (&$value) {
            return $value;
        }]);

        $chained = new ChainedDependency([
            'dependencies' => [$dep],
            'dependOnAll' => false,
        ]);

        $chained->evaluateDependency($this->cache);
        $this->assertFalse($chained->isChanged($this->cache));

        $value = 'b';
        $this->assertTrue($chained->isChanged($this->cache));
    }

    public function testThreeDependenciesDependOnAllTrueMiddleChanged(): void
    {
        $v2 = 'stable';
        $dep1 = new CallbackDependency(['callback' => function () {
            return 'fixed';
        }]);
        $dep2 = new CallbackDependency(['callback' => function () use (&$v2) {
            return $v2;
        }]);
        $dep3 = new CallbackDependency(['callback' => function () {
            return 'fixed';
        }]);

        $chained = new ChainedDependency([
            'dependencies' => [$dep1, $dep2, $dep3],
            'dependOnAll' => true,
        ]);

        $chained->evaluateDependency($this->cache);
        $v2 = 'changed';
        $this->assertTrue($chained->isChanged($this->cache));
    }

    public function testThreeDependenciesDependOnAllFalseMiddleUnchanged(): void
    {
        $v1 = 1;
        $v3 = 3;
        $dep1 = new CallbackDependency(['callback' => function () use (&$v1) {
            return $v1;
        }]);
        $dep2 = new CallbackDependency(['callback' => function () {
            return 'stable';
        }]);
        $dep3 = new CallbackDependency(['callback' => function () use (&$v3) {
            return $v3;
        }]);

        $chained = new ChainedDependency([
            'dependencies' => [$dep1, $dep2, $dep3],
            'dependOnAll' => false,
        ]);

        $chained->evaluateDependency($this->cache);
        $v1 = 10;
        $v3 = 30;
        $this->assertFalse($chained->isChanged($this->cache));
    }
}
