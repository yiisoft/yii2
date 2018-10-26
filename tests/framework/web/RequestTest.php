<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\web\Request;
use yiiunit\TestCase;

/**
 * @group web
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
        $request = new Request([
            'trustedHosts' => [
                '192.168.0.0/24',
            ],
        ]);
        $_SERVER = $server;

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
        $request = new Request();

        $request->getHeaders()->set('HTTP_AUTHORIZATION', 'Basic ' . $secret);
        $this->assertSame($request->getAuthCredentials(), $expected);
        $this->assertSame($request->getAuthUser(), $expected[0]);
        $this->assertSame($request->getAuthPassword(), $expected[1]);
        $request->getHeaders()->offsetUnset('HTTP_AUTHORIZATION');

        $request->getHeaders()->set('REDIRECT_HTTP_AUTHORIZATION', 'Basic ' . $secret);
        $this->assertSame($request->getAuthCredentials(), $expected);
        $this->assertSame($request->getAuthUser(), $expected[0]);
        $this->assertSame($request->getAuthPassword(), $expected[1]);
    }

    public function testHttpAuthCredentialsFromServerSuperglobal()
    {
        $original = $_SERVER;
        list($user, $pw) = ['foo', 'bar'];
        $_SERVER['PHP_AUTH_USER'] = $user;
        $_SERVER['PHP_AUTH_PW'] = $pw;

        $request = new Request();
        $request->getHeaders()->set('HTTP_AUTHORIZATION', 'Basic ' . base64_encode('less-priority:than-PHP_AUTH_*'));

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
}
