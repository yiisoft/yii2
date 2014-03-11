<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\html;

use yii\apidoc\helpers\ApiMarkdown;
use yii\console\Controller;
use yii\helpers\Console;
use yii\apidoc\renderers\GuideRenderer as BaseGuideRenderer;
use Yii;
use yii\helpers\Html;
use yii\web\AssetManager;
use yii\web\View;

/**
 *
 * @property View $view The view instance. This property is read-only.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
abstract class GuideRenderer extends BaseGuideRenderer
{
	public $pageTitle;
	public $layout;

	/**
	 * @var View
	 */
	private $_view;
	private $_targetDir;


	public function init()
	{
		parent::init();

		if ($this->pageTitle === null) {
			$this->pageTitle = 'Yii Framework 2.0 API Documentation'; // TODO guess page title
		}
	}

	/**
	 * @return View the view instance
	 */
	public function getView()
	{
		if ($this->_view === null) {
			$this->_view = new View();
			$assetPath = Yii::getAlias($this->_targetDir) . '/assets';
			if (!is_dir($assetPath)) {
				mkdir($assetPath);
			}
			$this->_view->assetManager = new AssetManager([
				'basePath' => $assetPath,
				'baseUrl' => './assets',
			]);
		}
		return $this->_view;
	}


	/**
	 * Renders a given [[Context]].
	 *
	 * @param Controller $controller the apidoc controller instance. Can be used to control output.
	 */
	public function render($files, $targetDir)
	{
		$this->_targetDir = $targetDir;

		$fileCount = count($files) + 1;
		if ($this->controller !== null) {
			Console::startProgress(0, $fileCount, 'Rendering markdown files: ', false);
		}
		$done = 0;
		$fileData = [];
		$headlines = [];
		foreach ($files as $file) {
			$fileData[$file] = file_get_contents($file);
			if (basename($file) == 'index.md') {
				continue; // to not add index file to nav
			}
			if (preg_match("/^(.*)\n=+/", $fileData[$file], $matches)) {
				$headlines[$file] = $matches[1];
			} else {
				$headlines[$file] = basename($file);
			}
		}

		foreach ($fileData as $file => $content) {
			$output = ApiMarkdown::process($content); // TODO generate links to yiiframework.com by default
			$output = $this->fixMarkdownLinks($output);
			if ($this->layout !== false) {
				$params = [
					'headlines' => $headlines,
					'currentFile' => $file,
					'content' => $output,
				];
				$output = $this->getView()->renderFile($this->layout, $params, $this);
			}
			$fileName = $this->generateGuideFileName($file);
			file_put_contents($targetDir . '/' . $fileName, $output);

			if ($this->controller !== null) {
				Console::updateProgress(++$done, $fileCount);
			}
		}
		if ($this->controller !== null) {
			Console::updateProgress(++$done, $fileCount);
			Console::endProgress(true);
			$this->controller->stdout('done.' . PHP_EOL, Console::FG_GREEN);
		}
	}

	protected function generateGuideFileName($file)
	{
		return static::GUIDE_PREFIX . basename($file, '.md') . '.html';
	}

	public function getGuideReferences()
	{
		// TODO implement for api docs
//		$refs = [];
//		foreach($this->markDownFiles as $file) {
//			$refName = 'guide-' . basename($file, '.md');
//			$refs[$refName] = ['url' => $this->generateGuideFileName($file)];
//		}
//		return $refs;
	}

	protected function fixMarkdownLinks($content)
	{
		$content = preg_replace('/href\s*=\s*"([^"\/]+)\.md(#.*)?"/i', 'href="' . static::GUIDE_PREFIX . '\1.html\2"', $content);
		return $content;
	}

	/**
	 * @inheritdoc
	 */
	protected function generateLink($text, $href, $options = [])
	{
		$options['href'] = $href;
		return Html::a($text, null, $options);
	}

	/**
	 * Generate an url to a type in apidocs
	 * @param $typeName
	 * @return mixed
	 */
	public function generateApiUrl($typeName)
	{
		return rtrim($this->apiUrl, '/') . '/' . strtolower(str_replace('\\', '-', $typeName)) . '.html';
	}
}
