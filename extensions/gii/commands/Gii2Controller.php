<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

use Yii;
use yii\console\Controller;
use yii\gii\Module;
use yii\helpers\Console;

// TODO consider moving this to gii module

/**
 * This command allows you to generate code with gii code generator
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class GiiController extends Controller
{
	/**
	 * @var string controller default action ID.
	 */
	public $defaultAction = 'generate';


	/**
	 * @return \yii\gii\Module
	 */
	public function getGiiModule()
	{
		// TODO check for null (maybe controller should only be available when gii is loaded)

		$m = Yii::$app->getModule('gii');
		if ($m === null) {
			$m = new Module('gii', Yii::$app);
			Yii::$app->setModule('gii', $m);
		}
		return $m;
	}

	/**
	 * @param $generator
	 * @return int
	 */
	public function actionGenerate($generator)
	{
		$this->printHeader();
		if (!isset($this->giiModule->generators[$generator])) {
			$this->stderr("Generator '$generator' does not exist\n", Console::FG_RED);
			return 1;
		}


	}

	/**
	 * List all available generators
	 */
	public function actionList()
	{
		$this->printHeader();

		$this->stdout('Here is a list of available generators:' . "\n");
		foreach ($this->giiModule->generators as $id => $generator) {
			$this->stdout($id . "\n" . $generator->getName() . "\n");
			$this->stdout($generator->getDescription() . "\n\n");
		}
	}

	public function printHeader()
	{
		Console::beginAnsiFormat(array(Console::BOLD, Console::FG_GREY));
		echo "Welcome to";
		Console::beginAnsiFormat(array(Console::FG_GREEN));
		echo
		<<<EOF
  _   _
     __ _  (_) (_)
    / _` | | | | |
   | (_| | | | | |
    \__, | |_| |_|
    |___/
EOF;
		Console::beginAnsiFormat(array(Console::BOLD, Console::FG_GREY));
		echo "  a magic tool that can write code for you!\n";
		Console::endAnsiFormat();
		echo "\n\n";
	}
}
