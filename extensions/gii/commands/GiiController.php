<?php
/**
 * @link      http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license   http://www.yiiframework.com/license/
 */

namespace yii\gii\commands;

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

    /**
     * @var array stores generator attributes
     */
    private $_attributes = [];

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

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = [];
        foreach (Yii::$app->getModule('gii')->generators as $name => $generator) {
            // create a generate action for every generator
            $actions[$name] = [
                'class'         => '\yii\gii\commands\GenerateAction',
                'generatorName' => $name,
            ];
        }
        return $actions;
    }

    /**
     * @inheritdoc
     */
    public function options($id)
    {
        $generator = \Yii::$app->getModule('gii')->generators[$id];
        return array_merge(
            parent::options($id),
            ['generate'],
            array_keys($generator->attributes) // global for all actions
        );
    }
}
