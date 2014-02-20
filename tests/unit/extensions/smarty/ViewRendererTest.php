<?php
/**
 * 
 * 
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace yiiunit\extensions\smarty;

use yii\web\AssetManager;
use yii\web\JqueryAsset;
use yii\web\View;
use Yii;
use yiiunit\TestCase;

/**
 * @group smarty
 */
class ViewRendererTest extends TestCase
{
	protected function setUp()
	{
		$this->mockApplication();
	}

	/**
	 * https://github.com/yiisoft/yii2/issues/2265
	 */
	public function testNoParams()
	{
		$view = $this->mockView();
		$content = $view->renderFile('@yiiunit/extensions/smarty/views/simple.tpl');

		$this->assertEquals('simple view without parameters.', $content);
	}

	public function testRender()
	{
		$view = $this->mockView();
		$content = $view->renderFile('@yiiunit/extensions/smarty/views/view.tpl', ['param' => 'Hello World!']);

		$this->assertEquals('test view Hello World!.', $content);
	}

	/**
	 * @return View
	 */
	protected function mockView()
	{
		return new View([
			'renderers' => [
				'tpl' => [
					'class' => 'yii\smarty\ViewRenderer',
				],
			],
			'assetManager' => $this->mockAssetManager(),
		]);
	}

	protected function mockAssetManager()
	{
		$assetDir = Yii::getAlias('@runtime/assets');
		if (!is_dir($assetDir)) {
			mkdir($assetDir, 0777, true);
		}
		return new AssetManager([
			'basePath' => $assetDir,
			'baseUrl' => '/assets',
		]);
	}
} 