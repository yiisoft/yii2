<?php

namespace yiiunit\framework\web;

use yii\web\UrlRule;

class UrlRuleTest extends \yiiunit\TestCase
{
	public function testCreateUrl()
	{
		$suites = $this->getTestsForCreateUrl();
		foreach ($suites as $i => $suite) {
			list ($name, $config, $tests) = $suite;
			$rule = new UrlRule($config);
			foreach ($tests as $j => $test) {
				list ($route, $params, $expected) = $test;
				$url = $rule->createUrl($route, $params);
				$this->assertEquals($expected, $url, "Test#$i-$j: $name");
			}
		}
	}

	public function testParseUrl()
	{
		$suites = $this->getTestsForParseUrl();
		foreach ($suites as $i => $suite) {
			list ($name, $config, $tests) = $suite;
			$rule = new UrlRule($config);
			foreach ($tests as $j => $test) {
				$pathInfo = $test[0];
				$route = $test[1];
				$params = isset($test[2]) ? $test[2] : array();
				$result = $rule->parseUrl($pathInfo);
				if ($route === false) {
					$this->assertFalse($result, "Test#$i-$j: $name");
				} else {
					$this->assertEquals(array($route, $params), $result, "Test#$i-$j: $name");
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
		return array(
			array(
				'empty pattern',
				array(
					'pattern' => '',
					'route' => 'post/index',
				),
				array(
					array('post/index', array(), ''),
					array('comment/index', array(), false),
					array('post/index', array('page' => 1), '?page=1'),
				),
			),
			array(
				'without param',
				array(
					'pattern' => 'posts',
					'route' => 'post/index',
				),
				array(
					array('post/index', array(), 'posts'),
					array('comment/index', array(), false),
					array('post/index', array('page' => 1), 'posts?page=1'),
				),
			),
			array(
				'with param',
				array(
					'pattern' => 'post/<page>',
					'route' => 'post/index',
				),
				array(
					array('post/index', array(), false),
					array('comment/index', array(), false),
					array('post/index', array('page' => 1), 'post/1'),
					array('post/index', array('page' => 1, 'tag' => 'a'), 'post/1?tag=a'),
				),
			),
			array(
				'with param requirement',
				array(
					'pattern' => 'post/<page:\d+>',
					'route' => 'post/index',
				),
				array(
					array('post/index', array('page' => 'abc'), false),
					array('post/index', array('page' => 1), 'post/1'),
					array('post/index', array('page' => 1, 'tag' => 'a'), 'post/1?tag=a'),
				),
			),
			array(
				'with multiple params',
				array(
					'pattern' => 'post/<page:\d+>-<tag>',
					'route' => 'post/index',
				),
				array(
					array('post/index', array('page' => '1abc'), false),
					array('post/index', array('page' => 1), false),
					array('post/index', array('page' => 1, 'tag' => 'a'), 'post/1-a'),
				),
			),
			array(
				'with optional param',
				array(
					'pattern' => 'post/<page:\d+>/<tag>',
					'route' => 'post/index',
					'defaults' => array('page' => 1),
				),
				array(
					array('post/index', array('page' => 1), false),
					array('post/index', array('page' => '1abc', 'tag' => 'a'), false),
					array('post/index', array('page' => 1, 'tag' => 'a'), 'post/a'),
					array('post/index', array('page' => 2, 'tag' => 'a'), 'post/2/a'),
				),
			),
			array(
				'with optional param not in pattern',
				array(
					'pattern' => 'post/<tag>',
					'route' => 'post/index',
					'defaults' => array('page' => 1),
				),
				array(
					array('post/index', array('page' => 1), false),
					array('post/index', array('page' => '1abc', 'tag' => 'a'), false),
					array('post/index', array('page' => 2, 'tag' => 'a'), false),
					array('post/index', array('page' => 1, 'tag' => 'a'), 'post/a'),
				),
			),
			array(
				'multiple optional params',
				array(
					'pattern' => 'post/<page:\d+>/<tag>/<sort:yes|no>',
					'route' => 'post/index',
					'defaults' => array('page' => 1, 'sort' => 'yes'),
				),
				array(
					array('post/index', array('page' => 1), false),
					array('post/index', array('page' => '1abc', 'tag' => 'a'), false),
					array('post/index', array('page' => 1, 'tag' => 'a', 'sort' => 'YES'), false),
					array('post/index', array('page' => 1, 'tag' => 'a', 'sort' => 'yes'), 'post/a'),
					array('post/index', array('page' => 2, 'tag' => 'a', 'sort' => 'yes'), 'post/2/a'),
					array('post/index', array('page' => 2, 'tag' => 'a', 'sort' => 'no'), 'post/2/a/no'),
					array('post/index', array('page' => 1, 'tag' => 'a', 'sort' => 'no'), 'post/a/no'),
				),
			),
			array(
				'optional param and required param separated by dashes',
				array(
					'pattern' => 'post/<page:\d+>-<tag>',
					'route' => 'post/index',
					'defaults' => array('page' => 1),
				),
				array(
					array('post/index', array('page' => 1), false),
					array('post/index', array('page' => '1abc', 'tag' => 'a'), false),
					array('post/index', array('page' => 1, 'tag' => 'a'), 'post/-a'),
					array('post/index', array('page' => 2, 'tag' => 'a'), 'post/2-a'),
				),
			),
			array(
				'optional param at the end',
				array(
					'pattern' => 'post/<tag>/<page:\d+>',
					'route' => 'post/index',
					'defaults' => array('page' => 1),
				),
				array(
					array('post/index', array('page' => 1), false),
					array('post/index', array('page' => '1abc', 'tag' => 'a'), false),
					array('post/index', array('page' => 1, 'tag' => 'a'), 'post/a'),
					array('post/index', array('page' => 2, 'tag' => 'a'), 'post/a/2'),
				),
			),
			array(
				'consecutive optional params',
				array(
					'pattern' => 'post/<page:\d+>/<tag>',
					'route' => 'post/index',
					'defaults' => array('page' => 1, 'tag' => 'a'),
				),
				array(
					array('post/index', array('page' => 1), false),
					array('post/index', array('page' => '1abc', 'tag' => 'a'), false),
					array('post/index', array('page' => 1, 'tag' => 'a'), 'post'),
					array('post/index', array('page' => 2, 'tag' => 'a'), 'post/2'),
					array('post/index', array('page' => 1, 'tag' => 'b'), 'post/b'),
					array('post/index', array('page' => 2, 'tag' => 'b'), 'post/2/b'),
				),
			),
			array(
				'consecutive optional params separated by dash',
				array(
					'pattern' => 'post/<page:\d+>-<tag>',
					'route' => 'post/index',
					'defaults' => array('page' => 1, 'tag' => 'a'),
				),
				array(
					array('post/index', array('page' => 1), false),
					array('post/index', array('page' => '1abc', 'tag' => 'a'), false),
					array('post/index', array('page' => 1, 'tag' => 'a'), 'post/-'),
					array('post/index', array('page' => 1, 'tag' => 'b'), 'post/-b'),
					array('post/index', array('page' => 2, 'tag' => 'a'), 'post/2-'),
					array('post/index', array('page' => 2, 'tag' => 'b'), 'post/2-b'),
				),
			),
			array(
				'route has parameters',
				array(
					'pattern' => '<controller>/<action>',
					'route' => '<controller>/<action>',
					'defaults' => array(),
				),
				array(
					array('post/index', array('page' => 1), 'post/index?page=1'),
					array('module/post/index', array(), false),
				),
			),
			array(
				'route has parameters with regex',
				array(
					'pattern' => '<controller:post|comment>/<action>',
					'route' => '<controller>/<action>',
					'defaults' => array(),
				),
				array(
					array('post/index', array('page' => 1), 'post/index?page=1'),
					array('comment/index', array('page' => 1), 'comment/index?page=1'),
					array('test/index', array('page' => 1), false),
					array('post', array(), false),
					array('module/post/index', array(), false),
					array('post/index', array('controller' => 'comment'), 'post/index?controller=comment'),
				),
			),
			array(
				'route has default parameter',
				array(
					'pattern' => '<controller:post|comment>/<action>',
					'route' => '<controller>/<action>',
					'defaults' => array('action' => 'index'),
				),
				array(
					array('post/view', array('page' => 1), 'post/view?page=1'),
					array('comment/view', array('page' => 1), 'comment/view?page=1'),
					array('test/view', array('page' => 1), false),
					array('test/index', array('page' => 1), false),
					array('post/index', array('page' => 1), 'post?page=1'),
				),
			),
		);
	}

	protected function getTestsForParseUrl()
	{
		// structure of each test
		//   message for the test
		//   config for the URL rule
		//   list of inputs and outputs
		//     pathInfo
		//     expected route, or false if the rule doesn't apply
		//     expected params, or not set if empty
		return array(
			array(
				'empty pattern',
				array(
					'pattern' => '',
					'route' => 'post/index',
				),
				array(
					array('', 'post/index'),
					array('a', false),
				),
			),
			array(
				'without param',
				array(
					'pattern' => 'posts',
					'route' => 'post/index',
				),
				array(
					array('posts', 'post/index'),
					array('a', false),
				),
			),
			array(
				'with param',
				array(
					'pattern' => 'post/<page>',
					'route' => 'post/index',
				),
				array(
					array('post/1', 'post/index', array('page' => '1')),
					array('post/a', 'post/index', array('page' => 'a')),
					array('post', false),
					array('posts', false),
				),
			),
			array(
				'with param requirement',
				array(
					'pattern' => 'post/<page:\d+>',
					'route' => 'post/index',
				),
				array(
					array('post/1', 'post/index', array('page' => '1')),
					array('post/a', false),
					array('post/1/a', false),
				),
			),
			array(
				'with multiple params',
				array(
					'pattern' => 'post/<page:\d+>-<tag>',
					'route' => 'post/index',
				),
				array(
					array('post/1-a', 'post/index', array('page' => '1', 'tag' => 'a')),
					array('post/a', false),
					array('post/1', false),
					array('post/1/a', false),
				),
			),
			array(
				'with optional param',
				array(
					'pattern' => 'post/<page:\d+>/<tag>',
					'route' => 'post/index',
					'defaults' => array('page' => 1),
				),
				array(
					array('post/1/a', 'post/index', array('page' => '1', 'tag' => 'a')),
					array('post/2/a', 'post/index', array('page' => '2', 'tag' => 'a')),
					array('post/a', 'post/index', array('page' => '1', 'tag' => 'a')),
					array('post/1', 'post/index', array('page' => '1', 'tag' => '1')),
				),
			),
			array(
				'with optional param not in pattern',
				array(
					'pattern' => 'post/<tag>',
					'route' => 'post/index',
					'defaults' => array('page' => 1),
				),
				array(
					array('post/a', 'post/index', array('page' => '1', 'tag' => 'a')),
					array('post/1', 'post/index', array('page' => '1', 'tag' => '1')),
					array('post', false),
				),
			),
			array(
				'multiple optional params',
				array(
					'pattern' => 'post/<page:\d+>/<tag>/<sort:yes|no>',
					'route' => 'post/index',
					'defaults' => array('page' => 1, 'sort' => 'yes'),
				),
				array(
					array('post/1/a/yes', 'post/index', array('page' => '1', 'tag' => 'a', 'sort' => 'yes')),
					array('post/2/a/no', 'post/index', array('page' => '2', 'tag' => 'a', 'sort' => 'no')),
					array('post/2/a', 'post/index', array('page' => '2', 'tag' => 'a', 'sort' => 'yes')),
					array('post/a/no', 'post/index', array('page' => '1', 'tag' => 'a', 'sort' => 'no')),
					array('post/a', 'post/index', array('page' => '1', 'tag' => 'a', 'sort' => 'yes')),
					array('post', false),
				),
			),
			array(
				'optional param and required param separated by dashes',
				array(
					'pattern' => 'post/<page:\d+>-<tag>',
					'route' => 'post/index',
					'defaults' => array('page' => 1),
				),
				array(
					array('post/1-a', 'post/index', array('page' => '1', 'tag' => 'a')),
					array('post/2-a', 'post/index', array('page' => '2', 'tag' => 'a')),
					array('post/-a', 'post/index', array('page' => '1', 'tag' => 'a')),
					array('post/a', false),
					array('post-a', false),
				),
			),
			array(
				'optional param at the end',
				array(
					'pattern' => 'post/<tag>/<page:\d+>',
					'route' => 'post/index',
					'defaults' => array('page' => 1),
				),
				array(
					array('post/a/1', 'post/index', array('page' => '1', 'tag' => 'a')),
					array('post/a/2', 'post/index', array('page' => '2', 'tag' => 'a')),
					array('post/a', 'post/index', array('page' => '1', 'tag' => 'a')),
					array('post/2', 'post/index', array('page' => '1', 'tag' => '2')),
					array('post', false),
				),
			),
			array(
				'consecutive optional params',
				array(
					'pattern' => 'post/<page:\d+>/<tag>',
					'route' => 'post/index',
					'defaults' => array('page' => 1, 'tag' => 'a'),
				),
				array(
					array('post/2/b', 'post/index', array('page' => '2', 'tag' => 'b')),
					array('post/2', 'post/index', array('page' => '2', 'tag' => 'a')),
					array('post', 'post/index', array('page' => '1', 'tag' => 'a')),
					array('post/b', 'post/index', array('page' => '1', 'tag' => 'b')),
					array('post//b', false),
				),
			),
			array(
				'consecutive optional params separated by dash',
				array(
					'pattern' => 'post/<page:\d+>-<tag>',
					'route' => 'post/index',
					'defaults' => array('page' => 1, 'tag' => 'a'),
				),
				array(
					array('post/2-b', 'post/index', array('page' => '2', 'tag' => 'b')),
					array('post/2-', 'post/index', array('page' => '2', 'tag' => 'a')),
					array('post/-b', 'post/index', array('page' => '1', 'tag' => 'b')),
					array('post/-', 'post/index', array('page' => '1', 'tag' => 'a')),
					array('post', false),
				),
			),
			array(
				'route has parameters',
				array(
					'pattern' => '<controller>/<action>',
					'route' => '<controller>/<action>',
					'defaults' => array(),
				),
				array(
					array('post/index', 'post/index'),
					array('module/post/index', false),
				),
			),
			array(
				'route has parameters with regex',
				array(
					'pattern' => '<controller:post|comment>/<action>',
					'route' => '<controller>/<action>',
					'defaults' => array(),
				),
				array(
					array('post/index', 'post/index'),
					array('comment/index', 'comment/index'),
					array('test/index', false),
					array('post', false),
					array('module/post/index', false),
				),
			),
			array(
				'route has default parameter',
				array(
					'pattern' => '<controller:post|comment>/<action>',
					'route' => '<controller>/<action>',
					'defaults' => array('action' => 'index'),
				),
				array(
					array('post/view', 'post/view'),
					array('comment/view', 'comment/view'),
					array('test/view', false),
					array('post', 'post/index'),
					array('posts', false),
					array('test', false),
					array('index', false),
				),
			),
		);
	}
}
