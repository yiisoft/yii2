<?php

use yiiunit\TestCase;
use yii\console\controllers\AssetController;

/**
 * Unit test for [[yii\console\controllers\AssetController]].
 * @see AssetController
 */
class AssetControllerTest extends TestCase
{
	/**
	 * @var string path for the test files.
	 */
	protected $testFilePath = '';

	public function setUp()
	{
		$this->testFilePath = Yii::getAlias('@yiiunit/runtime') . DIRECTORY_SEPARATOR . get_class($this);
		$this->createDir($this->testFilePath);
	}

	public function tearDown()
	{
		$this->removeDir($this->testFilePath);
	}

	/**
	 * Creates directory.
	 * @param $dirName directory full name.
	 */
	protected function createDir($dirName)
	{
		if (!file_exists($dirName)) {
			mkdir($dirName, 0777, true);
		}
	}

	/**
	 * Removes directory.
	 * @param $dirName directory full name
	 */
	protected function removeDir($dirName)
	{
		if (!empty($dirName) && file_exists($dirName)) {
			exec("rm -rf {$dirName}");
		}
	}

	/**
	 * Creates test asset controller instance.
	 * @return AssetController
	 */
	protected function createAssetController()
	{
		$module = $this->getMock('yii\\base\\Module', array('fake'), array('console'));
		$assetController = new AssetController('asset', $module);
		$assetController->interactive = false;
		$assetController->jsCompressor = 'cp {from} {to}';
		$assetController->cssCompressor = 'cp {from} {to}';
		return $assetController;
	}

	/**
	 * Emulates running of the asset controller action.
	 * @param string $actionId id of action to be run.
	 * @param array $args action arguments.
	 * @return string command output.
	 */
	protected function runAssetControllerAction($actionId, array $args=array())
	{
		$controller = $this->createAssetController();
		ob_start();
		ob_implicit_flush(false);
		$params = array(
			\yii\console\Request::ANONYMOUS_PARAMS => $args
		);
		$controller->run($actionId, $params);
		return ob_get_clean();
	}

	// Tests :

	public function testActionTemplate()
	{
		$configFileName = $this->testFilePath . DIRECTORY_SEPARATOR . 'config.php';
		$this->runAssetControllerAction('template', array($configFileName));

		$this->assertTrue(file_exists($configFileName), 'Unable to create config file template!');
	}
}
