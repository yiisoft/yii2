<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\rest;

use Yii;
use yii\helpers\VarDumper;
use yii\rest\UrlRule;
use yii\web\Request;
use yii\web\UrlManager;
use yii\web\UrlRule as WebUrlRule;
use yiiunit\TestCase;

/**
 * @group rest
 */
class UrlRuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testInitControllerNamePluralization()
    {
        $suites = $this->getTestsForControllerNamePluralization();
        foreach ($suites as $i => $suite) {
            list($name, $tests) = $suite;
            foreach ($tests as $j => $test) {
                list($config, $expected) = $test;
                $rule = new UrlRule($config);
                $this->assertEquals($expected, $rule->controller, "Test#$i-$j: $name");
            }
        }
    }

    public function testParseRequest()
    {
        $manager = new UrlManager(['cache' => null]);
        $request = new Request(['hostInfo' => 'http://en.example.com', 'methodParam' => '_METHOD']);
        $suites = $this->getTestsForParseRequest();
        foreach ($suites as $i => $suite) {
            list($name, $config, $tests) = $suite;
            $rule = new UrlRule($config);
            foreach ($tests as $j => $test) {
                $request->pathInfo = $test[0];
                $route = $test[1];
                $params = isset($test[2]) ? $test[2] : [];
                $_POST['_METHOD'] = isset($test[3]) ? $test[3] : 'GET';
                $result = $rule->parseRequest($manager, $request);
                if ($route === false) {
                    $this->assertFalse($result, "Test#$i-$j: $name");
                } else {
                    $this->assertEquals([$route, $params], $result, "Test#$i-$j: $name");
                }
            }
        }
    }

    protected function getTestsForParseRequest()
    {
        // structure of each test
        //   message for the test
        //   config for the URL rule
        //   list of inputs and outputs
        //     pathInfo
        //     expected route, or false if the rule doesn't apply
        //     expected params
        //     method
        return [
            [
                'pluralized name',
                ['controller' => 'post'],
                [
                    ['posts', 'post/index'],
                ],
            ],
            [
                'prefixed route',
                ['controller' => 'post', 'prefix' => 'admin'],
                [
                    ['admin/posts', 'post/index'],
                    ['different/posts', false],
                    ['posts', false],
                ],
            ],
            [
                'suffixed route',
                ['controller' => 'post', 'suffix' => '.json'],
                [
                    ['posts.json', 'post/index'],
                    ['posts.json', 'post/create', [], 'POST'],
                    ['posts/123.json', 'post/view', ['id' => 123], 'GET'],
                ],
            ],
            [
                'default routes according request method',
                ['controller' => 'post'],
                [
                    ['posts', 'post/index', [], 'GET'],
                    ['posts', 'post/index', [], 'HEAD'],
                    ['posts', 'post/create', [], 'POST'],
                    ['posts', 'post/options', [], 'PATCH'],
                    ['posts', 'post/options', [], 'PUT'],
                    ['posts', 'post/options', [], 'DELETE'],

                    ['posts/123', 'post/view', ['id' => 123], 'GET'],
                    ['posts/123', 'post/view', ['id' => 123], 'HEAD'],
                    ['posts/123', 'post/options', ['id' => 123], 'POST'],
                    ['posts/123', 'post/update', ['id' => 123], 'PATCH'],
                    ['posts/123', 'post/update', ['id' => 123], 'PUT'],
                    ['posts/123', 'post/delete', ['id' => 123], 'DELETE'],

                    ['posts/new', false],
                ],
            ],
            [
                'only selected routes',
                ['controller' => 'post', 'only' => ['index']],
                [
                    ['posts', 'post/index'],
                    ['posts/123', false],
                    ['posts', false, [], 'POST'],
                ],
            ],
            [
                'except routes',
                ['controller' => 'post', 'except' => ['delete', 'create']],
                [
                    ['posts', 'post/index'],
                    ['posts/123', 'post/view', ['id' => 123]],
                    ['posts/123', 'post/options', ['id' => 123], 'DELETE'],
                    ['posts', 'post/options', [], 'POST'],
                ],
            ],
            [
                'extra patterns',
                ['controller' => 'post', 'extraPatterns' => ['POST new' => 'create']],
                [
                    ['posts/new', 'post/create', [], 'POST'],
                    ['posts', 'post/create', [], 'POST'],
                ],
            ],
            [
                'extra patterns overwrite patterns',
                ['controller' => 'post', 'extraPatterns' => ['POST' => 'new']],
                [
                    ['posts', 'post/new', [], 'POST'],
                ],
            ],
            [
                'extra patterns rule is higher priority than patterns',
                ['controller' => 'post', 'extraPatterns' => ['GET 1337' => 'leet']],
                [
                    ['posts/1337', 'post/leet'],
                    ['posts/1338', 'post/view', ['id' => 1338]],
                ],
            ],
            [
                'prefix with token',
                ['controller' => 'post', 'prefix' => 'admin/<name>'],
                [
                    ['admin/aaa/posts', 'post/index', ['name' => 'aaa']],
                ],
            ],
        ];
    }

    protected function getTestsForControllerNamePluralization()
    {
        return [
            [
                'pluralized automatically',
                [
                    [
                        ['controller' => 'user'],
                        ['users' => 'user'],
                    ],
                    [
                        ['controller' => 'admin/user'],
                        ['admin/users' => 'admin/user'],
                    ],
                    [
                        ['controller' => ['admin/user', 'post']],
                        ['admin/users' => 'admin/user', 'posts' => 'post'],
                    ],
                ],
            ],
            [
                'explicitly specified',
                [
                    [
                        ['controller' => ['customer' => 'user']],
                        ['customer' => 'user'],
                    ],
                ],
            ],
            [
                'do not pluralize',
                [
                    [
                        [
                            'pluralize' => false,
                            'controller' => ['admin/user', 'post'],
                        ],
                        ['admin/user' => 'admin/user', 'post' => 'post'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Provides test cases for createUrl() method.
     *
     * - first param are properties of the UrlRule
     * - second param is an array of test cases, containing two element arrays:
     *   - first element is the route to create
     *   - second element is the expected URL
     */
    public function createUrlDataProvider()
    {
        return [
            // with pluralize
            [
                [ // Rule properties
                    'controller' => 'v1/channel',
                    'pluralize' => true,
                ],
                [ // test cases: route, expected
                    [['v1/channel/index'], 'v1/channels'],
                    [['v1/channel/index', 'offset' => 1], 'v1/channels?offset=1'],
                    [['v1/channel/view', 'id' => 42], 'v1/channels/42'],
                    [['v1/channel/options'], 'v1/channels'],
                    [['v1/channel/options', 'id' => 42], 'v1/channels/42'],
                    [['v1/channel/delete'], false],
                ],
            ],
            [
                [ // Rule properties
                    'controller' => ['v1/channel'],
                    'pluralize' => true,
                ],
                [ // test cases: route, expected
                    [['v1/channel/index'], 'v1/channels'],
                    [['v1/channel/index', 'offset' => 1], 'v1/channels?offset=1'],
                    [['v1/channel/view', 'id' => 42], 'v1/channels/42'],
                    [['v1/channel/options'], 'v1/channels'],
                    [['v1/channel/options', 'id' => 42], 'v1/channels/42'],
                    [['v1/channel/delete'], false],
                ],
            ],
            [
                [ // Rule properties
                    'controller' => ['v1/channel', 'v1/u' => 'v1/user'],
                    'pluralize' => true,
                ],
                [ // test cases: route, expected
                    [['v1/channel/index'], 'v1/channels'],
                    [['v1/channel/view', 'id' => 42], 'v1/channels/42'],
                    [['v1/channel/options'], 'v1/channels'],
                    [['v1/channel/options', 'id' => 42], 'v1/channels/42'],
                    [['v1/channel/delete'], false],
                    [['v1/user/index'], 'v1/u'],
                    [['v1/user/view', 'id' => 1], 'v1/u/1'],
                    [['v1/channel/options'], 'v1/channels'],
                    [['v1/channel/options', 'id' => 42], 'v1/channels/42'],
                    [['v1/user/delete'], false],
                ],
            ],


            // without pluralize
            [
                [ // Rule properties
                    'controller' => 'v1/channel',
                    'pluralize' => false,
                ],
                [ // test cases: route, expected
                    [['v1/channel/index'], 'v1/channel'],
                    [['v1/channel/index', 'offset' => 1], 'v1/channel?offset=1'],
                    [['v1/channel/view', 'id' => 42], 'v1/channel/42'],
                    [['v1/channel/options'], 'v1/channel'],
                    [['v1/channel/options', 'id' => 42], 'v1/channel/42'],
                    [['v1/channel/delete'], false],
                ],
            ],
            [
                [ // Rule properties
                    'controller' => ['v1/channel'],
                    'pluralize' => false,
                ],
                [ // test cases: route, expected
                    [['v1/channel/index'], 'v1/channel'],
                    [['v1/channel/index', 'offset' => 1], 'v1/channel?offset=1'],
                    [['v1/channel/view', 'id' => 42], 'v1/channel/42'],
                    [['v1/channel/options'], 'v1/channel'],
                    [['v1/channel/options', 'id' => 42], 'v1/channel/42'],
                    [['v1/channel/delete'], false],
                ],
            ],
            [
                [ // Rule properties
                    'controller' => ['v1/channel', 'v1/u' => 'v1/user'],
                    'pluralize' => false,
                ],
                [ // test cases: route, expected
                    [['v1/channel/index'], 'v1/channel'],
                    [['v1/channel/view', 'id' => 42], 'v1/channel/42'],
                    [['v1/channel/options'], 'v1/channel'],
                    [['v1/channel/options', 'id' => 42], 'v1/channel/42'],
                    [['v1/channel/delete'], false],
                    [['v1/user/index'], 'v1/u'],
                    [['v1/user/view', 'id' => 1], 'v1/u/1'],
                    [['v1/user/options'], 'v1/u'],
                    [['v1/user/options', 'id' => 42], 'v1/u/42'],
                    [['v1/user/delete'], false],
                ],
            ],

            // using extra patterns
            [
                [ // Rule properties
                    'controller' => 'v1/channel',
                    'pluralize' => true,
                    'extraPatterns' => [
                        '{id}/my' => 'my',
                        'my' => 'my',
                        // since 2.0.41 this should create a URL (previously it was false)
                        'POST {id}/my2' => 'my2',
                    ],
                ],
                [ // test cases: route, expected
                    // normal actions should behave as before
                    [['v1/channel/index'], 'v1/channels'],
                    [['v1/channel/index', 'offset' => 1], 'v1/channels?offset=1'],
                    [['v1/channel/view', 'id' => 42], 'v1/channels/42'],
                    [['v1/channel/options'], 'v1/channels'],
                    [['v1/channel/options', 'id' => 42], 'v1/channels/42'],
                    [['v1/channel/delete'], false],

                    [['v1/channel/my'], 'v1/channels/my'],
                    [['v1/channel/my', 'id' => 42], 'v1/channels/42/my'],
                    [['v1/channel/my2'], false],
                    [['v1/channel/my2', 'id' => 42], 'v1/channels/42/my2'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider createUrlDataProvider
     * @param array $ruleConfig
     * @param array $tests
     */
    public function testCreateUrl($ruleConfig, $tests)
    {
        foreach ($tests as $test) {
            list($params, $expected) = $test;

            $this->mockWebApplication();
            Yii::$app->set('request', new Request(['hostInfo' => 'http://api.example.com', 'scriptUrl' => '/index.php']));
            $route = array_shift($params);

            $manager = new UrlManager([
                'cache' => null,
            ]);
            $rule = new UrlRule($ruleConfig);
            $this->assertEquals($expected, $rule->createUrl($manager, $route, $params));
        }
    }

    /**
     * @dataProvider getCreateUrlStatusProvider
     *
     * @param array $ruleConfig
     * @param array $tests
     */
    public function testGetCreateUrlStatus($ruleConfig, $tests)
    {
        foreach ($tests as $test) {
            list($params, $expected, $status) = $test;

            $this->mockWebApplication();
            Yii::$app->set('request', new Request(['hostInfo' => 'http://api.example.com', 'scriptUrl' => '/index.php']));
            $route = array_shift($params);

            $manager = new UrlManager([
                'cache' => null,
            ]);
            $rule = new UrlRule($ruleConfig);
            $errorMessage = 'Failed test: ' . VarDumper::dumpAsString($test);
            $this->assertSame($expected, $rule->createUrl($manager, $route, $params), $errorMessage);
            $this->assertNotNull($status, $errorMessage);
            if ($status > 0) {
                $this->assertSame($status, $rule->getCreateUrlStatus() & $status, $errorMessage);
            } else {
                $this->assertSame($status, $rule->getCreateUrlStatus(), $errorMessage);
            }
        }
    }

    /**
     * Provides test cases for getCreateUrlStatus() method.
     *
     * - first param are properties of the UrlRule
     * - second param is an array of test cases, containing two element arrays:
     *   - first element is the route to create
     *   - second element is the expected URL
     *   - third element is the expected result of getCreateUrlStatus() method
     */
    public static function getCreateUrlStatusProvider()
    {
        return [
            'single controller' => [
                // rule properties
                [
                    'controller' => ['v1/channel'],
                    'pluralize' => true,
                ],
                // test cases: route, expected, createStatus
                [
                    [['v1/channel/index'], 'v1/channels', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/index', 'offset' => 1], 'v1/channels?offset=1', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/view', 'id' => 42], 'v1/channels/42', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/view'], false, WebUrlRule::CREATE_STATUS_PARAMS_MISMATCH],
                    [['v1/channel/options'], 'v1/channels', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/options', 'id' => 42], 'v1/channels/42', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/delete'], false, WebUrlRule::CREATE_STATUS_PARAMS_MISMATCH],
                    [['v1/channel/delete', 'id' => 43], 'v1/channels/43', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/create'], 'v1/channels', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/update', 'id' => 44], 'v1/channels/44', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/update'], false, WebUrlRule::CREATE_STATUS_PARAMS_MISMATCH],

                    [['v1/missing/view'], false, WebUrlRule::CREATE_STATUS_ROUTE_MISMATCH],
                ],
            ],
            'multiple controllers' => [
                // rule properties
                [
                    'controller' => ['v1/channel', 'v1/u' => 'v1/user'],
                    'pluralize' => false,
                ],
                // test cases: route, expected, createStatus
                [
                    [['v1/channel/index'], 'v1/channel', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/view', 'id' => 42], 'v1/channel/42', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/options'], 'v1/channel', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/options', 'id' => 42], 'v1/channel/42', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/delete'], false, WebUrlRule::CREATE_STATUS_PARAMS_MISMATCH],
                    [['v1/channel/delete', 'id' => 43], 'v1/channel/43', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/create'], 'v1/channel', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/update'], false, WebUrlRule::CREATE_STATUS_PARAMS_MISMATCH],
                    [['v1/channel/update', 'id' => 45], 'v1/channel/45', WebUrlRule::CREATE_STATUS_SUCCESS],

                    [['v1/user/index'], 'v1/u', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/user/view', 'id' => 1], 'v1/u/1', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/user/view'], false, WebUrlRule::CREATE_STATUS_PARAMS_MISMATCH],
                    [['v1/user/options'], 'v1/u', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/user/options', 'id' => 42], 'v1/u/42', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/user/delete', 'id' => 44], 'v1/u/44', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/user/delete'], false, WebUrlRule::CREATE_STATUS_PARAMS_MISMATCH],
                    [['v1/user/create'], 'v1/u', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/user/update', 'id' => 46], 'v1/u/46', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/user/update'], false, WebUrlRule::CREATE_STATUS_PARAMS_MISMATCH],

                    [['v1/missing/view'], false, WebUrlRule::CREATE_STATUS_ROUTE_MISMATCH],
                ],
            ],
        ];
    }
}
