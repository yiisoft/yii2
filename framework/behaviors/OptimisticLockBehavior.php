<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\behaviors;

use Yii;
use yii\db\BaseActiveRecord;
use yii\base\InvalidCallException;
use yii\validators\NumberValidator;
use yii\helpers\ArrayHelper;

/**
 * OptimisticLockBehavior automatically upgrades a model's lock version using the column name
 * returned by [[\yii\db\BaseActiveRecord::optimisticLock()|optimisticLock()]].
 *
 * Optimistic locking allows multiple users to access the same record for edits and avoids
 * potential conflicts. In case when a user attempts to save the record upon some staled data
 * (because another user has modified the data), a [[StaleObjectException]] exception will be thrown,
 * and the update or deletion is skipped.
 *
 * To use this behavior, first enable optimistic lock by following the steps listed in
 * [[\yii\db\BaseActiveRecord::optimisticLock()|optimisticLock()]], remove the column name
 * holding the lock version from the [[\yii\base\Model::rules()|rules()]] method of your
 * ActiveRecord class, then add the following code to it:
 *
 * ```
 * use yii\behaviors\OptimisticLockBehavior;
 *
 * public function behaviors()
 * {
 *     return [
 *         OptimisticLockBehavior::class,
 *     ];
 * }
 * ```
 *
 * By default, OptimisticLockBehavior will use [[\yii\web\Request::getBodyParam()|getBodyParam()]] to parse
 * the submitted value or set it to 0 on any fail. That means a request not holding the version attribute
 * may achieve a first successful update to entity, but starting from there any further try should fail
 * unless the request is holding the expected version number.
 *
 * Once attached, internal use of the model class should also fail to save the record if the version number
 * isn't held by [[\yii\web\Request::getBodyParam()|getBodyParam()]]. It may be useful to extend your model class,
 * enable optimistic lock in parent class by overriding [[\yii\db\BaseActiveRecord::optimisticLock()|optimisticLock()]],
 * then attach the behavior to the child class so you can tie the parent model to internal use while linking the child model
 * holding this behavior to the controllers responsible of receiving end user inputs.
 * Alternatively, you can also configure the [[value]] property with a PHP callable to implement a different logic.
 *
 * OptimisticLockBehavior also provides a method named [[upgrade()]] that increases a model's
 * version by one, that may be useful when you need to mark an entity as stale among connected clients
 * and avoid any change to it until they load it again:
 *
 * ```
 * $model->upgrade();
 * ```
 *
 * @author Salem Ouerdani <tunecino@gmail.com>
 * @since 2.0.16
 * @see \yii\db\BaseActiveRecord::optimisticLock() for details on how to enable optimistic lock.
 *
 * @template T of BaseActiveRecord
 * @extends AttributeBehavior<T>
 */
class OptimisticLockBehavior extends AttributeBehavior
{
    /**
     * {@inheritdoc}
     *
     * In case of `null` value it will be directly parsed from [[\yii\web\Request::getBodyParam()|getBodyParam()]] or set to 0.
     */
    public $value;
    /**
     * {@inheritdoc}
     */
    public $skipUpdateOnClean = false;

    /**
     * @var string the attribute name holding the version value.
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
     * Returns the column name to hold the version value as defined in [[\yii\db\BaseActiveRecord::optimisticLock()|optimisticLock()]].
     * @return string the property name.
     * @throws InvalidCallException if [[\yii\db\BaseActiveRecord::optimisticLock()|optimisticLock()]] is not properly configured.
     * @since 2.0.16
     */
    protected function getLockAttribute()
    {
        if ($this->_lockAttribute) {
            return $this->_lockAttribute;
        }

        /** @var BaseActiveRecord $owner */
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
     * In case of `null`, value will be parsed from [[\yii\web\Request::getBodyParam()|getBodyParam()]] or set to 0.
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
     * Upgrades the version value by one and stores it to database.
     *
     * ```
     * $model->upgrade();
     * ```
     * @throws InvalidCallException if owner is a new record.
     * @since 2.0.16
     */
    public function upgrade()
    {
        /** @var BaseActiveRecord $owner */
        $owner = $this->owner;
        if ($owner->getIsNewRecord()) {
            throw new InvalidCallException('Upgrading the model version is not possible on a new record.');
        }
        $lock = $this->getLockAttribute();
        $version = $owner->$lock ?: 0;
        $owner->updateAttributes([$lock => $version + 1]);
    }
}
