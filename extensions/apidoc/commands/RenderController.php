<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\commands;

use phpDocumentor\Reflection\FileReflector;
use TokenReflection\ReflectionFile;
use yii\apidoc\templates\BaseRenderer;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\apidoc\components\OfflineRenderer;
use yii\apidoc\models\Context;
use Yii;

/**
 * Command to render API Documentation files
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class RenderController extends Controller
{
	public $template = 'bootstrap';

	public $guide;

	/**
	 * Renders API documentation files
	 * @param array $sourceDirs
	 * @param string $targetDir
	 * @return int
	 */
	public function actionIndex(array $sourceDirs, $targetDir)
	{
		$targetDir = rtrim(Yii::getAlias($targetDir), '\\/');
		if (is_dir($targetDir) && !$this->confirm('TargetDirectory already exists. Overwrite?')) {
			return 2;
		}
		if (!is_dir($targetDir)) {
			mkdir($targetDir);
		}

		$renderer = $this->findRenderer();
		if ($renderer === false) {
			return 1;
		}
		$renderer->targetDir = $targetDir;
		if ($this->guide !== null && $renderer->hasProperty('guideUrl')) {
			$renderer->guideUrl = './';
		}

		$this->stdout('Searching files to process... ');
		$files = [];
		foreach($sourceDirs as $source) {
			foreach($this->findFiles($source) as $fileName) {
				$files[$fileName] = $fileName;
			}
		}

		$this->stdout('done.' . PHP_EOL, Console::FG_GREEN);

		$context = new Context();

		$cacheFile = $targetDir . '/cache/' . md5(serialize($files)) . '.tmp';
		if (file_exists($cacheFile)) {
			$this->stdout('Loading processed data from cache... ');
			$context = unserialize(file_get_contents($cacheFile));
			$this->stdout('done.' . PHP_EOL, Console::FG_GREEN);

			$this->stdout('Checking for updated files... ');
			foreach($context->files as $file => $sha) {
				if (sha1_file($file) === $sha) {
					unset($files[$file]);
				}
			}
			$this->stdout('done.' . PHP_EOL, Console::FG_GREEN);
		}

		$fileCount = count($files);
		$this->stdout($fileCount . ' file' . ($fileCount == 1 ? '' : 's') . ' to update.' . PHP_EOL);
		Console::startProgress(0, $fileCount, 'Processing files... ', false);
		$done = 0;
		foreach($files as $file) {
			$context->addFile($file);
			Console::updateProgress(++$done, $fileCount);
		}
		Console::endProgress(true);
		$this->stdout('done.' . PHP_EOL, Console::FG_GREEN);

		// save processed data to cache
		if (!is_dir(dirname($cacheFile))) {
			mkdir(dirname($cacheFile));
		}
		file_put_contents($cacheFile, serialize($context));

		$this->stdout('Updating cross references and backlinks... ');
		$context->updateReferences();
		$this->stdout('done.' . PHP_EOL, Console::FG_GREEN);

		// render models
		$renderer->renderApi($context, $this);

		ArrayHelper::multisort($context->errors, 'file');
		print_r($context->errors);

		// render guide if specified
		if ($this->guide !== null) {
			$renderer->renderMarkdownFiles($this->findMarkdownFiles($this->guide, ['README.md']), $this);

			$this->stdout('Publishing images...');
			FileHelper::copyDirectory(rtrim($this->guide, '/\\') . '/images', $targetDir . '/images');
			$this->stdout('done.' . PHP_EOL, Console::FG_GREEN);
		}
	}

	/**
	 * @return BaseRenderer
	 */
	protected function findRenderer()
	{
		$rendererClass = 'yii\\apidoc\\templates\\' . $this->template . '\\Renderer';
		if (!class_exists($rendererClass)) {
			$this->stderr('Renderer not found.' . PHP_EOL);
			return false;
		}
		return new $rendererClass();
	}

	protected function findFiles($path, $except = ['vendor/', 'tests/'])
	{
		$path = FileHelper::normalizePath($path);
		$options = [
			'filter' => function ($path) {
				if (is_file($path)) {
					$file = basename($path);
					if ($file[0] < 'A' || $file[0] > 'Z') {
						return false;
					}
				}
				return null;
			},
			'only' => ['*.php'],
			'except' => $except,
		];
		return FileHelper::findFiles($path, $options);
	}

	protected function findMarkdownFiles($path, $except = [])
	{
		$path = FileHelper::normalizePath($path);
		$options = [
			'only' => ['*.md'],
			'except' => $except,
		];
		return FileHelper::findFiles($path, $options);
	}

	/**
	 * @inheritdoc
	 */
	public function globalOptions()
	{
		return array_merge(parent::globalOptions(), ['template', 'guide']);
	}
}
