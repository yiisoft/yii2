<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\web\ForbiddenHttpException;

/**
 * ActiveController 实现一组通用操作，以支持对 ActiveRecord 的 RESTful  访问
 *
 * ActiveRecord 类应该通过 [[modelClass]] 指定，并继承了 [[\yii\db\ActiveRecordInterface]]。
 * 默认情况下，支持以下操作：
 *
 * - `index`：模型列表
 * - `view`：返回模型的详细信息
 * - `create`：创建一个新模型
 * - `update`：更新现有模型
 * - `delete`：删除现有模型
 * - `options`：返回允许的 HTTP 方法
 *
 * 您可以通过覆盖 [[actions()]] 方法，取消其中相应的动作，来禁用其中一些操作。
 *
 * 要添加新操作，可以覆盖 [[actions()]] 方法，加上新的 Action 对象，或者直接写个 action 方法。
 * 记得要重写 [[verbs()]] 方法声明你新 action 所允许的 HTTP 方法。
 *
 * 通常，你可以重写 [[checkAccess()]] 方法，以检查当前用户是否具有执行权限。
 *
 *
 * 关于 ActiveController 的更多使用参考，请查看 [Rest 控制器指南](guide:rest-controllers)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveController extends Controller
{
    /**
     * @var string 模型的类名。此属性必须设置
     */
    public $modelClass;
    /**
     * @var string 更新模型时所用的场景。
     * @see \yii\base\Model::scenarios()
     */
    public $updateScenario = Model::SCENARIO_DEFAULT;
    /**
     * @var string 创建模型时所用的场景。
     * @see \yii\base\Model::scenarios()
     */
    public $createScenario = Model::SCENARIO_DEFAULT;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->modelClass === null) {
            throw new InvalidConfigException('The "modelClass" property must be set.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'view' => [
                'class' => 'yii\rest\ViewAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'create' => [
                'class' => 'yii\rest\CreateAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->createScenario,
            ],
            'update' => [
                'class' => 'yii\rest\UpdateAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->updateScenario,
            ],
            'delete' => [
                'class' => 'yii\rest\DeleteAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'options' => [
                'class' => 'yii\rest\OptionsAction',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD'],
            'view' => ['GET', 'HEAD'],
            'create' => ['POST'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE'],
        ];
    }

    /**
     * 检查当前用户的权限。
     *
     * 应该重写此方法以检查当前用户是否具有该权限
     * 对指定的数据模型运行指定的操作。
     * 如果用户没有访问权限，则应抛出 [[ForbiddenHttpException]] 异常
     *
     * @param string $action 被执行的动作类的 ID
     * @param object $model 被访问的模型类。如果为 null，则意味着没有特别的模型对象被访问。
     * @param array $params 额外的参数
     * @throws ForbiddenHttpException 如果用户没有访问权限
     */
    public function checkAccess($action, $model = null, $params = [])
    {
    }
}
