<?php

namespace yii\behaviors;

use yii\db\BaseActiveRecord;
use yii\base\ModelEvent;
use yii\db\Exception;

class SoftDeleteBehavior extends \yii\base\Behavior
{

    /**
     * @var string|array
     */
    public $attributes = ['deleted'];

    /**
     *
     * @var mixed
     */
    public $value = true;

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
            if (!is_int($key)) {
                $value = $attribute;
                $attribute = $key;
            } else {
                $value = $this->value;
            }

            $value = $this->getValue($value, $event);

            if ($model->$attribute === $value) {
                continue;
            }

            $updatedValues[$attribute] = $this->value;
        }

        $event->isValid = false;

        if (empty($updatedValues)) {
            return $event->isValid;
        }

        $class = $model->className();
        $updatedRows = $class::updateAll($updatedValues, $model->getPrimaryKey(true));
        if ($updatedRows === 0) {
            throw new Exception('Could not update attributes');
        } else if ($updatedRows !== count($updatedValues)) {
            throw new Exception('Not all rows has been updated');
        }

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
