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
 * $ ./yii gii/<generator> --property1=foo --property2=bar --generate=true
 * @author Tobias Munk <schmunk@usrbin.de>
 * @since  2.0
 */
class GiiController extends Controller
{
    /**
     * @var boolean whether to generate all files and overwrite existing files
     */
    public $generate = false;

    // TODO: magic getter and setter currently needed in controller and action
    private $_attributes;

    public function __set($key, $value)
    {
        $this->_attributes[$key] = $value;
    }

    public function __get($key)
    {
        if (isset($this->_attributes[$key])) {
            return $this->_attributes[$key];
        }
    }

    public function actions()
    {
        $actions = [];
        foreach (Yii::$app->getModule('console-gii')->generators as $name => $generator) {
            // create a generate action for every generator
            $actions[$name] = [
                'class'         => '\yii\console\controllers\GenerateAction',
                'generatorName' => $name,
            ];
            // create action properties from generator
            $properties = $generator->attributes;
            foreach ($properties as $property => $value) {
                $actions[$name][$property] = $value;
            }

        }
        return $actions;
    }

    /**
     * @inheritdoc
     */
    public function options($id)
    {
        return array_merge(
            parent::options($id),
            // TODO: read array from generator
            [
                'generator',
                'template',
                'attribute',
                'generate',
                'modelClass',
                'searchModelClass',
                'controllerClass'
            ] // global for all actions
        );
    }
}
