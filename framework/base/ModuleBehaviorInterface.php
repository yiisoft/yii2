<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ModuleBehaviorInterface is the class that should be implemented by behaviors who want to add controllers to the module.
 * 
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0.12
 */
interface ModuleBehaviorInterface
{
    /**
     * Declares external controllers for the controller that the behavior is attached to.
     * It should return an array, with array keys being action IDs, and array values the corresponding
     * action class names or action configuration arrays. For example,
     *
     * ```php
     * return [
     *     'controller1' => 'app\controllers\Controller1',
     *     'controller2' => [
     *         'class' => 'app\controllers\Controller2',
     *         'property1' => 'value1',
     *         'property2' => 'value2',
     *     ],
     * ];
     * ```
     *
     * [[\Yii::createObject()]] will be used later to create the requested controller
     * using the configuration provided here.
     */
    public function controllers();
    /**
     * Declares external modules for the module that the behavior is attached to.
     * It should return an array, with array keys being action IDs, and array values the corresponding
     * action class names or action configuration arrays. For example,
     *
     * ```php
     * return [
     *     'module1' => 'app\modules\Module1',
     *     'module2' => [
     *         'class' => 'app\modules\Module2',
     *         'property1' => 'value1',
     *         'property2' => 'value2',
     *     ],
     * ];
     * ```
     *
     * [[\Yii::createObject()]] will be used later to create the requested module
     * using the configuration provided here.
     */
    public function modules();
}
