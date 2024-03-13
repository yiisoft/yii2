<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\web\Request;
use yiiunit\TestCase;

/**
 * @group web
 * @backupGlobals enabled
 */
class RequestTest extends TestCase
{
    public function testParseAcceptHeader()
    {
        $request = new Request();

        $this->assertEquals([], $request->parseAcceptHeader(' '));

        $this->assertEquals([
            'audio/basic' => ['q' => 1],
            'audio/*' => ['q' => 0.2],
        ], $request->parseAcceptHeader('audio/*; q=0.2, audio/basic'));

        $this->assertEquals([
            'application/json' => ['q' => 1, 'version' => '1.0'],
            'application/xml' => ['q' => 1, 'version' => '2.0', 'x'],
            'text/x-c' => ['q' => 1],
            'text/x-dvi' => ['q' => 0.8],
            'text/plain' => ['q' => 0.5],
        ], $request->parseAcceptHeader('text/plain; q=0.5,
            application/json; version=1.0,
            application/xml; version=2.0; x,
            text/x-dvi; q=0.8, text/x-c'));
    }

    public function testPreferredLanguage()
    {
        $this->mockApplication([
            'language' => 'en',
        ]);

        $request = new Request();
        $request->acceptableLanguages = [];
        $this->assertEquals('en', $request->getPreferredLanguage());

        $request = new Request();
        $request->acceptableLanguages = ['de'];
        $this->assertEquals('en', $request->getPreferredLanguage());

        $request = new Request();
        $request->acceptableLanguages = ['en-us', 'de', 'ru-RU'];
        $this->assertEquals('en', $request->getPreferredLanguage(['en']));

        $request = new Request();
        $request->acceptableLanguages = ['en-us', 'de', 'ru-RU'];
        $this->assertEquals('de', $request->getPreferredLanguage(['ru', 'de']));
        $this->assertEquals('de-DE', $request->getPreferredLanguage(['ru', 'de-DE']));

        $request = new Request();
        $request->acceptableLanguages = ['en-us', 'de', 'ru-RU'];
        $this->assertEquals('de', $request->getPreferredLanguage(['de', 'ru']));

        $request = new Request();
        $request->acceptableLanguages = ['en-us', 'de', 'ru-RU'];
        $this->assertEquals('ru-ru', $request->getPreferredLanguage(['ru-ru']));

        $request = new Request();
        $request->acceptableLanguages = ['en-us', 'de'];
        $this->assertEquals('ru-ru', $request->getPreferredLanguage(['ru-ru', 'pl']));
        $this->assertEquals('ru-RU', $request->getPreferredLanguage(['ru-RU', 'pl']));

        $request = new Request();
        $request->acceptableLanguages = ['en-us', 'de'];
        $this->assertEquals('pl', $request->getPreferredLanguage(['pl', 'ru-ru']));
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/14542
     */
    public function testCsrfTokenContainsASCIIOnly()
    {
        $this->mockWebApplication();

        $request = new Request();
        $request->enableCsrfCookie = false;

        $token = $request->getCsrfToken();
        $this->assertRegExp('~[-_=a-z0-9]~i', $token);
    }

    public function testCsrfTokenValidation()
    {
        $this->mockWebApplication();

        $request = new Request();
        $request->enableCsrfCookie = false;

        $token = $request->getCsrfToken();

        // accept any value if CSRF validation is disabled
        $request->enableCsrfValidation = false;
        $this->assertTrue($request->validateCsrfToken($token));
        $this->assertTrue($request->validateCsrfToken($token . 'a'));
        $this->assertTrue($request->validateCsrfToken([]));
        $this->assertTrue($request->validateCsrfToken([$token]));
        $this->assertTrue($request->validateCsrfToken(0));
        $this->assertTrue($request->validateCsrfToken(null));

        // enable validation
        $request->enableCsrfValidation = true;

        // accept any value on GET request
        foreach (['GET', 'HEAD', 'OPTIONS'] as $method) {
            $_POST[$request->methodParam] = $method;
            $this->assertTrue($request->validateCsrfToken($token));
            $this->assertTrue($request->validateCsrfToken($token . 'a'));
            $this->assertTrue($request->validateCsrfToken([]));
            $this->assertTrue($request->validateCsrfToken([$token]));
            $this->assertTrue($request->validateCsrfToken(0));
            $this->assertTrue($request->validateCsrfToken(null));
        }

        // only accept valid token on POST
        foreach (['POST', 'PUT', 'DELETE'] as $method) {
            $_POST[$request->methodParam] = $method;
            $this->assertTrue($request->validateCsrfToken($token));
            $this->assertFalse($request->validateCsrfToken($token . 'a'));
            $this->assertFalse($request->validateCsrfToken([]));
            $this->assertFalse($request->validateCsrfToken([$token]));
            $this->assertFalse($request->validateCsrfToken(0));
            $this->assertFalse($request->validateCsrfToken(null));
        }
    }

    public function testIssue15317()
    {
        $this->mockWebApplication();
        $_COOKIE[(new Request())->csrfParam] = '';
        $request = new Request();
        $request->enableCsrfCookie = true;
        $request->enableCookieValidation = false;

        $_SERVER['REQUEST_METHOD'] = 'POST';
        \Yii::$app->security->unmaskToken('');
        $this->assertFalse($request->validateCsrfToken(''));

        // When an empty CSRF token is given it is regenerated.
        $this->assertNotEmpty($request->getCsrfToken());

    }
    /**
     * Test CSRF token validation by POST param.
     */
    public function testCsrfTokenPost()
    {
        $this->mockWebApplication();

        $request = new Request();
        $request->enableCsrfCookie = false;

        $token = $request->getCsrfToken();

        // accept no value on GET request
        foreach (['GET', 'HEAD', 'OPTIONS'] as $method) {
            $_POST[$request->methodParam] = $method;
            $this->assertTrue($request->validateCsrfToken());
        }

        // only accept valid token on POST
        foreach (['POST', 'PUT', 'DELETE'] as $method) {
            $_POST[$request->methodParam] = $method;
            $request->setBodyParams([]);
            $this->assertFalse($request->validateCsrfToken());
            $request->setBodyParams([$request->csrfParam => $token]);
            $this->assertTrue($request->validateCsrfToken());
        }
    }

    /**
     * Test CSRF token validation by POST param.
     */
    public function testCsrfTokenHeader()
    {
        $this->mockWebApplication();

        $request = new Request();
        $request->enableCsrfCookie = false;

        $token = $request->getCsrfToken();

        // accept no value on GET request
        foreach (['GET', 'HEAD', 'OPTIONS'] as $method) {
            $_POST[$request->methodParam] = $method;
            $this->assertTrue($request->validateCsrfToken());
        }

        // only accept valid token on POST
        foreach (['POST', 'PUT', 'DELETE'] as $method) {
            $_POST[$request->methodParam] = $method;
            $request->setBodyParams([]);
            $request->headers->remove(Request::CSRF_HEADER);
            $this->assertFalse($request->validateCsrfToken());
            $request->headers->add(Request::CSRF_HEADER, $token);
            $this->assertTrue($request->validateCsrfToken());
        }
    }

    public function testResolve()
    {
        $this->mockWebApplication([
            'components' => [
                'urlManager' => [
                    'enablePrettyUrl' => true,
                    'showScriptName' => false,
                    'cache' => null,
                    'rules' => [
                        'posts' => 'post/list',
                        'post/<id>' => 'post/view',
                    ],
                ],
            ],
        ]);

        $request = new Request();
        $request->pathInfo = 'posts';

        $_GET['page'] = 1;
        $result = $request->resolve();
        $this->assertEquals(['post/list', ['page' => 1]], $result);
        $this->assertEquals($_GET, ['page' => 1]);

        $request->setQueryParams(['page' => 5]);
        $result = $request->resolve();
        $this->assertEquals(['post/list', ['page' => 5]], $result);
        $this->assertEquals($_GET, ['page' => 1]);

        $request->setQueryParams(['custom-page' => 5]);
        $result = $request->resolve();
        $this->assertEquals(['post/list', ['custom-page' => 5]], $result);
        $this->assertEquals($_GET, ['page' => 1]);

        unset($_GET['page']);

        $request = new Request();
        $request->pathInfo = 'post/21';

        $this->assertEquals($_GET, []);
        $result = $request->resolve();
        $this->assertEquals(['post/view', ['id' => 21]], $result);
        $this->assertEquals($_GET, ['id' => 21]);

        $_GET['id'] = 42;
        $result = $request->resolve();
        $this->assertEquals(['post/view', ['id' => 21]], $result);
        $this->assertEquals($_GET, ['id' => 21]);

        $_GET['id'] = 63;
        $request->setQueryParams(['token' => 'secret']);
        $result = $request->resolve();
        $this->assertEquals(['post/view', ['id' => 21, 'token' => 'secret']], $result);
        $this->assertEquals($_GET, ['id' => 63]);
    }

    public function getHostInfoDataProvider()
    {
        return [
            // empty
            [
                [],
                [null, null]
            ],
            // normal
            [
                [
                    'HTTP_HOST' => 'example1.com',
                    'SERVER_NAME' => 'example2.com',
                ],
                [
                    'http://example1.com',
                    'example1.com',
                ]
            ],
            // HTTP header missing
            [
                [
                    'SERVER_NAME' => 'example2.com',
                ],
                [
                    'http://example2.com',
                    'example2.com',
                ]
            ],
            // forwarded from untrusted server
            [
                [
                    'HTTP_X_FORWARDED_HOST' => 'example3.com',
                    'HTTP_HOST' => 'example1.com',
                    'SERVER_NAME' => 'example2.com',
                ],
                [
                    'http://example1.com',
                    'example1.com',
                ]
            ],
            // forwarded from trusted proxy
            [
                [
                    'HTTP_X_FORWARDED_HOST' => 'example3.com',
                    'HTTP_HOST' => 'example1.com',
                    'SERVER_NAME' => 'example2.com',
                    'REMOTE_ADDR' => '192.168.0.1',
                ],
                [
                    'http://example3.com',
                    'example3.com',
                ]
            ],
            // forwarded from trusted proxy
            [
                [
                    'HTTP_X_FORWARDED_HOST' => 'example3.com, example2.com',
                    'HTTP_HOST' => 'example1.com',
                    'SERVER_NAME' => 'example2.com',
                    'REMOTE_ADDR' => '192.168.0.1',
                ],
                [
                    'http://example3.com',
                    'example3.com',
                ]
            ],
            // RFC 7239 forwarded from untrusted server
            [
                [
                    'HTTP_FORWARDED' => 'host=example3.com',
                    'HTTP_HOST' => 'example1.com',
                    'SERVER_NAME' => 'example2.com',
                ],
                [
                    'http://example1.com',
                    'example1.com',
                ]
            ],
            // RFC 7239 forwarded from trusted proxy
            [
                [
                    'HTTP_FORWARDED' => 'host=example3.com',
                    'HTTP_HOST' => 'example1.com',
                    'REMOTE_ADDR' => '192.168.0.1',
                ],
                [
                    'http://example3.com',
                    'example3.com',
                ]
            ],
            // RFC 7239 forwarded from trusted proxy
            [
                [
                    'HTTP_FORWARDED' => 'host=example3.com,host=example2.com',
                    'HTTP_HOST' => 'example1.com',
                    'REMOTE_ADDR' => '192.168.0.1',
                ],
                [
                    'http://example2.com',
                    'example2.com',
                ]
            ],
        ];
    }

    /**
     * @dataProvider getHostInfoDataProvider
     * @param array $server
     * @param array $expected
     */
    public function testGetHostInfo($server, $expected)
    {
        $original = $_SERVER;
        $_SERVER = $server;
        $request = new Request([
            'trustedHosts' => [
                '192.168.0.0/24',
            ],
            'secureHeaders' => [
                'X-Forwarded-For',
                'X-Forwarded-Host',
                'X-Forwarded-Proto',
                'forwarded',
            ],
        ]);


        $this->assertEquals($expected[0], $request->getHostInfo());
        $this->assertEquals($expected[1], $request->getHostName());

        $request = new Request([
            'trustedHosts' => [
                '192.168.0.0/24' => ['X-Forwarded-Host', 'forwarded'],
            ],
            'secureHeaders' => [
                'X-Forwarded-For',
                'X-Forwarded-Host',
                'X-Forwarded-Proto',
                'forwarded',
            ],
        ]);


        $this->assertEquals($expected[0], $request->getHostInfo());
        $this->assertEquals($expected[1], $request->getHostName());
        $_SERVER = $original;
    }


    public function testSetHostInfo()
    {
        $request = new Request();

        unset($_SERVER['SERVER_NAME'], $_SERVER['HTTP_HOST']);
        $this->assertNull($request->getHostInfo());
        $this->assertNull($request->getHostName());

        $request->setHostInfo('http://servername.com:80');
        $this->assertSame('http://servername.com:80', $request->getHostInfo());
        $this->assertSame('servername.com', $request->getHostName());
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     */
    public function testGetScriptFileWithEmptyServer()
    {
        $request = new Request();
        $_SERVER = [];

        $request->getScriptFile();
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     */
    public function testGetScriptUrlWithEmptyServer()
    {
        $request = new Request();
        $_SERVER = [];

        $request->getScriptUrl();
    }

    public function testGetServerName()
    {
        $request = new Request();

        $_SERVER['SERVER_NAME'] = 'servername';
        $this->assertEquals('servername', $request->getServerName());

        unset($_SERVER['SERVER_NAME']);
        $this->assertNull($request->getServerName());
    }

    public function testGetServerPort()
    {
        $request = new Request();

        $_SERVER['SERVER_PORT'] = 33;
        $this->assertEquals(33, $request->getServerPort());

        unset($_SERVER['SERVER_PORT']);
        $this->assertNull($request->getServerPort());
    }

    public function isSecureServerDataProvider()
    {
        return [
            [['HTTPS' => 1], true],
            [['HTTPS' => 'on'], true],
            [['HTTPS' => 0], false],
            [['HTTPS' => 'off'], false],
            [[], false],
            [['HTTP_X_FORWARDED_PROTO' => 'https'], false],
            [['HTTP_X_FORWARDED_PROTO' => 'http'], false],
            [[
                'HTTP_X_FORWARDED_PROTO' => 'https',
                'REMOTE_HOST' => 'test.com',
            ], false],
            [[
                'HTTP_X_FORWARDED_PROTO' => 'https',
                'REMOTE_HOST' => 'othertest.com',
            ], false],
            [[
                'HTTP_X_FORWARDED_PROTO' => 'https',
                'REMOTE_ADDR' => '192.168.0.1',
            ], true],
            [[
                'HTTP_X_FORWARDED_PROTO' => 'https',
                'REMOTE_ADDR' => '192.169.0.1',
            ], false],
            [['HTTP_FRONT_END_HTTPS' => 'on'], false],
            [['HTTP_FRONT_END_HTTPS' => 'off'], false],
            [[
                'HTTP_FRONT_END_HTTPS' => 'on',
                'REMOTE_HOST' => 'test.com',
            ], false],
            [[
                'HTTP_FRONT_END_HTTPS' => 'on',
                'REMOTE_HOST' => 'othertest.com',
            ], false],
            [[
                'HTTP_FRONT_END_HTTPS' => 'on',
                'REMOTE_ADDR' => '192.168.0.1',
            ], true],
            [[
                'HTTP_FRONT_END_HTTPS' => 'on',
                'REMOTE_ADDR' => '192.169.0.1',
            ], false],
            // RFC 7239 forwarded from untrusted proxy
            [[
                'HTTP_FORWARDED' => 'proto=https',
            ], false],
            // RFC 7239 forwarded from two untrusted proxies
            [[
                'HTTP_FORWARDED' => 'proto=https,proto=http',
            ], false],
            // RFC 7239 forwarded from trusted proxy
            [[
                'HTTP_FORWARDED' => 'proto=https',
                'REMOTE_ADDR' => '192.168.0.1',
            ], true],
            // RFC 7239 forwarded from trusted proxy, second proxy not encrypted
            [[
                'HTTP_FORWARDED' => 'proto=https,proto=http',
                'REMOTE_ADDR' => '192.168.0.1',
            ], false],
            // RFC 7239 forwarded from trusted proxy, second proxy encrypted, while client request not encrypted
            [[
                'HTTP_FORWARDED' => 'proto=http,proto=https',
                'REMOTE_ADDR' => '192.168.0.1',
            ], true],
            // RFC 7239 forwarded from untrusted proxy
            [[
                'HTTP_FORWARDED' => 'proto=https',
                'REMOTE_ADDR' => '192.169.0.1',
            ], false],
            // RFC 7239 forwarded from untrusted proxy, second proxy not encrypted
            [[
                'HTTP_FORWARDED' => 'proto=https,proto=http',
                'REMOTE_ADDR' => '192.169.0.1',
            ], false],
            // RFC 7239 forwarded from untrusted proxy, second proxy encrypted, while client request not encrypted
            [[
                'HTTP_FORWARDED' => 'proto=http,proto=https',
                'REMOTE_ADDR' => '192.169.0.1',
            ], false],
        ];
    }

    /**
     * @dataProvider isSecureServerDataProvider
     * @param array $server
     * @param bool $expected
     */
    public function testGetIsSecureConnection($server, $expected)
    {
        $original = $_SERVER;
        $_SERVER = $server;

        $request = new Request([
            'trustedHosts' => [
                '192.168.0.0/24',
            ],
            'secureHeaders' => [
                'Front-End-Https',
                'X-Rewrite-Url',
                'X-Forwarded-For',
                'X-Forwarded-Host',
                'X-Forwarded-Proto',
                'forwarded',
            ],
        ]);
        $this->assertEquals($expected, $request->getIsSecureConnection());

        $request = new Request([
            'trustedHosts' => [
                '192.168.0.0/24' => ['Front-End-Https', 'X-Forwarded-Proto', 'forwarded'],
            ],
            'secureHeaders' => [
                'Front-End-Https',
                'X-Rewrite-Url',
                'X-Forwarded-For',
                'X-Forwarded-Host',
                'X-Forwarded-Proto',
                'forwarded',
            ],
        ]);
        $this->assertEquals($expected, $request->getIsSecureConnection());

        $_SERVER = $original;
    }

    public function isSecureServerWithoutTrustedHostDataProvider()
    {
        return [
            // RFC 7239 forwarded header is not enabled
            [[
                'HTTP_FORWARDED' => 'proto=https',
                'REMOTE_ADDR' => '192.168.0.1',
            ], false],
        ];
    }

    /**
     * @dataProvider isSecureServerWithoutTrustedHostDataProvider
     * @param array $server
     * @param bool $expected
     */
    public function testGetIsSecureConnectionWithoutTrustedHost($server, $expected)
    {
        $original = $_SERVER;
        $_SERVER = $server;

        $request = new Request([
            'trustedHosts' => [
                '192.168.0.0/24' => ['Front-End-Https', 'X-Forwarded-Proto'],
            ],
            'secureHeaders' => [
                'Front-End-Https',
                'X-Rewrite-Url',
                'X-Forwarded-For',
                'X-Forwarded-Host',
                'X-Forwarded-Proto',
                'forwarded',
            ],
        ]);
        $this->assertEquals($expected, $request->getIsSecureConnection());

        $_SERVER = $original;
    }

    public function getUserIPDataProvider()
    {
        return [
            [
                [
                    'HTTP_X_FORWARDED_PROTO' => 'https',
                    'HTTP_X_FORWARDED_FOR' => '123.123.123.123',
                    'REMOTE_ADDR' => '192.168.0.1',
                ],
                '123.123.123.123',
            ],
            [
                [
                    'HTTP_X_FORWARDED_PROTO' => 'https',
                    'HTTP_X_FORWARDED_FOR' => '123.123.123.123',
                    'REMOTE_ADDR' => '192.169.1.1',
                ],
                '192.169.1.1',
            ],
            [
                [
                    'HTTP_X_FORWARDED_PROTO' => 'https',
                    'HTTP_X_FORWARDED_FOR' => '123.123.123.123',
                    'REMOTE_HOST' => 'untrusted.com',
                    'REMOTE_ADDR' => '192.169.1.1',
                ],
                '192.169.1.1',
            ],
            [
                [
                    'HTTP_X_FORWARDED_PROTO' => 'https',
                    'HTTP_X_FORWARDED_FOR' => '192.169.1.1',
                    'REMOTE_HOST' => 'untrusted.com',
                    'REMOTE_ADDR' => '192.169.1.1',
                ],
                '192.169.1.1',
            ],
            // RFC 7239 forwarded from trusted proxy
            [
                [
                    'HTTP_FORWARDED' => 'for=123.123.123.123',
                    'REMOTE_ADDR' => '192.168.0.1',
                ],
                '123.123.123.123',
            ],
            // RFC 7239 forwarded from trusted proxy with optinal port
            [
                [
                    'HTTP_FORWARDED' => 'for=123.123.123.123:2222',
                    'REMOTE_ADDR' => '192.168.0.1',
                ],
                '123.123.123.123',
            ],
            // RFC 7239 forwarded from trusted proxy, through another proxy
            [
                [
                    'HTTP_FORWARDED' => 'for=123.123.123.123,for=122.122.122.122',
                    'REMOTE_ADDR' => '192.168.0.1',
                ],
                '122.122.122.122',
            ],
            // RFC 7239 forwarded from trusted proxy, through another proxy, client IP with optional port
            [
                [
                    'HTTP_FORWARDED' => 'for=123.123.123.123:2222,for=122.122.122.122:2222',
                    'REMOTE_ADDR' => '192.168.0.1',
                ],
                '122.122.122.122',
            ],
            // RFC 7239 forwarded from untrusted proxy
            [
                [
                    'HTTP_FORWARDED' => 'for=123.123.123.123',
                    'REMOTE_ADDR' => '192.169.1.1',
                ],
                '192.169.1.1',
            ],
            // RFC 7239 forwarded from trusted proxy with optional port
            [
                [
                    'HTTP_FORWARDED' => 'for=123.123.123.123:2222',
                    'REMOTE_ADDR' => '192.169.1.1',
                ],
                '192.169.1.1',
            ],
            // RFC 7239 forwarded from trusted proxy with client IPv6
            [
                [
                    'HTTP_FORWARDED' => 'for="2001:0db8:85a3:0000:0000:8a2e:0370:7334"',
                    'REMOTE_ADDR' => '192.168.0.1',
                ],
                '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            ],
            // RFC 7239 forwarded from trusted proxy with client IPv6 and optional port
            [
                [
                    'HTTP_FORWARDED' => 'for="[2001:0db8:85a3:0000:0000:8a2e:0370:7334]:2222"',
                    'REMOTE_ADDR' => '192.168.0.1',
                ],
                '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            ],
            // RFC 7239 forwarded from trusted proxy, through another proxy with client IPv6
            [
                [
                    'HTTP_FORWARDED' => 'for=122.122.122.122,for="2001:0db8:85a3:0000:0000:8a2e:0370:7334"',
                    'REMOTE_ADDR' => '192.168.0.1',
                ],
                '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            ],
            // RFC 7239 forwarded from trusted proxy, through another proxy with client IPv6 and optional port
            [
                [
                    'HTTP_FORWARDED' => 'for=122.122.122.122:2222,for="[2001:0db8:85a3:0000:0000:8a2e:0370:7334]:2222"',
                    'REMOTE_ADDR' => '192.168.0.1',
                ],
                '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            ],
            // RFC 7239 forwarded from untrusted proxy with client IPv6
            [
                [
                    'HTTP_FORWARDED' => 'for"=2001:0db8:85a3:0000:0000:8a2e:0370:7334"',
                    'REMOTE_ADDR' => '192.169.1.1',
                ],
                '192.169.1.1',
            ],
            // RFC 7239 forwarded from untrusted proxy, through another proxy with client IPv6 and optional port
            [
                [
                    'HTTP_FORWARDED' => 'for="[2001:0db8:85a3:0000:0000:8a2e:0370:7334]:2222"',
                    'REMOTE_ADDR' => '192.169.1.1',
                ],
                '192.169.1.1',
            ],
        ];
    }

    /**
     * @dataProvider getUserIPDataProvider
     * @param array $server
     * @param string $expected
     */
    public function testGetUserIP($server, $expected)
    {
        $original = $_SERVER;
        $_SERVER = $server;
        $request = new Request([
            'trustedHosts' => [
                '192.168.0.0/24',
            ],
            'secureHeaders' => [
                'Front-End-Https',
                'X-Rewrite-Url',
                'X-Forwarded-For',
                'X-Forwarded-Host',
                'X-Forwarded-Proto',
                'forwarded',
            ],
        ]);
        $this->assertEquals($expected, $request->getUserIP());

        $request = new Request([
            'trustedHosts' => [
                '192.168.0.0/24' => ['X-Forwarded-For', 'forwarded'],
            ],
            'secureHeaders' => [
                'Front-End-Https',
                'X-Rewrite-Url',
                'X-Forwarded-For',
                'X-Forwarded-Host',
                'X-Forwarded-Proto',
                'forwarded',
            ],
        ]);
        $this->assertEquals($expected, $request->getUserIP());

        $_SERVER = $original;
    }

    public function getUserIPWithoutTruestHostDataProvider()
    {
        return [
            // RFC 7239 forwarded is not enabled
            [
                [
                    'HTTP_FORWARDED' => 'for=123.123.123.123',
                    'REMOTE_ADDR' => '192.168.0.1',
                ],
                '192.168.0.1',
            ],
        ];
    }

    /**
    * @dataProvider getUserIPWithoutTruestHostDataProvider
    * @param array $server
    * @param string $expected
    */
   public function testGetUserIPWithoutTrustedHost($server, $expected)
   {
       $original = $_SERVER;
       $_SERVER = $server;

       $request = new Request([
           'trustedHosts' => [
               '192.168.0.0/24' => ['X-Forwarded-For'],
           ],
           'secureHeaders' => [
               'Front-End-Https',
               'X-Rewrite-Url',
               'X-Forwarded-For',
               'X-Forwarded-Host',
               'X-Forwarded-Proto',
               'forwarded',
           ],
       ]);
       $this->assertEquals($expected, $request->getUserIP());

       $_SERVER = $original;
   }

    public function getMethodDataProvider()
    {
        return [
            [
                [
                    'REQUEST_METHOD' => 'DEFAULT',
                    'HTTP_X-HTTP-METHOD-OVERRIDE' => 'OVERRIDE',
                ],
                'OVERRIDE',
            ],
            [
                [
                    'REQUEST_METHOD' => 'DEFAULT',
                ],
                'DEFAULT',
            ],
        ];
    }

    /**
     * @dataProvider getMethodDataProvider
     * @param array $server
     * @param string $expected
     */
    public function testGetMethod($server, $expected)
    {
        $original = $_SERVER;
        $_SERVER = $server;
        $request = new Request();

        $this->assertEquals($expected, $request->getMethod());
        $_SERVER = $original;
    }

    public function getIsAjaxDataProvider()
    {
        return [
            [
                [
                ],
                false,
            ],
            [
                [
                    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
                ],
                true,
            ],
        ];
    }

    /**
     * @dataProvider getIsAjaxDataProvider
     * @param array $server
     * @param bool $expected
     */
    public function testGetIsAjax($server, $expected)
    {
        $original = $_SERVER;
        $_SERVER = $server;
        $request = new Request();

        $this->assertEquals($expected, $request->getIsAjax());
        $_SERVER = $original;
    }

    public function getIsPjaxDataProvider()
    {
        return [
            [
                [
                ],
                false,
            ],
            [
                [
                    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
                    'HTTP_X_PJAX' => 'any value',
                ],
                true,
            ],
        ];
    }

    /**
     * @dataProvider getIsPjaxDataProvider
     * @param array $server
     * @param bool $expected
     */
    public function testGetIsPjax($server, $expected)
    {
        $original = $_SERVER;
        $_SERVER = $server;
        $request = new Request();

        $this->assertEquals($expected, $request->getIsPjax());
        $_SERVER = $original;
    }

    public function testGetOrigin()
    {
        $_SERVER['HTTP_ORIGIN'] = 'https://www.w3.org';
        $request = new Request();
        $this->assertEquals('https://www.w3.org', $request->getOrigin());

        unset($_SERVER['HTTP_ORIGIN']);
        $request = new Request();
        $this->assertNull($request->getOrigin());
    }

    public function httpAuthorizationHeadersProvider()
    {
        return [
            ['not a base64 at all', [base64_decode('not a base64 at all'), null]],
            [base64_encode('user:'), ['user', null]],
            [base64_encode('user'), ['user', null]],
            [base64_encode('user:pw'), ['user', 'pw']],
            [base64_encode('user:pw'), ['user', 'pw']],
            [base64_encode('user:a:b'), ['user', 'a:b']],
            [base64_encode(':a:b'), [null, 'a:b']],
            [base64_encode(':'), [null, null]],
        ];
    }

    /**
     * @dataProvider httpAuthorizationHeadersProvider
     * @param string $secret
     * @param array $expected
     */
    public function testHttpAuthCredentialsFromHttpAuthorizationHeader($secret, $expected)
    {
        $original = $_SERVER;

        $request = new Request();
        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic ' . $secret;
        $this->assertSame($request->getAuthCredentials(), $expected);
        $this->assertSame($request->getAuthUser(), $expected[0]);
        $this->assertSame($request->getAuthPassword(), $expected[1]);
        $_SERVER = $original;

        $request = new Request();
        $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] = 'Basic ' . $secret;
        $this->assertSame($request->getAuthCredentials(), $expected);
        $this->assertSame($request->getAuthUser(), $expected[0]);
        $this->assertSame($request->getAuthPassword(), $expected[1]);
        $_SERVER = $original;
    }

    public function testHttpAuthCredentialsFromServerSuperglobal()
    {
        $original = $_SERVER;
        list($user, $pw) = ['foo', 'bar'];
        $_SERVER['PHP_AUTH_USER'] = $user;
        $_SERVER['PHP_AUTH_PW'] = $pw;

        $request = new Request();
        $request->getHeaders()->set('Authorization', 'Basic ' . base64_encode('less-priority:than-PHP_AUTH_*'));

        $this->assertSame($request->getAuthCredentials(), [$user, $pw]);
        $this->assertSame($request->getAuthUser(), $user);
        $this->assertSame($request->getAuthPassword(), $pw);

        $_SERVER = $original;
    }

    public function testGetBodyParam()
    {
        $request = new Request();

        $request->setBodyParams([
            'someParam' => 'some value',
            'param.dot' => 'value.dot',
        ]);
        $this->assertSame('some value', $request->getBodyParam('someParam'));
        $this->assertSame('value.dot', $request->getBodyParam('param.dot'));
        $this->assertSame(null, $request->getBodyParam('unexisting'));
        $this->assertSame('default', $request->getBodyParam('unexisting', 'default'));

        // @see https://github.com/yiisoft/yii2/issues/14135
        $bodyParams = new \stdClass();
        $bodyParams->someParam = 'some value';
        $bodyParams->{'param.dot'} = 'value.dot';
        $request->setBodyParams($bodyParams);
        $this->assertSame('some value', $request->getBodyParam('someParam'));
        $this->assertSame('value.dot', $request->getBodyParam('param.dot'));
        $this->assertSame(null, $request->getBodyParam('unexisting'));
        $this->assertSame('default', $request->getBodyParam('unexisting', 'default'));
    }

    public function getBodyParamsDataProvider()
    {
        return [
            'json' => ['application/json', '{"foo":"bar","baz":1}', ['foo' => 'bar', 'baz' => 1]],
            'jsonp' => ['application/javascript', 'parseResponse({"foo":"bar","baz":1});', ['foo' => 'bar', 'baz' => 1]],
            'get' => ['application/x-www-form-urlencoded', 'foo=bar&baz=1', ['foo' => 'bar', 'baz' => '1']],
        ];
    }

    /**
     * @dataProvider getBodyParamsDataProvider
     */
    public function testGetBodyParams($contentType, $rawBody, array $expected)
    {
        $_SERVER['CONTENT_TYPE'] = $contentType;
        $request = new Request();
        $request->parsers = [
            'application/json' => 'yii\web\JsonParser',
            'application/javascript' => 'yii\web\JsonParser',
        ];
        $request->setRawBody($rawBody);
        $this->assertSame($expected, $request->getBodyParams());
    }

    public function trustedHostAndInjectedXForwardedForDataProvider()
    {
        return [
            'emptyIPs' => ['1.1.1.1', '', null, ['10.10.10.10'], '1.1.1.1'],
            'invalidIp' => ['1.1.1.1', '127.0.0.1, 8.8.8.8, 2.2.2.2, apple', null, ['10.10.10.10'], '1.1.1.1'],
            'invalidIp2' => ['1.1.1.1', '127.0.0.1, 8.8.8.8, 2.2.2.2, 300.300.300.300', null, ['10.10.10.10'], '1.1.1.1'],
            'invalidIp3' => ['1.1.1.1', '127.0.0.1, 8.8.8.8, 2.2.2.2, 10.0.0.0/26', null, ['10.0.0.0/24'], '1.1.1.1'],
            'invalidLatestIp' => ['1.1.1.1', '127.0.0.1, 8.8.8.8, 2.2.2.2, apple, 2.2.2.2', null, ['1.1.1.1', '2.2.2.2'], '2.2.2.2'],
            'notTrusted' => ['1.1.1.1', '127.0.0.1, 8.8.8.8, 2.2.2.2', null, ['10.10.10.10'], '1.1.1.1'],
            'trustedLevel1' => ['1.1.1.1', '127.0.0.1, 8.8.8.8, 2.2.2.2', null, ['1.1.1.1'], '2.2.2.2'],
            'trustedLevel2' => ['1.1.1.1', '127.0.0.1, 8.8.8.8, 2.2.2.2', null, ['1.1.1.1', '2.2.2.2'], '8.8.8.8'],
            'trustedLevel3' => ['1.1.1.1', '127.0.0.1, 8.8.8.8, 2.2.2.2', null, ['1.1.1.1', '2.2.2.2', '8.8.8.8'], '127.0.0.1'],
            'trustedLevel4' => ['1.1.1.1', '127.0.0.1, 8.8.8.8, 2.2.2.2', null, ['1.1.1.1', '2.2.2.2', '8.8.8.8', '127.0.0.1'], '127.0.0.1'],
            'trustedLevel4EmptyElements' => ['1.1.1.1', '127.0.0.1, 8.8.8.8,,,, ,   , 2.2.2.2', null, ['1.1.1.1', '2.2.2.2', '8.8.8.8', '127.0.0.1'], '127.0.0.1'],
            'trustedWithCidr' => ['10.0.0.2', '127.0.0.1, 8.8.8.8, 10.0.0.240, 10.0.0.32, 10.0.0.99', null, ['10.0.0.0/24'], '8.8.8.8'],
            'trustedAll' => ['10.0.0.2', '127.0.0.1, 8.8.8.8, 10.0.0.240, 10.0.0.32, 10.0.0.99', null, ['0.0.0.0/0'], '127.0.0.1'],
            'emptyIpHeaders' => ['1.1.1.1', '127.0.0.1, 8.8.8.8, 2.2.2.2', [], ['1.1.1.1'], '1.1.1.1'],
        ];
    }

    /**
     * @dataProvider trustedHostAndInjectedXForwardedForDataProvider
     */
    public function testTrustedHostAndInjectedXForwardedFor($remoteAddress, $xForwardedFor, $ipHeaders, $trustedHosts, $expectedUserIp)
    {
        $_SERVER['REMOTE_ADDR'] = $remoteAddress;
        $_SERVER['HTTP_X_FORWARDED_FOR'] = $xForwardedFor;
        $params = [
            'trustedHosts' => $trustedHosts,
        ];
        if($ipHeaders !== null) {
            $params['ipHeaders'] = $ipHeaders;
        }
        $request = new Request($params);
        $this->assertSame($expectedUserIp, $request->getUserIP());
    }

    public function trustedHostAndXForwardedPortDataProvider()
    {
        return [
            'defaultPlain' => ['1.1.1.1', 80, null, null, 80],
            'defaultSSL' => ['1.1.1.1', 443, null, null, 443],
            'untrustedForwardedSSL' => ['1.1.1.1', 80, 443, ['10.0.0.0/8'], 80],
            'untrustedForwardedPlain' => ['1.1.1.1', 443, 80, ['10.0.0.0/8'], 443],
            'trustedForwardedSSL' => ['10.10.10.10', 80, 443, ['10.0.0.0/8'], 443],
            'trustedForwardedPlain' => ['10.10.10.10', 443, 80, ['10.0.0.0/8'], 80],
        ];
    }

    /**
     * @dataProvider trustedHostAndXForwardedPortDataProvider
     */
    public function testTrustedHostAndXForwardedPort($remoteAddress, $requestPort, $xForwardedPort, $trustedHosts, $expectedPort)
    {
        $_SERVER['REMOTE_ADDR'] = $remoteAddress;
        $_SERVER['SERVER_PORT'] = $requestPort;
        $_SERVER['HTTP_X_FORWARDED_PORT'] = $xForwardedPort;
        $params = [
            'trustedHosts' => $trustedHosts,
        ];
        $request = new Request($params);
        $this->assertSame($expectedPort, $request->getServerPort());
    }

    /**
     * @testWith    ["POST", "GET", "POST"]
     *              ["POST", "OPTIONS", "POST"]
     *              ["POST", "HEAD", "POST"]
     *              ["POST", "DELETE", "DELETE"]
     *              ["POST", "CUSTOM", "CUSTOM"]
     */
    public function testRequestMethodCanNotBeDowngraded($requestMethod, $requestOverrideMethod, $expectedMethod)
    {
        $request = new Request();

        $_SERVER['REQUEST_METHOD'] = $requestMethod;
        $_POST[$request->methodParam] = $requestOverrideMethod;

        $this->assertSame($expectedMethod, $request->getMethod());
    }

    public function alreadyResolvedIpDataProvider() {
        return [
            'resolvedXForwardedFor' => [
                '50.0.0.1',
                '1.1.1.1, 8.8.8.8, 9.9.9.9',
                'http',
                ['0.0.0.0/0'],
                // checks:
                '50.0.0.1',
                '50.0.0.1',
                false,
            ],
            'resolvedXForwardedForWithHttps' => [
                '50.0.0.1',
                '1.1.1.1, 8.8.8.8, 9.9.9.9',
                'https',
                ['0.0.0.0/0'],
                // checks:
                '50.0.0.1',
                '50.0.0.1',
                true,
            ],
        ];
    }

    /**
     * @dataProvider alreadyResolvedIpDataProvider
     */
    public function testAlreadyResolvedIp($remoteAddress, $xForwardedFor, $xForwardedProto, $trustedHosts, $expectedRemoteAddress, $expectedUserIp, $expectedIsSecureConnection) {
        $_SERVER['REMOTE_ADDR'] = $remoteAddress;
        $_SERVER['HTTP_X_FORWARDED_FOR'] = $xForwardedFor;
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = $xForwardedProto;
        $request = new Request([
            'trustedHosts' => $trustedHosts,
            'ipHeaders' => []
        ]);
        $this->assertSame($expectedRemoteAddress, $request->remoteIP, 'Remote IP fail!');
        $this->assertSame($expectedUserIp, $request->userIP, 'User IP fail!');
        $this->assertSame($expectedIsSecureConnection, $request->isSecureConnection, 'Secure connection fail!');
    }

    public function parseForwardedHeaderDataProvider()
    {
        return [
            [
                '192.168.10.10',
                'for=10.0.0.2;host=yiiframework.com;proto=https',
                'https://yiiframework.com',
                '10.0.0.2'
            ],
            [
                '192.168.10.10',
                'for=10.0.0.2;proto=https',
                'https://example.com',
                '10.0.0.2'
            ],
            [
                '192.168.10.10',
                'host=yiiframework.com;proto=https',
                'https://yiiframework.com',
                '192.168.10.10'
            ],
            [
                '192.168.10.10',
                'host=yiiframework.com;for=10.0.0.2',
                'http://yiiframework.com',
                '10.0.0.2'
            ],
            [
                '192.168.20.10',
                'host=yiiframework.com;for=10.0.0.2;proto=https',
                'https://yiiframework.com',
                '10.0.0.2'
            ],
            [
                '192.168.10.10',
                'for=10.0.0.1;host=yiiframework.com;proto=https, for=192.168.20.20;host=awesome.proxy.com;proto=http',
                'https://yiiframework.com',
                '10.0.0.1'
            ],
            [
                '192.168.10.10',
                'for=8.8.8.8;host=spoofed.host;proto=https, for=10.0.0.1;host=yiiframework.com;proto=https, for=192.168.20.20;host=trusted.proxy;proto=http',
                'https://yiiframework.com',
                '10.0.0.1'
            ]
        ];
    }

    /**
     * @dataProvider parseForwardedHeaderDataProvider
     */
    public function testParseForwardedHeaderParts($remoteAddress, $forwardedHeader, $expectedHostInfo, $expectedUserIp)
    {
        $_SERVER['REMOTE_ADDR'] = $remoteAddress;
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['HTTP_FORWARDED'] = $forwardedHeader;

        $request = new Request([
            'trustedHosts' => [
                '192.168.10.0/24',
                '192.168.20.0/24'
            ],
            'secureHeaders' => [
                'X-Forwarded-For',
                'X-Forwarded-Host',
                'X-Forwarded-Proto',
                'forwarded',
            ],
        ]);

        $this->assertSame($expectedUserIp, $request->userIP, 'User IP fail!');
        $this->assertSame($expectedHostInfo, $request->hostInfo, 'Host info fail!');
    }

    public function testForwardedNotTrusted()
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.10.10';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['HTTP_FORWARDED'] = 'for=8.8.8.8;host=spoofed.host;proto=https';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '10.0.0.1';
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'yiiframework.com';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'http';

        $request = new Request([
            'trustedHosts' => [
                '192.168.10.0/24',
                '192.168.20.0/24'
            ],
            'secureHeaders' => [
                'X-Forwarded-For',
                'X-Forwarded-Host',
                'X-Forwarded-Proto',
            ],
        ]);

        $this->assertSame('10.0.0.1', $request->userIP, 'User IP fail!');
        $this->assertSame('http://yiiframework.com', $request->hostInfo, 'Host info fail!');
    }
}
