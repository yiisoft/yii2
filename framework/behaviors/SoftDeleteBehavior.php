<?php

namespace yii\behaviors;

use yii\db\BaseActiveRecord;
use yii\base\ModelEvent;

/**
 * SoftDeleteBehavior automatically aborts row hard deletion
 * in order to perform soft deletion (attribute change)
 * 
 * If you want to hard delete row you ought to call
 * ```php
 * $model = Model::find()->one();
 * 
 * $model->deleteHard();
 * ```
 * 
 * @author Tomasz Romik <manetamajster@gmail.com>
 */
class SoftDeleteBehavior extends \yii\base\Behavior
{

    /**
     * @var string|array
     */
    public $attributes = [];

    /**
     * @var string
     */
    public $deletedAttribute = 'deleted';

    /**
     * @var string
     */
    public $deletedAtAttribute = 'deleted_at';

    /**
     * @var mixed
     */
    public $value = 1;

    /**
     * @var boolean
     */
    protected $hard = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
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
     * 
     * @see \yii\db\BaseActiveRecord::delete()
     */
    public function deleteHard()
    {
        $this->hard = true;
        return $this->owner->delete();
    }

}
