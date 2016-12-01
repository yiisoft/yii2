<?php

namespace yiiunit\framework\rest;

use yii\web\UrlManager;
use yii\rest\UrlRule;
use yii\web\Request;
use yiiunit\TestCase;

/**
 * @group rest
 */
class UrlRuleTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testInitControllerNamePluralization()
    {
        $suites = $this->getTestsForControllerNamePluralization();
        foreach ($suites as $i => $suite) {
            list ($name, $tests) = $suite;
            foreach ($tests as $j => $test) {
                list ($config, $expected) = $test;
                $rule = new UrlRule($config);
                $this->assertEquals($expected, $rule->controller, "Test#$i-$j: $name");
            }
        }
    }

    public function testParseRequest()
    {
        $manager = new UrlManager(['cache' => null]);
        $request = new Request(['hostInfo' => 'http://en.example.com', 'methodParam' => '_METHOD',]);
        $suites = $this->getTestsForParseRequest();
        foreach ($suites as $i => $suite) {
            list ($name, $config, $tests) = $suite;
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
                ['controller' => 'post', 'prefix' => 'admin',],
                [
                    ['admin/posts', 'post/index'],
                ],
            ],
            [
                'suffixed route',
                ['controller' => 'post', 'suffix' => '.json',],
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
                ['controller' => 'post', 'only' => ['index'],],
                [
                    ['posts', 'post/index'],
                    ['posts/123', false],
                    ['posts', false, [], 'POST'],
                ],
            ],
            [
                'except routes',
                ['controller' => 'post', 'except' => ['delete', 'create'],],
                [
                    ['posts', 'post/index'],
                    ['posts/123', 'post/view', ['id' => 123]],
                    ['posts/123', 'post/options', ['id' => 123], 'DELETE'],
                    ['posts', 'post/options', [], 'POST'],
                ],
            ],
            [
                'extra patterns',
                ['controller' => 'post', 'extraPatterns' => ['POST new' => 'create',],],
                [
                    ['posts/new', 'post/create', [], 'POST'],
                    ['posts', 'post/create', [], 'POST'],
                ],
            ],
            [
                'extra patterns overwrite patterns',
                ['controller' => 'post', 'extraPatterns' => ['POST' => 'new',],],
                [
                    ['posts', 'post/new', [], 'POST'],
                ],
            ],
            [
                'extra patterns rule is higher priority than patterns',
                ['controller' => 'post', 'extraPatterns' => ['GET 1337' => 'leet',],],
                [
                    ['posts/1337', 'post/leet'],
                    ['posts/1338', 'post/view', ['id' => 1338]],
                ],
            ],
        ];
    }

    protected function getTestsForControllerNamePluralization()
    {
        return [
            [
                'pluralized automatically', [
                [
                    ['controller' => 'user'],
                    ['users' => 'user']
                ],
                [
                    ['controller' => 'admin/user'],
                    ['admin/users' => 'admin/user']
                ],
                [
                    ['controller' => ['admin/user', 'post']],
                    ['admin/users' => 'admin/user', 'posts' => 'post']
                ],
            ]],
            [
                'explicitly specified', [
                [
                    ['controller' => ['customer' => 'user']],
                    ['customer' => 'user']
                ]
            ]],
            [
                'do not pluralize', [
                [
                    [
                        'pluralize' => false,
                        'controller' => ['admin/user', 'post'],
                    ],
                    ['admin/user' => 'admin/user', 'post' => 'post',]
                ]
            ]],

        ];
    }
}
