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

	/**
	 * Creates test compress config.
	 * @return array config array.
	 */
	protected function createCompressConfig()
	{
		$baseUrl = '/test';

		$assetsBasePath = $this->testFilePath.DIRECTORY_SEPARATOR.'assets';
		$this->createDir($assetsBasePath);

		$config = array(
			'bundles' => $this->createBundleConfig(),
			'targets' => array(
				'all' => array(
					'basePath' => $assetsBasePath,
					'baseUrl' => $baseUrl,
					'js' => 'all-{ts}.js',
					'css' => 'all-{ts}.css',
				),
			),
			'assetManager' => array(
				'basePath' => $assetsBasePath,
				'baseUrl' => $baseUrl,
			),
		);
		return $config;
	}

	/**
	 * Creates test bundle configuration.
	 * @return array bundle config.
	 */
	protected function createBundleConfig()
	{
		$baseUrl = '/test';
		$bundles = array(
			'app' => array(
				'basePath' => $this->testFilePath,
				'baseUrl' => $baseUrl,
				'css' => array(
					'css/test.css',
				),
				'js' => array(
					'js/test.js',
				),
				'depends' => array(
					'yii',
				),
			),
		);
		return $bundles;
	}

	/**
	 * Creates test bundles configuration file.
	 * @param string $fileName output filename.
	 * @return boolean success.
	 */
	protected function createBundleFile($fileName)
	{
		$content = '<?php return '.var_export($this->createBundleConfig(), true).';';
		return (file_put_contents($fileName, $content) > 0);
	}

	/**
	 * Creates test compress config file.
	 * @param string $fileName output file name.
	 * @return boolean success.
	 */
	protected function createCompressConfigFile($fileName)
	{
		$content = '<?php return '.var_export($this->createCompressConfig(), true).';';
		return (file_put_contents($fileName, $content) > 0);
	}

	// Tests :

	public function testActionTemplate()
	{
		$configFileName = $this->testFilePath . DIRECTORY_SEPARATOR . 'config.php';
		$this->runAssetControllerAction('template', array($configFileName));
		$this->assertTrue(file_exists($configFileName), 'Unable to create config file template!');
	}

	public function testActionCompress()
	{
		$configFile = $this->testFilePath . DIRECTORY_SEPARATOR . 'config.php';
		$this->createCompressConfigFile($configFile);
		$bundleFile = $this->testFilePath . DIRECTORY_SEPARATOR . 'bundle.php';
		$this->createBundleFile($bundleFile);

		$this->runAssetControllerAction('compress', array($configFile, $bundleFile));

		$this->markTestIncomplete();
	}
}
