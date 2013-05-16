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
	 * @var array configuration for [[yii\web\AssetManager]] instance, which will be used
	 * for assets publishing.
	 */
	public $assetManager = array();
	/**
	 * @var string|callback Java Script file compressor.
	 * If a string, it is treated as shell command template, which should contain
	 * placeholders {from} - source file name - and {to} - output file name.
	 * If an array, it is treated as PHP callback, which should perform the compression.
	 *
	 * Default value relies on usage of "Closure Compiler"
	 * @see https://developers.google.com/closure/compiler/
	 */
	public $jsCompressor = 'java -jar compiler.jar --js {from} --js_output_file {to}';
	/**
	 * @var string|callback CSS file compressor.
	 * If a string, it is treated as shell command template, which should contain
	 * placeholders {from} - source file name - and {to} - output file name.
	 * If an array, it is treated as PHP callback, which should perform the compression.
	 *
	 * Default value relies on usage of "YUI Compressor"
	 * @see https://github.com/yui/yuicompressor/
	 */
	public $cssCompressor = 'java -jar yuicompressor.jar {from} -o {to}';

	/**
	 * Compresses the asset files according to the given configuration.
	 * @param string $configFile configuration file name.
	 * @param string $bundleFile
	 */
	public function actionCompress($configFile, $bundleFile)
	{
		$this->loadConfiguration($configFile);
		$bundles = $this->loadBundles($this->bundles, $this->extensions);
		$targets = $this->loadTargets($this->targets, $bundles);
		$this->publishBundles($bundles, $this->assetManager);
		$timestamp = time();
		foreach ($targets as $target) {
			if (!empty($target->js)) {
				$this->buildTarget($target, 'js', $bundles, $timestamp);
			}
			if (!empty($target->css)) {
				$this->buildTarget($target, 'css', $bundles, $timestamp);
			}
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
		foreach (require($configFile) as $name => $value) {
			if (property_exists($this, $name)) {
				$this->$name = $value;
			} else {
				throw new Exception("Unknown configuration option: $name");
			}
		}

		if (!isset($this->assetManager['basePath'])) {
			throw new Exception("Please specify 'basePath' for the 'assetManager' option.");
		}
		if (!isset($this->assetManager['baseUrl'])) {
			throw new Exception("Please specify 'baseUrl' for the 'assetManager' option.");
		}
	}

	/**
	 * Creates full list of asset bundles.
	 * @param array[] $bundles list of asset bundle configurations.
	 * @param array $extensions list of the extension paths.
	 * @return \yii\web\AssetBundle[] list of asset bundles.
	 */
	protected function loadBundles($bundles, $extensions)
	{
		$result = array();
		foreach ($bundles as $name => $bundle) {
			$bundle['class'] = 'yii\\web\\AssetBundle';
			$result[$name] = Yii::createObject($bundle);
		}
		foreach ($extensions as $path) {
			$manifest = $path . '/assets.php';
			if (!is_file($manifest)) {
				continue;
			}
			foreach (require($manifest) as $name => $bundle) {
				if (!isset($result[$name])) {
					$bundle['class'] = 'yii\\web\\AssetBundle';
					$result[$name] = Yii::createObject($bundle);
				}
			}
		}
		return $result;
	}

	/**
	 * @param array $targets
	 * @param \yii\web\AssetBundle[] $bundles list of asset bundles.
	 * @return \yii\web\AssetBundle[]
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
	 * @param \yii\web\AssetBundle[] $bundles
	 * @param array $options
	 */
	protected function publishBundles($bundles, $options)
	{
		if (!isset($options['class'])) {
			$options['class'] = 'yii\\web\\AssetManager';
		}
		$am = Yii::createObject($options);
		foreach ($bundles as $bundle) {
			$bundle->publish($am);
		}
	}

	/**
	 * @param \yii\web\AssetBundle $target
	 * @param string $type either "js" or "css"
	 * @param \yii\web\AssetBundle[] $bundles
	 * @param integer $timestamp
	 * @throws Exception
	 */
	protected function buildTarget($target, $type, $bundles, $timestamp)
	{
		$outputFile = strtr($target->$type, array(
			'{ts}' => $timestamp,
		));
		$inputFiles = array();

		foreach ($target->depends as $name) {
			if (isset($bundles[$name])) {
				foreach ($bundles[$name]->$type as $file) {
					$inputFiles[] = $bundles[$name]->basePath . $file;
				}
			} else {
				throw new Exception("Unknown bundle: $name");
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
	 * @param \yii\web\AssetBundle[] $targets
	 * @param \yii\web\AssetBundle[] $bundles
	 * @return \yii\web\AssetBundle[]
	 */
	protected function adjustDependency($targets, $bundles)
	{
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
		file_put_contents($bundleFile, <<<EOD
<?php
/**
 * This file is generated by the "yiic script" command.
 * DO NOT MODIFY THIS FILE DIRECTLY.
 * @version $version
 */
return $array;
EOD
		);
	}

	/**
	 * Compresses given Java Script files and combines them into the single one.
	 * @param array $inputFiles list of source file names.
	 * @param string $outputFile output file name.
	 */
	protected function compressJsFiles($inputFiles, $outputFile)
	{
		if (is_string($this->jsCompressor)) {
			$tmpFile = $outputFile . '.tmp';
			$this->combineJsFiles($inputFiles, $tmpFile);
			$log = shell_exec(strtr($this->jsCompressor, array(
				'{from}' => $tmpFile,
				'{to}' => $outputFile,
			)));
			@unlink($tmpFile);
		} else {
			$log = call_user_func($this->jsCompressor, $this, $inputFiles, $outputFile);
		}
	}

	/**
	 * Compresses given CSS files and combines them into the single one.
	 * @param array $inputFiles list of source file names.
	 * @param string $outputFile output file name.
	 */
	protected function compressCssFiles($inputFiles, $outputFile)
	{
		if (is_string($this->cssCompressor)) {
			$tmpFile = $outputFile . '.tmp';
			$this->combineCssFiles($inputFiles, $tmpFile);
			$log = shell_exec(strtr($this->cssCompressor, array(
				'{from}' => $tmpFile,
				'{to}' => $outputFile,
			)));
			@unlink($tmpFile);
		} else {
			$log = call_user_func($this->cssCompressor, $this, $inputFiles, $outputFile);
		}
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
