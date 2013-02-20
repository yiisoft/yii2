<?php
namespace yiiunit\framework\web;

use yii\web\Application;
use yii\web\UrlManager;

class UrlManagerTest extends \yiiunit\TestCase
{
	public function testCreateUrl()
	{
		new Application('test', __DIR__ . '/../..');
		// default setting with '/' as base url
		$manager = new UrlManager(array(
			'baseUrl' => '/',
		));
		$url = $manager->createUrl('post/view');
		$this->assertEquals('/?r=post/view', $url);
		$url = $manager->createUrl('post/view', array('id' => 1, 'title' => 'sample post'));
		$this->assertEquals('/?r=post/view&id=1&title=sample+post', $url);

		// default setting with '/test/' as base url
		$manager = new UrlManager(array(
			'baseUrl' => '/test/',
		));
		$url = $manager->createUrl('post/view', array('id' => 1, 'title' => 'sample post'));
		$this->assertEquals('/test/?r=post/view&id=1&title=sample+post', $url);

		// pretty URL without rules
		$manager = new UrlManager(array(
			'enablePrettyUrl' => true,
			'baseUrl' => '/',
		));
		$url = $manager->createUrl('post/view', array('id' => 1, 'title' => 'sample post'));
		$this->assertEquals('/post/view?id=1&title=sample+post', $url);
		$manager = new UrlManager(array(
			'enablePrettyUrl' => true,
			'baseUrl' => '/test/',
		));
		$url = $manager->createUrl('post/view', array('id' => 1, 'title' => 'sample post'));
		$this->assertEquals('/test/post/view?id=1&title=sample+post', $url);
		$manager = new UrlManager(array(
			'enablePrettyUrl' => true,
			'baseUrl' => '/test/index.php',
		));
		$url = $manager->createUrl('post/view', array('id' => 1, 'title' => 'sample post'));
		$this->assertEquals('/test/index.php/post/view?id=1&title=sample+post', $url);

		// todo: test showScriptName

		// pretty URL with rules
		$manager = new UrlManager(array(
			'enablePrettyUrl' => true,
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
	}

	public function testCreateAbsoluteUrl()
	{
		
	}

	public function testParseRequest()
	{

	}
}
