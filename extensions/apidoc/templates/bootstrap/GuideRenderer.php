<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\bootstrap;
use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\ConstDoc;
use yii\apidoc\models\Context;
use yii\apidoc\models\EventDoc;
use yii\apidoc\models\InterfaceDoc;
use yii\apidoc\models\MethodDoc;
use yii\apidoc\models\PropertyDoc;
use yii\apidoc\models\TraitDoc;
use yii\console\Controller;
use Yii;
use yii\helpers\Console;
use yii\helpers\Html;

/**
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class GuideRenderer extends \yii\apidoc\templates\html\GuideRenderer
{
	public $layout = '@yii/apidoc/templates/bootstrap/layouts/guide.php';

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
//
//	/**
//	 * Renders a given [[Context]].
//	 *
//	 * @param Controller $controller the apidoc controller instance. Can be used to control output.
//	 */
//	public function renderMarkdownFiles($controller)
//	{
//		$files = $this->markDownFiles;
//		$dir = Yii::getAlias($this->targetDir);
//		if (!is_dir($dir)) {
//			mkdir($dir, 0777, true);
//		}
//
//		ApiMarkdown::$renderer = $this;
//
//		$fileCount = count($files) + 1;
//		Console::startProgress(0, $fileCount, 'Rendering markdown files: ', false);
//		$done = 0;
//		$fileData = [];
//		$headlines = [];
//		foreach($files as $file) {
//			$fileData[$file] = file_get_contents($file);
//			if (basename($file) == 'index.md') {
//				continue; // to not add index file to nav
//			}
//			if (preg_match("/^(.*)\n=+/", $fileData[$file], $matches)) {
//				$headlines[$file] = $matches[1];
//			} else {
//				$headlines[$file] = basename($file);
//			}
//		}
//
//		foreach($fileData as $file => $content) {
//			$output = ApiMarkdown::process($content); // TODO generate links to yiiframework.com by default
//			$output = $this->fixMarkdownLinks($output);
//			if ($this->guideLayout !== false) {
//				$params = [
//					'headlines' => $headlines,
//					'currentFile' => $file,
//					'content' => $output,
//				];
//				$output = $this->getView()->renderFile($this->guideLayout, $params, $this);
//			}
//			$fileName = $this->generateGuideFileName($file);
//			file_put_contents($dir . '/' . $fileName, $output);
//			Console::updateProgress(++$done, $fileCount);
//		}
//		Console::updateProgress(++$done, $fileCount);
//		Console::endProgress(true);
//		$controller->stdout('done.' . PHP_EOL, Console::FG_GREEN);
//	}
//

}