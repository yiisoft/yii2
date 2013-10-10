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
		$view->setAssetManager(new AssetManager(array(
			'basePath' => '@testWebRoot/assets',
			'baseUrl' => '@testWeb/assets',
		)));

		return $view;
	}

	public function testRegister()
	{
		$view = $this->getView();

		$this->assertEmpty($view->assetBundles);
		TestJqueryAsset::register($view);
		$this->assertEquals(1, count($view->assetBundles));
		$this->assertArrayHasKey('yiiunit\\framework\\web\\TestJqueryAsset', $view->assetBundles);
	}

	public function testSimpleDependency()
	{
		$view = $this->getView();

		$this->assertEmpty($view->assetBundles);
		TestAssetBundle::register($view);
		$this->assertEquals(2, count($view->assetBundles));
		$this->assertArrayHasKey('yiiunit\\framework\\web\\TestAssetBundle', $view->assetBundles);
		$this->assertArrayHasKey('yiiunit\\framework\\web\\TestJqueryAsset', $view->assetBundles);
	}
}

class TestAssetBundle extends AssetBundle
{
	public $basePath = '@testWebRoot/files';
	public $baseUrl = '@testWeb/files';
	public $css = array(
		'cssFile.css',
	);
	public $js = array(
		'jsFile.js',
	);
	public $depends = array(
		'yiiunit\\framework\\web\\TestJqueryAsset'
	);
}

class TestJqueryAsset extends AssetBundle
{
	public $basePath = '@testWebRoot/js';
	public $baseUrl = '@testWeb/js';
	public $js = array(
		'jquery.js',
	);
}