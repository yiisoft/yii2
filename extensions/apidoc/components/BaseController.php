<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\components;

use yii\apidoc\renderers\BaseRenderer;
use yii\console\Controller;
use yii\helpers\Console;
use yii\apidoc\models\Context;
use Yii;

/**
 * Command to render API Documentation files
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
abstract class BaseController extends Controller
{
	/**
	 * @var string template to use for rendering
	 */
	public $template = 'bootstrap';
	/**
	 * @var string|array files to exclude.
	 */
	public $exclude;


	protected function normalizeTargetDir($target)
	{
		$target = rtrim(Yii::getAlias($target), '\\/');
		if (file_exists($target)) {
			if (is_dir($target) && !$this->confirm('TargetDirectory already exists. Overwrite?', true)) {
				$this->stderr('User aborted.' . PHP_EOL);
				return false;
			}
			if (is_file($target)) {
				$this->stderr("Error: Target directory \"$target\" is a file!" . PHP_EOL);
				return false;
			}
		} else {
			mkdir($target, 0777, true);
		}
		return $target;
	}

	protected function searchFiles($sourceDirs)
	{
		$this->stdout('Searching files to process... ');
		$files = [];

		if (is_array($this->exclude)) {
			$exclude = $this->exclude;
		} elseif (is_string($this->exclude)) {
			$exclude = explode(',', $this->exclude);
		} else {
			$exclude = [];
		}

		foreach ($sourceDirs as $source) {
			foreach ($this->findFiles($source, $exclude) as $fileName) {
				$files[$fileName] = $fileName;
			}
		}
		$this->stdout('done.' . PHP_EOL, Console::FG_GREEN);

		if (empty($files)) {
			$this->stderr('Error: No files found to process.' . PHP_EOL);
			return false;
		}
		return $files;
	}

	protected abstract function findFiles($dir, $except = []);

	protected function loadContext($location)
	{
		$context = new Context();

		$cacheFile = $location . '/cache/apidoc.data';
		$this->stdout('Loading apidoc data from cache... ');
		if (file_exists($cacheFile)) {
			$context = unserialize(file_get_contents($cacheFile));
			$this->stdout('done.' . PHP_EOL, Console::FG_GREEN);
		} else {
			$this->stdout('no data available.' . PHP_EOL, Console::FG_YELLOW);
		}
		return $context;
	}

	protected function storeContext($context, $location)
	{
		$cacheFile = $location . '/cache/apidoc.data';
		if (!is_dir($dir = dirname($cacheFile))) {
			mkdir($dir, 0777, true);
		}
		file_put_contents($cacheFile, serialize($context));
	}

	/**
	 * @param Context $context
	 */
	protected function updateContext($context)
	{
		$this->stdout('Updating cross references and backlinks... ');
		$context->updateReferences();
		$this->stdout('done.' . PHP_EOL, Console::FG_GREEN);
	}

	/**
	 * @param string $template
	 * @return BaseRenderer
	 */
	protected abstract function findRenderer($template);

	/**
	 * @inheritdoc
	 */
	public function options($id)
	{
		return array_merge(parent::options($id), ['template', 'exclude']);
	}
}
