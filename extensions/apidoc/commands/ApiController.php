<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\commands;

use yii\apidoc\components\BaseController;
use yii\apidoc\models\Context;
use yii\apidoc\renderers\ApiRenderer;
use yii\apidoc\renderers\BaseRenderer;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\helpers\FileHelper;

/**
 * Generate class API documentation.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ApiController extends BaseController
{
	/**
	 * @var string url to where the guide files are located
	 */
	public $guide;

	// TODO add force update option

	/**
	 * Renders API documentation files
	 * @param array $sourceDirs
	 * @param string $targetDir
	 * @return int
	 */
	public function actionIndex(array $sourceDirs, $targetDir)
	{
		$renderer = $this->findRenderer($this->template);
		$targetDir = $this->normalizeTargetDir($targetDir);
		if ($targetDir === false || $renderer === false) {
			return 1;
		}

		$renderer->apiUrl = './';

		// setup reference to guide
		if ($this->guide !== null) {
			$guideUrl = $this->guide;
			$referenceFile = $guideUrl . '/' . BaseRenderer::GUIDE_PREFIX . 'references.txt';
		} else {
			$guideUrl = './';
			$referenceFile = $targetDir . '/' . BaseRenderer::GUIDE_PREFIX . 'references.txt';
		}
		if (file_exists($referenceFile)) {
			$renderer->guideUrl = $guideUrl;
			$renderer->guideReferences = [];
			foreach (explode("\n", file_get_contents($referenceFile)) as $reference) {
				$renderer->guideReferences[BaseRenderer::GUIDE_PREFIX . $reference]['url'] = $renderer->generateGuideUrl($reference);
			}
		}

		// search for files to process
		if (($files = $this->searchFiles($sourceDirs)) === false) {
			return 1;
		}

		// load context from cache
		$context = $this->loadContext($targetDir);
		$this->stdout('Checking for updated files... ');
		foreach ($context->files as $file => $sha) {
			if (!file_exists($file)) {
				$this->stdout('At least one file has been removed. Rebuilding the context...');
				$context = new Context();
				if (($files = $this->searchFiles($sourceDirs)) === false) {
					return 1;
				}
				break;
			}
			if (sha1_file($file) === $sha) {
				unset($files[$file]);
			}
		}
		$this->stdout('done.' . PHP_EOL, Console::FG_GREEN);

		// process files
		$fileCount = count($files);
		$this->stdout($fileCount . ' file' . ($fileCount == 1 ? '' : 's') . ' to update.' . PHP_EOL);
		Console::startProgress(0, $fileCount, 'Processing files... ', false);
		$done = 0;
		foreach ($files as $file) {
			$context->addFile($file);
			Console::updateProgress(++$done, $fileCount);
		}
		Console::endProgress(true);
		$this->stdout('done.' . PHP_EOL, Console::FG_GREEN);

		// save processed data to cache
		$this->storeContext($context, $targetDir);

		$this->updateContext($context);

		// render models
		$renderer->controller = $this;
		$renderer->render($context, $targetDir);

		if (!empty($context->errors)) {
			ArrayHelper::multisort($context->errors, 'file');
			file_put_contents($targetDir . '/errors.txt', print_r($context->errors, true));
			$this->stdout(count($context->errors) . " errors have been logged to $targetDir/errors.txt\n", Console::FG_RED, Console::BOLD);
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function findFiles($path, $except = [])
	{
		if (empty($except)) {
			$except = ['vendor/', 'tests/'];
		}
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

	/**
	 * @inheritdoc
	 * @return ApiRenderer
	 */
	protected function findRenderer($template)
	{
		$rendererClass = 'yii\\apidoc\\templates\\' . $template . '\\ApiRenderer';
		if (!class_exists($rendererClass)) {
			$this->stderr('Renderer not found.' . PHP_EOL);
			return false;
		}
		return new $rendererClass();
	}

	/**
	 * @inheritdoc
	 */
	public function options($id)
	{
		return array_merge(parent::options($id), ['template', 'guide']);
	}
}
