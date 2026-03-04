<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\web;

use yii\web\Cookie;
use yiiunit\TestCase;

/**
 * @group web
 */
class CookieTest extends TestCase
{
    public function testDefaultPropertyValues(): void
    {
        $cookie = new Cookie();

        $this->assertNull($cookie->name);
        $this->assertSame('', $cookie->value);
        $this->assertSame('', $cookie->domain);
        $this->assertSame(0, $cookie->expire);
        $this->assertSame('/', $cookie->path);
        $this->assertFalse($cookie->secure);
        $this->assertTrue($cookie->httpOnly);
        $this->assertSame(Cookie::SAME_SITE_LAX, $cookie->sameSite);
    }

    public function testConstructorWithConfig(): void
    {
        $cookie = new Cookie([
            'name' => 'session',
            'value' => 'abc123',
            'domain' => '.example.com',
            'expire' => 1234567890,
            'path' => '/admin',
            'secure' => true,
            'httpOnly' => false,
            'sameSite' => Cookie::SAME_SITE_STRICT,
        ]);

        $this->assertSame('session', $cookie->name);
        $this->assertSame('abc123', $cookie->value);
        $this->assertSame('.example.com', $cookie->domain);
        $this->assertSame(1234567890, $cookie->expire);
        $this->assertSame('/admin', $cookie->path);
        $this->assertTrue($cookie->secure);
        $this->assertFalse($cookie->httpOnly);
        $this->assertSame(Cookie::SAME_SITE_STRICT, $cookie->sameSite);
    }

    /**
     * @dataProvider provideToStringData
     */
    public function testToString($value, string $expected): void
    {
        $cookie = new Cookie(['name' => 'test', 'value' => $value]);

        $this->assertSame($expected, (string) $cookie);
    }

    public static function provideToStringData(): array
    {
        return [
            'string value' => ['hello', 'hello'],
            'empty string' => ['', ''],
            'numeric string' => ['42', '42'],
            'null value' => [null, ''],
            'integer value' => [0, '0'],
        ];
    }
}
