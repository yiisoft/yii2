<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\test;

use yii\base\Component;

/**
 * 夹具指代的是测试环境的一个固定的数据状态。
 *
 * 每个夹具实例代表测试环境的一个特定的方面。比如，你可以通过 `UserFixture` 来用一系列已知数据初始化用户数据表。
 * 你可以在运行每个测试方法的时候，都加载夹具，这样，用户数据表始终包含固定的数据，这样你的测试就是确定的，可重复的。
 *
 * 一个夹具可能会依赖其他的夹具，你可以通过 [[depends]] 属性指定这个依赖关系。当一个夹具被加载之前，它的依赖夹具会被自动的加载；当一个夹具被卸载之后，
 * 它的依赖夹具也会被自动的卸载。
 *
 * 你通常可以重写 [[load()]] 方法来自定义初始化夹具流程；也可以通过重写 [[unload()]] 方法来自定义清理夹具的流程。
 *
 * 关于夹具的更多使用信息详情，参考 [guide article on fixtures](guide:test-fixtures)
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Fixture extends Component
{
    /**
     * @var array 这个夹具依赖的夹具类。这个属性必须是一个依赖夹具类名列表。
     */
    public $depends = [];


    /**
     * 加载夹具。
     * 这个方法会在执行每个测试方法之前调用。
     * 你需要用具体实现重写这个方法来初始化夹具。
     */
    public function load()
    {
    }

    /**
     * 这个方法会在当前测试用例的夹具数据被加载前调用。
     */
    public function beforeLoad()
    {
    }

    /**
     * 这个方法会在当前测试用例的所有夹具数据都会被加载后调用。
     */
    public function afterLoad()
    {
    }

    /**
     * 卸载夹具。
     * 这个方法会在每个测试方法结束时调用。
     * 你可以重写这个方法以执行一些夹具必要的清理工作。
     */
    public function unload()
    {
    }

    /**
     * 这个方法会在当前测试的任意夹具数据被卸载前调用。
     */
    public function beforeUnload()
    {
    }

    /**
     * 这个方法会在当前测试的所有的夹具数据都被卸载后调用。
     */
    public function afterUnload()
    {
    }
}
