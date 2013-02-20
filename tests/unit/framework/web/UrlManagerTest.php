<?php
namespace yiiunit\framework\web;

use yii\web\UrlManager;

class UrlManagerTest extends \yiiunit\TestCase
{
	public function testCreateUrl()
	{
		// default setting with '/' as base url
		$manager = new UrlManager(array(
			'baseUrl' => '/',
		));
		$url = $manager->createUrl('post/view');
		$this->assertEquals('/?r=post/view', $url);
		$url = $manager->createUrl('post/view', array('id' => 1, 'title' => 'sample post'));
		$this->assertEquals('/?r=post/view&id=1&title=sample%20post', $url);

		// default setting with '/test/' as base url
		$manager = new UrlManager(array(
			'baseUrl' => '/test/',
		));
		$url = $manager->createUrl('post/view', array('id' => 1, 'title' => 'sample post'));
		$this->assertEquals('/test/?r=post/view&id=1&title=sample%20post', $url);

		// pretty URL without rules
		$manager = new UrlManager(array(
			'enablePrettyUrl' => true,
			'baseUrl' => '/',
		));
		$url = $manager->createUrl('post/view', array('id' => 1, 'title' => 'sample post'));
		$this->assertEquals('/post/view?r=post/view&id=1&title=sample%20post', $url);
		$manager = new UrlManager(array(
			'enablePrettyUrl' => true,
			'baseUrl' => '/test/',
		));
		$url = $manager->createUrl('post/view', array('id' => 1, 'title' => 'sample post'));
		$this->assertEquals('/test/post/view?r=post/view&id=1&title=sample%20post', $url);
		$manager = new UrlManager(array(
			'enablePrettyUrl' => true,
			'baseUrl' => '/test/index.php',
		));
		$url = $manager->createUrl('post/view', array('id' => 1, 'title' => 'sample post'));
		$this->assertEquals('/test/index.php/post/view?r=post/view&id=1&title=sample%20post', $url);

		// todo: test showScriptName

		// pretty URL with rules
		$manager = new UrlManager(array(
			'enablePrettyUrl' => true,
			'rules' => array(
				array(
					'pattern' => 'post/<id>/<title>',
				),
			),
			'baseUrl' => '/',
		));
		$url = $manager->createUrl('post/view', array('id' => 1, 'title' => 'sample post'));
		$this->assertEquals('/post/view/1/sample%20test', $url);
		$url = $manager->createUrl('post/index', array('page' => 1));
		$this->assertEquals('/post/index?page=1', $url);

		// pretty URL with rules and suffix
		$manager = new UrlManager(array(
			'enablePrettyUrl' => true,
			'rules' => array(
				array(
					'pattern' => 'post/<id>/<title>',
				),
			),
			'baseUrl' => '/',
			'suffix' => '.html',
		));
		$url = $manager->createUrl('post/view', array('id' => 1, 'title' => 'sample post'));
		$this->assertEquals('/post/view/1/sample%20test.html', $url);
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
