<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\QueryInterface;

/**
 * ExistValidator validates that the attribute value exists in a table.
 *
 * ExistValidator checks if the value being validated can be found in the table column specified by
 * the ActiveRecord class [[targetClass]] and the attribute [[targetAttribute]].
 *
 * This validator is often used to verify that a foreign key contains a value
 * that can be found in the foreign table.
 *
 * The following are examples of validation rules using this validator:
 *
 * ```php
 * // a1 needs to exist
 * ['a1', 'exist']
 * // a1 needs to exist, but its value will use a2 to check for the existence
 * ['a1', 'exist', 'targetAttribute' => 'a2']
 * // a1 and a2 need to exist together, and they both will receive error message
 * [['a1', 'a2'], 'exist', 'targetAttribute' => ['a1', 'a2']]
 * // a1 and a2 need to exist together, only a1 will receive error message
 * ['a1', 'exist', 'targetAttribute' => ['a1', 'a2']]
 * // a1 needs to exist by checking the existence of both a2 and a3 (using a1 value)
 * ['a1', 'exist', 'targetAttribute' => ['a2', 'a1' => 'a3']]
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ExistValidator extends Validator
{
    /**
     * @var string the name of the ActiveRecord class that should be used to validate the existence
     * of the current attribute value. If not set, it will use the ActiveRecord class of the attribute being validated.
     * @see targetAttribute
     */
    public $targetClass;
    /**
     * @var string|array the name of the ActiveRecord attribute that should be used to
     * validate the existence of the current attribute value. If not set, it will use the name
     * of the attribute currently being validated. You may use an array to validate the existence
     * of multiple columns at the same time. The array key is the name of the attribute with the value to validate,
     * the array value is the name of the database field to search.
     */
    public $targetAttribute;
    /**
     * @var string the name of the relation that should be used to validate the existence of the current attribute value
     * This param overwrites $targetClass and $targetAttribute
     * @since 2.0.14
     */
    public $targetRelation;
    /**
     * @var string|array|\Closure additional filter to be applied to the DB query used to check the existence of the attribute value.
     * This can be a string or an array representing the additional query condition (refer to [[\yii\db\Query::where()]]
     * on the format of query condition), or an anonymous function with the signature `function ($query)`, where `$query`
     * is the [[\yii\db\Query|Query]] object that you can modify in the function.
     */
    public $filter;
    /**
     * @var bool whether to allow array type attribute.
     */
    public $allowArray = false;
    /**
     * @var string and|or define how target attributes are related
     * @since 2.0.11
     */
    public $targetAttributeJunction = 'and';
    /**
     * @var bool whether this validator is forced to always use master DB
     * @since 2.0.14
     */
    public $forceMasterDb = true;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} is invalid.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        if (!empty($this->targetRelation)) {
            $this->checkTargetRelationExistence($model, $attribute);
        } else {
            $this->checkTargetAttributeExistence($model, $attribute);
        }
    }

    /**
     * Validates existence of the current attribute based on relation name
     * @param \yii\db\ActiveRecord $model the data model to be validated
     * @param string $attribute the name of the attribute to be validated.
     */
    private function checkTargetRelationExistence($model, $attribute)
    {
        $exists = false;
        /** @var ActiveQuery $relationQuery */
        $relationQuery = $model->{'get' . ucfirst($this->targetRelation)}();

        if ($this->filter instanceof \Closure) {
            call_user_func($this->filter, $relationQuery);
        } elseif ($this->filter !== null) {
            $relationQuery->andWhere($this->filter);
        }

        if ($this->forceMasterDb && method_exists($model::getDb(), 'useMaster')) {
            $model::getDb()->useMaster(function() use ($relationQuery, &$exists) {
                $exists = $relationQuery->exists();
            });
        } else {
            $exists = $relationQuery->exists();
        }


        if (!$exists) {
            $this->addError($model, $attribute, $this->message);
        }
    }

    /**
     * Validates existence of the current attribute based on targetAttribute
     * @param \yii\base\Model $model the data model to be validated
     * @param string $attribute the name of the attribute to be validated.
     */
    private function checkTargetAttributeExistence($model, $attribute)
    {
        $targetAttribute = $this->targetAttribute === null ? $attribute : $this->targetAttribute;
        $params = $this->prepareConditions($targetAttribute, $model, $attribute);
        $conditions = [$this->targetAttributeJunction == 'or' ? 'or' : 'and'];

        if (!$this->allowArray) {
            foreach ($params as $key => $value) {
                if (is_array($value)) {
                    $this->addError($model, $attribute, Yii::t('yii', '{attribute} is invalid.'));

                    return;
                }
                $conditions[] = [$key => $value];
            }
        } else {
            $conditions[] = $params;
        }

        $targetClass = $this->targetClass === null ? get_class($model) : $this->targetClass;
        $query = $this->createQuery($targetClass, $conditions);

        if (!$this->valueExists($targetClass, $query, $model->$attribute)) {
            $this->addError($model, $attribute, $this->message);
        }
    }

    /**
     * Processes attributes' relations described in $targetAttribute parameter into conditions, compatible with
     * [[\yii\db\Query::where()|Query::where()]] key-value format.
     *
     * @param $targetAttribute array|string $attribute the name of the ActiveRecord attribute that should be used to
     * validate the existence of the current attribute value. If not set, it will use the name
     * of the attribute currently being validated. You may use an array to validate the existence
     * of multiple columns at the same time. The array key is the name of the attribute with the value to validate,
     * the array value is the name of the database field to search.
     * If the key and the value are the same, you can just specify the value.
     * @param \yii\base\Model $model the data model to be validated
     * @param string $attribute the name of the attribute to be validated in the $model
     * @return array conditions, compatible with [[\yii\db\Query::where()|Query::where()]] key-value format.
     * @throws InvalidConfigException
     */
    private function prepareConditions($targetAttribute, $model, $attribute)
    {
        if (is_array($targetAttribute)) {
            if ($this->allowArray) {
                throw new InvalidConfigException('The "targetAttribute" property must be configured as a string.');
            }
            $conditions = [];
            foreach ($targetAttribute as $k => $v) {
                $conditions[$v] = is_int($k) ? $model->$v : $model->$k;
            }
        } else {
            $conditions = [$targetAttribute => $model->$attribute];
        }

        $targetModelClass = $this->getTargetClass($model);
        if (!is_subclass_of($targetModelClass, 'yii\db\ActiveRecord')) {
            return $conditions;
        }

        /** @var ActiveRecord $targetModelClass */
        return $this->applyTableAlias($targetModelClass::find(), $conditions);
    }

    /**
     * @param Model $model the data model to be validated
     * @return string Target class name
     */
    private function getTargetClass($model)
    {
        return $this->targetClass === null ? get_class($model) : $this->targetClass;
    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        if ($this->targetClass === null) {
            throw new InvalidConfigException('The "targetClass" property must be set.');
        }
        if (!is_string($this->targetAttribute)) {
            throw new InvalidConfigException('The "targetAttribute" property must be configured as a string.');
        }

        if (is_array($value) && !$this->allowArray) {
            return [$this->message, []];
        }

        $query = $this->createQuery($this->targetClass, [$this->targetAttribute => $value]);

        return $this->valueExists($this->targetClass, $query, $value) ? null : [$this->message, []];
    }

    /**
     * Check whether value exists in target table
     *
     * @param string $targetClass
     * @param QueryInterface $query
     * @param mixed $value the value want to be checked
     * @return bool
     */
    private function valueExists($targetClass, $query, $value)
    {
        $db = $targetClass::getDb();
        $exists = false;

        if ($this->forceMasterDb && method_exists($db, 'useMaster')) {
            $db->useMaster(function ($db) use ($query, $value, &$exists) {
                $exists = $this->queryValueExists($query, $value);
            });
        } else {
            $exists = $this->queryValueExists($query, $value);
        }

        return $exists;
    }


    /**
     * Run query to check if value exists
     *
     * @param QueryInterface $query
     * @param mixed $value the value to be checked
     * @return bool
     */
    private function queryValueExists($query, $value)
    {
        if (is_array($value)) {
            return $query->count("DISTINCT [[$this->targetAttribute]]") == count($value) ;
        }
        return $query->exists();
    }

    /**
     * Creates a query instance with the given condition.
     * @param string $targetClass the target AR class
     * @param mixed $condition query condition
     * @return \yii\db\ActiveQueryInterface the query instance
     */
    protected function createQuery($targetClass, $condition)
    {
        /* @var $targetClass \yii\db\ActiveRecordInterface */
        $query = $targetClass::find()->andWhere($condition);
        if ($this->filter instanceof \Closure) {
            call_user_func($this->filter, $query);
        } elseif ($this->filter !== null) {
            $query->andWhere($this->filter);
        }

        return $query;
    }

    /**
     * Returns conditions with alias.
     * @param ActiveQuery $query
     * @param array $conditions array of condition, keys to be modified
     * @param null|string $alias set empty string for no apply alias. Set null for apply primary table alias
     * @return array
     */
    private function applyTableAlias($query, $conditions, $alias = null)
    {
        if ($alias === null) {
            $alias = array_keys($query->getTablesUsedInFrom())[0];
        }
        $prefixedConditions = [];
        foreach ($conditions as $columnName => $columnValue) {
            if (strpos($columnName, '(') === false) {
                $prefixedColumn = "{$alias}.[[" . preg_replace(
                    '/^' . preg_quote($alias) . '\.(.*)$/',
                    '$1',
                    $columnName) . ']]';
            } else {
                // there is an expression, can't prefix it reliably
                $prefixedColumn = $columnName;
            }

            $prefixedConditions[$prefixedColumn] = $columnValue;
        }

        return $prefixedConditions;
    }
}
