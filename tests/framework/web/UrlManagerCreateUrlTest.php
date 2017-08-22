<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use Yii;
use yii\caching\ArrayCache;
use yii\web\UrlManager;
use yii\web\UrlRule;
use yiiunit\framework\web\stubs\CachedUrlRule;
use yiiunit\TestCase;

/**
 * This class implements the tests for URL creation with "pretty" url format.
 *
 * See [[UrlManagerTest]] for tests with "default" URL format.
 * See [[UrlManagerParseUrlTest]] for url parsing with "pretty" URL format.
 *
 * Behavior of UrlManager::createUrl() for the "pretty" URL format varies among the following options:
 *  - show script name = true / false
 *  - rules format
 *    - key => value
 *    - array config
 *
 * The following features are tested:
 *  - route only Url::to(['post/index']);
 *  - with params Url::to(['post/view', 'id' => 100]);
 *  - with anchor Url::to(['post/view', 'id' => 100, '#' => 'content']);
 *  - named parameters
 *    - as query params
 *    - as controller/actions '<controller:(post|comment)>/<id:\d+>/<action:(update|delete)>' => '<controller>/<action>',
 *  - Rules with Server Names
 *    - with protocol (TODO)
 *    - without protocol i.e protocol relative, see https://github.com/yiisoft/yii2/pull/12697 (TODO)
 *    - with parameters
 *  - with suffix
 *  - with default values
 *  - with HTTP methods (TODO)
 *  - absolute/relative
 *
 *  - Adding rules dynamically (TODO)
 *  - Test custom rules that only implement the interface (TODO)
 *
 * NOTE: if a test is added here, you probably also need to add one in UrlManagerParseUrlTest.
 *
 * @group web
 */
class UrlManagerCreateUrlTest extends TestCase
{
    protected function getUrlManager($config = [], $showScriptName = true)
    {
        // in this test class, all tests have enablePrettyUrl enabled.
        $config['enablePrettyUrl'] = true;

        // set default values if they are not set
        $config = array_merge([
            'baseUrl' => '',
            'scriptUrl' => '/index.php',
            'hostInfo' => 'http://www.example.com',
            'cache' => null,
            'showScriptName' => $showScriptName,
        ], $config);

        return new UrlManager($config);
    }


    public function variationsProvider()
    {
        $baseUrlConfig = [
            'baseUrl' => '/test',
            'scriptUrl' => '/test/index.php',
        ];

        return [
            // method name, $showScriptName, expected URL prefix
            ['createUrl', true, '/index.php', []],
            ['createUrl', false, '', []],
            ['createAbsoluteUrl', true, 'http://www.example.com/index.php', []],
            ['createAbsoluteUrl', false, 'http://www.example.com', []],

            // with different baseUrl
            ['createUrl', true, '/test/index.php', $baseUrlConfig],
            ['createUrl', false, '/test', $baseUrlConfig],
            ['createAbsoluteUrl', true, 'http://www.example.com/test/index.php', $baseUrlConfig],
            ['createAbsoluteUrl', false, 'http://www.example.com/test', $baseUrlConfig],
        ];
    }

