<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\rest;

use Yii;
use yii\helpers\VarDumper;
use yii\rest\GroupUrlRule;
use yii\web\Request;
use yii\web\UrlManager;
use yii\web\UrlRule as WebUrlRule;
use yiiunit\TestCase;

/**
 * @group rest
 */
class GroupUrlRuleTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }


    /**
     * Provides test cases for testParseRequest() method.
     *
     * - first param are message for the test
     * - second param is an config for the URL rule
     * - thirds param is an array of test cases, containing four element arrays:
     *   - first element is a pathInfo
     *   - second element is a expected route, or false if the rule doesn't apply
     *   - thirds element is a expected params
     *   - fourth element is a method
     */
    public function parseRequestDataProvider(){
        return [
            [
                'short syntax',
                [
                    'prefix' => 'v1',
                    'rules' => [
                        'post',
                        'p' => 'post',
                    ],
                ],
                [
                    ['v1/posts', 'v1/post/index'],
                    ['v1/p', 'v1/post/index'],
                ],
            ],

            [
                'pluralized name',
                [
                    'prefix' => 'v1',
                    'rules' => [
                        ['controller' => 'post'],
                    ],
                ],
                [
                    ['v1/posts', 'v1/post/index'],
                ],
            ],

            [
                'prefixed route',
                [
                    'prefix' => 'v1',
                    'rules' => [
                        ['controller' => 'post', 'prefix' => 'admin'],
                    ],
                ],
                [
                    ['v1/admin/posts', 'v1/post/index'],
                ],
            ],

            [
                'suffixed route',
                [
                    'prefix' => 'v1',
                    'rules' => [
                        ['controller' => 'post', 'suffix' => '.json'],
                    ],
                ],
                [
                    ['v1/posts.json', 'v1/post/index'],
                    ['v1/posts.json', 'v1/post/create', [], 'POST'],
                    ['v1/posts/123.json', 'v1/post/view', ['id' => 123], 'GET'],
                ],
            ],

            [
                'default routes according request method',
                [
                    'prefix' => 'v1',
                    'rules' => [
                        ['controller' => 'post'],
                    ],
                ],
                [
                    ['v1/posts', 'v1/post/index', [], 'GET'],
                    ['v1/posts', 'v1/post/index', [], 'HEAD'],
                    ['v1/posts', 'v1/post/create', [], 'POST'],
                    ['v1/posts', 'v1/post/options', [], 'PATCH'],
                    ['v1/posts', 'v1/post/options', [], 'PUT'],
                    ['v1/posts', 'v1/post/options', [], 'DELETE'],

                    ['v1/posts/123', 'v1/post/view', ['id' => 123], 'GET'],
                    ['v1/posts/123', 'v1/post/view', ['id' => 123], 'HEAD'],
                    ['v1/posts/123', 'v1/post/options', ['id' => 123], 'POST'],
                    ['v1/posts/123', 'v1/post/update', ['id' => 123], 'PATCH'],
                    ['v1/posts/123', 'v1/post/update', ['id' => 123], 'PUT'],
                    ['v1/posts/123', 'v1/post/delete', ['id' => 123], 'DELETE'],

                    ['v1/posts/new', false],
                ],
            ],

            [
                'only selected routes',
                [
                    'prefix' => 'v1',
                    'rules' => [
                        ['controller' => 'post', 'only' => ['index']],
                    ],
                ],
                [
                    ['v1/posts', 'v1/post/index'],
                    ['v1/posts/123', false],
                    ['v1/posts', false, [], 'POST'],
                ],
            ],

            [
                'except routes',
                [
                    'prefix' => 'v1',
                    'rules' => [
                        ['controller' => 'post', 'except' => ['delete', 'create']],
                    ],
                ],
                [
                    ['v1/posts', 'v1/post/index'],
                    ['v1/posts/123', 'v1/post/view', ['id' => 123]],
                    ['v1/posts/123', 'v1/post/options', ['id' => 123], 'DELETE'],
                    ['v1/posts', 'v1/post/options', [], 'POST'],
                ],
            ],

            [
                'extra patterns',
                [
                    'prefix' => 'v1',
                    'rules' => [
                        ['controller' => 'post', 'extraPatterns' => ['POST new' => 'create']],
                    ],
                ],
                [
                    ['v1/posts/new', 'v1/post/create', [], 'POST'],
                    ['v1/posts', 'v1/post/create', [], 'POST'],
                ],
            ],

            [
                'extra patterns overwrite patterns',
                [
                    'prefix' => 'v1',
                    'rules' => [
                        ['controller' => 'post', 'extraPatterns' => ['POST' => 'new']],
                    ],
                ],
                [
                    ['v1/posts', 'v1/post/new', [], 'POST'],
                ],
            ],

            [
                'extra patterns rule is higher priority than patterns',
                [
                    'prefix' => 'v1',
                    'rules' => [
                        ['controller' => 'post', 'extraPatterns' => ['GET 1337' => 'leet']],
                    ],
                ],
                [
                    ['v1/posts/1337', 'v1/post/leet'],
                    ['v1/posts/1338', 'v1/post/view', ['id' => 1338]],
                ],
            ],
        ];

    }

    /**
     * @dataProvider parseRequestDataProvider
     * @param $name
     * @param $config
     * @param $tests
     */
    public function testParseRequest($name, $config, $tests)
    {
        static $i;
        $i++;
        $manager = new UrlManager(['cache' => null]);
        $request = new Request(['hostInfo' => 'http://en.example.com', 'methodParam' => '_METHOD']);
        $rule = new GroupUrlRule($config);
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
            // single controller, string
            [
                [ // Rule properties
                    'prefix' => 'v1',
                    'rules' => [
                        [
                            'controller' => 'channel',
                            'pluralize' => true,
                        ],
                    ],
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
            // with pluralize
            // single controller, array
            [
                [ // Rule properties
                    'prefix' => 'v1',
                    'rules' => [
                        [
                            'controller' => ['channel'],
                            'pluralize' => true,
                        ],
                    ],
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
            // with pluralize
            // multiple controller, key-value pair
            [
                [ // Rule properties
                    'prefix' => 'v1',
                    'rules' => [
                        [
                            'controller' => ['channel', 'u' => 'user'],
                            'pluralize' => true,
                        ],
                    ],
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
            // with pluralize
            // short syntax array, key-value pair
            [
                [ // Rule properties
                    'prefix' => 'v1',
                    'rules' => [
                        'channel',
                        'u' => 'user',
                    ],
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
            // single controller, string
            [
                [ // Rule properties
                    'prefix' => 'v1',
                    'rules' => [
                        [
                            'controller' => 'channel',
                            'pluralize' => false,
                        ],
                    ],
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
            // without pluralize
            // single controller, array
            [
                [ // Rule properties
                    'prefix' => 'v1',
                    'rules' => [
                        [
                            'controller' => ['channel'],
                            'pluralize' => false,
                        ],
                    ],
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
            // without pluralize
            // multiple controller, key-value pair
            [
                [ // Rule properties
                    'prefix' => 'v1',
                    'rules' => [
                        [
                            'controller' => ['channel', 'u' => 'user'],
                            'pluralize' => false,
                        ],
                    ],
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
                    'prefix' => 'v1',
                    'rules' => [
                        [
                            'controller' => 'channel',
                            'pluralize' => true,
                            'extraPatterns' => [
                                '{id}/my' => 'my',
                                'my' => 'my',
                                // this should not create a URL, no GET definition
                                'POST {id}/my2' => 'my2',
                            ],
                        ],
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
                    [['v1/channel/my2', 'id' => 42], false],
                ],
            ],
        ];
    }

    /**
     * @dataProvider createUrlDataProvider
     * @param array $config
     * @param array $tests
     */
    public function testCreateUrl($config, $tests)
    {
        $this->mockWebApplication();
        Yii::$app->set('request', new Request(['hostInfo' => 'http://api.example.com', 'scriptUrl' => '/index.php']));
        $manager = new UrlManager(['cache' => null]);
        $rule = new GroupUrlRule($config);

        foreach ($tests as $test) {
            list($params, $expected) = $test;
            $route = array_shift($params);
            
            $this->assertEquals($expected, $rule->createUrl($manager, $route, $params));
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
    public function testGetCreateUrlStatusProvider()
    {
        return [
            'single controller' => [
                // rule properties
                [
                    'prefix' => 'v1',
                    'rules' => [
                        [
                            'controller' => ['channel'],
                            'pluralize' => true,
                        ],
                    ],
                ],
                // test cases: route, expected, createStatus
                [
                    [['v1/channel/index'], 'v1/channels', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/index', 'offset' => 1], 'v1/channels?offset=1', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/view', 'id' => 42], 'v1/channels/42', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/options'], 'v1/channels', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/options', 'id' => 42], 'v1/channels/42', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/delete'], false, WebUrlRule::CREATE_STATUS_PARSING_ONLY],
                    [['v1/missing/view'], false, WebUrlRule::CREATE_STATUS_ROUTE_MISMATCH],
                    [['v1/channel/view'], false, WebUrlRule::CREATE_STATUS_PARAMS_MISMATCH],
                ],
            ],
            'multiple controllers' => [
                // rule properties
                [
                    'prefix' => 'v1',
                    'rules' => [
                        [
                            'controller' => ['channel', 'u' => 'user'],
                            'pluralize' => false,
                        ],
                    ],
                ],
                // test cases: route, expected, createStatus
                [
                    [['v1/channel/index'], 'v1/channel', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/view', 'id' => 42], 'v1/channel/42', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/options'], 'v1/channel', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/options', 'id' => 42], 'v1/channel/42', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/delete'], false, WebUrlRule::CREATE_STATUS_PARSING_ONLY],
                    [['v1/user/index'], 'v1/u', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/user/view', 'id' => 1], 'v1/u/1', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/user/options'], 'v1/u', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/user/options', 'id' => 42], 'v1/u/42', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/user/delete'], false, WebUrlRule::CREATE_STATUS_PARSING_ONLY],
                    [['v1/user/view'], false, WebUrlRule::CREATE_STATUS_PARAMS_MISMATCH],
                    [['v1/missing/view'], false, WebUrlRule::CREATE_STATUS_ROUTE_MISMATCH],
                ],
            ],
            'short syntax' => [
                // rule properties
                [
                    'prefix' => 'v1',
                    'rules' => [
                        'channel',
                        'u' => 'user',
                    ],
                ],
                // test cases: route, expected, createStatus
                [
                    [['v1/channel/index'], 'v1/channels', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/index', 'offset' => 1], 'v1/channels?offset=1', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/view', 'id' => 42], 'v1/channels/42', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/options'], 'v1/channels', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/options', 'id' => 42], 'v1/channels/42', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/channel/delete'], false, WebUrlRule::CREATE_STATUS_PARSING_ONLY],
                    [['v1/missing/view'], false, WebUrlRule::CREATE_STATUS_ROUTE_MISMATCH],
                    [['v1/channel/view'], false, WebUrlRule::CREATE_STATUS_PARAMS_MISMATCH],
                    [['v1/user/index'], 'v1/u', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/user/view', 'id' => 1], 'v1/u/1', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/user/options'], 'v1/u', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/user/options', 'id' => 42], 'v1/u/42', WebUrlRule::CREATE_STATUS_SUCCESS],
                    [['v1/user/delete'], false, WebUrlRule::CREATE_STATUS_PARSING_ONLY],
                    [['v1/user/view'], false, WebUrlRule::CREATE_STATUS_PARAMS_MISMATCH],
                ],
            ],
        ];
    }

    /**
     * @dataProvider testGetCreateUrlStatusProvider
     * @param array $config
     * @param array $tests
     */
    public function testGetCreateUrlStatus($config, $tests)
    {
        $this->mockWebApplication();
        Yii::$app->set('request', new Request(['hostInfo' => 'http://api.example.com', 'scriptUrl' => '/index.php']));
        $manager = new UrlManager(['cache' => null]);

        $rule = new GroupUrlRule($config);

        foreach ($tests as $test) {
            list($params, $expected, $status) = $test;
            $route = array_shift($params);

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

}
