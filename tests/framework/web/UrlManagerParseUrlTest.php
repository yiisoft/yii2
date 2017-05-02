<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\web\Request;
use yii\web\UrlManager;
use yiiunit\TestCase;

/**
 * This class implements the tests for URL parsing with "pretty" url format.
 *
 * See [[UrlManagerTest]] for tests with "default" URL format.
 * See [[UrlManagerCreateUrlTest]] for url creation with "pretty" URL format.
 *
 * Behavior of UrlManager::parseRequest() for the "pretty" URL format varies among the following options:
 *  - strict parsing = true / false
 *  - rules format
 *    - key => value
 *    - array config
 *
 * The following features are tested:
 *  - named parameters
 *    - as query params
 *    - as controller/actions '<controller:(post|comment)>/<id:\d+>/<action:(update|delete)>' => '<controller>/<action>',
 *  - Rules with Server Names
 *    - with protocol
 *    - without protocol i.e protocol relative, see https://github.com/yiisoft/yii2/pull/12697
 *    - with parameters
 *  - with suffix
 *  - with default values
 *  - with HTTP methods
 *
 *  - Adding rules dynamically
 *  - Test custom rules that only implement the interface
 *
 * NOTE: if a test is added here, you probably also need to add one in UrlManagerCreateUrlTest.
 *
 * @group web
 */
class UrlManagerParseUrlTest extends TestCase
{
    protected function getUrlManager($config = [])
    {
        // in this test class, all tests have enablePrettyUrl enabled.
        $config['enablePrettyUrl'] = true;
        $config['cache'] = null;
        // normalizer is tested in UrlNormalizerTest
        $config['normalizer'] = false;

        return new UrlManager($config);
    }

    protected function getRequest($pathInfo, $hostInfo = 'http://www.example.com', $method = 'GET', $config = [])
    {
        $config['pathInfo'] = $pathInfo;
        $config['hostInfo'] = $hostInfo;
        $_POST['_method'] = $method;
        return new Request($config);
    }

    protected function tearDown()
    {
        unset($_POST['_method']);
        parent::tearDown();
    }

    public function testWithoutRules()
    {
        $manager = $this->getUrlManager();

        // empty pathinfo
        $result = $manager->parseRequest($this->getRequest(''));
        $this->assertEquals(['', []], $result);
        // normal pathinfo
        $result = $manager->parseRequest($this->getRequest('site/index'));
        $this->assertEquals(['site/index', []], $result);
        // pathinfo with module
        $result = $manager->parseRequest($this->getRequest('module/site/index'));
        $this->assertEquals(['module/site/index', []], $result);
        // pathinfo with trailing slashes
        $result = $manager->parseRequest($this->getRequest('module/site/index/'));
        $this->assertEquals(['module/site/index/', []], $result);
    }

    public function testWithoutRulesStrict()
    {
        $manager = $this->getUrlManager();
        $manager->enableStrictParsing = true;

        // empty pathinfo
        $this->assertFalse($manager->parseRequest($this->getRequest('')));
        // normal pathinfo
        $this->assertFalse($manager->parseRequest($this->getRequest('site/index')));
        // pathinfo with module
        $this->assertFalse($manager->parseRequest($this->getRequest('module/site/index')));
        // pathinfo with trailing slashes
        $this->assertFalse($manager->parseRequest($this->getRequest('module/site/index/')));
    }

    public function suffixProvider()
    {
        return [
            ['.html'],
            ['/'],
        ];
    }

    /**
     * @dataProvider suffixProvider
     */
    public function testWithoutRulesWithSuffix($suffix)
    {
        $manager = $this->getUrlManager(['suffix' => $suffix]);

        // empty pathinfo
        $result = $manager->parseRequest($this->getRequest(''));
        $this->assertEquals(['', []], $result);
        // normal pathinfo
        $result = $manager->parseRequest($this->getRequest('site/index'));
        $this->assertFalse($result);
        $result = $manager->parseRequest($this->getRequest("site/index$suffix"));
        $this->assertEquals(['site/index', []], $result);
        // pathinfo with module
        $result = $manager->parseRequest($this->getRequest('module/site/index'));
        $this->assertFalse($result);
        $result = $manager->parseRequest($this->getRequest("module/site/index$suffix"));
        $this->assertEquals(['module/site/index', []], $result);
        // pathinfo with trailing slashes
        if ($suffix !== '/') {
            $result = $manager->parseRequest($this->getRequest('module/site/index/'));
            $this->assertFalse($result);
        }
        $result = $manager->parseRequest($this->getRequest("module/site/index/$suffix"));
        $this->assertEquals(['module/site/index/', []], $result);
    }

    public function testSimpleRules()
    {
        $config = [
            'rules' => [
                'post/<id:\d+>' => 'post/view',
                'posts' => 'post/index',
                'book/<id:\d+>/<title>' => 'book/view',
            ],
        ];
        $manager = $this->getUrlManager($config);

        // matching pathinfo
        $result = $manager->parseRequest($this->getRequest('book/123/this+is+sample'));
        $this->assertEquals(['book/view', ['id' => '123', 'title' => 'this+is+sample']], $result);
        // trailing slash is significant, no match
        $result = $manager->parseRequest($this->getRequest('book/123/this+is+sample/'));
        $this->assertEquals(['book/123/this+is+sample/', []], $result);
        // empty pathinfo
        $result = $manager->parseRequest($this->getRequest(''));
        $this->assertEquals(['', []], $result);
        // normal pathinfo
        $result = $manager->parseRequest($this->getRequest('site/index'));
        $this->assertEquals(['site/index', []], $result);
        // pathinfo with module
        $result = $manager->parseRequest($this->getRequest('module/site/index'));
        $this->assertEquals(['module/site/index', []], $result);
    }