    /**
     * Test createUrl() and createAbsoluteUrl()
     * with varying $showScriptName
     * without rules.
     *
     * @dataProvider variationsProvider
     * @param string $method
     * @param bool $showScriptName
     * @param string $prefix
     * @param array $config
     */
    public function testWithoutRules($method, $showScriptName, $prefix, $config)
    {
        $manager = $this->getUrlManager($config, $showScriptName);

        $url = $manager->$method('post/view');
        $this->assertSame("$prefix/post/view", $url);
        $url = $manager->$method(['post/view']);
        $this->assertSame("$prefix/post/view", $url);

        $url = $manager->$method(['post/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertSame("$prefix/post/view?id=1&title=sample+post", $url);

        $url = $manager->$method(['post/view', '#' => 'testhash']);
        $this->assertSame("$prefix/post/view#testhash", $url);

        $url = $manager->$method(['post/view', 'id' => 1, 'title' => 'sample post', '#' => 'testhash']);
        $this->assertSame("$prefix/post/view?id=1&title=sample+post#testhash", $url);

        // with defaultAction
        $url = $manager->$method(['/post', 'page' => 1]);
        $this->assertSame("$prefix/post?page=1", $url);
    }

    /**
     * Test createUrl() and createAbsoluteUrl().
     *
     * - with varying $showScriptName,
     * - without rules,
     * - with UrlManager::$suffix.
     *
     * @dataProvider variationsProvider
     * @param string $method
     * @param bool $showScriptName
     * @param string $prefix
     * @param array $config
     */
    public function testWithoutRulesWithSuffix($method, $showScriptName, $prefix, $config)
    {
        $config['suffix'] = '.html';
        $manager = $this->getUrlManager($config, $showScriptName);

        $url = $manager->$method('post/view');
        $this->assertSame("$prefix/post/view.html", $url);
        $url = $manager->$method(['post/view']);
        $this->assertSame("$prefix/post/view.html", $url);

        $url = $manager->$method(['post/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertSame("$prefix/post/view.html?id=1&title=sample+post", $url);

        $url = $manager->$method(['post/view', '#' => 'testhash']);
        $this->assertSame("$prefix/post/view.html#testhash", $url);

        $url = $manager->$method(['post/view', 'id' => 1, 'title' => 'sample post', '#' => 'testhash']);
        $this->assertSame("$prefix/post/view.html?id=1&title=sample+post#testhash", $url);

        // with defaultAction
        $url = $manager->$method(['/post', 'page' => 1]);
        $this->assertSame("$prefix/post.html?page=1", $url);


        // test suffix '/' as it may be trimmed
        $config['suffix'] = '/';
        $manager = $this->getUrlManager($config, $showScriptName);

        $url = $manager->$method('post/view');
        $this->assertSame("$prefix/post/view/", $url);
        $url = $manager->$method(['post/view']);
        $this->assertSame("$prefix/post/view/", $url);

        $url = $manager->$method(['post/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertSame("$prefix/post/view/?id=1&title=sample+post", $url);

        $url = $manager->$method(['post/view', '#' => 'testhash']);
        $this->assertSame("$prefix/post/view/#testhash", $url);

        $url = $manager->$method(['post/view', 'id' => 1, 'title' => 'sample post', '#' => 'testhash']);
        $this->assertSame("$prefix/post/view/?id=1&title=sample+post#testhash", $url);

        // with defaultAction
        $url = $manager->$method(['/post', 'page' => 1]);
        $this->assertSame("$prefix/post/?page=1", $url);
    }

    /**
     * Test createUrl() and createAbsoluteUrl()
     * with varying $showScriptName
     * with simple rules.
     *
     * @dataProvider variationsProvider
     * @param string $method
     * @param bool $showScriptName
     * @param string $prefix
     * @param array $config
     */
    public function testSimpleRules($method, $showScriptName, $prefix, $config)
    {
        $config['rules'] = [
            'post/<id:\d+>' => 'post/view',
            'posts' => 'post/index',
            'book/<id:\d+>/<title>' => 'book/view',
        ];
        $manager = $this->getUrlManager($config, $showScriptName);

        // does not match any rule
        $url = $manager->$method(['post/view']);
        $this->assertSame("$prefix/post/view", $url);

        // with defaultAction also does not match any rule
        $url = $manager->$method(['/post', 'page' => 1]);
        $this->assertSame("$prefix/post?page=1", $url);

        // match first rule
        $url = $manager->$method(['post/view', 'id' => 1]);
        $this->assertSame("$prefix/post/1", $url);

        // match first rule with additional param
        $url = $manager->$method(['post/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertSame("$prefix/post/1?title=sample+post", $url);
        // match first rule with hash
        $url = $manager->$method(['post/view', 'id' => 1, '#' => 'testhash']);
        $this->assertSame("$prefix/post/1#testhash", $url);

        // match second rule
        $url = $manager->$method(['post/index']);
        $this->assertSame("$prefix/posts", $url);

        // match second rule with additional param
        $url = $manager->$method(['post/index', 'category' => 'test']);
        $this->assertSame("$prefix/posts?category=test", $url);

        // match third rule, ensure encoding of params
        $url = $manager->$method(['book/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertSame("$prefix/book/1/sample+post", $url);
    }

    /**
     * Test createUrl() and createAbsoluteUrl().
     *
     * - with varying $showScriptName,
     * - with simple rules,
     * - with UrlManager::$suffix.
     *
     * @dataProvider variationsProvider
     * @param string $method
     * @param bool $showScriptName
     * @param string $prefix
     * @param array $config
     */
    public function testSimpleRulesWithSuffix($method, $showScriptName, $prefix, $config)
    {
        $config['rules'] = [
            'post/<id:\d+>' => 'post/view',
            'posts' => 'post/index',
            'book/<id:\d+>/<title>' => 'book/view',
        ];
        $config['suffix'] = '/';
        $manager = $this->getUrlManager($config, $showScriptName);

        // does not match any rule
        $url = $manager->$method(['post/view']);
        $this->assertSame("$prefix/post/view/", $url);

        // with defaultAction also does not match any rule
        $url = $manager->$method(['/post', 'page' => 1]);
        $this->assertSame("$prefix/post/?page=1", $url);

        // match first rule
        $url = $manager->$method(['post/view', 'id' => 1]);
        $this->assertSame("$prefix/post/1/", $url);

        // match first rule with additional param
        $url = $manager->$method(['post/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertSame("$prefix/post/1/?title=sample+post", $url);
        // match first rule with hash
        $url = $manager->$method(['post/view', 'id' => 1, '#' => 'testhash']);
        $this->assertSame("$prefix/post/1/#testhash", $url);

        // match second rule
        $url = $manager->$method(['post/index']);
        $this->assertSame("$prefix/posts/", $url);

        // match second rule with additional param
        $url = $manager->$method(['post/index', 'category' => 'test']);
        $this->assertSame("$prefix/posts/?category=test", $url);

        // match third rule, ensure encoding of params
        $url = $manager->$method(['book/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertSame("$prefix/book/1/sample+post/", $url);
    }

    /**
     * Test createUrl() and createAbsoluteUrl()
     * with varying $showScriptName
     * with rules that have varadic controller/actions.
     *
     * @dataProvider variationsProvider
     * @param string $method
     * @param bool $showScriptName
     * @param string $prefix
     * @param array $config
     */
    public function testControllerActionParams($method, $showScriptName, $prefix, $config)
    {
        $config['rules'] = [
            '<controller>/<id:\d+>' => '<controller>/view',
            '<controller>s' => '<controller>/index',
            '<controller>/default' => '<controller>', // rule to match default action
            '<controller>/test/<action:\w+>' => '<controller>/<action>',
        ];
        $manager = $this->getUrlManager($config, $showScriptName);

        // match last rule
        $url = $manager->$method(['post/view']);
        $this->assertSame("$prefix/post/test/view", $url);

        // defaultAction should match third rule
        $url = $manager->$method(['/post/']);
        $this->assertSame("$prefix/post/default", $url);
        $url = $manager->$method(['/post']);
        $this->assertSame("$prefix/post/default", $url);

        // match first rule
        $url = $manager->$method(['post/view', 'id' => 1]);
        $this->assertSame("$prefix/post/1", $url);

        // match first rule with additional param
        $url = $manager->$method(['post/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertSame("$prefix/post/1?title=sample+post", $url);
        // match first rule with hash
        $url = $manager->$method(['post/view', 'id' => 1, '#' => 'testhash']);
        $this->assertSame("$prefix/post/1#testhash", $url);

        // match second rule
        $url = $manager->$method(['post/index']);
        $this->assertSame("$prefix/posts", $url);

        // match second rule with additional param
        $url = $manager->$method(['post/index', 'category' => 'test']);
        $this->assertSame("$prefix/posts?category=test", $url);
    }

    /**
     * Test createUrl() and createAbsoluteUrl()
     * with varying $showScriptName
     * with rules that have default values for parameters.
     *
     * @dataProvider variationsProvider
     * @param string $method
     * @param bool $showScriptName
     * @param string $prefix
     * @param array $config
     */
    public function testRulesWithDefaultParams($method, $showScriptName, $prefix, $config)
    {
        $config['rules'] = [
            [
                'pattern' => '',
                'route' => 'frontend/page/view',
                'defaults' => ['slug' => 'index'],
            ],
            'page/<slug>' => 'frontend/page/view',
            [
                'pattern' => '<language>',
                'route' => 'site/index',
                'defaults' => [
                    'language' => 'en',
                ],
            ],
        ];
        $manager = $this->getUrlManager($config, $showScriptName);

        // match first rule
        $url = $manager->$method(['frontend/page/view', 'slug' => 'index']);
        $this->assertSame("$prefix/", $url);

        // match first rule with additional param
        $url = $manager->$method(['frontend/page/view', 'slug' => 'index', 'sort' => 'name']);
        $this->assertSame("$prefix/?sort=name", $url);

        // match first rule with hash
        $url = $manager->$method(['frontend/page/view', 'slug' => 'index', '#' => 'testhash']);
        $this->assertSame("$prefix/#testhash", $url);

        // match second rule
        $url = $manager->$method(['frontend/page/view', 'slug' => 'services']);
        $this->assertSame("$prefix/page/services", $url);

        // match second rule with additional param
        $url = $manager->$method(['frontend/page/view', 'slug' => 'services', 'sort' => 'name']);
        $this->assertSame("$prefix/page/services?sort=name", $url);

        // match second rule with hash
        $url = $manager->$method(['frontend/page/view', 'slug' => 'services', '#' => 'testhash']);
        $this->assertSame("$prefix/page/services#testhash", $url);

        // match third rule
        $url = $manager->$method(['site/index', 'language' => 'en']);
        $this->assertSame("$prefix/", $url);
        $url = $manager->$method(['site/index', 'language' => 'de']);
        $this->assertSame("$prefix/de", $url);

        // match third rule with additional param
        $url = $manager->$method(['site/index', 'language' => 'en', 'sort' => 'name']);
        $this->assertSame("$prefix/?sort=name", $url);
        $url = $manager->$method(['site/index', 'language' => 'de', 'sort' => 'name']);
        $this->assertSame("$prefix/de?sort=name", $url);

        // match first rule with hash
        $url = $manager->$method(['site/index', 'language' => 'en', '#' => 'testhash']);
        $this->assertSame("$prefix/#testhash", $url);
        $url = $manager->$method(['site/index', 'language' => 'de', '#' => 'testhash']);
        $this->assertSame("$prefix/de#testhash", $url);

        // matches none of the rules
        $url = $manager->$method(['frontend/page/view']);
        $this->assertSame("$prefix/frontend/page/view", $url);
    }

    /**
     * Test createUrl() and createAbsoluteUrl()
     * with varying $showScriptName
     * with empty or null parameters.
     *
     * @dataProvider variationsProvider
     * @see https://github.com/yiisoft/yii2/issues/10935
     * @param string $method
     * @param bool $showScriptName
     * @param string $prefix
     * @param array $config
     */
    public function testWithNullParams($method, $showScriptName, $prefix, $config)
    {
        $config['rules'] = [
            '<param1>/<param2>' => 'site/index',
            '<param1>' => 'site/index',
        ];
        $manager = $this->getUrlManager($config, $showScriptName);

        // match first rule
        $url = $manager->$method(['site/index', 'param1' => 111, 'param2' => 222]);
        $this->assertSame("$prefix/111/222", $url);
        $url = $manager->$method(['site/index', 'param1' => 112, 'param2' => 222]);
        $this->assertSame("$prefix/112/222", $url);

        // match second rule
        $url = $manager->$method(['site/index', 'param1' => 111, 'param2' => null]);
        $this->assertSame("$prefix/111", $url);
        $url = $manager->$method(['site/index', 'param1' => 123, 'param2' => null]);
        $this->assertSame("$prefix/123", $url);

        // match none of the rules
        $url = $manager->$method(['site/index', 'param1' => null, 'param2' => 111]);
        $this->assertSame("$prefix/site/index?param2=111", $url);
        $url = $manager->$method(['site/index', 'param1' => null, 'param2' => 123]);
        $this->assertSame("$prefix/site/index?param2=123", $url);
    }


    /**
     * Test createUrl() and createAbsoluteUrl()
     * with varying $showScriptName
     * with empty pattern.
     *
     * @dataProvider variationsProvider
     * @see https://github.com/yiisoft/yii2/issues/6717
     * @param string $method
     * @param bool $showScriptName
     * @param string $prefix
     * @param array $config
     */
    public function testWithEmptyPattern($method, $showScriptName, $prefix, $config)
    {
        $assertations = function ($manager) use ($method, $prefix) {
            // match first rule
            $url = $manager->$method(['front/site/index']);
            $this->assertSame("$prefix/", $url);
            $url = $manager->$method(['/front/site/index']);
            $this->assertSame("$prefix/", $url);

            // match first rule with additional parameter
            $url = $manager->$method(['front/site/index', 'page' => 1]);
            $this->assertSame("$prefix/?page=1", $url);
            $url = $manager->$method(['/front/site/index', 'page' => 1]);
            $this->assertSame("$prefix/?page=1", $url);

            // no match
            $url = $manager->$method(['']);
            $this->assertSame("$prefix/", $url);
            $url = $manager->$method(['/']);
            $this->assertSame("$prefix/", $url);
        };

        // normal rule
        $config['rules'] = [
            '' => 'front/site/index',
        ];
        $manager = $this->getUrlManager($config, $showScriptName);
        $assertations($manager);

        // rule prefixed with /
        $config['rules'] = [
            '' => '/front/site/index',
        ];
        $manager = $this->getUrlManager($config, $showScriptName);
        $assertations($manager);

        // with suffix
        $config['rules'] = [
            '' => 'front/site/index',
        ];
        $config['suffix'] = '/';
        $manager = $this->getUrlManager($config, $showScriptName);
        $assertations($manager);
    }


    public function absolutePatternsVariations()
    {
        $baseUrlConfig = [
            'baseUrl' => '/test',
            'scriptUrl' => '/test/index.php',
        ];

        return [
            // $showScriptName, expected URL prefix
            [true, '/index.php', []],
            [false, '', []],

            // with different baseUrl
            [true, '/test/index.php', $baseUrlConfig],
            [false, '/test', $baseUrlConfig],
        ];
    }

    /**
     * Test rules that have host info in the patterns.
     * @dataProvider absolutePatternsVariations
     * @param bool $showScriptName
     * @param string $prefix
     * @param array $config
     */
    public function testAbsolutePatterns($showScriptName, $prefix, $config)
    {
        $config['rules'] = [
            [
                'pattern' => 'post/<id>/<title>',
                'route' => 'post/view',
                'host' => 'http://<lang:en|fr>.example.com',
            ],
            // note: baseUrl is not included in the pattern
            'http://www.example.com/login' => 'site/login',
        ];
        $manager = $this->getUrlManager($config, $showScriptName);
        // first rule matches
        $urlParams = ['post/view', 'id' => 1, 'title' => 'sample post', 'lang' => 'en'];
        $expected = "http://en.example.com$prefix/post/1/sample+post";
        $this->assertSame($expected, $manager->createUrl($urlParams));
        $this->assertSame($expected, $manager->createAbsoluteUrl($urlParams));
        $this->assertSame($expected, $manager->createAbsoluteUrl($urlParams, true));
        $this->assertSame($expected, $manager->createAbsoluteUrl($urlParams, 'http'));
        $this->assertSame('https' . substr($expected, 4), $manager->createAbsoluteUrl($urlParams, 'https'));
        $this->assertSame(substr($expected, 5), $manager->createAbsoluteUrl($urlParams, '')); // protocol relative Url

        $urlParams = ['post/view', 'id' => 1, 'title' => 'sample post', 'lang' => 'en', '#' => 'testhash'];
        $expected = "http://en.example.com$prefix/post/1/sample+post#testhash";
        $this->assertSame($expected, $manager->createUrl($urlParams));
        $this->assertSame($expected, $manager->createAbsoluteUrl($urlParams));

        // second rule matches
        $urlParams = ['site/login'];
        $expected = "http://www.example.com$prefix/login";
        $this->assertSame($expected, $manager->createUrl($urlParams));
        $this->assertSame($expected, $manager->createAbsoluteUrl($urlParams));
        $this->assertSame($expected, $manager->createAbsoluteUrl($urlParams, true));
        $this->assertSame($expected, $manager->createAbsoluteUrl($urlParams, 'http'));
        $this->assertSame('https' . substr($expected, 4), $manager->createAbsoluteUrl($urlParams, 'https'));
        $this->assertSame(substr($expected, 5), $manager->createAbsoluteUrl($urlParams, '')); // protocol relative Url

        // none of the rules matches
        $urlParams = ['post/index', 'page' => 1];
        $this->assertSame("$prefix/post/index?page=1", $manager->createUrl($urlParams));
        $expected = "http://www.example.com$prefix/post/index?page=1";
        $this->assertSame($expected, $manager->createAbsoluteUrl($urlParams));
        $this->assertSame($expected, $manager->createAbsoluteUrl($urlParams, true));
        $this->assertSame($expected, $manager->createAbsoluteUrl($urlParams, 'http'));
        $this->assertSame('https' . substr($expected, 4), $manager->createAbsoluteUrl($urlParams, 'https'));
        $this->assertSame(substr($expected, 5), $manager->createAbsoluteUrl($urlParams, '')); // protocol relative Url

        $urlParams = ['post/index', 'page' => 1, '#' => 'testhash'];
        $this->assertSame("$prefix/post/index?page=1#testhash", $manager->createUrl($urlParams));
        $expected = "http://www.example.com$prefix/post/index?page=1#testhash";
        $this->assertSame($expected, $manager->createAbsoluteUrl($urlParams));
    }

    /**
     * Test rules that have host info in the patterns, that are protocol relative.
     * @dataProvider absolutePatternsVariations
     * @see https://github.com/yiisoft/yii2/issues/12691
     * @param bool $showScriptName
     * @param string $prefix
     * @param array $config
     */
    public function testProtocolRelativeAbsolutePattern($showScriptName, $prefix, $config)
    {
        $config['rules'] = [
            [
                'pattern' => 'post/<id>/<title>',
                'route' => 'post/view',
                'host' => '//<lang:en|fr>.example.com',
            ],
            // note: baseUrl is not included in the pattern
            '//www.example.com/login' => 'site/login',
            '//app.example.com' => 'app/index',
            '//app2.example.com/' => 'app2/index',
        ];
        $manager = $this->getUrlManager($config, $showScriptName);
        // first rule matches
        $urlParams = ['post/view', 'id' => 1, 'title' => 'sample post', 'lang' => 'en'];
        $expected = "//en.example.com$prefix/post/1/sample+post";
        $this->assertSame($expected, $manager->createUrl($urlParams));
        $this->assertSame("http:$expected", $manager->createAbsoluteUrl($urlParams));
        $this->assertSame("http:$expected", $manager->createAbsoluteUrl($urlParams, true));
        $this->assertSame("http:$expected", $manager->createAbsoluteUrl($urlParams, 'http'));
        $this->assertSame("https:$expected", $manager->createAbsoluteUrl($urlParams, 'https'));
        $this->assertSame($expected, $manager->createAbsoluteUrl($urlParams, '')); // protocol relative Url

        $urlParams = ['post/view', 'id' => 1, 'title' => 'sample post', 'lang' => 'en', '#' => 'testhash'];
        $expected = "//en.example.com$prefix/post/1/sample+post#testhash";
        $this->assertSame($expected, $manager->createUrl($urlParams));
        $this->assertSame("http:$expected", $manager->createAbsoluteUrl($urlParams));

        // second rule matches
        $urlParams = ['site/login'];
        $expected = "//www.example.com$prefix/login";
        $this->assertSame($expected, $manager->createUrl($urlParams));
        $this->assertSame("http:$expected", $manager->createAbsoluteUrl($urlParams));
        $this->assertSame("http:$expected", $manager->createAbsoluteUrl($urlParams, true));
        $this->assertSame("http:$expected", $manager->createAbsoluteUrl($urlParams, 'http'));
        $this->assertSame("https:$expected", $manager->createAbsoluteUrl($urlParams, 'https'));
        $this->assertSame($expected, $manager->createAbsoluteUrl($urlParams, '')); // protocol relative Url

        // third rule matches
        $urlParams = ['app/index'];
        $expected = "//app.example.com$prefix";
        $this->assertSame($expected, $manager->createUrl($urlParams));
        $this->assertSame("http:$expected", $manager->createAbsoluteUrl($urlParams));
        $this->assertSame("http:$expected", $manager->createAbsoluteUrl($urlParams, true));
        $this->assertSame("http:$expected", $manager->createAbsoluteUrl($urlParams, 'http'));
        $this->assertSame("https:$expected", $manager->createAbsoluteUrl($urlParams, 'https'));
        $this->assertSame($expected, $manager->createAbsoluteUrl($urlParams, '')); // protocol relative Url

        // fourth rule matches
        $urlParams = ['app2/index'];
        $expected = "//app2.example.com$prefix";
        $this->assertSame($expected, $manager->createUrl($urlParams));
        $this->assertSame("http:$expected", $manager->createAbsoluteUrl($urlParams));
        $this->assertSame("http:$expected", $manager->createAbsoluteUrl($urlParams, true));
        $this->assertSame("http:$expected", $manager->createAbsoluteUrl($urlParams, 'http'));
        $this->assertSame("https:$expected", $manager->createAbsoluteUrl($urlParams, 'https'));
        $this->assertSame($expected, $manager->createAbsoluteUrl($urlParams, '')); // protocol relative Url

        // none of the rules matches
        $urlParams = ['post/index', 'page' => 1];
        $this->assertSame("$prefix/post/index?page=1", $manager->createUrl($urlParams));
        $expected = "//www.example.com$prefix/post/index?page=1";
        $this->assertSame("http:$expected", $manager->createAbsoluteUrl($urlParams));
        $this->assertSame("http:$expected", $manager->createAbsoluteUrl($urlParams, true));
        $this->assertSame("http:$expected", $manager->createAbsoluteUrl($urlParams, 'http'));
        $this->assertSame("https:$expected", $manager->createAbsoluteUrl($urlParams, 'https'));
        $this->assertSame($expected, $manager->createAbsoluteUrl($urlParams, '')); // protocol relative Url

        $urlParams = ['post/index', 'page' => 1, '#' => 'testhash'];
        $this->assertSame("$prefix/post/index?page=1#testhash", $manager->createUrl($urlParams));
        $expected = "http://www.example.com$prefix/post/index?page=1#testhash";
        $this->assertSame($expected, $manager->createAbsoluteUrl($urlParams));
    }

    public function multipleHostsRulesDataProvider()
    {
        return [
            ['http://example.com'],
            ['https://example.com'],
            ['http://example.fr'],
            ['https://example.fr'],
        ];
    }

    /**
     * Test matching of Url rules dependent on the current host info.
     *
     * @dataProvider multipleHostsRulesDataProvider
     * @see https://github.com/yiisoft/yii2/issues/7948
     * @param string $host
     */
    public function testMultipleHostsRules($host)
    {
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'cache' => null,
            'rules' => [
                ['host' => 'http://example.com', 'pattern' => '<slug:(search)>', 'route' => 'products/search', 'defaults' => ['lang' => 'en']],
                ['host' => 'http://example.fr', 'pattern' => '<slug:(search)>', 'route' => 'products/search', 'defaults' => ['lang' => 'fr']],
            ],
            'hostInfo' => $host,
            'baseUrl' => '/',
            'scriptUrl' => '',
        ]);
        $url = $manager->createAbsoluteUrl(['products/search', 'lang' => 'en', 'slug' => 'search'], 'https');
        $this->assertSame('https://example.com/search', $url);
        $url = $manager->createUrl(['products/search', 'lang' => 'en', 'slug' => 'search']);
        $this->assertSame('http://example.com/search', $url);
        $url = $manager->createUrl(['products/search', 'lang' => 'en', 'slug' => 'search', 'param1' => 'value1']);
        $this->assertSame('http://example.com/search?param1=value1', $url);
        $url = $manager->createAbsoluteUrl(['products/search', 'lang' => 'fr', 'slug' => 'search'], 'https');
        $this->assertSame('https://example.fr/search', $url);
        $url = $manager->createUrl(['products/search', 'lang' => 'fr', 'slug' => 'search']);
        $this->assertSame('http://example.fr/search', $url);
        $url = $manager->createUrl(['products/search', 'lang' => 'fr', 'slug' => 'search', 'param1' => 'value1']);
        $this->assertSame('http://example.fr/search?param1=value1', $url);
    }

    public function testCreateUrlCache()
    {
        /* @var $rules CachedUrlRule[] */
        $rules = [
            Yii::createObject([
                'class' => CachedUrlRule::className(),
                'route' => 'user/show',
                'pattern' => 'user/<name:[\w-]+>',
            ]),
            Yii::createObject([
                'class' => CachedUrlRule::className(),
                'route' => '<controller>/<action>',
                'pattern' => '<controller:\w+>/<action:\w+>',
            ]),
        ];
        $manager = $this->getUrlManager([
            'rules' => $rules,
        ], false);

        $this->assertSame('/user/rob006', $manager->createUrl(['user/show', 'name' => 'rob006']));
        $this->assertSame(UrlRule::CREATE_STATUS_SUCCESS, $rules[0]->getCreateUrlStatus());
        $this->assertSame(1, $rules[0]->createCounter);
        $this->assertSame(0, $rules[1]->createCounter);

        $this->assertSame('/user/show?name=John+Doe', $manager->createUrl(['user/show', 'name' => 'John Doe']));
        $this->assertSame(UrlRule::CREATE_STATUS_PARAMS_MISMATCH, $rules[0]->getCreateUrlStatus());
        $this->assertSame(UrlRule::CREATE_STATUS_SUCCESS, $rules[1]->getCreateUrlStatus());
        $this->assertSame(2, $rules[0]->createCounter);
        $this->assertSame(1, $rules[1]->createCounter);

        $this->assertSame('/user/profile?name=rob006', $manager->createUrl(['user/profile', 'name' => 'rob006']));
        $this->assertSame(UrlRule::CREATE_STATUS_ROUTE_MISMATCH, $rules[0]->getCreateUrlStatus());
        $this->assertSame(UrlRule::CREATE_STATUS_SUCCESS, $rules[1]->getCreateUrlStatus());
        $this->assertSame(3, $rules[0]->createCounter);
        $this->assertSame(2, $rules[1]->createCounter);

        $this->assertSame('/user/profile?name=John+Doe', $manager->createUrl(['user/profile', 'name' => 'John Doe']));
        $this->assertSame(UrlRule::CREATE_STATUS_ROUTE_MISMATCH, $rules[0]->getCreateUrlStatus());
        $this->assertSame(UrlRule::CREATE_STATUS_SUCCESS, $rules[1]->getCreateUrlStatus());
        // fist rule is skipped - cached rule has precedence
        $this->assertSame(3, $rules[0]->createCounter);
        $this->assertSame(3, $rules[1]->createCounter);
    }

    /**
     * @see https://github.com/yiisoft/yii2/pull/1335
     */
    public function testUrlCreateCacheWithParameterMismatch()
    {
        /* @var $rules CachedUrlRule[] */
        $rules = [
            Yii::createObject([
                'class' => CachedUrlRule::className(),
                'route' => 'user/show',
                'pattern' => 'user/<name:[\w-]+>',
            ]),
            Yii::createObject([
                'class' => CachedUrlRule::className(),
                'route' => '<controller>/<action>',
                'pattern' => '<controller:\w+>/<action:\w+>',
            ]),
        ];
        $manager = $this->getUrlManager([
            'rules' => $rules,
        ], false);

        $this->assertSame('/user/show?name=John+Doe', $manager->createUrl(['user/show', 'name' => 'John Doe']));
        $this->assertSame(UrlRule::CREATE_STATUS_PARAMS_MISMATCH, $rules[0]->getCreateUrlStatus());
        $this->assertSame(UrlRule::CREATE_STATUS_SUCCESS, $rules[1]->getCreateUrlStatus());
        $this->assertSame(1, $rules[0]->createCounter);
        $this->assertSame(1, $rules[1]->createCounter);

        $this->assertSame('/user/rob006', $manager->createUrl(['user/show', 'name' => 'rob006']));
        $this->assertSame(UrlRule::CREATE_STATUS_SUCCESS, $rules[0]->getCreateUrlStatus());
        $this->assertSame(2, $rules[0]->createCounter);
        $this->assertSame(1, $rules[1]->createCounter);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/14406
     */
    public function testCreatingRulesWithDifferentRuleConfigAndEnabledCache()
    {
        $this->mockWebApplication([
            'components' => [
                'cache' => ArrayCache::className(),
            ],
        ]);
        $urlManager = $this->getUrlManager([
            'cache' => 'cache',
            'rules' => [
                '/' => 'site/index',
            ],
        ]);

        $cachedUrlManager = $this->getUrlManager([
            'cache' => 'cache',
            'ruleConfig' => [
                'class' => CachedUrlRule::className(),
            ],
            'rules' => [
                '/' => 'site/index',
            ],
        ]);

        $this->assertNotSame($urlManager->rules, $cachedUrlManager->rules);
        $this->assertInstanceOf(UrlRule::className(), $urlManager->rules[0]);
        $this->assertInstanceOf(CachedUrlRule::className(), $cachedUrlManager->rules[0]);
    }
}
