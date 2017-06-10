<?php

namespace yiiunit\framework\web;

use yii\web\UrlManager;
use yii\web\GroupUrlRule;
use yii\web\Request;
use yii\web\UrlRule;
use yiiunit\TestCase;

/**
 * @group web
 */
class GroupUrlRuleTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testCreateUrl()
    {
        $manager = new UrlManager(['cache' => null]);
        $suites = $this->getTestsForCreateUrl();
        foreach ($suites as $i => [$name, $config, $tests]) {
            $rule = new GroupUrlRule($config);
            foreach ($tests as $j => $test) {
                [$route, $params, $expected, $status] = $test;
                $url = $rule->createUrl($manager, $route, $params);
                $this->assertEquals($expected, $url, "Test#$i-$j: $name");
                $this->assertSame($status, $rule->getCreateUrlStatus(), "Test#$i-$j: $name");
            }
        }
    }

    public function testParseRequest()
    {
        $manager = new UrlManager(['cache' => null]);
        $request = new Request(['hostInfo' => 'http://en.example.com']);
        $suites = $this->getTestsForParseRequest();
        foreach ($suites as $i => [$name, $config, $tests]) {
            $rule = new GroupUrlRule($config);
            foreach ($tests as $j => $test) {
                [$request->pathInfo, $route] = $test;
                $params = $test[2] ?? [];
                $result = $rule->parseRequest($manager, $request);
                if ($route === false) {
                    $this->assertFalse($result, "Test#$i-$j: $name");
                } else {
                    $this->assertEquals([$route, $params], $result, "Test#$i-$j: $name");
                }
            }
        }
    }

    protected function getTestsForCreateUrl()
    {
        // structure of each test
        //   message for the test
        //   config for the URL rule
        //   list of inputs and outputs
        //     route
        //     params
        //     expected output
        //     expected getCreateUrlStatus() result
        return [
            [
                'no prefix',
                [
                    'rules' => [
                        'login' => 'user/login',
                        'logout' => 'user/logout',
                    ],
                ],
                [
                    ['user/login', [], 'login', UrlRule::CREATE_STATUS_SUCCESS],
                    ['user/logout', [], 'logout', UrlRule::CREATE_STATUS_SUCCESS],
                    ['user/create', [], false, UrlRule::CREATE_STATUS_ROUTE_MISMATCH],
                ],
            ],
            [
                'prefix only',
                [
                    'prefix' => 'admin',
                    'rules' => [
                        'login' => 'user/login',
                        'logout' => 'user/logout',
                    ],
                ],
                [
                    ['admin/user/login', [], 'admin/login', UrlRule::CREATE_STATUS_SUCCESS],
                    ['admin/user/logout', [], 'admin/logout', UrlRule::CREATE_STATUS_SUCCESS],
                    ['user/create', [], false, UrlRule::CREATE_STATUS_ROUTE_MISMATCH],
                ],
            ],
            [
                'prefix and routePrefix different',
                [
                    'prefix' => '_',
                    'routePrefix' => 'admin',
                    'rules' => [
                        'login' => 'user/login',
                        'logout' => 'user/logout',
                    ],
                ],
                [
                    ['admin/user/login', [], '_/login', UrlRule::CREATE_STATUS_SUCCESS],
                    ['admin/user/logout', [], '_/logout', UrlRule::CREATE_STATUS_SUCCESS],
                    ['user/create', [], false, UrlRule::CREATE_STATUS_ROUTE_MISMATCH],
                ],
            ],
            [
                'ruleConfig with suffix',
                [
                    'prefix' => '_',
                    'routePrefix' => 'admin',
                    'ruleConfig' => [
                        'suffix' => '.html',
                        'class' => 'yii\\web\\UrlRule'
                    ],
                    'rules' => [
                        'login' => 'user/login',
                        'logout' => 'user/logout',
                    ],
                ],
                [
                    ['admin/user/login', [], '_/login.html', UrlRule::CREATE_STATUS_SUCCESS],
                    ['admin/user/logout', [], '_/logout.html', UrlRule::CREATE_STATUS_SUCCESS],
                    ['user/create', [], false, UrlRule::CREATE_STATUS_ROUTE_MISMATCH],
                ],
            ],
            [
                'createStatus for failed statuses',
                [
                    'prefix' => '_',
                    'routePrefix' => 'admin',
                    'ruleConfig' => [
                        'suffix' => '.html',
                        'class' => 'yii\web\UrlRule'
                    ],
                    'rules' => [
                        'login' => 'user/login',
                        [
                            'pattern' => 'logout',
                            'route' => 'user/logout',
                            'mode' => UrlRule::PARSING_ONLY,
                        ],
                        [
                            'pattern' => 'logout/<token:\w+>',
                            'route' => 'user/logout',
                        ],
                    ],
                ],
                [
                    [
                        'admin/user/logout', [], false,
                        UrlRule::CREATE_STATUS_PARSING_ONLY | UrlRule::CREATE_STATUS_ROUTE_MISMATCH | UrlRule::CREATE_STATUS_PARAMS_MISMATCH
                    ],
                ],
            ],
        ];
    }

    protected function getTestsForParseRequest()
    {
        // structure of each test
        //   message for the test
        //   config for the URL rule
        //   list of inputs and outputs
        //     pathInfo
        //     expected route, or false if the rule doesn't apply
        //     expected params, or not set if empty
        return [
            [
                'no prefix',
                [
                    'rules' => [
                        'login' => 'user/login',
                        'logout' => 'user/logout',
                    ],
                ],
                [
                    ['login', 'user/login'],
                    ['logout', 'user/logout'],
                    ['create', false],
                ],
            ],
            [
                'prefix only',
                [
                    'prefix' => 'admin',
                    'rules' => [
                        'login' => 'user/login',
                        'logout' => 'user/logout',
                    ],
                ],
                [
                    ['admin/login', 'admin/user/login'],
                    ['admin/logout', 'admin/user/logout'],
                    ['admin/create', false],
                    ['create', false],
                ],
            ],
            [
                'prefix and routePrefix different',
                [
                    'prefix' => '_',
                    'routePrefix' => 'admin',
                    'rules' => [
                        'login' => 'user/login',
                        'logout' => 'user/logout',
                    ],
                ],
                [
                    ['_/login', 'admin/user/login'],
                    ['_/logout', 'admin/user/logout'],
                    ['_/create', false],
                    ['create', false],
                ],
            ],
            [
                'ruleConfig with suffix',
                [
                    'prefix' => '_',
                    'routePrefix' => 'admin',
                    'ruleConfig' => [
                        'suffix' => '.html',
                        'class' => 'yii\\web\\UrlRule'
                    ],
                    'rules' => [
                        'login' => 'user/login',
                        'logout' => 'user/logout',
                    ],
                ],
                [
                    ['_/login.html', 'admin/user/login'],
                    ['_/logout.html', 'admin/user/logout'],
                    ['_/logout', false],
                    ['_/create.html', false],
                ],
            ],
        ];
    }
}
