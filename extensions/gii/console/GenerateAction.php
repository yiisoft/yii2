<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii\console;

use yii\helpers\Console;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class GenerateAction extends \yii\base\Action
{
    /**
     * @var \yii\gii\Generator
     */
    public $generator;
    /**
     * @var GenerateController
     */
    public $controller;

    /**
     * @inheritdoc
     */
    public function run()
    {
        echo "Running '{$this->generator->getName()}'...\n\n";

        if ($this->generator->validate()) {
            $this->generateCode();
        } else {
            $this->displayValidationErrors();
        }
    }

    protected function displayValidationErrors()
    {
        $this->controller->stdout("Code not generated. Please fix the following errors:\n\n", Console::FG_RED);
        foreach ($this->generator->errors as $attribute => $errors) {
            echo ' - ' . $this->controller->ansiFormat($attribute, Console::FG_CYAN) . ': ' . implode('; ', $errors) . "\n";
        }
        echo "\n";
    }

    protected function generateCode()
    {
        $files = $this->generator->generate();
        $n = count($files);
        if ($n === 0) {
            echo "No code to be generated.\n";
            return;
        }
        echo "The following files will be generated:\n";
        $skipAll = $this->controller->interactive ? null : true;
        $answers = [];
        foreach ($files as $file) {
            $path = $file->getRelativePath();
            if (is_file($file->path)) {
                if (file_get_contents($file->path) === $file->content) {
                    echo '  ' . $this->controller->ansiFormat('[unchanged]', Console::FG_GREY);
                    echo $this->controller->ansiFormat(" $path\n", Console::FG_CYAN);
                    $answers[$file->id] = false;
                } else {
                    echo '    ' . $this->controller->ansiFormat('[changed]', Console::FG_RED);
                    echo $this->controller->ansiFormat(" $path\n", Console::FG_CYAN);
                    if ($skipAll !== null) {
                        $answers[$file->id] = !$skipAll;
                    } else {
                        $answer = $this->controller->select("Do you want to overwrite this file?", [
                            'y' => 'Overwrite this file.',
                            'n' => 'Skip this file.',
                            'ya' => 'Overwrite this and the rest of the changed files.',
                            'na' => 'Skip this and the rest of the changed files.',
                        ]);
                        $answers[$file->id] = $answer === 'y' || $answer === 'ya';
                        if ($answer === 'ya') {
                            $skipAll = false;
                        } elseif ($answer === 'na') {
                            $skipAll = true;
                        }
                    }
                }
            } else {
                echo '        ' . $this->controller->ansiFormat('[new]', Console::FG_GREEN);
                echo $this->controller->ansiFormat(" $path\n", Console::FG_CYAN);
                $answers[$file->id] = true;
            }
        }

        if (!array_sum($answers)) {
            $this->controller->stdout("\nNo files were chosen to be generated.\n", Console::FG_CYAN);
            return;
        }

        if (!$this->controller->confirm("\nReady to generate the selected files?", true)) {
            $this->controller->stdout("\nNo file was generated.\n", Console::FG_CYAN);
            return;
        }

        if ($this->generator->save($files, (array)$answers, $results)) {
            $this->controller->stdout("\nFiles were generated successfully!\n", Console::FG_GREEN);
        } else {
            $this->controller->stdout("\nSome errors occurred while generating the files.", Console::FG_RED);
        }
        echo preg_replace('%<span class="error">(.*?)</span>%', '\1', $results) . "\n";
    }
}
