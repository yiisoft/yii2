<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\bootstrap;
use yii\apidoc\helpers\Markdown;
use yii\apidoc\models\Context;
use yii\console\Controller;
use Yii;
use yii\helpers\Console;
use yii\helpers\FileHelper;

/**
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class Renderer extends \yii\apidoc\templates\html\Renderer
{
	public $apiLayout = '@yii/apidoc/templates/bootstrap/layouts/api.php';
	public $guideLayout = '@yii/apidoc/templates/bootstrap/layouts/guide.php';
	public $indexView = '@yii/apidoc/templates/bootstrap/views/index.php';

	public $pageTitle = 'Yii Framework 2.0 Documentation';

	public $guideUrl;

	/**
	 * Renders a given [[Context]].
	 *
	 * @param Context $context the api documentation context to render.
	 * @param Controller $controller the apidoc controller instance. Can be used to control output.
	 */
	public function renderMarkdownFiles($files, $controller)
	{
		$dir = Yii::getAlias($this->targetDir);
		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}

		Markdown::$renderer = $this;

		$fileCount = count($files) + 1;
		Console::startProgress(0, $fileCount, 'Rendering markdown files: ', false);
		$done = 0;
		$fileData = [];
		$headlines = [];
		foreach($files as $file) {
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

		foreach($fileData as $file => $content) {
			$output = Markdown::process($content); // TODO generate links to yiiframework.com by default
			$output = $this->fixMarkdownLinks($output);
			if ($this->guideLayout !== false) {
				$params = [
					'headlines' => $headlines,
					'currentFile' => $file,
					'content' => $output,
				];
				$output = $this->getView()->renderFile($this->guideLayout, $params, $this);
			}
			$fileName = 'guide_' . str_replace('.md', '.html', basename($file));
			file_put_contents($dir . '/' . $fileName, $output);
			Console::updateProgress(++$done, $fileCount);
		}
		Console::updateProgress(++$done, $fileCount);
		Console::endProgress(true);
		$controller->stdout('done.' . PHP_EOL, Console::FG_GREEN);
	}

	protected function fixMarkdownLinks($content)
	{
		$content = preg_replace('/href\s*=\s*"([^"]+)\.md"/i', 'href="guide_\1.html"', $content);
		return $content;
	}
}