<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

use Yii;
use yii\console\Exception;
use yii\console\Controller;

/**
 * This command allows you to combine and compress your JavaScript and CSS files.
 *
 * @property array|\yii\web\AssetManager $assetManager asset manager, which will be used for assets processing.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AssetController extends Controller
{
	/**
	 * @var string controller default action ID.
	 */
	public $defaultAction = 'compress';
	/**
	 * @var array list of asset bundles to be compressed.
	 * The keys are the bundle names, and the values are the configuration
	 * arrays for creating the [[yii\web\AssetBundle]] objects.
	 */
	public $bundles = array();
	/**
	 * @var array list of paths to the extensions, which assets should be also compressed.
	 * Each path should contain asset manifest file named "assets.php".
	 */
	public $extensions = array();
	/**
	 * @var array list of asset bundles, which represents output compressed files.
	 * You can specify the name of the output compressed file using 'css' and 'js' keys:
	 * For example:
	 * ~~~
	 * 'all' => array(
	 *     'css' => 'all.css',
	 *     'js' => 'js.css',
	 *     'depends' => array( ... ),
	 * )
	 * ~~~
	 * File names can contain placeholder "{ts}", which will be filled by current timestamp, while
	 * file creation.
	 */
	public $targets = array();
	/**
	 * @var array|\yii\web\AssetManager [[yii\web\AssetManager]] instance or its array configuration, which will be used
	 * for assets processing.
	 */
	private $_assetManager = array();
	/**
	 * @var string|callback Java Script file compressor.
	 * If a string, it is treated as shell command template, which should contain
	 * placeholders {from} - source file name - and {to} - output file name.
	 * Otherwise, it is treated as PHP callback, which should perform the compression.
	 *
	 * Default value relies on usage of "Closure Compiler"
	 * @see https://developers.google.com/closure/compiler/
	 */
	public $jsCompressor = 'java -jar compiler.jar --js {from} --js_output_file {to}';
	/**
	 * @var string|callback CSS file compressor.
	 * If a string, it is treated as shell command template, which should contain
	 * placeholders {from} - source file name - and {to} - output file name.
	 * Otherwise, it is treated as PHP callback, which should perform the compression.
	 *
	 * Default value relies on usage of "YUI Compressor"
	 * @see https://github.com/yui/yuicompressor/
	 */
	public $cssCompressor = 'java -jar yuicompressor.jar {from} -o {to}';

	/**
	 * Returns the asset manager instance.
	 * @throws \yii\console\Exception on invalid configuration.
	 * @return \yii\web\AssetManager asset manager instance.
	 */
	public function getAssetManager()
	{
		if (!is_object($this->_assetManager)) {
			$options = $this->_assetManager;
			if (!isset($options['class'])) {
				$options['class'] = 'yii\\web\\AssetManager';
			}
			if (!isset($options['basePath'])) {
				throw new Exception("Please specify 'basePath' for the 'assetManager' option.");
			}
			if (!isset($options['baseUrl'])) {
				throw new Exception("Please specify 'baseUrl' for the 'assetManager' option.");
			}
			$this->_assetManager = Yii::createObject($options);
		}
		return $this->_assetManager;
	}

	/**
	 * Sets asset manager instance or configuration.
	 * @param \yii\web\AssetManager|array $assetManager asset manager instance or its array configuration.
	 * @throws \yii\console\Exception on invalid argument type.
	 */
	public function setAssetManager($assetManager)
	{
		if (is_scalar($assetManager)) {
			throw new Exception('"' . get_class($this) . '::assetManager" should be either object or array - "' . gettype($assetManager) . '" given.');
		}
		$this->_assetManager = $assetManager;
	}

	/**
	 * Combines and compresses the asset files according to the given configuration.
	 * During the process new asset bundle configuration file will be created.
	 * You should replace your original asset bundle configuration with this file in order to use compressed files.
	 * @param string $configFile configuration file name.
	 * @param string $bundleFile output asset bundles configuration file name.
	 */
	public function actionCompress($configFile, $bundleFile)
	{
		$this->loadConfiguration($configFile);
		$bundles = $this->loadBundles($this->bundles, $this->extensions);
		$targets = $this->loadTargets($this->targets, $bundles);
		$this->publishBundles($bundles, $this->assetManager);
		$timestamp = time();
		foreach ($targets as $name => $target) {
			echo "Creating output bundle '{$name}':\n";
			if (!empty($target->js)) {
				$this->buildTarget($target, 'js', $bundles, $timestamp);
			}
			if (!empty($target->css)) {
				$this->buildTarget($target, 'css', $bundles, $timestamp);
			}
			echo "\n";
		}

		$targets = $this->adjustDependency($targets, $bundles);
		$this->saveTargets($targets, $bundleFile);
	}

	/**
	 * Applies configuration from the given file to self instance.
	 * @param string $configFile configuration file name.
	 * @throws \yii\console\Exception on failure.
	 */
	protected function loadConfiguration($configFile)
	{
		echo "Loading configuration from '{$configFile}'...\n";

		foreach (require($configFile) as $name => $value) {
			if (property_exists($this, $name) || $this->canSetProperty($name)) {
				$this->$name = $value;
			} else {
				throw new Exception("Unknown configuration option: $name");
			}
		}

		$this->getAssetManager(); // check asset manager configuration
	}

	/**
	 * Creates full list of source asset bundles.
	 * @param array[] $bundles list of asset bundle configurations.
	 * @param array $extensions list of the extension paths.
	 * @return \yii\web\AssetBundle[] list of source asset bundles.
	 */
	protected function loadBundles($bundles, $extensions)
	{
		echo "Collecting source bundles information...\n";

		$assetManager = $this->getAssetManager();
		$result = array();

		$assetManager->bundles = $bundles;
		foreach ($assetManager->bundles as $name => $bundle) {
			$result[$name] = $assetManager->getBundle($name);
		}

		foreach ($extensions as $path) {
			$manifest = $path . '/assets.php';
			if (!is_file($manifest)) {
				continue;
			}
			$assetManager->bundles = require($manifest);
			foreach ($assetManager->bundles as $name => $bundle) {
				if (!isset($result[$name])) {
					$result[$name] = $assetManager->getBundle($name);
				}
			}
		}

		foreach ($result as $name => $bundle) {
			$this->loadBundleDependency($name, $bundle, $result);
		}

		return $result;
	}

	/**
	 * Loads asset bundle dependencies recursively.
	 * @param string $name bundle name
	 * @param \yii\web\AssetBundle $bundle bundle instance
	 * @param array $result already loaded bundles list.
	 * @throws \yii\console\Exception on failure.
	 */
	protected function loadBundleDependency($name, $bundle, &$result) {
		if (!empty($bundle->depends)) {
			$assetManager = $this->getAssetManager();
			foreach ($bundle->depends as $dependencyName) {
				if (!array_key_exists($dependencyName, $result)) {
					$dependencyBundle = $assetManager->getBundle($dependencyName);
					if ($dependencyBundle === null) {
						throw new Exception("Unable to load dependency bundle '{$dependencyName}' for bundle '{$name}'.");
					} else {
						$result[$dependencyName] = false;
						$this->loadBundleDependency($dependencyName, $dependencyBundle, $result);
						$result[$dependencyName] = $dependencyBundle;
					}
				} else {
					if ($result[$dependencyName] === false) {
						throw new Exception("A circular dependency is detected for target '{$dependencyName}'.");
					}
				}
			}
		}
	}

	/**
	 * Creates full list of output asset bundles.
	 * @param array $targets output asset bundles configuration.
	 * @param \yii\web\AssetBundle[] $bundles list of source asset bundles.
	 * @return \yii\web\AssetBundle[] list of output asset bundles.
	 * @throws \yii\console\Exception on failure.
	 */
	protected function loadTargets($targets, $bundles)
	{
		// build the dependency order of bundles
		$registered = array();
		foreach ($bundles as $name => $bundle) {
			$this->registerBundle($bundles, $name, $registered);
		}
		$bundleOrders = array_combine(array_keys($registered), range(0, count($bundles) - 1));

		// fill up the target which has empty 'depends'.
		$referenced = array();
		foreach ($targets as $name => $target) {
			if (empty($target['depends'])) {
				if (!isset($all)) {
					$all = $name;
				} else {
					throw new Exception("Only one target can have empty 'depends' option. Found two now: $all, $name");
				}
			} else {
				foreach ($target['depends'] as $bundle) {
					if (!isset($referenced[$bundle])) {
						$referenced[$bundle] = $name;
					} else {
						throw new Exception("Target '{$referenced[$bundle]}' and '$name' cannot contain the bundle '$bundle' at the same time.");
					}
				}
			}
		}
		if (isset($all)) {
			$targets[$all]['depends'] = array_diff(array_keys($registered), array_keys($referenced));
		}

		// adjust the 'depends' order for each target according to the dependency order of bundles
		// create an AssetBundle object for each target
		foreach ($targets as $name => $target) {
			if (!isset($target['basePath'])) {
				throw new Exception("Please specify 'basePath' for the '$name' target.");
			}
			if (!isset($target['baseUrl'])) {
				throw new Exception("Please specify 'baseUrl' for the '$name' target.");
			}
			usort($target['depends'], function ($a, $b) use ($bundleOrders) {
				if ($bundleOrders[$a] == $bundleOrders[$b]) {
					return 0;
				} else {
					return $bundleOrders[$a] > $bundleOrders[$b] ? 1 : -1;
				}
			});
			$target['class'] = 'yii\\web\\AssetBundle';
			$targets[$name] = Yii::createObject($target);
		}
		return $targets;
	}

	/**
	 * Publishes given asset bundles.
	 * @param \yii\web\AssetBundle[] $bundles asset bundles to be published.
	 */
	protected function publishBundles($bundles)
	{
		echo "\nPublishing bundles:\n";
		$assetManager = $this->getAssetManager();
		foreach ($bundles as $name => $bundle) {
			$bundle->publish($assetManager);
			echo "  '".$name."' published.\n";
		}
		echo "\n";
	}

	/**
	 * Builds output asset bundle.
	 * @param \yii\web\AssetBundle $target output asset bundle
	 * @param string $type either "js" or "css".
	 * @param \yii\web\AssetBundle[] $bundles source asset bundles.
	 * @param integer $timestamp current timestamp.
	 * @throws Exception on failure.
	 */
	protected function buildTarget($target, $type, $bundles, $timestamp)
	{
		$outputFile = strtr($target->$type, array(
			'{ts}' => $timestamp,
		));
		$inputFiles = array();
		foreach ($target->depends as $name) {
			if (isset($bundles[$name])) {
				$bundle = $bundles[$name];
				foreach ($bundle->$type as $file) {
					if ($bundle->sourcePath === null) {
						// native :
						$inputFiles[] = $bundle->basePath . $file;
					} else {
						// published :
						$inputFiles[] = $this->getAssetManager()->basePath . $file;
					}
				}
			} else {
				throw new Exception("Unknown bundle: '{$name}'");
			}
		}
		if ($type === 'js') {
			$this->compressJsFiles($inputFiles, $target->basePath . '/' . $outputFile);
		} else {
			$this->compressCssFiles($inputFiles, $target->basePath . '/' . $outputFile);
		}
		$target->$type = array($outputFile);
	}

	/**
	 * Adjust dependencies between asset bundles in the way source bundles begin to depend on output ones.
	 * @param \yii\web\AssetBundle[] $targets output asset bundles.
	 * @param \yii\web\AssetBundle[] $bundles source asset bundles.
	 * @return \yii\web\AssetBundle[] output asset bundles.
	 */
	protected function adjustDependency($targets, $bundles)
	{
		echo "Creating new bundle configuration...\n";

		$map = array();
		foreach ($targets as $name => $target) {
			foreach ($target->depends as $bundle) {
				$map[$bundle] = $name;
			}
		}

		foreach ($targets as $name => $target) {
			$depends = array();
			foreach ($target->depends as $bn) {
				foreach ($bundles[$bn]->depends as $bundle) {
					$depends[$map[$bundle]] = true;
				}
			}
			unset($depends[$name]);
			$target->depends = array_keys($depends);
		}

		// detect possible circular dependencies
		foreach ($targets as $name => $target) {
			$registered = array();
			$this->registerBundle($targets, $name, $registered);
		}

		foreach ($map as $bundle => $target) {
			$targets[$bundle] = Yii::createObject(array(
				'class' => 'yii\\web\\AssetBundle',
				'depends' => array($target),
			));
		}
		return $targets;
	}

	/**
	 * Registers asset bundles including their dependencies.
	 * @param \yii\web\AssetBundle[] $bundles asset bundles list.
	 * @param string $name bundle name.
	 * @param array $registered stores already registered names.
	 * @throws \yii\console\Exception if circular dependency is detected.
	 */
	protected function registerBundle($bundles, $name, &$registered)
	{
		if (!isset($registered[$name])) {
			$registered[$name] = false;
			$bundle = $bundles[$name];
			foreach ($bundle->depends as $depend) {
				$this->registerBundle($bundles, $depend, $registered);
			}
			unset($registered[$name]);
			$registered[$name] = true;
		} elseif ($registered[$name] === false) {
			throw new Exception("A circular dependency is detected for target '$name'.");
		}
	}

	/**
	 * Saves new asset bundles configuration.
	 * @param \yii\web\AssetBundle[] $targets list of asset bundles to be saved.
	 * @param string $bundleFile output file name.
	 * @throws \yii\console\Exception on failure.
	 * @return void
	 */
	protected function saveTargets($targets, $bundleFile)
	{
		$array = array();
		foreach ($targets as $name => $target) {
			foreach (array('js', 'css', 'depends', 'basePath', 'baseUrl') as $prop) {
				if (!empty($target->$prop)) {
					$array[$name][$prop] = $target->$prop;
				}
			}
		}
		$array = var_export($array, true);
		$version = date('Y-m-d H:i:s', time());
		$bytesWritten = file_put_contents($bundleFile, <<<EOD
<?php
/**
 * This file is generated by the "yii script" command.
 * DO NOT MODIFY THIS FILE DIRECTLY.
 * @version $version
 */
return $array;
EOD
		);
		if ($bytesWritten <= 0) {
			throw new Exception("Unable to write output bundle configuration at '{$bundleFile}'.");
		}
		echo "Output bundle configuration created at '{$bundleFile}'.\n";
	}

	/**
	 * Compresses given Java Script files and combines them into the single one.
	 * @param array $inputFiles list of source file names.
	 * @param string $outputFile output file name.
	 * @throws \yii\console\Exception on failure
	 */
	protected function compressJsFiles($inputFiles, $outputFile)
	{
		if (empty($inputFiles)) {
			return;
		}
		echo "  Compressing JavaScript files...\n";
		if (is_string($this->jsCompressor)) {
			$tmpFile = $outputFile . '.tmp';
			$this->combineJsFiles($inputFiles, $tmpFile);
			$log = shell_exec(strtr($this->jsCompressor, array(
				'{from}' => escapeshellarg($tmpFile),
				'{to}' => escapeshellarg($outputFile),
			)));
			@unlink($tmpFile);
		} else {
			$log = call_user_func($this->jsCompressor, $this, $inputFiles, $outputFile);
		}
		if (!file_exists($outputFile)) {
			throw new Exception("Unable to compress JavaScript files into '{$outputFile}'.");
		}
		echo "  JavaScript files compressed into '{$outputFile}'.\n";
	}

	/**
	 * Compresses given CSS files and combines them into the single one.
	 * @param array $inputFiles list of source file names.
	 * @param string $outputFile output file name.
	 * @throws \yii\console\Exception on failure
	 */
	protected function compressCssFiles($inputFiles, $outputFile)
	{
		if (empty($inputFiles)) {
			return;
		}
		echo "  Compressing CSS files...\n";
		if (is_string($this->cssCompressor)) {
			$tmpFile = $outputFile . '.tmp';
			$this->combineCssFiles($inputFiles, $tmpFile);
			$log = shell_exec(strtr($this->cssCompressor, array(
				'{from}' => escapeshellarg($tmpFile),
				'{to}' => escapeshellarg($outputFile),
			)));
			@unlink($tmpFile);
		} else {
			$log = call_user_func($this->cssCompressor, $this, $inputFiles, $outputFile);
		}
		if (!file_exists($outputFile)) {
			throw new Exception("Unable to compress CSS files into '{$outputFile}'.");
		}
		echo "  CSS files compressed into '{$outputFile}'.\n";
	}

	/**
	 * Combines Java Script files into a single one.
	 * @param array $inputFiles source file names.
	 * @param string $outputFile output file name.
	 */
	public function combineJsFiles($inputFiles, $outputFile)
	{
		$content = '';
		foreach ($inputFiles as $file) {
			$content .= "/*** BEGIN FILE: $file ***/\n"
				. file_get_contents($file)
				. "/*** END FILE: $file ***/\n";
		}
		file_put_contents($outputFile, $content);
	}

	/**
	 * Combines CSS files into a single one.
	 * @param array $inputFiles source file names.
	 * @param string $outputFile output file name.
	 */
	public function combineCssFiles($inputFiles, $outputFile)
	{
		// todo: adjust url() references in CSS files
		$content = '';
		foreach ($inputFiles as $file) {
			$content .= "/*** BEGIN FILE: $file ***/\n"
				. file_get_contents($file)
				. "/*** END FILE: $file ***/\n";
		}
		file_put_contents($outputFile, $content);
	}

	/**
	 * Creates template of configuration file for [[actionCompress]].
	 * @param string $configFile output file name.
	 */
	public function actionTemplate($configFile)
	{
		$template = <<<EOD
<?php

return array(
	//
	'bundles' => require('path/to/bundles.php'),
	//
	'extensions' => require('path/to/namespaces.php'),
	//
	'targets' => array(
		'all' => array(
			'basePath' => __DIR__,
			'baseUrl' => '/test',
			'js' => 'all-{ts}.js',
			'css' => 'all-{ts}.css',
		),
	),

	'assetManager' => array(
		'basePath' => __DIR__,
		'baseUrl' => '/test',
	),
);
EOD;
		if (file_exists($configFile)) {
			if (!$this->confirm("File '{$configFile}' already exists. Do you wish to overwrite it?")) {
				return;
			}
		}
		$bytesWritten = file_put_contents($configFile, $template);
		if ($bytesWritten<=0) {
			echo "Error: unable to write file '{$configFile}'!\n\n";
		} else {
			echo "Configuration file template created at '{$configFile}'.\n\n";
		}
	}
}
