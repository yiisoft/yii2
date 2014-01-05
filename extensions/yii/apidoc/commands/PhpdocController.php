<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\commands;

use phpDocumentor\Reflection\FileReflector;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\apidoc\components\OfflineRenderer;
use yii\apidoc\models\Context;
use Yii;

/**
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class PhpdocController extends Controller
{
	public function actionIndex($targetDir)
	{
		echo "hi\n";

		$targetDir = Yii::getAlias($targetDir);
		if (is_dir($targetDir) && !$this->confirm('TargetDirectory already exists. Overwrite?')) {
			return 2;
		}

		// TODO determine files to analyze
		$this->stdout('Searching files to process... ');
		$files = $this->findFiles(YII_PATH);
//		$files = array_slice($files, 0, 42); // TODO remove this line
		$this->stdout('done.' . PHP_EOL, Console::FG_GREEN);

		$fileCount = count($files);
		Console::startProgress(0, $fileCount, 'Processing files... ', false);
		$context = new Context();
		$done = 0;
		foreach($files as $file) {
			$context->addFile($file);
			Console::updateProgress(++$done, $fileCount);
		}
		Console::endProgress(true);
		$this->stdout('done.' . PHP_EOL, Console::FG_GREEN);

		$this->stdout('Updating cross references and backlinks... ');
		$context->updateReferences();
		$this->stdout('done.' . PHP_EOL, Console::FG_GREEN);


		// TODO LATER analyze for dead links and similar stuff

		// TODO render models
		$renderer = new OfflineRenderer();
		$renderer->targetDir = $targetDir;
		$renderer->render($context, $this);
	}


	protected function findFiles($path, $except = [])
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
			'only' => ['.php'],
			'except' => array_merge($except, [
				'/views/',
				'/requirements/',
				'/gii/generators/',
			]),
		];
		return FileHelper::findFiles($path, $options);
	}

}