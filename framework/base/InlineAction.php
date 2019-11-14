<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * InlineAction 表示定义为控制器方法的动作。
 *
 * 控制器方法的名称可通过 [[actionMethod]]
 * 获得，该 [[controller]] 由创建此动作的 [[controller]] 设置。
 *
 * 有关 InlineAction 的更多详细信息和使用信息，请参阅 [有关动作的指南文章](guide:structure-controllers).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InlineAction extends Action
{
    /**
     * @var string 与此内联动作关联的控制器方法
     */
    public $actionMethod;


    /**
     * @param string $id 此动作的ID
     * @param Controller $controller 拥有此动作的控制器
     * @param string $actionMethod 与此内联动作关联的控制器方法
     * @param array $config 将用于初始化对象属性的键值对
     */
    public function __construct($id, $controller, $actionMethod, $config = [])
    {
        $this->actionMethod = $actionMethod;
        parent::__construct($id, $controller, $config);
    }

    /**
     * 使用指定的参数运行此动作。
     * 该方法主要由控制器调用。
     * @param array $params 动作参数
     * @return mixed 动作的结果
     */
    public function runWithParams($params)
    {
        $args = $this->controller->bindActionParams($this, $params);
        Yii::debug('Running action: ' . get_class($this->controller) . '::' . $this->actionMethod . '()', __METHOD__);
        if (Yii::$app->requestedParams === null) {
            Yii::$app->requestedParams = $args;
        }

        return call_user_func_array([$this->controller, $this->actionMethod], $args);
    }
}
