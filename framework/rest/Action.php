<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecordInterface;
use yii\web\NotFoundHttpException;

/**
 * Action 是实现了 RESTful API 的动作类的基类。
 *
 * 关于 Action 的更多使用参考，请查看 [Rest 控制器指南](guide:rest-controllers)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Action extends \yii\base\Action
{
    /**
     * @var string 模型的类名，用于在这个动作中处理数据。
     * 此模型类必须继承 [[ActiveRecordInterface]]。
     * 此属性必设置
     */
    public $modelClass;
    /**
     * @var callable PHP 回调，用于返回相应的模型实例，
     * 基于所给的主键值。如果不设置此属性，默认是调用 [[findModel()]] 方法。
     * 这个回调的形式如下：
     *
     * ```php
     * function ($id, $action) {
     *     // $id 主键值。 如果是复合主键，
     *     // 则为逗号分隔的键值。
     *     // $action 当前在运行的动作对象
     * }
     * ```
     *
     * 这个回调应当返回查找出的模型实例，如果找不到则抛出异常。
     */
    public $findModel;
    /**
     * @var callable PHP 回调，会在此时被调用：在判断当前用户是否有此动作
     * 的运行权限。如果不设置，默认权限检查不执行。
     * 这个回调的形式如下，
     *
     * ```php
     * function ($action, $model = null) {
     *     // $model 请求的模型实例。
     *     // 如果为 Null，意味着没有特别的模型对象（比如 IndexAction）
     * }
     * ```
     */
    public $checkAccess;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if ($this->modelClass === null) {
            throw new InvalidConfigException(get_class($this) . '::$modelClass must be set.');
        }
    }

    /**
     * 返回有数据的模型类，根据所给的主键值。
     * 如果数据没有找到，会抛出一个 404 HTTP 异常。
     * @param string $id 模型将被加载的 ID 。如果模型有复合主键，
     * 这 ID 则是逗号分隔的键值字符串，
     * 键值的顺序应当和模型类 的 `primaryKey()` 方法返回的一致。
     *
     * @return ActiveRecordInterface 查找出的模型实例
     * @throws NotFoundHttpException 如果模型找不到
     */
    public function findModel($id)
    {
        if ($this->findModel !== null) {
            return call_user_func($this->findModel, $id, $this);
        }

        /* @var $modelClass ActiveRecordInterface */
        $modelClass = $this->modelClass;
        $keys = $modelClass::primaryKey();
        if (count($keys) > 1) {
            $values = explode(',', $id);
            if (count($keys) === count($values)) {
                $model = $modelClass::findOne(array_combine($keys, $values));
            }
        } elseif ($id !== null) {
            $model = $modelClass::findOne($id);
        }

        if (isset($model)) {
            return $model;
        }

        throw new NotFoundHttpException("Object not found: $id");
    }
}
