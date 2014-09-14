<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii\commands;

use Yii;
use yii\console\Controller;
use yii\gii\CodeFile;
use yii\gii\Generator;
use yii\gii\Module;
use yii\helpers\Console;

// TODO consider moving this to gii module

/**
 * This command allows you to generate code with gii code generator
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class InteractiveController extends Controller
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
	public function actionGenerate($generator = null)
	{
		$this->printHeader();
        if ($generator === null) {
            $generators = $this->giiModule->generators;
            $this->displayGenerators($generators);
            $generator = Console::select('Please select a generator: ', $generators);
        }

		if (!isset($this->giiModule->generators[$generator])) {
			$this->stderr("Generator '$generator' does not exist\n", Console::FG_RED);
			return 1;
		}

        /** @var Generator $model */
        $model = $this->giiModule->generators[$generator];

        $this->consumeGeneratorInput($model);

        if ($model->validate()) {
            $model->saveStickyAttributes();
            $files = $model->generate();
//            if (isset($_POST['generate']) && !empty($_POST['answers'])) {
//                $params['hasError'] = $generator->save($files, (array) $_POST['answers'], $results);
//                $params['results'] = $results;
//            } else {
//                $params['files'] = $files;
//                $params['answers'] = isset($_POST['answers']) ? $_POST['answers'] : null;
//            }

            $this->stdout("\nFiles to generate:\n\n", Console::BOLD);
            $answers = [];
            foreach($files as $file) {
                /** @var CodeFile $file */
                $this->stdout(" - $file->relativePath\n");
                $answers[$file->id] = 1;
            }

            // TODO add preview
            // TODO allow selection of files
            if ($this->confirm('Generate files now?', true)) {
                $model->save($files, $answers, $result);
                $this->stdout($result);
            } else {
                $this->stdout('exiting.');
            }

        } else {
            foreach($model->getFirstErrors() as $error) {
                $this->stdout($error . "\n", Console::FG_RED);
            }
        }

        return 0;
	}

    /**
     * @param Generator $generator
     */
    protected function consumeGeneratorInput($generator)
    {
        $generator->loadStickyAttributes();

        $hints = $generator->hints();
        foreach($generator->safeAttributes() as $attribute) {
            if ($attribute === 'template') {
                $generator->template = key($generator->templates);
                continue; // TODO select template
            }

            if (isset($hints[$attribute])) {
                $this->stdout($generator->getAttributeLabel($attribute) . "\n", Console::BOLD);
                $this->stdout($hints[$attribute] . "\n");
            }
            do {
                if ($generator->hasErrors($attribute)) {
                    foreach($generator->getErrors($attribute) as $error) {
                        $this->stdout($error . "\n", Console::FG_RED);
                    }
                }
                $generator->$attribute = $this->prompt($generator->getAttributeLabel($attribute), [
//                    'required' => $model->isAttributeRequired($attribute),
                    'default' => $generator->$attribute,
                ]);
            } while(!$generator->validate([$attribute], true));
        }
    }

    protected function displayGenerators($generators)
    {
        $this->stdout('Here is a list of available generators:' . "\n\n");
        $maxlen = array_reduce(array_map('strlen', array_keys($generators)), 'max', 0);
        foreach ($generators as $id => $generator) {
            $this->stdout($id . str_repeat(' ', $maxlen + 1 - strlen($id)) . ' - ' . $generator->getName() . "\n", Console::BOLD);
            $indent = str_repeat(' ', $maxlen + 4);
            $this->stdout($indent . preg_replace("/\n\s*/", "\n$indent", $generator->getDescription()) . "\n\n");
        }
        $this->stdout("\n");
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
