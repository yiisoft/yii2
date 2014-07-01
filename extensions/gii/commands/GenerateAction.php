<?php
/**
 * @link      http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license   http://www.yiiframework.com/license/
 */


namespace yii\gii\commands;

/**
 * Action to run a Gii generator.
 * @author Tobias Munk <schmunk@usrbin.de>
 * @since  2.0
 */
class GenerateAction extends \yii\base\Action
{
    public $generatorName;
    public $generator;

    // TODO: is there are better way, needed for `./yii help gii`
    public function getUniqueId()
    {
        return 'gii/' . $this->generatorName;
    }

    public function getActionSummaryReflectionClass()
    {
        $this->loadGenerator($this->generatorName);
        return new \ReflectionClass($this->generator);
    }

    public function getActionHelpReflectionMethod()
    {
        $this->loadGenerator($this->generatorName);
        return new \ReflectionMethod($this->generator, 'generate');
    }

    public function getOptionHelpsReflectionClass()
    {
        $this->loadGenerator($this->generatorName);
        return $this->generator;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        echo "Loading generator '$this->generatorName'...\n\n";
        $generator = $this->loadGenerator($this->generatorName);
        if ($generator->validate()) {
            $files   = $generator->generate();
            $answers = [];
            if ($this->controller->generate == true) {
                foreach ($files AS $file) {
                    $answers[$file->id] = true;
                }
            } else {
                echo "NOT generating new files or overwriting existing files. Use --generate=true to enable file creation.\n";
            }
            $params['hasError'] = $generator->save($files, (array)$answers, $results);
            $params['results']  = $results;
            echo $params['hasError'];
            echo "\n";
            echo $results;
        } else {
            echo "Attribute Errors\n";
            echo "----------------\n";
            foreach ($generator->errors AS $attribute => $errors) {
                echo "$attribute: " . implode('; ', $errors) . "\n";
            }
            echo "\n";
        }
    }

    /**
     * Loads the generator with the specified ID.
     *
     * @param  string $id the ID of the generator to be loaded.
     *
     * @return \yii\gii\Generator    the loaded generator
     * @throws NotFoundHttpException
     */
    private function loadGenerator($id)
    {
        if (isset(\Yii::$app->getModule('gii')->generators[$this->generatorName])) {
            // using a clone for multiple controller runs
            $this->generator = clone(\Yii::$app->getModule('gii')->generators[$this->generatorName]);
            foreach ($this->generator->attributes AS $name => $attribute) {
                if ($this->controller->$name) {
                    $this->generator->$name = $this->controller->$name;
                }
            }
            $this->generator->init();
            return $this->generator;
        } else {
            throw new \yii\console\Exception("Code generator not found: $id");
        }
    }

}