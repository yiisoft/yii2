<?php
/**
 * 
 * 
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace yiiunit\framework\web;

use Yii;
use yii\base\View;
use yii\web\AssetBundle;
use yii\web\AssetManager;

/**
 * @group web
 */
class AssetBundleTest extends \yiiunit\TestCase
{
	protected function setUp()
	{
		parent::setUp();
		$this->mockApplication();

		Yii::setAlias('@testWeb', '/');
		Yii::setAlias('@testWebRoot', '@yiiunit/data/web');
	}

	protected function getView()
	{
		$view = new View();
		$view->setAssetManager(new AssetManager([
			'basePath' => '@testWebRoot/assets',
			'baseUrl' => '@testWeb/assets',
		]));

		return $view;
	}

	public function testRegister()
	{
		$view = $this->getView();

		$this->assertEmpty($view->assetBundles);
		TestSimpleAsset::register($view);
		$this->assertEquals(1, count($view->assetBundles));
		$this->assertArrayHasKey('yiiunit\\framework\\web\\TestSimpleAsset', $view->assetBundles);
		$this->assertTrue($view->assetBundles['yiiunit\\framework\\web\\TestSimpleAsset'] instanceof AssetBundle);

		$expected = <<<EOF
123<script src="/js/jquery.js"></script>4
EOF;
		$this->assertEquals($expected, $view->renderFile('@yiiunit/data/views/rawlayout.php'));
	}

	public function testSimpleDependency()
	{
		$view = $this->getView();

		$this->assertEmpty($view->assetBundles);
		TestAssetBundle::register($view);
		$this->assertEquals(3, count($view->assetBundles));
		$this->assertArrayHasKey('yiiunit\\framework\\web\\TestAssetBundle', $view->assetBundles);
		$this->assertArrayHasKey('yiiunit\\framework\\web\\TestJqueryAsset', $view->assetBundles);
		$this->assertArrayHasKey('yiiunit\\framework\\web\\TestAssetLevel3', $view->assetBundles);
		$this->assertTrue($view->assetBundles['yiiunit\\framework\\web\\TestAssetBundle'] instanceof AssetBundle);
		$this->assertTrue($view->assetBundles['yiiunit\\framework\\web\\TestJqueryAsset'] instanceof AssetBundle);
		$this->assertTrue($view->assetBundles['yiiunit\\framework\\web\\TestAssetLevel3'] instanceof AssetBundle);

		$expected = <<<EOF
1<link href="/files/cssFile.css" rel="stylesheet">23<script src="/js/jquery.js"></script>
<script src="/files/jsFile.js"></script>4
EOF;
		$this->assertEquals($expected, $view->renderFile('@yiiunit/data/views/rawlayout.php'));
	}

	public function positionProvider()
	{
		return [
			[View::POS_HEAD, true],
			[View::POS_HEAD, false],
			[View::POS_BEGIN, true],
			[View::POS_BEGIN, false],
			[View::POS_END, true],
			[View::POS_END, false],
		];
	}

