<?php
/**
 * @link      http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license   http://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\caching\Cache;

/**
 * Allows you to run Gii from the command line.

 * Example command:
 *
 * $ ./yii gii/generate --generator=module --generate=true \
 *   --attributes='template:default;moduleClass:app\modules\foobar\Module;moduleID:foobar'
 *
 * @author Tobias Munk <schmunk@usrbin.de>
 * @since  2.0
 */
class GiiController extends Controller
{

    /**
     * @var string Generator ID the be used
     */
    public $generator;

    /**
     * @var string Template ID the be used
     */
    public $template;

    /**
     * @var string List of generator (model) attributes, Format `attributeName1:value1;...;attributeNameN:valueN`
     */
    public $attributes;

    /**
     * @var boolean whether to generate all files and overwrite existing files
     */
    public $generate = false;

    /**
     * @inheritdoc
     */
    public function options($id)
    {
        return array_merge(
            parent::options($id),
            ['generator', 'template', 'attributes', 'generate'] // global for all actions
        );
    }

    /**
     * tbd.
     */
    public function actionIndex()
    {
        echo "File info\n";
        echo "---------\n";
        $generator = $this->loadGenerator($this->generator);
        $files     = $generator->generate();
        foreach ($files AS $file) {
            echo $file->id . " => " . $file->path . "\n";
        }
    }

    /**
     * Runs the generation process, by using the command params to initialize the
     * and populate the generator model and triggering file creation.
     */
    public function actionGenerate()
    {
        echo "Loading generator '$this->generator'...\n\n";
        $generator = $this->loadGenerator($this->generator);
        if ($generator->validate()) {
            $files   = $generator->generate();
            $answers = [];
            if ($this->generate == true) {
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
    protected function loadGenerator($id)
    {
        if (isset(Yii::$app->getModule('console-gii')->generators[$this->generator])) {
            $this->generator = Yii::$app->getModule('console-gii')->generators[$this->generator];
            $this->generator->init();
            $this->generator->load($this->parseAttributes($this->attributes));
            return $this->generator;
        } else {
            throw new \yii\console\Exception("Code generator not found: $id");
        }
    }

    /**
     * @param $attributes
     *
     * @return array Attributes from command param as a two-dimensional array.
     */
    private function parseAttributes($attributes)
    {
        $pairs      = explode(';', $attributes);
        $attributes = [];
        foreach ($pairs AS $pair) {
            $data = explode(':', $pair);
            if (empty($data[1])) {
                continue;
            }
            $attributes['Generator'][$data[0]] = $data[1];
        }
        return $attributes;
    }
}
