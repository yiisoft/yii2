<?php

namespace yiiunit\framework\web;

use yii\web\UrlManager;
use yii\web\UrlRule;
use yii\web\GroupUrlRule;
use yii\web\Request;
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
        foreach ($suites as $i => $suite) {
            list ($name, $config, $tests) = $suite;
            $rule = new GroupUrlRule($config);
            foreach ($tests as $j => $test) {
                list ($route, $params, $expected) = $test;
                $url = $rule->createUrl($manager, $route, $params);
                $this->assertEquals($expected, $url, "Test#$i-$j: $name");
            }
        }
    }

    public function testParseRequest()
    {
        $manager = new UrlManager(['cache' => null]);
        $request = new Request(['hostInfo' => 'http://en.example.com']);
        $suites = $this->getTestsForParseRequest();
        foreach ($suites as $i => $suite) {
            list ($name, $config, $tests) = $suite;
            $rule = new GroupUrlRule($config);
            foreach ($tests as $j => $test) {
                $request->pathInfo = $test[0];
                $route = $test[1];
                $params = isset($test[2]) ? $test[2] : [];
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
                    ['user/login', [], 'login'],
                    ['user/logout', [], 'logout'],
                    ['user/create', [], false],
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
                    ['admin/user/login', [], 'admin/login'],
                    ['admin/user/logout', [], 'admin/logout'],
                    ['user/create', [], false],
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
                    ['admin/user/login', [], '_/login'],
                    ['admin/user/logout', [], '_/logout'],
                    ['user/create', [], false],
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
                    ['admin/user/login', [], '_/login.html'],
                    ['admin/user/logout', [], '_/logout.html'],
                    ['user/create', [], false],
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
