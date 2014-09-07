<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii\console;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Action extends \yii\base\Action
{
    /**
     * @var \yii\gii\Generator
     */
    public $generator;

    /**
     * @inheritdoc
     */
    public function run()
    {
        echo "Loading generator '$this->id'...\n\n";
        if ($this->generator->validate()) {
            $files = $this->generator->generate();
            $answers = [];
            foreach ($files AS $file) {
                $answers[$file->id] = true;
            }
            $params['hasError'] = $this->generator->save($files, (array)$answers, $results);
            $params['results'] = $results;
            echo $params['hasError'];
            echo "\n";
            echo $results;
        } else {
            echo "Attribute Errors\n";
            echo "----------------\n";
            foreach ($this->generator->errors AS $attribute => $errors) {
                echo "$attribute: " . implode('; ', $errors) . "\n";
            }
            echo "\n";
        }
    }
}
