<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\web\Cookie;
use yii\web\CookieCollection;
use yiiunit\TestCase;

/**
 * Unit tests for {@see CookieCollection}.
 *
 * {@see \yiiunit\framework\web\providers\CookieCollectionProvider} for test case data providers.
 *
 * @group web
 */
class CookieCollectionTest extends TestCase
{
    /**
     * @dataProvider \yiiunit\framework\web\providers\CookieCollectionProvider::has
     */
    public function testHas(string $name, array $cookies, bool $expectedResult): void
    {
        $cookieCollection = new CookieCollection($cookies);
        $result = $cookieCollection->has($name);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider \yiiunit\framework\web\providers\CookieCollectionProvider::hasWithExpire
     */
    public function testHasWithExpire(callable $expire, int $offset, bool $expectedResult): void
    {
        $cookieCollection = new CookieCollection([
            'test' => new Cookie([
                'value' => 'test',
                'expire' => $expire(time() + $offset),
            ]),
        ]);

        $this->assertSame($expectedResult, $cookieCollection->has('test'));
    }
}
