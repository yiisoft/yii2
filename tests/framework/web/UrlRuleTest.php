<?php

namespace yiiunit\framework\web;

use yii\web\NotFoundHttpException;
use yii\web\UrlManager;
use yii\web\UrlNormalizer;
use yii\web\UrlNormalizerRedirectException;
use yii\web\UrlRule;
use yii\web\Request;
use yiiunit\TestCase;

/**
 * @group web
 */
class UrlRuleTest extends TestCase
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
            $rule = new UrlRule($config);
            foreach ($tests as $j => $test) {
                list ($route, $params, $expected) = $test;
                $url = $rule->createUrl($manager, $route, $params);
                $this->assertSame($expected, $url, "Test#$i-$j: $name");
            }
        }
    }

    public function testParseRequest()
    {
        $manager = new UrlManager([
            'cache' => null,
            'normalizer' => false,
        ]);
        $request = new Request(['hostInfo' => 'http://en.example.com']);
        $suites = $this->getTestsForParseRequest();
        foreach ($suites as $i => $suite) {
            list ($name, $config, $tests) = $suite;
            $rule = new UrlRule($config);
            foreach ($tests as $j => $test) {
                $request->pathInfo = $test[0];
                $expected = $test[1];
                $result = $rule->parseRequest($manager, $request);
                if ($expected === false) {
                    $this->assertFalse($result, "Test#$i-$j: $name");
                } else {
                    $this->assertEquals($expected, $result, "Test#$i-$j: $name");
                }
            }
        }
    }

    public function testParseRequestWithNormalizer()
    {
        $manager = new UrlManager([
            'cache' => null,
            'normalizer' => UrlNormalizer::className(),
        ]);
        $request = new Request(['hostInfo' => 'http://en.example.com']);
        $suites = $this->getTestsForParseRequest();
        foreach ($suites as $i => $suite) {
            list ($name, $config, $tests) = $suite;
            $rule = new UrlRule($config);
            foreach ($tests as $j => $test) {
                $request->pathInfo = $test[0];
                $expected = isset($test[2]) ? $test[2] : $test[1];
                try {
                    $result = $rule->parseRequest($manager, $request);
                    if ($expected === false) {
                        $this->assertFalse($result, "Test#$i-$j: $name");
                    } else {
                        $this->assertEquals($expected, $result, "Test#$i-$j: $name");
                    }
                } catch (UrlNormalizerRedirectException $exc) {
                    $this->assertEquals([$expected[0]] + $expected[1], $exc->url, "Test#$i-$j: $name");
                }
            }
        }
    }

    public function testParseRequestWithUrlManagerCustomNormalizer()
    {
        $manager = new UrlManager([
            'cache' => null,
            'normalizer' => [
                'class' => UrlNormalizer::className(),
                'action' => UrlNormalizer::ACTION_REDIRECT_PERMANENT,
            ],
        ]);
        $request = new Request(['hostInfo' => 'http://en.example.com']);
        $suites = $this->getTestsForParseRequest();
        foreach ($suites as $i => $suite) {
            list ($name, $config, $tests) = $suite;
            $rule = new UrlRule($config);
            foreach ($tests as $j => $test) {
                $request->pathInfo = $test[0];
                $expected = isset($test[2]) ? $test[2] : $test[1];
                try {
                    $result = $rule->parseRequest($manager, $request);
                    if ($expected === false) {
                        $this->assertFalse($result, "Test#$i-$j: $name");
                    } else {
                        $this->assertEquals($expected, $result, "Test#$i-$j: $name");
                    }
                } catch (UrlNormalizerRedirectException $exc) {
                    $this->assertEquals(UrlNormalizer::ACTION_REDIRECT_PERMANENT, $exc->statusCode, "Test-statusCode#$i-$j: $name");
                    $this->assertEquals([$expected[0]] + $expected[1], $exc->url, "Test#$i-$j: $name");
                }
            }
        }

        $manager = new UrlManager([
            'cache' => null,
            'normalizer' => [
                'class' => UrlNormalizer::className(),
                'action' => UrlNormalizer::ACTION_REDIRECT_TEMPORARY,
            ],
        ]);
        $request = new Request(['hostInfo' => 'http://en.example.com']);
        $suites = $this->getTestsForParseRequest();
        foreach ($suites as $i => $suite) {
            list ($name, $config, $tests) = $suite;
            $rule = new UrlRule($config);
            foreach ($tests as $j => $test) {
                $request->pathInfo = $test[0];
                $expected = isset($test[2]) ? $test[2] : $test[1];
                try {
                    $result = $rule->parseRequest($manager, $request);
                    if ($expected === false) {
                        $this->assertFalse($result, "Test#$i-$j: $name");
                    } else {
                        $this->assertEquals($expected, $result, "Test#$i-$j: $name");
                    }
                } catch (UrlNormalizerRedirectException $exc) {
                    $this->assertEquals(UrlNormalizer::ACTION_REDIRECT_TEMPORARY, $exc->statusCode, "Test-statusCode#$i-$j: $name");
                    $this->assertEquals([$expected[0]] + $expected[1], $exc->url, "Test#$i-$j: $name");
                }
            }
        }

        $manager = new UrlManager([
            'cache' => null,
            'normalizer' => [
                'class' => UrlNormalizer::className(),
                'action' => UrlNormalizer::ACTION_NOT_FOUND,
            ],
        ]);
        $request = new Request(['hostInfo' => 'http://en.example.com']);
        $suites = $this->getTestsForParseRequest();
        foreach ($suites as $i => $suite) {
            list ($name, $config, $tests) = $suite;
            $rule = new UrlRule($config);
            foreach ($tests as $j => $test) {
                $request->pathInfo = $test[0];
                $expected = $test[1];
                try {
                    $result = $rule->parseRequest($manager, $request);
                    if ($expected === false) {
                        $this->assertFalse($result, "Test#$i-$j: $name");
                    } else {
                        $this->assertEquals($expected, $result, "Test#$i-$j: $name");
                    }
                } catch (NotFoundHttpException $exc) {
                    $this->assertFalse($expected, "Test#$i-$j: $name");
                }
            }
        }

        $manager = new UrlManager([
            'cache' => null,
            'normalizer' => [
                'class' => UrlNormalizer::className(),
                'action' => null,
            ],
        ]);
        $request = new Request(['hostInfo' => 'http://en.example.com']);
        $suites = $this->getTestsForParseRequest();
        foreach ($suites as $i => $suite) {
            list ($name, $config, $tests) = $suite;
            $rule = new UrlRule($config);
            foreach ($tests as $j => $test) {
                $request->pathInfo = $test[0];
                $expected = isset($test[2]) ? $test[2] : $test[1];
                $result = $rule->parseRequest($manager, $request);
                if ($expected === false) {
                    $this->assertFalse($result, "Test#$i-$j: $name");
                } else {
                    $this->assertEquals($expected, $result, "Test#$i-$j: $name");
                }
            }
        }

        $normalizerAction = function ($route) {
            $route[1]['oldRoute'] = $route[0];
            $route[0] = 'site/myCustomRoute';
            return $route;
        };
        $manager = new UrlManager([
            'cache' => null,
            'normalizer' => [
                'class' => UrlNormalizer::className(),
                'action' => $normalizerAction,
            ],
        ]);
        $request = new Request(['hostInfo' => 'http://en.example.com']);
        $suites = $this->getTestsForParseRequest();
        foreach ($suites as $i => $suite) {
            list ($name, $config, $tests) = $suite;
            $rule = new UrlRule($config);
            foreach ($tests as $j => $test) {
                $request->pathInfo = $test[0];
                $expected = isset($test[2]) ? $normalizerAction($test[2]) : $test[1];
                $result = $rule->parseRequest($manager, $request);
                if ($expected === false) {
                    $this->assertFalse($result, "Test#$i-$j: $name");
                } else {
                    $this->assertEquals($expected, $result, "Test#$i-$j: $name");
                }
            }
        }
    }

    public function testParseRequestWithUrlRuleCustomNormalizer()
    {
        $manager = new UrlManager([
            'cache' => null,
        ]);
        $request = new Request([
            'hostInfo' => 'http://en.example.com',
            'pathInfo' => 'post/1-a/',
        ]);

        $rule = new UrlRule([
            'pattern' => 'post/<page:\d+>-<tag>',
            'route' => 'post/index',
            'normalizer' => false,
        ]);
        $result = $rule->parseRequest($manager, $request);
        $this->assertFalse($result);

        $rule = new UrlRule([
            'pattern' => 'post/<page:\d+>-<tag>',
            'route' => 'post/index',
            'normalizer' => [
                'class' => 'yii\web\UrlNormalizer',
                'normalizeTrailingSlash' => false,
            ],
        ]);
        $result = $rule->parseRequest($manager, $request);
        $this->assertFalse($result);

        $rule = new UrlRule([
            'pattern' => 'post/<page:\d+>-<tag>',
            'route' => 'post/index',
            'normalizer' => [
                'class' => 'yii\web\UrlNormalizer',
                'normalizeTrailingSlash' => true,
                'action' => null,
            ],
        ]);
        $result = $rule->parseRequest($manager, $request);
        $this->assertEquals(['post/index', ['page' => 1, 'tag' => 'a']], $result);
    }

    public function testToString()
    {
        $suites = $this->getTestsForToString();
        foreach ($suites as $i => $suite) {
            list ($name, $config, $test) = $suite;
            $rule = new UrlRule($config);
            $this->assertEquals($rule->__toString(), $test, "Test#$i: $name");
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
                'empty pattern',
                [
                    'pattern' => '',
                    'route' => 'post/index',
                ],
                [
                    ['post/index', [], ''],
                    ['comment/index', [], false],
                    ['post/index', ['page' => 1], '?page=1'],
                ],
            ],
            [
                'without param',
                [
                    'pattern' => 'posts',
                    'route' => 'post/index',
                ],
                [
                    ['post/index', [], 'posts'],
                    ['comment/index', [], false],
                    ['post/index', ['page' => 1], 'posts?page=1'],
                ],
            ],
            [
                'parsing only',
                [
                    'pattern' => 'posts',
                    'route' => 'post/index',
                    'mode' => UrlRule::PARSING_ONLY,
                ],
                [
                    ['post/index', [], false],
                ],
            ],
            [
                'with param',
                [
                    'pattern' => 'post/<page>',
                    'route' => 'post/index',
                ],
                [
                    ['post/index', [], false],
                    ['comment/index', [], false],
                    ['post/index', ['page' => 1], 'post/1'],
                    ['post/index', ['page' => 1, 'tag' => 'a'], 'post/1?tag=a'],
                ],
            ],
            [
                'with param requirement',
                [
                    'pattern' => 'post/<page:\d+>',
                    'route' => 'post/index',
                ],
                [
                    ['post/index', ['page' => 'abc'], false],
                    ['post/index', ['page' => 1], 'post/1'],
                    ['post/index', ['page' => 1, 'tag' => 'a'], 'post/1?tag=a'],
                ],
            ],
            [
                'with multiple params',
                [
                    'pattern' => 'post/<page:\d+>-<tag>',
                    'route' => 'post/index',
                ],
                [
                    ['post/index', ['page' => '1abc'], false],
                    ['post/index', ['page' => 1], false],
                    ['post/index', ['page' => 1, 'tag' => 'a'], 'post/1-a'],
                ],
            ],
            [
                'multiple params with special chars',
                [
                    'pattern' => 'post/<page-number:\d+>/<per_page:\d+>/<author.login>',
                    'route' => 'post/index',
                ],
                [
                    ['post/index', [], false],
                    ['post/index', ['page-number' => '1', 'per_page' => '25'], false],
                    ['post/index', ['page-number' => '1', 'per_page' => '25', 'author.login' => 'yiiuser'], 'post/1/25/yiiuser'],
                ],
            ],
            [
                'multiple params with leading non-letter chars',
                [
                    'pattern' => 'post/<1page-number:\d+>/<-per_page:\d+>/<_author.login>',
                    'route' => 'post/index',
                ],
                [
                    ['post/index', [], false],
                    ['post/index', ['1page-number' => '1', '-per_page' => '25'], false],
                    ['post/index', ['1page-number' => '1', '-per_page' => '25', '_author.login' => 'yiiuser'], 'post/1/25/yiiuser'],
                ],
            ],
            [
                'with optional param',
                [
                    'pattern' => 'post/<page:\d+>/<tag>',
                    'route' => 'post/index',
                    'defaults' => ['page' => 1],
                ],
                [
                    ['post/index', ['page' => 1], false],
                    ['post/index', ['page' => '1abc', 'tag' => 'a'], false],
                    ['post/index', ['page' => 1, 'tag' => 'a'], 'post/a'],
                    ['post/index', ['page' => 2, 'tag' => 'a'], 'post/2/a'],
                ],
            ],
            [
                'with optional param not in pattern',
                [
                    'pattern' => 'post/<tag>',
                    'route' => 'post/index',
                    'defaults' => ['page' => 1],
                ],
                [
                    ['post/index', ['page' => 1], false],
                    ['post/index', ['page' => '1abc', 'tag' => 'a'], false],
                    ['post/index', ['page' => 2, 'tag' => 'a'], false],
                    ['post/index', ['page' => 1, 'tag' => 'a'], 'post/a'],
                ],
            ],
            [
                'multiple optional params',
                [
                    'pattern' => 'post/<page:\d+>/<tag>/<sort:yes|no>',
                    'route' => 'post/index',
                    'defaults' => ['page' => 1, 'sort' => 'yes'],
                ],
                [
                    ['post/index', ['page' => 1], false],
                    ['post/index', ['page' => '1abc', 'tag' => 'a'], false],
                    ['post/index', ['page' => 1, 'tag' => 'a', 'sort' => 'YES'], false],
                    ['post/index', ['page' => 1, 'tag' => 'a', 'sort' => 'yes'], 'post/a'],
                    ['post/index', ['page' => 2, 'tag' => 'a', 'sort' => 'yes'], 'post/2/a'],
                    ['post/index', ['page' => 2, 'tag' => 'a', 'sort' => 'no'], 'post/2/a/no'],
                    ['post/index', ['page' => 1, 'tag' => 'a', 'sort' => 'no'], 'post/a/no'],
                ],
            ],
            [
                'optional param and required param separated by dashes',
                [
                    'pattern' => 'post/<page:\d+>-<tag>',
                    'route' => 'post/index',
                    'defaults' => ['page' => 1],
                ],
                [
                    ['post/index', ['page' => 1], false],
                    ['post/index', ['page' => '1abc', 'tag' => 'a'], false],
                    ['post/index', ['page' => 1, 'tag' => 'a'], 'post/-a'],
                    ['post/index', ['page' => 2, 'tag' => 'a'], 'post/2-a'],
                ],
            ],
            [
                'optional param at the end',
                [
                    'pattern' => 'post/<tag>/<page:\d+>',
                    'route' => 'post/index',
                    'defaults' => ['page' => 1],
                ],
                [
                    ['post/index', ['page' => 1], false],
                    ['post/index', ['page' => '1abc', 'tag' => 'a'], false],
                    ['post/index', ['page' => 1, 'tag' => 'a'], 'post/a'],
                    ['post/index', ['page' => 2, 'tag' => 'a'], 'post/a/2'],
                ],
            ],
            [
                'consecutive optional params',
                [
                    'pattern' => 'post/<page:\d+>/<tag>',
                    'route' => 'post/index',
                    'defaults' => ['page' => 1, 'tag' => 'a'],
                ],
                [
                    ['post/index', ['page' => 1], false],
                    ['post/index', ['page' => '1abc', 'tag' => 'a'], false],
                    ['post/index', ['page' => 1, 'tag' => 'a'], 'post'],
                    ['post/index', ['page' => 2, 'tag' => 'a'], 'post/2'],
                    ['post/index', ['page' => 1, 'tag' => 'b'], 'post/b'],
                    ['post/index', ['page' => 2, 'tag' => 'b'], 'post/2/b'],
                ],
            ],
            [
                'consecutive optional params separated by dash',
                [
                    'pattern' => 'post/<page:\d+>-<tag>',
                    'route' => 'post/index',
                    'defaults' => ['page' => 1, 'tag' => 'a'],
                ],
                [
                    ['post/index', ['page' => 1], false],
                    ['post/index', ['page' => '1abc', 'tag' => 'a'], false],
                    ['post/index', ['page' => 1, 'tag' => 'a'], 'post/-'],
                    ['post/index', ['page' => 1, 'tag' => 'b'], 'post/-b'],
                    ['post/index', ['page' => 2, 'tag' => 'a'], 'post/2-'],
                    ['post/index', ['page' => 2, 'tag' => 'b'], 'post/2-b'],
                ],
            ],
            [
                'optional params - example from guide',
                [
                    'pattern' => 'posts/<page:\d+>/<tag>',
                    'route' => 'post/index',
                    'defaults' => ['page' => 1, 'tag' => ''],
                ],
                [
                    ['post/index', ['page' => 1, 'tag' => ''], 'posts'],
                    ['post/index', ['page' => 2, 'tag' => ''], 'posts/2'],
                    ['post/index', ['page' => 2, 'tag' => 'news'], 'posts/2/news'],
                    ['post/index', ['page' => 1, 'tag' => 'news'], 'posts/news'],
                    // allow skip empty params on URL creation
                    ['post/index', [], false],
                    ['post/index', ['tag' => ''], false],
                    ['post/index', ['page' => 1], 'posts'],
                    ['post/index', ['page' => 2], 'posts/2'],
                ],
            ],
            [
                'required params',
                [
                    'pattern' => 'about-me',
                    'route' => 'site/page',
                    'defaults' => ['id' => 1],
                ],
                [
                    ['site/page', ['id' => 1], 'about-me'],
                    ['site/page', ['id' => 2], false],
                ],
            ],
            [
                'required default param',
                [
                    'pattern' => '',
                    'route' => 'site/home',
                    'defaults' => ['lang' => 'en'],
                ],
                [
                    ['site/home', ['lang' => 'en'], ''],
                    ['site/home', ['lang' => ''], false],
                    ['site/home', [], false],
                ],
            ],
            [
                'required default empty param',
                [
                    'pattern' => '',
                    'route' => 'site/home',
                    'defaults' => ['lang' => ''],
                ],
                [
                    ['site/home', ['lang' => ''], ''],
                    ['site/home', ['lang' => 'en'], false],
                    ['site/home', [], false],
                ],
            ],
            [
                'route has parameters',
                [
                    'pattern' => '<controller>/<action>',
                    'route' => '<controller>/<action>',
                    'defaults' => [],
                ],
                [
                    ['post/index', ['page' => 1], 'post/index?page=1'],
                    ['module/post/index', [], false],
                ],
            ],
            [
                'route has parameters with regex',
                [
                    'pattern' => '<controller:post|comment>/<action>',
                    'route' => '<controller>/<action>',
                    'defaults' => [],
                ],
                [
                    ['post/index', ['page' => 1], 'post/index?page=1'],
                    ['comment/index', ['page' => 1], 'comment/index?page=1'],
                    ['test/index', ['page' => 1], false],
                    ['post', [], false],
                    ['module/post/index', [], false],
                    ['post/index', ['controller' => 'comment'], 'post/index?controller=comment'],
                ],
            ],
            [
                'route has default parameter',
                [
                    'pattern' => '<controller:post|comment>/<action>',
                    'route' => '<controller>/<action>',
                    'defaults' => ['action' => 'index'],
                ],
                [
                    ['post/view', ['page' => 1], 'post/view?page=1'],
                    ['comment/view', ['page' => 1], 'comment/view?page=1'],
                    ['test/view', ['page' => 1], false],
                    ['test/index', ['page' => 1], false],
                    ['post/index', ['page' => 1], 'post?page=1'],
                ],
            ],
            [
                'empty pattern with suffix',
                [
                    'pattern' => '',
                    'route' => 'post/index',
                    'suffix' => '.html',
                ],
                [
                    ['post/index', [], ''],
                    ['comment/index', [], false],
                    ['post/index', ['page' => 1], '?page=1'],
                ],
            ],
            [
                'regular pattern with suffix',
                [
                    'pattern' => 'posts',
                    'route' => 'post/index',
                    'suffix' => '.html',
                ],
                [
                    ['post/index', [], 'posts.html'],
                    ['comment/index', [], false],
                    ['post/index', ['page' => 1], 'posts.html?page=1'],
                ],
            ],
            [
                'empty pattern with slash suffix',
                [
                    'pattern' => '',
                    'route' => 'post/index',
                    'suffix' => '/',
                ],
                [
                    ['post/index', [], ''],
                    ['comment/index', [], false],
                    ['post/index', ['page' => 1], '?page=1'],
                ],
            ],
            [
                'regular pattern with slash suffix',
                [
                    'pattern' => 'posts',
                    'route' => 'post/index',
                    'suffix' => '/',
                ],
                [
                    ['post/index', [], 'posts/'],
                    ['comment/index', [], false],
                    ['post/index', ['page' => 1], 'posts/?page=1'],
                ],
            ],
            [
                'with host info',
                [
                    'pattern' => 'post/<page:\d+>/<tag>',
                    'route' => 'post/index',
                    'defaults' => ['page' => 1],
                    'host' => 'http://<lang:en|fr>.example.com',
                ],
                [
                    ['post/index', ['page' => 1, 'tag' => 'a'], false],
                    ['post/index', ['page' => 1, 'tag' => 'a', 'lang' => 'en'], 'http://en.example.com/post/a'],
                ],
            ],
            [
                'with host info in pattern',
                [
                    'pattern' => 'http://<lang:en|fr>.example.com/post/<page:\d+>/<tag>',
                    'route' => 'post/index',
                    'defaults' => ['page' => 1],
                ],
                [
                    ['post/index', ['page' => 1, 'tag' => 'a'], false],
                    ['post/index', ['page' => 1, 'tag' => 'a', 'lang' => 'en'], 'http://en.example.com/post/a'],
                ],
            ],
            [
                'with relative host info',
                [
                    'pattern' => 'post/<page:\d+>/<tag>',
                    'route' => 'post/index',
                    'defaults' => ['page' => 1],
                    'host' => '//<lang:en|fr>.example.com',
                ],
                [
                    ['post/index', ['page' => 1, 'tag' => 'a'], false],
                    ['post/index', ['page' => 1, 'tag' => 'a', 'lang' => 'en'], '//en.example.com/post/a'],
                ],
            ],
            [
                'with relative host info in pattern',
                [
                    'pattern' => '//<lang:en|fr>.example.com/post/<page:\d+>/<tag>',
                    'route' => 'post/index',
                    'defaults' => ['page' => 1],
                ],
                [
                    ['post/index', ['page' => 1, 'tag' => 'a'], false],
                    ['post/index', ['page' => 1, 'tag' => 'a', 'lang' => 'en'], '//en.example.com/post/a'],
                ],
            ],
            [
                'with unicode',
                [
                    'pattern' => '/blog/search/<tag:[a-zA-Zа-яА-Я0-9\_\+\-]{1,255}>',
                    'route' => 'blog/search',
                ],
                [
                    ['blog/search', ['tag' => 'метра'], 'blog/search/%D0%BC%D0%B5%D1%82%D1%80%D0%B0'],
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
        //     expected result (in format [route, params]) with normalization disabled, or false if the rule doesn't apply
        //     expected result if noralizer is enabled, or not set if result should be the same as without normalizer
        return [
            [
                'empty pattern',
                [
                    'pattern' => '',
                    'route' => 'post/index',
                ],
                [
                    ['', ['post/index', []]],
                    ['a', false],
                ],
            ],
            [
                'without param',
                [
                    'pattern' => 'posts',
                    'route' => 'post/index',
                ],
                [
                    ['posts', ['post/index', []]],
                    ['a', false],
                ],
            ],
            [
                'with dot', // https://github.com/yiisoft/yii/issues/2945
                [
                    'pattern' => 'posts.html',
                    'route' => 'post/index',
                ],
                [
                    ['posts.html', ['post/index', []]],
                    ['postsahtml', false],
                ],
            ],
            [
                'creation only',
                [
                    'pattern' => 'posts',
                    'route' => 'post/index',
                    'mode' => UrlRule::CREATION_ONLY,
                ],
                [
                    ['posts', false],
                ],
            ],
            [
                'with param',
                [
                    'pattern' => 'post/<page>',
                    'route' => 'post/index',
                ],
                [
                    ['post/1', ['post/index', ['page' => '1']]],
                    ['post/a', ['post/index', ['page' => 'a']]],
                    ['post', false],
                    ['posts', false],
                ],
            ],
            [
                'with param requirement',
                [
                    'pattern' => 'post/<page:\d+>',
                    'route' => 'post/index',
                ],
                [
                    ['post/1', ['post/index', ['page' => '1']]],
                    ['post/a', false],
                    ['post/1/a', false],
                ],
            ],
            [
                'with multiple params',
                [
                    'pattern' => 'post/<page:\d+>-<tag>',
                    'route' => 'post/index',
                ],
                [
                    ['post/1-a', ['post/index', ['page' => '1', 'tag' => 'a']]],
                    ['post/a', false],
                    ['post/1', false],
                    ['post/1/a', false],
                ],
            ],
            [
                'multiple params with special chars',
                [
                    'pattern' => 'post/<page-number:\d+>/<per_page:\d+>/<author.login>',
                    'route' => 'post/index',
                ],
                [
                    ['post/1/25/yiiuser', ['post/index', ['page-number' => '1', 'per_page' => '25', 'author.login' => 'yiiuser']]],
                    ['post/1/25', false],
                    ['post', false],
                ],
            ],
            [
                'multiple params with special chars',
                [
                    'pattern' => 'post/<1page-number:\d+>/<-per_page:\d+>/<_author.login>',
                    'route' => 'post/index',
                ],
                [
                    ['post/1/25/yiiuser', ['post/index', ['1page-number' => '1', '-per_page' => '25', '_author.login' => 'yiiuser']]],
                    ['post/1/25', false],
                    ['post', false],
                ],
            ],
            [
                'with optional param',
                [
                    'pattern' => 'post/<page:\d+>/<tag>',
                    'route' => 'post/index',
                    'defaults' => ['page' => 1],
                ],
                [
                    ['post/1/a', ['post/index', ['page' => '1', 'tag' => 'a']]],
                    ['post/2/a', ['post/index', ['page' => '2', 'tag' => 'a']]],
                    ['post/a', ['post/index', ['page' => '1', 'tag' => 'a']]],
                    ['post/1', ['post/index', ['page' => '1', 'tag' => '1']]],
                ],
            ],
            [
                'with optional param not in pattern',
                [
                    'pattern' => 'post/<tag>',
                    'route' => 'post/index',
                    'defaults' => ['page' => 1],
                ],
                [
                    ['post/a', ['post/index', ['page' => '1', 'tag' => 'a']]],
                    ['post/1', ['post/index', ['page' => '1', 'tag' => '1']]],
                    ['post', false],
                ],
            ],
            [
                'multiple optional params',
                [
                    'pattern' => 'post/<page:\d+>/<tag>/<sort:yes|no>',
                    'route' => 'post/index',
                    'defaults' => ['page' => 1, 'sort' => 'yes'],
                ],
                [
                    ['post/1/a/yes', ['post/index', ['page' => '1', 'tag' => 'a', 'sort' => 'yes']]],
                    ['post/1/a/no', ['post/index', ['page' => '1', 'tag' => 'a', 'sort' => 'no']]],
                    ['post/2/a/no', ['post/index', ['page' => '2', 'tag' => 'a', 'sort' => 'no']]],
                    ['post/2/a', ['post/index', ['page' => '2', 'tag' => 'a', 'sort' => 'yes']]],
                    ['post/a/no', ['post/index', ['page' => '1', 'tag' => 'a', 'sort' => 'no']]],
                    ['post/a', ['post/index', ['page' => '1', 'tag' => 'a', 'sort' => 'yes']]],
                    ['post', false],
                ],
            ],
            [
                'optional param and required param separated by dashes',
                [
                    'pattern' => 'post/<page:\d+>-<tag>',
                    'route' => 'post/index',
                    'defaults' => ['page' => 1],
                ],
                [
                    ['post/1-a', ['post/index', ['page' => '1', 'tag' => 'a']]],
                    ['post/2-a', ['post/index', ['page' => '2', 'tag' => 'a']]],
                    ['post/-a', ['post/index', ['page' => '1', 'tag' => 'a']]],
                    ['post/a', false],
                    ['post-a', false],
                ],
            ],
            [
                'optional param at the end',
                [
                    'pattern' => 'post/<tag>/<page:\d+>',
                    'route' => 'post/index',
                    'defaults' => ['page' => 1],
                ],
                [
                    ['post/a/1', ['post/index', ['page' => '1', 'tag' => 'a']]],
                    ['post/a/2', ['post/index', ['page' => '2', 'tag' => 'a']]],
                    ['post/a', ['post/index', ['page' => '1', 'tag' => 'a']]],
                    ['post/2', ['post/index', ['page' => '1', 'tag' => '2']]],
                    ['post', false],
                ],
            ],
            [
                'consecutive optional params',
                [
                    'pattern' => 'post/<page:\d+>/<tag>',
                    'route' => 'post/index',
                    'defaults' => ['page' => 1, 'tag' => 'a'],
                ],
                [
                    ['post/2/b', ['post/index', ['page' => '2', 'tag' => 'b']]],
                    ['post/2', ['post/index', ['page' => '2', 'tag' => 'a']]],
                    ['post', ['post/index', ['page' => '1', 'tag' => 'a']]],
                    ['post/b', ['post/index', ['page' => '1', 'tag' => 'b']]],
                    ['post//b', false, ['post/index', ['page' => '1', 'tag' => 'b']]],
                ],
            ],
            [
                'consecutive optional params separated by dash',
                [
                    'pattern' => 'post/<page:\d+>-<tag>',
                    'route' => 'post/index',
                    'defaults' => ['page' => 1, 'tag' => 'a'],
                ],
                [
                    ['post/2-b', ['post/index', ['page' => '2', 'tag' => 'b']]],
                    ['post/2-', ['post/index', ['page' => '2', 'tag' => 'a']]],
                    ['post/-b', ['post/index', ['page' => '1', 'tag' => 'b']]],
                    ['post/-', ['post/index', ['page' => '1', 'tag' => 'a']]],
                    ['post', false],
                ],
            ],
            [
                'route has parameters',
                [
                    'pattern' => '<controller>/<action>',
                    'route' => '<controller>/<action>',
                    'defaults' => [],
                ],
                [
                    ['post/index', ['post/index', []]],
                    ['module/post/index', false],
                ],
            ],
            [
                'route has parameters with regex',
                [
                    'pattern' => '<controller:post|comment>/<action>',
                    'route' => '<controller>/<action>',
                    'defaults' => [],
                ],
                [
                    ['post/index', ['post/index', []]],
                    ['comment/index', ['comment/index', []]],
                    ['test/index', false],
                    ['post', false],
                    ['module/post/index', false],
                ],
            ],
            [
                'route has default parameter',
                [
                    'pattern' => '<controller:post|comment>/<action>',
                    'route' => '<controller>/<action>',
                    'defaults' => ['action' => 'index'],
                ],
                [
                    ['post/view', ['post/view', []]],
                    ['comment/view', ['comment/view', []]],
                    ['test/view', false],
                    ['post', ['post/index', []]],
                    ['posts', false],
                    ['test', false],
                    ['index', false],
                ],
            ],
            [
                'empty pattern with suffix',
                [
                    'pattern' => '',
                    'route' => 'post/index',
                    'suffix' => '.html',
                ],
                [
                    ['', ['post/index', []]],
                    ['.html', false],
                    ['a.html', false],
                ],
            ],
            [
                'regular pattern with suffix',
                [
                    'pattern' => 'posts',
                    'route' => 'post/index',
                    'suffix' => '.html',
                ],
                [
                    ['posts.html', ['post/index', []]],
                    ['posts', false],
                    ['posts.HTML', false],
                    ['a.html', false],
                    ['a', false],
                ],
            ],
            [
                'empty pattern with slash suffix',
                [
                    'pattern' => '',
                    'route' => 'post/index',
                    'suffix' => '/',
                ],
                [
                    ['', ['post/index', []]],
                    ['a', false],
                ],
            ],
            [
                'regular pattern with slash suffix',
                [
                    'pattern' => 'posts',
                    'route' => 'post/index',
                    'suffix' => '/',
                ],
                [
                    ['posts/', ['post/index', []]],
                    ['posts', false, ['post/index', []]],
                    ['a', false],
                ],
            ],
            [
                'with host info',
                [
                    'pattern' => 'post/<page:\d+>',
                    'route' => 'post/index',
                    'host' => 'http://<lang:en|fr>.example.com',
                ],
                [
                    ['post/1', ['post/index', ['page' => '1', 'lang' => 'en']]],
                    ['post/a', false],
                    ['post/1/a', false],
                ],
            ],
            [
                'with host info in pattern',
                [
                    'pattern' => 'http://<lang:en|fr>.example.com/post/<page:\d+>',
                    'route' => 'post/index',
                ],
                [
                    ['post/1', ['post/index', ['page' => '1', 'lang' => 'en']]],
                    ['post/a', false],
                    ['post/1/a', false],
                ],
            ],
            [
                'host info + defaults', // https://github.com/yiisoft/yii2/issues/6871
                [
                    'pattern' => 'http://en.example.com/<page>',
                    'route' => 'post/index',
                    'defaults' => ['page' => 1],
                ],
                [
                    ['', ['post/index', ['page' => 1]]],
                    ['2', ['post/index', ['page' => 2]]],
                ],
            ],
            [
                'with relative host info',
                [
                    'pattern' => 'post/<page:\d+>',
                    'route' => 'post/index',
                    'host' => '//<lang:en|fr>.example.com',
                ],
                [
                    ['post/1', ['post/index', ['page' => '1', 'lang' => 'en']]],
                    ['post/a', false],
                    ['post/1/a', false],
                ],
            ],
            [
                'with relative host info in pattern',
                [
                    'pattern' => '//<lang:en|fr>.example.com/post/<page:\d+>',
                    'route' => 'post/index',
                ],
                [
                    ['post/1', ['post/index', ['page' => '1', 'lang' => 'en']]],
                    ['post/a', false],
                    ['post/1/a', false],
                ],
            ],
        ];
    }

    protected function getTestsForToString()
    {
        return [
            [
                'empty pattern',
                [
                    'pattern' => '',
                    'route' => 'post/index',
                ],
                '/'
            ],
            [
                'multiple params with special chars',
                [
                    'pattern' => 'post/<page-number:\d+>/<per_page:\d+>/<author.login>',
                    'route' => 'post/index',
                ],
                'post/<page-number:\d+>/<per_page:\d+>/<author.login>'
            ],
            [
                'with host info',
                [
                    'pattern' => 'post/<page:\d+>/<tag>',
                    'route' => 'post/index',
                    'defaults' => ['page' => 1],
                    'host' => 'http://<lang:en|fr>.example.com',
                ],
                'http://<lang:en|fr>.example.com/post/<page:\d+>/<tag>'
            ],
            [
                'with host info in pattern',
                [
                    'pattern' => 'http://<lang:en|fr>.example.com/post/<page:\d+>/<tag>',
                    'route' => 'post/index',
                    'defaults' => ['page' => 1],
                ],
                'http://<lang:en|fr>.example.com/post/<page:\d+>/<tag>'
            ],
            [
                'with verb',
                [
                    'verb' => ['POST'],
                    'pattern' => 'post/<id:\d+>',
                    'route' => 'post/index'
                ],
                'POST post/<id:\d+>'
            ],
            [
                'with verbs',
                [
                    'verb' => ['PUT', 'POST'],
                    'pattern' => 'post/<id:\d+>',
                    'route' => 'post/index'
                ],
                'PUT,POST post/<id:\d+>'
            ],


        ];
    }
}
