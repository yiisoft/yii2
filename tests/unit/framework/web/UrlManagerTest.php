<?php
namespace yiiunit\framework\web;

use yii\web\Request;
use yii\web\UrlManager;
use yiiunit\TestCase;

class UrlManagerTest extends TestCase
{
	public function testCreateUrl()
	{
		// default setting with '/' as base url
		$manager = new UrlManager(array(
			'baseUrl' => '/',
			'cache' => null,
		));
		$url = $manager->createUrl('post/view');
		$this->assertEquals('?r=post/view', $url);
		$url = $manager->createUrl('post/view', array('id' => 1, 'title' => 'sample post'));
		$this->assertEquals('?r=post/view&id=1&title=sample+post', $url);

		// default setting with '/test/' as base url
		$manager = new UrlManager(array(
			'baseUrl' => '/test/',
			'cache' => null,
		));
		$url = $manager->createUrl('post/view', array('id' => 1, 'title' => 'sample post'));
		$this->assertEquals('/test?r=post/view&id=1&title=sample+post', $url);

		// pretty URL without rules
		$manager = new UrlManager(array(
			'enablePrettyUrl' => true,
			'baseUrl' => '/',
			'cache' => null,
		));
		$url = $manager->createUrl('post/view', array('id' => 1, 'title' => 'sample post'));
		$this->assertEquals('/post/view?id=1&title=sample+post', $url);
		$manager = new UrlManager(array(
			'enablePrettyUrl' => true,
			'baseUrl' => '/test/',
			'cache' => null,
		));
		$url = $manager->createUrl('post/view', array('id' => 1, 'title' => 'sample post'));
		$this->assertEquals('/test/post/view?id=1&title=sample+post', $url);
		$manager = new UrlManager(array(
			'enablePrettyUrl' => true,
			'baseUrl' => '/test/index.php',
			'cache' => null,
		));
		$url = $manager->createUrl('post/view', array('id' => 1, 'title' => 'sample post'));
		$this->assertEquals('/test/index.php/post/view?id=1&title=sample+post', $url);

		// todo: test showScriptName

		// pretty URL with rules
		$manager = new UrlManager(array(
			'enablePrettyUrl' => true,
			'cache' => null,
			'rules' => array(
				array(
					'pattern' => 'post/<id>/<title>',
					'route' => 'post/view',
				),
			),
			'baseUrl' => '/',
		));
		$url = $manager->createUrl('post/view', array('id' => 1, 'title' => 'sample post'));
		$this->assertEquals('/post/1/sample+post', $url);
		$url = $manager->createUrl('post/index', array('page' => 1));
		$this->assertEquals('/post/index?page=1', $url);

		// pretty URL with rules and suffix
		$manager = new UrlManager(array(
			'enablePrettyUrl' => true,
			'cache' => null,
			'rules' => array(
				array(
					'pattern' => 'post/<id>/<title>',
					'route' => 'post/view',
				),
			),
			'baseUrl' => '/',
			'suffix' => '.html',
		));
		$url = $manager->createUrl('post/view', array('id' => 1, 'title' => 'sample post'));
		$this->assertEquals('/post/1/sample+post.html', $url);
		$url = $manager->createUrl('post/index', array('page' => 1));
		$this->assertEquals('/post/index.html?page=1', $url);

		// pretty URL with rules that have host info
		$manager = new UrlManager(array(
			'enablePrettyUrl' => true,
			'cache' => null,
			'rules' => array(
				array(
					'pattern' => 'post/<id>/<title>',
					'route' => 'post/view',
					'host' => 'http://<lang:en|fr>.example.com',
				),
			),
			'baseUrl' => '/test',
		));
		$url = $manager->createUrl('post/view', array('id' => 1, 'title' => 'sample post', 'lang' => 'en'));
		$this->assertEquals('http://en.example.com/test/post/1/sample+post', $url);
		$url = $manager->createUrl('post/index', array('page' => 1));
		$this->assertEquals('/test/post/index?page=1', $url);
	}

	public function testCreateAbsoluteUrl()
	{
		$manager = new UrlManager(array(
			'baseUrl' => '/',
			'hostInfo' => 'http://www.example.com',
			'cache' => null,
		));
		$url = $manager->createAbsoluteUrl('post/view', array('id' => 1, 'title' => 'sample post'));
		$this->assertEquals('http://www.example.com?r=post/view&id=1&title=sample+post', $url);
	}

