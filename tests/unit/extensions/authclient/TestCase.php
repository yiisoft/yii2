<?php

namespace yiiunit\extensions\authclient;

use yii\helpers\FileHelper;
use Yii;

/**
 * TestCase for "authclient" extension.
 */
class TestCase extends \yiiunit\TestCase
{
	/**
	 * Adds sphinx extension files to [[Yii::$classPath]],
	 * avoiding the necessity of usage Composer autoloader.
	 */
	public static function loadClassMap()
	{
		$baseNameSpace = 'yii/authclient';
		$basePath = realpath(__DIR__. '/../../../../extensions/yii/authclient');
		$files = FileHelper::findFiles($basePath);
		foreach ($files as $file) {
			$classRelativePath = str_replace($basePath, '', $file);
			$classFullName = str_replace(['/', '.php'], ['\\', ''], $baseNameSpace . $classRelativePath);
			Yii::$classMap[$classFullName] = $file;
		}
	}
}

TestCase::loadClassMap();