<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\bootstrap;

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\Context;
use yii\console\Controller;
use Yii;
use yii\helpers\Console;

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

	public $extensions = [
		'apidoc',
		'authclient',
		'bootstrap',
		'codeception',
		'composer',
		'debug',
		'elasticsearch',
		'faker',
		'gii',
		'imagine',
		'jui',
		'mongodb',
		'redis',
		'smarty',
		'sphinx',
		'swiftmailer',
		'twig',
	];

	/**
	 * Renders a given [[Context]].
	 *
	 * @param Context $context the api documentation context to render.
	 * @param Controller $controller the apidoc controller instance. Can be used to control output.
	 */
	public function renderApi($context, $controller)
	{
		parent::renderApi($context, $controller);
		$dir = Yii::getAlias($this->targetDir);
		$types = array_merge($context->classes, $context->interfaces, $context->traits);

		$controller->stdout('generating extension index files...');
		foreach ($this->extensions as $ext) {
			$readme = @file_get_contents("https://raw.github.com/yiisoft/yii2-$ext/master/README.md");
			$indexFileContent = $this->renderWithLayout($this->indexView, [
				'docContext' => $context,
				'types' => $this->filterTypes($types, $ext),
				'readme' => $readme ?: null,
			]);
			file_put_contents($dir . "/ext_{$ext}_index.html", $indexFileContent);
		}
		$readme = @file_get_contents("https://raw.github.com/yiisoft/yii2-framework/master/README.md");
		$indexFileContent = $this->renderWithLayout($this->indexView, [
			'docContext' => $context,
			'types' => $this->filterTypes($types, 'yii'),
			'readme' => $readme ?: null,
		]);
		file_put_contents($dir . '/index.html', $indexFileContent);
		$controller->stdout('done.' . PHP_EOL, Console::FG_GREEN);
	}

	public function getNavTypes($type, $types)
	{
		if ($type === null) {
			return $types;
		}
		$extensions = $this->extensions;
		$navClasses = 'app';
		if (isset($type)) {
			if ($type->name == 'Yii') {
				$navClasses = 'yii';
			} elseif (strncmp($type->name, 'yii\\', 4) == 0) {
				$subName = substr($type->name, 4);
				if (($pos = strpos($subName, '\\')) !== false) {
					$subNamespace = substr($subName, 0, $pos);
					if (in_array($subNamespace, $extensions)) {
						$navClasses = $subNamespace;
					} else {
						$navClasses = 'yii';
					}
				}
			}
		}
		return $this->filterTypes($types, $navClasses);
	}

	protected function filterTypes($types, $navClasses)
	{
		switch ($navClasses)
		{
			case 'app':
				$types = array_filter($types, function ($val) {
					return strncmp($val->name, 'yii\\', 4) !== 0;
				});
				break;
			case 'yii':
				$self = $this;
				$types = array_filter($types, function ($val) use ($self) {
					if (strlen($val->name) < 5) {
						return false;
					}
					$subName = substr($val->name, 4, strpos($val->name, '\\', 5) - 4);
					return strncmp($val->name, 'yii\\', 4) === 0 && !in_array($subName, $self->extensions);
				});
				break;
			default:
				$types = array_filter($types, function ($val) use ($navClasses) {
					return strncmp($val->name, "yii\\$navClasses\\", strlen("yii\\$navClasses\\")) === 0;
				});
		}
		return $types;
	}

	/**
	 * Renders a given [[Context]].
	 *
	 * @param Controller $controller the apidoc controller instance. Can be used to control output.
	 */
	public function renderMarkdownFiles($controller)
	{
		$files = $this->markDownFiles;
		$dir = Yii::getAlias($this->targetDir);
		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}

		ApiMarkdown::$renderer = $this;

		$fileCount = count($files) + 1;
		Console::startProgress(0, $fileCount, 'Rendering markdown files: ', false);
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
			if ($this->guideLayout !== false) {
				$params = [
					'headlines' => $headlines,
					'currentFile' => $file,
					'content' => $output,
				];
				$output = $this->getView()->renderFile($this->guideLayout, $params, $this);
			}
			$fileName = $this->generateGuideFileName($file);
			file_put_contents($dir . '/' . $fileName, $output);
			Console::updateProgress(++$done, $fileCount);
		}
		Console::updateProgress(++$done, $fileCount);
		Console::endProgress(true);
		$controller->stdout('done.' . PHP_EOL, Console::FG_GREEN);
	}

	protected function generateGuideFileName($file)
	{
		return 'guide_' . basename($file, '.md') . '.html';
	}

	public function getGuideReferences()
	{
		$refs = [];
		foreach ($this->markDownFiles as $file) {
			$refName = 'guide-' . basename($file, '.md');
			$refs[$refName] = ['url' => $this->generateGuideFileName($file)];
		}
		return $refs;
	}

	protected function fixMarkdownLinks($content)
	{
		$content = preg_replace('/href\s*=\s*"([^"\/]+)\.md(#.*)?"/i', 'href="guide_\1.html\2"', $content);
		return $content;
	}
}
