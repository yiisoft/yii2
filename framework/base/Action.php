<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * Action 是所有控制器动作类的基类。
 *
 * Action 提供了重用动作方法代码的方法。
 * Action 类中的 action 方法可用于多个控制器或不同的项目中。
 *
 * 派生类必须实现名为 `run()` 的方法。
 * 请求动作时，控制器将调用此方法。
 * `run()` 方法可以有参数，
 * 这些参数将根据用户输入值的名称自动填充。
 * 例如，如果 `run()` 方法声明如下：
 *
 * ```php
 * public function run($id, $type = 'book') { ... }
 * ```
 *
 * 为该动作提供的参数是：`['id' => 1]`。
 * 然后 `run()` 方法将自动调用为 `run(1)`。
 *
 * 有关 Action 的更多详细信息和用法信息，请参阅 [动作指南](guide:structure-controllers)。
 *
 * @property string $uniqueId 整个应用程序中此动作的唯一 ID。
 * 此属性是只读的。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Action extends Component
{
    /**
     * @var string 动作的 ID
     */
    public $id;
    /**
     * @var Controller|\yii\web\Controller|\yii\console\Controller 拥有此操作的控制器
     */
    public $controller;


    /**
     * 构造函数。
     *
     * @param string $id 动作的 ID
     * @param Controller $controller 拥有此动作的控制器
     * @param array $config 将用于初始化对象属性的键值对
     */
    public function __construct($id, $controller, $config = [])
    {
        $this->id = $id;
        $this->controller = $controller;
        parent::__construct($config);
    }

    /**
     * 返回整个应用程序中此动作的唯一 ID。
     *
     * @return string 整个应用程序中此操作的唯一 ID。
     */
    public function getUniqueId()
    {
        return $this->controller->getUniqueId() . '/' . $this->id;
    }

    /**
     * 使用指定的参数运行此动作。
     * 该方法主要由控制器调用。
     *
     * @param array $params 要绑定到 action 的 run() 方法的参数。
     * @return mixed action 的结果
     * @throws InvalidConfigException 如果 action 类没有 run() 方法
     */
    public function runWithParams($params)
    {
        if (!method_exists($this, 'run')) {
            throw new InvalidConfigException(get_class($this) . ' must define a "run()" method.');
        }
        $args = $this->controller->bindActionParams($this, $params);
        Yii::debug('Running action: ' . get_class($this) . '::run()', __METHOD__);
        if (Yii::$app->requestedParams === null) {
            Yii::$app->requestedParams = $args;
        }
        if ($this->beforeRun()) {
            $result = call_user_func_array([$this, 'run'], $args);
            $this->afterRun();

            return $result;
        }

        return null;
    }

    /**
     * 在执行 `run()` 之前调用此方法。
     * 您可以重写此方法来为动作运行做准备工作。
     * 如果该方法返回 false，将取消该动作。
     *
     * @return bool 是否运行该动作。
     */
    protected function beforeRun()
    {
        return true;
    }

    /**
     * 执行 `run()` 后立即调用此方法。
     * 您可以重写此方法以对动作运行执行后处理工作。
     */
    protected function afterRun()
    {
    }
}
