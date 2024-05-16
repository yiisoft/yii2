<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\filters;

use Yii;
use yii\base\Action;
use yii\caching\ArrayCache;
use yii\caching\ExpressionDependency;
use yii\filters\PageCache;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\Cookie;
use yii\web\View;
use yiiunit\framework\caching\CacheTestCase;
use yiiunit\TestCase;

/**
 * @group filters
 */
class PageCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SERVER['SCRIPT_FILENAME'] = '/index.php';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
    }

    protected function tearDown(): void
    {
        CacheTestCase::$time = null;
        CacheTestCase::$microtime = null;
    }

    public function cacheTestCaseProvider()
    {
        return [
            // Basic
            [[
                'name' => 'disabled',
                'properties' => [
                    'enabled' => false,
                ],
                'cacheable' => false,
            ]],
            [[
                'name' => 'simple',
            ]],

            // Cookies
            [[
                'name' => 'allCookies',
                'properties' => [
                    'cacheCookies' => true,
                ],
                'cookies' => [
                    'test-cookie-1' => true,
                    'test-cookie-2' => true,
                ],
            ]],
            [[
                'name' => 'someCookies',
                'properties' => [
                    'cacheCookies' => ['test-cookie-2'],
                ],
                'cookies' => [
                    'test-cookie-1' => false,
                    'test-cookie-2' => true,
                ],
            ]],
            [[
                'name' => 'noCookies',
                'properties' => [
                    'cacheCookies' => false,
                ],
                'cookies' => [
                    'test-cookie-1' => false,
                    'test-cookie-2' => false,
                ],
            ]],

            // Headers
            [[
                'name' => 'allHeaders',
                'properties' => [
                    'cacheHeaders' => true,
                ],
                'headers' => [
                    'test-header-1' => true,
                    'test-header-2' => true,
                ],
            ]],
            [[
                'name' => 'someHeaders',
                'properties' => [
                    'cacheHeaders' => ['test-header-2'],
                ],
                'headers' => [
                    'test-header-1' => false,
                    'test-header-2' => true,
                ],
            ]],
            [[
                'name' => 'noHeaders',
                'properties' => [
                    'cacheHeaders' => false,
                ],
                'headers' => [
                    'test-header-1' => false,
                    'test-header-2' => false,
                ],
            ]],
            [[
                'name' => 'originalNameHeaders',
                'properties' => [
                    'cacheHeaders' => ['Test-Header-1'],
                ],
                'headers' => [
                    'Test-Header-1' => true,
                    'Test-Header-2' => false,
                ],
            ]],

            // All together
            [[
                'name' => 'someCookiesSomeHeaders',
                'properties' => [
                    'cacheCookies' => ['test-cookie-2'],
                    'cacheHeaders' => ['test-header-2'],
                ],
                'cookies' => [
                    'test-cookie-1' => false,
                    'test-cookie-2' => true,
                ],
                'headers' => [
                    'test-header-1' => false,
                    'test-header-2' => true,
                ],
            ]],
        ];
    }

    /**
     * @dataProvider cacheTestCaseProvider
     * @param array $testCase
     */
    public function testCache($testCase)
    {
        $testCase = ArrayHelper::merge([
            'properties' => [],
            'cacheable' => true,
        ], $testCase);
        if (isset(Yii::$app)) {
            $this->destroyApplication();
        }
        // Prepares the test response
        $this->mockWebApplication();
        $controller = new Controller('test', Yii::$app);
        $action = new Action('test', $controller);
        $filter = new PageCache(array_merge([
            'cache' => $cache = new ArrayCache(),
            'view' => new View(),
        ], $testCase['properties']));
        $this->assertTrue($filter->beforeAction($action), $testCase['name']);
        // Cookies
        $cookies = [];
        if (isset($testCase['cookies'])) {
            foreach (array_keys($testCase['cookies']) as $name) {
                $value = Yii::$app->security->generateRandomString();
                Yii::$app->response->cookies->add(new Cookie([
                    'name' => $name,
                    'value' => $value,
                    'expire' => strtotime('now +1 year'),
                ]));
                $cookies[$name] = $value;
            }
        }
        // Headers
        $headers = [];
        if (isset($testCase['headers'])) {
            foreach (array_keys($testCase['headers']) as $name) {
                $value = Yii::$app->security->generateRandomString();
                Yii::$app->response->headers->add($name, $value);
                $headers[$name] = $value;
            }
        }
        // Content
        $static = Yii::$app->security->generateRandomString();
        Yii::$app->params['dynamic'] = $dynamic = Yii::$app->security->generateRandomString();
        $content = $filter->view->render('@yiiunit/data/views/pageCacheLayout.php', ['static' => $static]);
        Yii::$app->response->content = $content;
        ob_start();
        Yii::$app->response->send();
        ob_end_clean();
        // Metadata
        $metadata = [
            'format' => Yii::$app->response->format,
            'version' => Yii::$app->response->version,
            'statusCode' => Yii::$app->response->statusCode,
            'statusText' => Yii::$app->response->statusText,
        ];
        if ($testCase['cacheable']) {
            $this->assertNotEmpty($this->getInaccessibleProperty($filter->cache, '_cache'), $testCase['name']);
        } else {
            $this->assertEmpty($this->getInaccessibleProperty($filter->cache, '_cache'), $testCase['name']);
            return;
        }

        // Verifies the cached response
        $this->destroyApplication();
        $this->mockWebApplication();
        $controller = new Controller('test', Yii::$app);
        $action = new Action('test', $controller);
        $filter = new PageCache(array_merge([
            'cache' => $cache,
            'view' => new View(),
        ]), $testCase['properties']);
        Yii::$app->params['dynamic'] = $dynamic = Yii::$app->security->generateRandomString();
        $this->assertFalse($filter->beforeAction($action), $testCase['name']);
        // Content
        $json = Json::decode(Yii::$app->response->content);
        $this->assertSame($static, $json['static'], $testCase['name']);
        $this->assertSame($dynamic, $json['dynamic'], $testCase['name']);
        // Metadata
        $this->assertSame($metadata['format'], Yii::$app->response->format, $testCase['name']);
        $this->assertSame($metadata['version'], Yii::$app->response->version, $testCase['name']);
        $this->assertSame($metadata['statusCode'], Yii::$app->response->statusCode, $testCase['name']);
        $this->assertSame($metadata['statusText'], Yii::$app->response->statusText, $testCase['name']);
        // Cookies
        if (isset($testCase['cookies'])) {
            foreach ($testCase['cookies'] as $name => $expected) {
                $this->assertSame($expected, Yii::$app->response->cookies->has($name), $testCase['name']);
                if ($expected) {
                    $this->assertSame($cookies[$name], Yii::$app->response->cookies->getValue($name), $testCase['name']);
                }
            }
        }
        // Headers
        if (isset($testCase['headers'])) {
            $headersExpected = Yii::$app->response->headers->toOriginalArray();
            foreach ($testCase['headers'] as $name => $expected) {
                $this->assertSame($expected, Yii::$app->response->headers->has($name), $testCase['name']);
                if ($expected) {
                    $this->assertSame($headers[$name], Yii::$app->response->headers->get($name), $testCase['name']);
                    $this->assertArrayHasKey($name, $headersExpected);
                }
            }
        }
    }

    public function testExpired()
    {
        CacheTestCase::$time = time();
        CacheTestCase::$microtime = microtime(true);

        // Prepares the test response
        $this->mockWebApplication();
        $controller = new Controller('test', Yii::$app);
        $action = new Action('test', $controller);
        $filter = new PageCache([
            'cache' => $cache = new ArrayCache(),
            'view' => new View(),
            'duration' => 1,
        ]);
        $this->assertTrue($filter->beforeAction($action));
        $static = Yii::$app->security->generateRandomString();
        Yii::$app->params['dynamic'] = $dynamic = Yii::$app->security->generateRandomString();
        $content = $filter->view->render('@yiiunit/data/views/pageCacheLayout.php', ['static' => $static]);
        Yii::$app->response->content = $content;
        ob_start();
        Yii::$app->response->send();
        ob_end_clean();

        $this->assertNotEmpty($this->getInaccessibleProperty($filter->cache, '_cache'));

        // mock sleep(2);
        CacheTestCase::$time += 2;
        CacheTestCase::$microtime += 2;

        // Verifies the cached response
        $this->destroyApplication();
        $this->mockWebApplication();
        $controller = new Controller('test', Yii::$app);
        $action = new Action('test', $controller);
        $filter = new PageCache([
            'cache' => $cache,
            'view' => new View(),
        ]);
        Yii::$app->params['dynamic'] = $dynamic = Yii::$app->security->generateRandomString();
        $this->assertTrue($filter->beforeAction($action));
        ob_start();
        Yii::$app->response->send();
        ob_end_clean();
    }

    public function testVaryByRoute()
    {
        $testCases = [
            false,
            true,
        ];

        foreach ($testCases as $enabled) {
            if (isset(Yii::$app)) {
                $this->destroyApplication();
            }
            // Prepares the test response
            $this->mockWebApplication();
            $controller = new Controller('test', Yii::$app);
            $action = new Action('test', $controller);
            Yii::$app->requestedRoute = $action->uniqueId;
            $filter = new PageCache([
                'cache' => $cache = new ArrayCache(),
                'view' => new View(),
                'varyByRoute' => $enabled,
            ]);
            $this->assertTrue($filter->beforeAction($action));
            $static = Yii::$app->security->generateRandomString();
            Yii::$app->params['dynamic'] = $dynamic = Yii::$app->security->generateRandomString();
            $content = $filter->view->render('@yiiunit/data/views/pageCacheLayout.php', ['static' => $static]);
            Yii::$app->response->content = $content;
            ob_start();
            Yii::$app->response->send();
            ob_end_clean();

            $this->assertNotEmpty($this->getInaccessibleProperty($filter->cache, '_cache'));

            // Verifies the cached response
            $this->destroyApplication();
            $this->mockWebApplication();
            $controller = new Controller('test', Yii::$app);
            $action = new Action('test2', $controller);
            Yii::$app->requestedRoute = $action->uniqueId;
            $filter = new PageCache([
                'cache' => $cache,
                'view' => new View(),
                'varyByRoute' => $enabled,
            ]);
            Yii::$app->params['dynamic'] = $dynamic = Yii::$app->security->generateRandomString();
            $this->assertSame($enabled, $filter->beforeAction($action), $enabled);
            ob_start();
            Yii::$app->response->send();
            ob_end_clean();
        }
    }

    public function testVariations()
    {
        $testCases = [
            [true, 'name' => 'value'],
            [false, 'name' => 'value2'],
        ];

        foreach ($testCases as $testCase) {
            if (isset(Yii::$app)) {
                $this->destroyApplication();
            }
            $expected = array_shift($testCase);
            // Prepares the test response
            $this->mockWebApplication();
            $controller = new Controller('test', Yii::$app);
            $action = new Action('test', $controller);
            $originalVariations = $testCases[0];
            array_shift($originalVariations);
            $filter = new PageCache([
                'cache' => $cache = new ArrayCache(),
                'view' => new View(),
                'variations' => $originalVariations,
            ]);
            $this->assertTrue($filter->beforeAction($action));
            $static = Yii::$app->security->generateRandomString();
            Yii::$app->params['dynamic'] = $dynamic = Yii::$app->security->generateRandomString();
            $content = $filter->view->render('@yiiunit/data/views/pageCacheLayout.php', ['static' => $static]);
            Yii::$app->response->content = $content;
            ob_start();
            Yii::$app->response->send();
            ob_end_clean();

            $this->assertNotEmpty($this->getInaccessibleProperty($filter->cache, '_cache'));

            // Verifies the cached response
            $this->destroyApplication();
            $this->mockWebApplication();
            $controller = new Controller('test', Yii::$app);
            $action = new Action('test', $controller);
            $filter = new PageCache([
                'cache' => $cache,
                'view' => new View(),
                'variations' => $testCase,
            ]);
            Yii::$app->params['dynamic'] = $dynamic = Yii::$app->security->generateRandomString();
            $this->assertNotSame($expected, $filter->beforeAction($action), $expected);
            ob_start();
            Yii::$app->response->send();
            ob_end_clean();
        }
    }

    public function testDependency()
    {
        $testCases = [
            false,
            true,
        ];

        foreach ($testCases as $changed) {
            if (isset(Yii::$app)) {
                $this->destroyApplication();
            }
            // Prepares the test response
            $this->mockWebApplication();
            $controller = new Controller('test', Yii::$app);
            $action = new Action('test', $controller);
            $filter = new PageCache([
                'cache' => $cache = new ArrayCache(),
                'view' => new View(),
                'dependency' => [
                    'class' => ExpressionDependency::className(),
                    'expression' => 'Yii::$app->params[\'dependency\']',
                ],
            ]);
            $this->assertTrue($filter->beforeAction($action));
            $static = Yii::$app->security->generateRandomString();
            Yii::$app->params['dynamic'] = $dynamic = Yii::$app->security->generateRandomString();
            Yii::$app->params['dependency'] = $dependency = Yii::$app->security->generateRandomString();
            $content = $filter->view->render('@yiiunit/data/views/pageCacheLayout.php', ['static' => $static]);
            Yii::$app->response->content = $content;
            ob_start();
            Yii::$app->response->send();
            ob_end_clean();

            $this->assertNotEmpty($this->getInaccessibleProperty($filter->cache, '_cache'));

            // Verifies the cached response
            $this->destroyApplication();
            $this->mockWebApplication();
            $controller = new Controller('test', Yii::$app);
            $action = new Action('test', $controller);
            $filter = new PageCache([
                'cache' => $cache,
                'view' => new View(),
            ]);
            Yii::$app->params['dynamic'] = $dynamic = Yii::$app->security->generateRandomString();
            if ($changed) {
                Yii::$app->params['dependency'] = Yii::$app->security->generateRandomString();
            } else {
                Yii::$app->params['dependency'] = $dependency;
            }
            $this->assertSame($changed, $filter->beforeAction($action), $changed);
            ob_start();
            Yii::$app->response->send();
            ob_end_clean();
        }
    }

    public function testCalculateCacheKey()
    {
        $expected = ['yii\filters\PageCache', 'test', 'ru'];
        Yii::$app->requestedRoute = 'test';
        $keys = $this->invokeMethod(new PageCache(['variations' => ['ru']]), 'calculateCacheKey');
        $this->assertEquals($expected, $keys);

        $keys = $this->invokeMethod(new PageCache(['variations' => 'ru']), 'calculateCacheKey');
        $this->assertEquals($expected, $keys);

        $keys = $this->invokeMethod(new PageCache(), 'calculateCacheKey');
        $this->assertEquals(['yii\filters\PageCache', 'test'], $keys);
    }

    public function testClosureVariations()
    {
        $keys = $this->invokeMethod(new PageCache([
            'variations' => function() {
                return [
                    'foobar'
                ];
            }
        ]), 'calculateCacheKey');
        $this->assertEquals(['yii\filters\PageCache', 'test', 'foobar'], $keys);

        // test type cast of string
        $keys = $this->invokeMethod(new PageCache([
            'variations' => function() {
                return 'foobarstring';
            }
        ]), 'calculateCacheKey');
        $this->assertEquals(['yii\filters\PageCache', 'test', 'foobarstring'], $keys);
    }
}