    public function testSimpleRulesStrict()
    {
        $config = [
            'rules' => [
                'post/<id:\d+>' => 'post/view',
                'posts' => 'post/index',
                'book/<id:\d+>/<title>' => 'book/view',
            ],
        ];
        $manager = $this->getUrlManager($config);
        $manager->enableStrictParsing = true;

        // matching pathinfo
        $result = $manager->parseRequest($this->getRequest('book/123/this+is+sample'));
        $this->assertEquals(['book/view', ['id' => '123', 'title' => 'this+is+sample']], $result);
        // trailing slash is significant, no match
        $result = $manager->parseRequest($this->getRequest('book/123/this+is+sample/'));
        $this->assertFalse($result);
        // empty pathinfo
        $result = $manager->parseRequest($this->getRequest(''));
        $this->assertFalse($result);
        // normal pathinfo
        $result = $manager->parseRequest($this->getRequest('site/index'));
        $this->assertFalse($result);
        // pathinfo with module
        $result = $manager->parseRequest($this->getRequest('module/site/index'));
        $this->assertFalse($result);
    }

    /**
     * @dataProvider suffixProvider
     */
    public function testSimpleRulesWithSuffix($suffix)
    {
        $config = [
            'rules' => [
                'post/<id:\d+>' => 'post/view',
                'posts' => 'post/index',
                'book/<id:\d+>/<title>' => 'book/view',
            ],
            'suffix' => $suffix,
        ];
        $manager = $this->getUrlManager($config);

        // matching pathinfo
        $result = $manager->parseRequest($this->getRequest('book/123/this+is+sample'));
        $this->assertFalse($result);
        $result = $manager->parseRequest($this->getRequest("book/123/this+is+sample$suffix"));
        $this->assertEquals(['book/view', ['id' => '123', 'title' => 'this+is+sample']], $result);
        // trailing slash is significant, no match
        $result = $manager->parseRequest($this->getRequest('book/123/this+is+sample/'));
        if ($suffix === '/') {
            $this->assertEquals(['book/view', ['id' => '123', 'title' => 'this+is+sample']], $result);
        } else {
            $this->assertFalse($result);
        }
        $result = $manager->parseRequest($this->getRequest("book/123/this+is+sample/$suffix"));
        $this->assertEquals(['book/123/this+is+sample/', []], $result);
        // empty pathinfo
        $result = $manager->parseRequest($this->getRequest(''));
        $this->assertEquals(['', []], $result);
        // normal pathinfo
        $result = $manager->parseRequest($this->getRequest('site/index'));
        $this->assertFalse($result);
        $result = $manager->parseRequest($this->getRequest("site/index$suffix"));
        $this->assertEquals(['site/index', []], $result);
        // pathinfo with module
        $result = $manager->parseRequest($this->getRequest('module/site/index'));
        $this->assertFalse($result);
        $result = $manager->parseRequest($this->getRequest("module/site/index$suffix"));
        $this->assertEquals(['module/site/index', []], $result);
    }

    /**
     * @dataProvider suffixProvider
     */
    public function testSimpleRulesWithSuffixStrict($suffix)
    {
        $config = [
            'rules' => [
                'post/<id:\d+>' => 'post/view',
                'posts' => 'post/index',
                'book/<id:\d+>/<title>' => 'book/view',
            ],
            'suffix' => $suffix,
        ];
        $manager = $this->getUrlManager($config);
        $manager->enableStrictParsing = true;

        // matching pathinfo
        $result = $manager->parseRequest($this->getRequest('book/123/this+is+sample'));
        $this->assertFalse($result);
        $result = $manager->parseRequest($this->getRequest("book/123/this+is+sample$suffix"));
        $this->assertEquals(['book/view', ['id' => '123', 'title' => 'this+is+sample']], $result);
        // trailing slash is significant, no match
        $result = $manager->parseRequest($this->getRequest('book/123/this+is+sample/'));
        if ($suffix === '/') {
            $this->assertEquals(['book/view', ['id' => '123', 'title' => 'this+is+sample']], $result);
        } else {
            $this->assertFalse($result);
        }
        $result = $manager->parseRequest($this->getRequest("book/123/this+is+sample/$suffix"));
        $this->assertFalse($result);
        // empty pathinfo
        $result = $manager->parseRequest($this->getRequest(''));
        $this->assertFalse($result);
        // normal pathinfo
        $result = $manager->parseRequest($this->getRequest('site/index'));
        $this->assertFalse($result);
        $result = $manager->parseRequest($this->getRequest("site/index$suffix"));
        $this->assertFalse($result);
        // pathinfo with module
        $result = $manager->parseRequest($this->getRequest('module/site/index'));
        $this->assertFalse($result);
        $result = $manager->parseRequest($this->getRequest("module/site/index$suffix"));
        $this->assertFalse($result);
    }



    // TODO implement with hostinfo



    public function testParseRESTRequest()
    {
        $request = new Request();

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
                    'baseUrl' => '/app',
                ],
            ],
        ], \yii\web\Application::className());
        $this->assertEquals('/app/post/delete?id=123', $manager->createUrl(['post/delete', 'id' => 123]));
        $this->destroyApplication();

        unset($_SERVER['REQUEST_METHOD']);
    }
}
