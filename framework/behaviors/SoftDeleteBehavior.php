<?php

namespace yii\behaviors;

use yii\db\BaseActiveRecord;
use yii\base\ModelEvent;

/**
 * SoftDeleteBehavior automatically aborts row hard deletion in order to perform soft deletion (attribute change)
 * 
 * Note that `$model->delete()` will **always** return false, when this behavior is attached to model.
 * 
 * If you want to hard delete row you ought to call
 * ```php
 * $model->deleteHard();
 * ```
 * Which will trigger standard validation chain and perform row deletion.
 * 
 * @author Tomasz Romik <manetamajster@gmail.com>
 */
class SoftDeleteBehavior extends \yii\base\Behavior
{

    /**
     * @var string|array List of attributes to be changed on soft deletion.
     * Default value of this property is `['deleted', 'deleted_at' => time()]`,
     * which means that `deleted` will receive default behavior value,
     * and 'deleted_at' will be updated to current timestamp.
     */
    public $attributes = [];

    /**
     * @var string the attribute that determines whether row is soft deleted
     */
    public $deletedAttribute = 'deleted';

    /**
     * @var string the attribute that will receive timestamp of row soft deletion
     */
    public $deletedAtAttribute = 'deleted_at';

    /**
     * @var boolean|string|integer|callable the value that will be assigned to [[deletedAttribute]].
     */
    public $value = 1;

    /**
     * @var boolean if the attribute is true, hard deletion will be performed
     */
    protected $hard = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (empty($this->attributes)) {
            $this->attributes = [$this->deletedAttribute, $this->deletedAtAttribute => time()];
        }
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete'
        ];
    }

    /**
     * @param ModelEvent $event
     * @return boolean
     */
    public function beforeDelete($event)
    {
        if ($this->hard === true) {
            $this->hard = false;
            $event->isValid = true;
            return $event->isValid;
        }

        $model = $this->owner;

        if (!is_array($this->attributes)) {
            $this->attributes = [$this->attributes];
        }

        $updatedValues = [];
        foreach ($this->attributes as $key => $attribute) {
            if (is_int($key)) {
                $value = $this->value;
            } else {
                $value = $attribute;
                $attribute = $key;
            }

            $value = $this->getValue($value, $event);

            if ($model->$attribute === $value) {
                continue;
            }

            $updatedValues[$attribute] = $model->$attribute = $value;
            $model->setOldAttribute($attribute, $model->$attribute);
        }

        $event->isValid = false;

        if (empty($updatedValues)) {
            return $event->isValid;
        }

        $class = $model->className();
        $class::updateAll($updatedValues, $model->getPrimaryKey(true));

        return $event->isValid;
    }

    /**
     * @param mixed $value
     * @param ModelEvent $event
     * @return mixed
     */
    protected function getValue($value, $event)
    {
        return $value instanceof \Closure ? call_user_func($value, $event) : $value;
    }

    /**
     * Performs hard delete on the row
     * @see \yii\db\BaseActiveRecord::delete()
     */
    public function deleteHard()
    {
        $this->hard = true;
        return $this->owner->delete();
    }

}
