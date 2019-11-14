<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\behaviors;

use Yii;
use yii\db\BaseActiveRecord;
use yii\base\InvalidCallException;
use yii\validators\NumberValidator;
use yii\helpers\ArrayHelper;

/**
 * OptimisticLockBehavior 自动地根据列名更新模型的锁版本（译者注：请读者自行查阅乐观锁的知识体系），
 * 列名是通过 [[\yii\db\BaseActiveRecord::optimisticLock()|optimisticLock()]] 读取的。
 *
 * 乐观锁机制允许多个用户对同一条记录进行更新并避免潜在的冲突。
 * 比如当用户试图保存含有脏数据的记录（因为另一个用户已经更新过该条记录）时，
 * 将会抛出 [[StaleObjectException]] 异常，
 * 这样更新或者删除操作就会跳过。
 * 
 * 要使用该行为，首先通过列在 [[\yii\db\BaseActiveRecord::optimisticLock()|optimisticLock()]] 
 * 方法注释里的几步来开启乐观锁验证机制，然后从你的 [[\yii\base\Model::rules()|rules()]]
 * 方法里清除掉存有锁版本的列，
 * 最后给你的 ActiveRecord 类添加下面的代码：
 *
 * ```php
 * use yii\behaviors\OptimisticLockBehavior;
 *
 * public function behaviors()
 * {
 *     return [
 *         OptimisticLockBehavior::className(),
 *     ];
 * }
 * ```
 *
 * 默认情况下，OptimisticLockBehavior 会从 [[\yii\web\Request::getBodyParam()|getBodyParam()]] 中解析
 * 提交过来的版本值或者在失败时设置为0。这意味着一个没有携带表示乐观锁版本值的请求也许可以在第一次成功更新该实体，
 * 但自那以后任何更多地请求都应该失败，直到这个请求携带了期望的版本号，
 * 才可以最终更新成功。

 * 该行为一旦附加到模型类中，如果 [[\yii\web\Request::getBodyParam()|getBodyParam()]] 中没有携带版本值，
 * 那么模型类的内部操作比如在保存记录时就会失败。它在扩展模型类上非常有用，
 * 通过覆盖父类的 [[\yii\db\BaseActiveRecord::optimisticLock()|optimisticLock()]] 方法来开启乐观锁，
 * 然后附加行为到子类中，这样可以把携带行为的子类关联到接收并处理终端用户输入数据的控制器的同时，
 * 也可以把子类绑定到父类的内部逻辑处理中，
 * 或者你也可以把 [[value]] 属性配置为 PHP 回调函数来实现不同的逻辑。
 * 
 * OptimisticLockBehavior 也提供了一个名为 [[upgrade()]] 的方法来给模型的版本值加一，
 * 当多个客户端连接出现的情况下，你需要主动标记某实体为脏数据时该方法将非常有用。
 * 这样可以在客户端最终加载该实体之前避免任何更新：
 *
 * ```php
 * $model->upgrade();
 * ```
 *
 * @author Salem Ouerdani <tunecino@gmail.com>
 * @since 2.0.16
 * @see \yii\db\BaseActiveRecord::optimisticLock() 详情来参考如何开启乐观锁。
 */
class OptimisticLockBehavior extends AttributeBehavior
{
    /**
     * {@inheritdoc}
     *
     * 如果是 `null` 它将直接从 [[\yii\web\Request::getBodyParam()|getBodyParam()]] 中解析或者解析失败时设置为 0。
     */
    public $value;
    /**
     * {@inheritdoc}
     */
    public $skipUpdateOnClean = false;
    /**
     * @var string 保存版本值的属性名。
     */
    private $_lockAttribute;


    /**
     * {@inheritdoc}
     */
    public function attach($owner)
    {
        parent::attach($owner);

        if (empty($this->attributes)) {
            $lock = $this->getLockAttribute();
            $this->attributes = array_fill_keys(array_keys($this->events()), $lock);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function events()
    {
        return Yii::$app->request instanceof \yii\web\Request ? [
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'evaluateAttributes',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'evaluateAttributes',
            BaseActiveRecord::EVENT_BEFORE_DELETE => 'evaluateAttributes',
        ] : [];
    }

    /**
     * 返回保存版本值的列名，该列名在 [[\yii\db\BaseActiveRecord::optimisticLock()|optimisticLock()]] 中定义。
     * @return string 属性名。
     * @throws InvalidCallException 如果 [[\yii\db\BaseActiveRecord::optimisticLock()|optimisticLock()]] 配置有误时。
     * @since 2.0.16
     */
    protected function getLockAttribute()
    {
        if ($this->_lockAttribute) {
            return $this->_lockAttribute;
        }

        /* @var $owner BaseActiveRecord */
        $owner = $this->owner;
        $lock = $owner->optimisticLock();
        if ($lock === null || $owner->hasAttribute($lock) === false) {
            throw new InvalidCallException("Unable to get the optimistic lock attribute. Probably 'optimisticLock()' method is misconfigured.");
        }
        $this->_lockAttribute = $lock;
        return $lock;
    }

    /**
     * {@inheritdoc}
     *
     * 如果是 `null`，版本值将直接从 [[\yii\web\Request::getBodyParam()|getBodyParam()]] 中解析或者解析失败时设置为 0。
     */
    protected function getValue($event)
    {
        if ($this->value === null) {
            $request = Yii::$app->getRequest();
            $lock = $this->getLockAttribute();
            $formName = $this->owner->formName();
            $formValue = $formName ? ArrayHelper::getValue($request->getBodyParams(), $formName . '.' . $lock) : null;
            $input = $formValue ?: $request->getBodyParam($lock);
            $isValid = $input && (new NumberValidator())->validate($input);
            return $isValid ? $input : 0;
        }

        return parent::getValue($event);
    }

    /**
     * 主动更新版本值进行加一操作，然后存入数据库中。
     *
     * ```php
     * $model->upgrade();
     * ```
     * @throws InvalidCallException 如果属主是一条新记录时。
     * @since 2.0.16
     */
    public function upgrade()
    {
        /* @var $owner BaseActiveRecord */
        $owner = $this->owner;
        if ($owner->getIsNewRecord()) {
            throw new InvalidCallException('Upgrading the model version is not possible on a new record.');
        }
        $lock = $this->getLockAttribute();
        $version = $owner->$lock ?: 0;
        $owner->updateAttributes([$lock => $version + 1]);
    }
}
