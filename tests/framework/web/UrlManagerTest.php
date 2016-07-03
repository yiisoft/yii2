<?php
namespace yiiunit\framework\web;

use yii\web\Request;
use yii\web\UrlManager;
use yiiunit\TestCase;

/**
 * @group web
 */
class UrlManagerTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testCreateUrl()
    {
        // default setting with '/' as base url
        $manager = new UrlManager([
            'baseUrl' => '/',
            'scriptUrl' => '',
            'cache' => null,
        ]);
        $url = $manager->createUrl(['post/view']);
        $this->assertEquals('?r=post%2Fview', $url);
        $url = $manager->createUrl(['post/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertEquals('?r=post%2Fview&id=1&title=sample+post', $url);

        // default setting with '/test/' as base url
        $manager = new UrlManager([
            'baseUrl' => '/test/',
            'scriptUrl' => '/test',
            'cache' => null,
        ]);
        $url = $manager->createUrl(['post/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertEquals('/test?r=post%2Fview&id=1&title=sample+post', $url);

        // pretty URL without rules
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'baseUrl' => '/',
            'scriptUrl' => '',
            'cache' => null,
        ]);
        $url = $manager->createUrl(['post/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertEquals('/post/view?id=1&title=sample+post', $url);
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'baseUrl' => '/test/',
            'scriptUrl' => '/test',
            'cache' => null,
        ]);
        $url = $manager->createUrl(['post/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertEquals('/test/post/view?id=1&title=sample+post', $url);
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'baseUrl' => '/test',
            'scriptUrl' => '/test/index.php',
            'cache' => null,
        ]);
        $url = $manager->createUrl(['post/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertEquals('/test/index.php/post/view?id=1&title=sample+post', $url);

        // test showScriptName
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'baseUrl' => '/test',
            'scriptUrl' => '/test/index.php',
            'showScriptName' => true,
            'cache' => null,
        ]);
        $url = $manager->createUrl(['post/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertEquals('/test/index.php/post/view?id=1&title=sample+post', $url);
        $url = $manager->createUrl(['/post/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertEquals('/test/index.php/post/view?id=1&title=sample+post', $url);
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'baseUrl' => '/test',
            'scriptUrl' => '/test/index.php',
            'showScriptName' => false,
            'cache' => null,
        ]);
        $url = $manager->createUrl(['post/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertEquals('/test/post/view?id=1&title=sample+post', $url);
        $url = $manager->createUrl(['/post/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertEquals('/test/post/view?id=1&title=sample+post', $url);

        // pretty URL with rules
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'cache' => null,
            'rules' => [
                [
                    'pattern' => 'post/<id>/<title>',
                    'route' => 'post/view',
                ],
            ],
            'baseUrl' => '/',
            'scriptUrl' => '',
        ]);
        $url = $manager->createUrl(['post/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertEquals('/post/1/sample+post', $url);
        $url = $manager->createUrl(['post/index', 'page' => 1]);
        $this->assertEquals('/post/index?page=1', $url);

        // rules with defaultAction
        $url = $manager->createUrl(['/post', 'page' => 1]);
        $this->assertEquals('/post?page=1', $url);

        // pretty URL with rules and suffix
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'cache' => null,
            'rules' => [
                [
                    'pattern' => 'post/<id>/<title>',
                    'route' => 'post/view',
                ],
            ],
            'baseUrl' => '/',
            'scriptUrl' => '',
            'suffix' => '.html',
        ]);
        $url = $manager->createUrl(['post/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertEquals('/post/1/sample+post.html', $url);
        $url = $manager->createUrl(['post/index', 'page' => 1]);
        $this->assertEquals('/post/index.html?page=1', $url);

        // pretty URL with rules that have host info
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'cache' => null,
            'rules' => [
                [
                    'pattern' => 'post/<id>/<title>',
                    'route' => 'post/view',
                    'host' => 'http://<lang:en|fr>.example.com',
                ],
            ],
            'baseUrl' => '/test',
            'scriptUrl' => '/test',
        ]);
        $url = $manager->createUrl(['post/view', 'id' => 1, 'title' => 'sample post', 'lang' => 'en']);
        $this->assertEquals('http://en.example.com/test/post/1/sample+post', $url);
        $url = $manager->createUrl(['post/index', 'page' => 1]);
        $this->assertEquals('/test/post/index?page=1', $url);

        // create url with the same route but different params/defaults
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'cache' => null,
            'rules' => [
                [
                    'pattern' => '',
                    'route' => 'frontend/page/view',
                    'defaults' => ['slug' => 'index'],
                ],
                'page/<slug>' => 'frontend/page/view',
            ],
            'baseUrl' => '/test',
            'scriptUrl' => '/test',
        ]);
        $url = $manager->createUrl(['frontend/page/view', 'slug' => 'services']);
        $this->assertEquals('/test/page/services', $url);
        $url = $manager->createUrl(['frontend/page/view', 'slug' => 'index']);
        $this->assertEquals('/test/', $url);
    }

    /**
     * @depends testCreateUrl
     * @see https://github.com/yiisoft/yii2/issues/10935
     */
    public function testCreateUrlWithNullParams()
    {
        $manager = new UrlManager([
            'rules' => [
                '<param1>/<param2>' => 'site/index',
                '<param1>' => 'site/index',
            ],
            'enablePrettyUrl' => true,
            'scriptUrl' => '/test',

        ]);
        $this->assertEquals('/test/111', $manager->createUrl(['site/index', 'param1' => 111, 'param2' => null]));
        $this->assertEquals('/test/123', $manager->createUrl(['site/index', 'param1' => 123, 'param2' => null]));
        $this->assertEquals('/test/111/222', $manager->createUrl(['site/index', 'param1' => 111, 'param2' => 222]));
        $this->assertEquals('/test/112/222', $manager->createUrl(['site/index', 'param1' => 112, 'param2' => 222]));
    }

    /**
     * https://github.com/yiisoft/yii2/issues/6717
     */
    public function testCreateUrlWithEmptyPattern()
    {
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'cache' => null,
            'rules' => [
                '' => 'front/site/index',
            ],
            'baseUrl' => '/',
            'scriptUrl' => '',
        ]);
        $url = $manager->createUrl(['front/site/index']);
        $this->assertEquals('/', $url);
        $url = $manager->createUrl(['/front/site/index']);
        $this->assertEquals('/', $url);
        $url = $manager->createUrl(['front/site/index', 'page' => 1]);
        $this->assertEquals('/?page=1', $url);
        $url = $manager->createUrl(['/front/site/index', 'page' => 1]);
        $this->assertEquals('/?page=1', $url);

        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'cache' => null,
            'rules' => [
                '' => '/front/site/index',
            ],
            'baseUrl' => '/',
            'scriptUrl' => '',
        ]);
        $url = $manager->createUrl(['front/site/index']);
        $this->assertEquals('/', $url);
        $url = $manager->createUrl(['/front/site/index']);
        $this->assertEquals('/', $url);
        $url = $manager->createUrl(['front/site/index', 'page' => 1]);
        $this->assertEquals('/?page=1', $url);
        $url = $manager->createUrl(['/front/site/index', 'page' => 1]);
        $this->assertEquals('/?page=1', $url);
    }

    public function testCreateAbsoluteUrl()
    {
        $manager = new UrlManager([
            'baseUrl' => '/',
            'scriptUrl' => '',
            'hostInfo' => 'http://www.example.com',
            'cache' => null,
        ]);
        $url = $manager->createAbsoluteUrl(['post/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertEquals('http://www.example.com?r=post%2Fview&id=1&title=sample+post', $url);

        $url = $manager->createAbsoluteUrl(['post/view', 'id' => 1, 'title' => 'sample post'], 'https');
        $this->assertEquals('https://www.example.com?r=post%2Fview&id=1&title=sample+post', $url);

        $manager->hostInfo = 'https://www.example.com';
        $url = $manager->createAbsoluteUrl(['post/view', 'id' => 1, 'title' => 'sample post'], 'http');
        $this->assertEquals('http://www.example.com?r=post%2Fview&id=1&title=sample+post', $url);
    }

    public function testCreateAbsoluteUrlWithSuffix()
    {
        $manager = new UrlManager([
            'baseUrl' => '/',
            'scriptUrl' => '',
            'hostInfo' => 'http://app.example.com',
            'cache' => null,

            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'suffix' => '/',
            'rules' => [
                'http://app.example.com/login' => 'site/login',
            ],
        ]);
        $url = $manager->createAbsoluteUrl(['site/login']);
        $this->assertEquals('http://app.example.com/login/', $url);
        $url = $manager->createUrl(['site/login']);
        $this->assertEquals('http://app.example.com/login/', $url);
    }

    public function testParseRequest()
    {
        $manager = new UrlManager(['cache' => null]);
        $request = new Request;

        // default setting without 'r' param
        unset($_GET['r']);
        $result = $manager->parseRequest($request);
        $this->assertEquals(['', []], $result);

        // default setting with 'r' param
        $_GET['r'] = 'site/index';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['site/index', []], $result);

        // default setting with 'r' param as an array
        $_GET['r'] = ['site/index'];
        $result = $manager->parseRequest($request);
        $this->assertEquals(['', []], $result);

        // pretty URL without rules
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'cache' => null,
        ]);
        // empty pathinfo
        $request->pathInfo = '';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['', []], $result);
        // normal pathinfo
        $request->pathInfo = 'site/index';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['site/index', []], $result);
        // pathinfo with module
        $request->pathInfo = 'module/site/index';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['module/site/index', []], $result);
        // pathinfo with trailing slashes
        $request->pathInfo = '/module/site/index/';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['module/site/index/', []], $result);

        // pretty URL rules
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'cache' => null,
            'rules' => [
                [
                    'pattern' => 'post/<id>/<title>',
                    'route' => 'post/view',
                ],
            ],
        ]);
        // matching pathinfo
        $request->pathInfo = 'post/123/this+is+sample';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['post/view', ['id' => '123', 'title' => 'this+is+sample']], $result);
        // trailing slash is significant
        $request->pathInfo = 'post/123/this+is+sample/';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['post/123/this+is+sample/', []], $result);
        // empty pathinfo
        $request->pathInfo = '';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['', []], $result);
        // normal pathinfo
        $request->pathInfo = 'site/index';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['site/index', []], $result);
        // pathinfo with module
        $request->pathInfo = 'module/site/index';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['module/site/index', []], $result);

        // pretty URL rules
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'suffix' => '.html',
            'cache' => null,
            'rules' => [
                [
                    'pattern' => 'post/<id>/<title>',
                    'route' => 'post/view',
                ],
            ],
        ]);
        // matching pathinfo
        $request->pathInfo = 'post/123/this+is+sample.html';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['post/view', ['id' => '123', 'title' => 'this+is+sample']], $result);
        // matching pathinfo without suffix
        $request->pathInfo = 'post/123/this+is+sample';
        $result = $manager->parseRequest($request);
        $this->assertFalse($result);
        // empty pathinfo
        $request->pathInfo = '';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['', []], $result);
        // normal pathinfo
        $request->pathInfo = 'site/index.html';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['site/index', []], $result);
        // pathinfo without suffix
        $request->pathInfo = 'site/index';
        $result = $manager->parseRequest($request);
        $this->assertFalse($result);

        // strict parsing
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'suffix' => '.html',
            'cache' => null,
            'rules' => [
                [
                    'pattern' => 'post/<id>/<title>',
                    'route' => 'post/view',
                ],
            ],
        ]);
        // matching pathinfo
        $request->pathInfo = 'post/123/this+is+sample.html';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['post/view', ['id' => '123', 'title' => 'this+is+sample']], $result);
        // unmatching pathinfo
        $request->pathInfo = 'site/index.html';
        $result = $manager->parseRequest($request);
        $this->assertFalse($result);
    }

    public function testParseRESTRequest()
    {
        $request = new Request;

        // pretty URL rules
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'cache' => null,
            'rules' => [
                'PUT,POST post/<id>/<title>' => 'post/create',
                'DELETE post/<id>' => 'post/delete',
                'post/<id>/<title>' => 'post/view',
                'POST/GET' => 'post/get',
            ],
        ]);
        // matching pathinfo GET request
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request->pathInfo = 'post/123/this+is+sample';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['post/view', ['id' => '123', 'title' => 'this+is+sample']], $result);
        // matching pathinfo PUT/POST request
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $request->pathInfo = 'post/123/this+is+sample';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['post/create', ['id' => '123', 'title' => 'this+is+sample']], $result);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request->pathInfo = 'post/123/this+is+sample';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['post/create', ['id' => '123', 'title' => 'this+is+sample']], $result);

        // no wrong matching
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request->pathInfo = 'POST/GET';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['post/get', []], $result);

        // createUrl should ignore REST rules
        $this->mockApplication([
            'components' => [
                'request' => [
                    'hostInfo' => 'http://localhost/',
                    'baseUrl' => '/app'
                ]
            ]
        ], \yii\web\Application::className());
        $this->assertEquals('/app/post/delete?id=123', $manager->createUrl(['post/delete', 'id' => 123]));
        $this->destroyApplication();

        unset($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Tests if hash-anchor present
     *
     * https://github.com/yiisoft/yii2/pull/9596
     */
    public function testHash()
    {
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'cache' => null,
            'rules' => [
                'http://example.com/testPage' => 'site/test',
            ],
            'hostInfo' => 'http://example.com',
            'scriptUrl' => '/index.php',
        ]);
        $url = $manager->createAbsoluteUrl(['site/test', '#' => 'testhash']);
        $this->assertEquals('http://example.com/index.php/testPage#testhash', $url);
    }

    /**
     * Tests if multislashes not accepted at the end of URL if PrettyUrl is enabled
     *
     * @see https://github.com/yiisoft/yii2/issues/10739
     */
    public function testMultiSlashesAtTheEnd()
    {
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
        ]);

        $request = new Request;

        $request->pathInfo = 'post/multi/slash/';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['post/multi/slash/', []], $result);

        $request->pathInfo = 'post/multi/slash//';
        $result = $manager->parseRequest($request);
        $this->assertEquals(false, $result);

        $request->pathInfo = 'post/multi/slash////';
        $result = $manager->parseRequest($request);
        $this->assertEquals(false, $result);

        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'suffix' => '/'
        ]);

        $request->pathInfo = 'post/multi/slash/';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['post/multi/slash', []], $result);

        $request->pathInfo = 'post/multi/slash//';
        $result = $manager->parseRequest($request);
        $this->assertEquals(false, $result);

        $request->pathInfo = 'post/multi/slash///////';
        $result = $manager->parseRequest($request);
        $this->assertEquals(false, $result);
    }
}
