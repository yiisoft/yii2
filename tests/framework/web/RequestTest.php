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
        $request = new Request;

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

    public function testPrefferedLanguage()
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

    public function testCsrfTokenValidation()
    {
        $this->mockWebApplication();

        $request = new Request();
        $request->enableCsrfCookie = false;

        $token = $request->getCsrfToken();

        $this->assertTrue($request->validateCsrfToken($token));
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
                ]
            ]
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

    public function testGetHostInfo()
    {
        $request = new Request();

        unset($_SERVER['SERVER_NAME'], $_SERVER['HTTP_HOST']);
        $this->assertEquals(null, $request->getHostInfo());

        $request->setHostInfo('http://servername.com:80');
        $this->assertEquals('http://servername.com:80', $request->getHostInfo());
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
        $this->assertEquals(null, $request->getServerName());
    }

    public function testGetServerPort()
    {
        $request = new Request();

        $_SERVER['SERVER_PORT'] = 33;
        $this->assertEquals(33, $request->getServerPort());

        unset($_SERVER['SERVER_PORT']);
        $this->assertEquals(null, $request->getServerPort());
    }

    public function testGetUserIp()
    {
        $request = new Request();
        $_SERVER['REMOTE_ADDR'] = '1.1.1.1';
        $this->assertEquals('1.1.1.1', $request->getUserIP());

        unset($_SERVER['REMOTE_ADDR']);
        $this->assertEquals(null, $request->getUserIP());
    }

    public function testGetUserHost()
    {
        $request = new Request();
        $_SERVER['REMOTE_HOST'] = 'yiiframework.com';
        $this->assertEquals('yiiframework.com', $request->getUserHost());

        unset($_SERVER['REMOTE_HOST']);
        $this->assertEquals(null, $request->getUserHost());
    }

    public function testGetUserIpBehindProxy()
    {
        $this->mockWebApplication();

        $request = new Request();
        $request->headers->set($request->trustedHeaders[Request::HEADER_USER_IP], '1.1.1.1, 2.2.2.2, 3.3.3.3');
        $request->trustedProxies = [
            '127.0.0.1',
        ];

        // Valid Proxy IP
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $this->assertEquals('1.1.1.1', $request->getUserIP());

        // Invalid Proxy IP
        $_SERVER['REMOTE_ADDR'] = '127.0.0.2';
        $this->assertEquals('127.0.0.2', $request->getUserIP());

        $request = new Request();
        $request->headers->set($request->trustedHeaders[Request::HEADER_USER_IP], '1.1.1.1');
        $request->trustedProxies = [
            'ranges' => [
                'localhost',
            ],
        ];

        // Valid Proxy IP
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $this->assertEquals('1.1.1.1', $request->getUserIP());
    }

    public function testGetUserHostBehindProxy()
    {
        $this->mockWebApplication();

        $request = new Request();
        $request->headers->set($request->trustedHeaders[Request::HEADER_USER_HOST], 'yiiframework.com:80');
        $request->trustedProxies = [
            '127.0.0.1',
        ];

        // Valid Proxy IP
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REMOTE_HOST'] = 'yiiproxy.com';
        $this->assertEquals('yiiframework.com', $request->getUserHost());

        // Invalid Proxy IP
        $_SERVER['REMOTE_ADDR'] = '127.0.0.2';
        $this->assertEquals('yiiproxy.com', $request->getUserHost());
    }
}
