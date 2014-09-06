<?php
/**
 * @link      http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license   http://www.yiiframework.com/license/
 */

namespace yii\gii\commands;

use Yii;
use yii\console\Controller;

/**
 * Allows you to run Gii from the command line.
 * Example command:
 *
 * ```
 * $ ./yii gii/<generator> --property1=foo --property2=bar --generate=true
 * ```
 *
 * @author Tobias Munk <schmunk@usrbin.de>
 * @since  2.0
 */
class GenerateController extends Controller
{
    /**
     * @var \yii\gii\Module
     */
    public $module;
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
        // todo: check if $key is a valid option
        $this->_attributes[$key] = $value;
    }

    public function __get($key)
    {
        // todo: check if $key is a valid option
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
        foreach ($this->module->generators as $name => $generator) {
            // create a generate action for every generator
            $actions[$name] = [
                'class' => 'yii\gii\console\Action',
                'generator' => $generator,
            ];
        }
        return $actions;
    }

    /**
     * @inheritdoc
     */
    public function options($id)
    {
        $generator = $this->module->generators[$id];
        return array_merge(
            parent::options($id),
            array_keys($generator->attributes) // global for all actions
        );
    }
}