	public function testParseRequest()
	{
		$manager = new UrlManager(array(
			'cache' => null,
		));
		$request = new Request;

		// default setting without 'r' param
		unset($_GET['r']);
		$result = $manager->parseRequest($request);
		$this->assertEquals(array('', array()), $result);

		// default setting with 'r' param
		$_GET['r'] = 'site/index';
		$result = $manager->parseRequest($request);
		$this->assertEquals(array('site/index', array()), $result);

		// default setting with 'r' param as an array
		$_GET['r'] = array('site/index');
		$result = $manager->parseRequest($request);
		$this->assertEquals(array('', array()), $result);

		// pretty URL without rules
		$manager = new UrlManager(array(
			'enablePrettyUrl' => true,
			'cache' => null,
		));
		// empty pathinfo
		$request->pathInfo = '';
		$result = $manager->parseRequest($request);
		$this->assertEquals(array('', array()), $result);
		// normal pathinfo
		$request->pathInfo = 'site/index';
		$result = $manager->parseRequest($request);
		$this->assertEquals(array('site/index', array()), $result);
		// pathinfo with module
		$request->pathInfo = 'module/site/index';
		$result = $manager->parseRequest($request);
		$this->assertEquals(array('module/site/index', array()), $result);
		// pathinfo with trailing slashes
		$request->pathInfo = 'module/site/index/';
		$result = $manager->parseRequest($request);
		$this->assertEquals(array('module/site/index', array()), $result);

		// pretty URL rules
		$manager = new UrlManager(array(
			'enablePrettyUrl' => true,
			'cache' => null,
			'rules' => array(
				array(
					'pattern' => 'post/<id>/<title>',
					'route' => 'post/view',
				),
			),
		));
		// matching pathinfo
		$request->pathInfo = 'post/123/this+is+sample';
		$result = $manager->parseRequest($request);
		$this->assertEquals(array('post/view', array('id' => '123', 'title' => 'this+is+sample')), $result);
		// matching pathinfo with trailing slashes
		$request->pathInfo = 'post/123/this+is+sample/';
		$result = $manager->parseRequest($request);
		$this->assertEquals(array('post/view', array('id' => '123', 'title' => 'this+is+sample')), $result);
		// empty pathinfo
		$request->pathInfo = '';
		$result = $manager->parseRequest($request);
		$this->assertEquals(array('', array()), $result);
		// normal pathinfo
		$request->pathInfo = 'site/index';
		$result = $manager->parseRequest($request);
		$this->assertEquals(array('site/index', array()), $result);
		// pathinfo with module
		$request->pathInfo = 'module/site/index';
		$result = $manager->parseRequest($request);
		$this->assertEquals(array('module/site/index', array()), $result);

		// pretty URL rules
		$manager = new UrlManager(array(
			'enablePrettyUrl' => true,
			'suffix' => '.html',
			'cache' => null,
			'rules' => array(
				array(
					'pattern' => 'post/<id>/<title>',
					'route' => 'post/view',
				),
			),
		));
		// matching pathinfo
		$request->pathInfo = 'post/123/this+is+sample.html';
		$result = $manager->parseRequest($request);
		$this->assertEquals(array('post/view', array('id' => '123', 'title' => 'this+is+sample')), $result);
		// matching pathinfo without suffix
		$request->pathInfo = 'post/123/this+is+sample';
		$result = $manager->parseRequest($request);
		$this->assertFalse($result);
		// empty pathinfo
		$request->pathInfo = '';
		$result = $manager->parseRequest($request);
		$this->assertEquals(array('', array()), $result);
		// normal pathinfo
		$request->pathInfo = 'site/index.html';
		$result = $manager->parseRequest($request);
		$this->assertEquals(array('site/index', array()), $result);
		// pathinfo without suffix
		$request->pathInfo = 'site/index';
		$result = $manager->parseRequest($request);
		$this->assertFalse($result);

		// strict parsing
		$manager = new UrlManager(array(
			'enablePrettyUrl' => true,
			'enableStrictParsing' => true,
			'suffix' => '.html',
			'cache' => null,
			'rules' => array(
				array(
					'pattern' => 'post/<id>/<title>',
					'route' => 'post/view',
				),
			),
		));
		// matching pathinfo
		$request->pathInfo = 'post/123/this+is+sample.html';
		$result = $manager->parseRequest($request);
		$this->assertEquals(array('post/view', array('id' => '123', 'title' => 'this+is+sample')), $result);
		// unmatching pathinfo
		$request->pathInfo = 'site/index.html';
		$result = $manager->parseRequest($request);
		$this->assertFalse($result);
	}

	public function testParseRESTRequest()
	{
		$manager = new UrlManager(array(
			'cache' => null,
		));
		$request = new Request;

		// pretty URL rules
		$manager = new UrlManager(array(
			'enablePrettyUrl' => true,
			'cache' => null,
			'rules' => array(
				'PUT,POST post/<id>/<title>' => 'post/create',
				'DELETE post/<id>' => 'post/delete',
				'post/<id>/<title>' => 'post/view',
				'POST/GET' => 'post/get',
			),
		));
		// matching pathinfo GET request
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$request->pathInfo = 'post/123/this+is+sample';
		$result = $manager->parseRequest($request);
		$this->assertEquals(array('post/view', array('id' => '123', 'title' => 'this+is+sample')), $result);
		// matching pathinfo PUT/POST request
		$_SERVER['REQUEST_METHOD'] = 'PUT';
		$request->pathInfo = 'post/123/this+is+sample';
		$result = $manager->parseRequest($request);
		$this->assertEquals(array('post/create', array('id' => '123', 'title' => 'this+is+sample')), $result);
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$request->pathInfo = 'post/123/this+is+sample';
		$result = $manager->parseRequest($request);
		$this->assertEquals(array('post/create', array('id' => '123', 'title' => 'this+is+sample')), $result);

		// no wrong matching
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$request->pathInfo = 'POST/GET';
		$result = $manager->parseRequest($request);
		$this->assertEquals(array('post/get', array()), $result);

		// createUrl should ignore REST rules
		$this->mockApplication(array(
			'components' => array(
				'request' => array(
					'hostInfo' => 'http://localhost/',
					'baseUrl' => '/app'
				)
			)
		), \yii\web\Application::className());
		$this->assertEquals('/app/post/delete?id=123', $manager->createUrl('post/delete', array('id' => 123)));
		$this->destroyApplication();

		unset($_SERVER['REQUEST_METHOD']);
	}
}