	/**
	 * @dataProvider positionProvider
	 */
	public function testPositionDependency($pos, $jqAlreadyRegistered)
	{
		$view = $this->getView();

		$view->getAssetManager()->bundles['yiiunit\\framework\\web\\TestAssetBundle'] = [
			'jsOptions' => [
				'position' => $pos,
			],
		];

		$this->assertEmpty($view->assetBundles);
		if ($jqAlreadyRegistered) {
			TestJqueryAsset::register($view);
		}
		TestAssetBundle::register($view);
		$this->assertEquals(3, count($view->assetBundles));
		$this->assertArrayHasKey('yiiunit\\framework\\web\\TestAssetBundle', $view->assetBundles);
		$this->assertArrayHasKey('yiiunit\\framework\\web\\TestJqueryAsset', $view->assetBundles);
		$this->assertArrayHasKey('yiiunit\\framework\\web\\TestAssetLevel3', $view->assetBundles);

		$this->assertTrue($view->assetBundles['yiiunit\\framework\\web\\TestAssetBundle'] instanceof AssetBundle);
		$this->assertTrue($view->assetBundles['yiiunit\\framework\\web\\TestJqueryAsset'] instanceof AssetBundle);
		$this->assertTrue($view->assetBundles['yiiunit\\framework\\web\\TestAssetLevel3'] instanceof AssetBundle);

		$this->assertArrayHasKey('position', $view->assetBundles['yiiunit\\framework\\web\\TestAssetBundle']->jsOptions);
		$this->assertEquals($pos, $view->assetBundles['yiiunit\\framework\\web\\TestAssetBundle']->jsOptions['position']);
		$this->assertArrayHasKey('position', $view->assetBundles['yiiunit\\framework\\web\\TestJqueryAsset']->jsOptions);
		$this->assertEquals($pos, $view->assetBundles['yiiunit\\framework\\web\\TestJqueryAsset']->jsOptions['position']);
		$this->assertArrayHasKey('position', $view->assetBundles['yiiunit\\framework\\web\\TestAssetLevel3']->jsOptions);
		$this->assertEquals($pos, $view->assetBundles['yiiunit\\framework\\web\\TestAssetLevel3']->jsOptions['position']);

		switch($pos)
		{
			case View::POS_HEAD:
				$expected = <<<EOF
1<link href="/files/cssFile.css" rel="stylesheet">
<script src="/js/jquery.js"></script>
<script src="/files/jsFile.js"></script>234
EOF;
			break;
			case View::POS_BEGIN:
				$expected = <<<EOF
1<link href="/files/cssFile.css" rel="stylesheet">2<script src="/js/jquery.js"></script>
<script src="/files/jsFile.js"></script>34
EOF;
			break;
			default:
			case View::POS_END:
				$expected = <<<EOF
1<link href="/files/cssFile.css" rel="stylesheet">23<script src="/js/jquery.js"></script>
<script src="/files/jsFile.js"></script>4
EOF;
			break;
		}
		$this->assertEquals($expected, $view->renderFile('@yiiunit/data/views/rawlayout.php'));
	}

	public function positionProvider2()
	{
		return [
			[View::POS_BEGIN, true],
			[View::POS_BEGIN, false],
			[View::POS_END, true],
			[View::POS_END, false],
		];
	}

	/**
	 * @dataProvider positionProvider
	 */
	public function testPositionDependencyConflict($pos, $jqAlreadyRegistered)
	{
		$view = $this->getView();

		$view->getAssetManager()->bundles['yiiunit\\framework\\web\\TestAssetBundle'] = [
			'jsOptions' => [
				'position' => $pos - 1,
			],
		];
		$view->getAssetManager()->bundles['yiiunit\\framework\\web\\TestJqueryAsset'] = [
			'jsOptions' => [
				'position' => $pos,
			],
		];

		$this->assertEmpty($view->assetBundles);
		if ($jqAlreadyRegistered) {
			TestJqueryAsset::register($view);
		}
		$this->setExpectedException('yii\\base\\InvalidConfigException');
		TestAssetBundle::register($view);
	}

	public function testCircularDependency()
	{
		$this->setExpectedException('yii\\base\\InvalidConfigException');
		TestAssetCircleA::register($this->getView());
	}
}

class TestSimpleAsset extends AssetBundle
{
	public $basePath = '@testWebRoot/js';
	public $baseUrl = '@testWeb/js';
	public $js = [
		'jquery.js',
	];
}

class TestAssetBundle extends AssetBundle
{
	public $basePath = '@testWebRoot/files';
	public $baseUrl = '@testWeb/files';
	public $css = [
		'cssFile.css',
	];
	public $js = [
		'jsFile.js',
	];
	public $depends = [
		'yiiunit\\framework\\web\\TestJqueryAsset'
	];
}

class TestJqueryAsset extends AssetBundle
{
	public $basePath = '@testWebRoot/js';
	public $baseUrl = '@testWeb/js';
	public $js = [
		'jquery.js',
	];
	public $depends = [
		'yiiunit\\framework\\web\\TestAssetLevel3'
	];
}

class TestAssetLevel3 extends AssetBundle
{
	public $basePath = '@testWebRoot/js';
	public $baseUrl = '@testWeb/js';
}

class TestAssetCircleA extends AssetBundle
{
	public $basePath = '@testWebRoot/js';
	public $baseUrl = '@testWeb/js';
	public $js = [
		'jquery.js',
	];
	public $depends = [
		'yiiunit\\framework\\web\\TestAssetCircleB'
	];
}

class TestAssetCircleB extends AssetBundle
{
	public $basePath = '@testWebRoot/js';
	public $baseUrl = '@testWeb/js';
	public $js = [
		'jquery.js',
	];
	public $depends = [
		'yiiunit\\framework\\web\\TestAssetCircleA'
	];
}