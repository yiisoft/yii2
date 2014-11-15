<?php

namespace yii\behaviors;

use yii\db\BaseActiveRecord;
use yii\base\ModelEvent;
use yii\db\Exception;

/**
 * SoftDeleteBehavior automatically aborts row hard deletion
 * in order to perform soft deletion (attribute change)
 * 
 * If you want to hard delete row you ought to call
 * ```php
 * $model = Model::find()->one();
 * 
 * Model::deleteAll($model->getPrimaryKey(true));
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

}
