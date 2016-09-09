<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\data;

use Yii;
use yii\base\InvalidParamException;
use yii\base\Object;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\NotAcceptableHttpException;
use yii\web\Request;

/**
 * Filter represents information relevant to filtering.
 * 
 * When data needs to be filtered according to one or several fields, we can use Filter to represent the 
 * filtering information.
 * 
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 * @since 2.0.10
 */
class Filter extends Object
{
    /**
     * @var array list of filter rules. The rules can be a little. Syntax:
     * 
     * - * — allowed filtering by all fields (by default),
     * - <field> — allowed filtering by field with name <field>,
     * - !<field> — not allowed filtering by field with name <field>.
     */
    public $fields = ['*'];
    /**
     * @var string the name of the parameter that specifies which attributes to be sorted
     * in which direction. Defaults to 'filter'.
     */
    public $filterParam = 'filter';
    /**
     * @var array builders for operators from the requested filter. The key of array is operator or system name 
     * (for example, builder for like or simple conditions), the value — name of the method builder.
     */
    public $builders = [
        '>=' => 'buildSimpleCondition',
        '<=' => 'buildSimpleCondition',
        '>' => 'buildSimpleCondition',
        '<' => 'buildSimpleCondition',
        '!=' => 'buildNeqCondition',
        'simple' => 'buildSimpleCondition',
        'like' => 'buildLikeCondition'
    ];

    /**
     * @param string|null $modelClass
     * @return array
     * @throws NotAcceptableHttpException
     */
    public function getConditions($modelClass = null)
    {
        $filter = $this->getRequestFilter();
        $conditions = [];

        if (!empty($filter)) {

            if(!is_null($modelClass)) {
                /** @var ActiveRecord $model */
                $model = new $modelClass;
            }

            foreach ($filter as $field => $value) {
                list($operator, $field) = $this->prepareField($field);

                if (!$this->isAcceptableField($field) || (!empty($model) && $model->hasAttribute($field))) {
                    throw new NotAcceptableHttpException('Filter by field "' . $field . '" unsupported.');
                }
                
                $conditions[] = $this->buildCondition($operator, $field, $value);
            }
        }
        
        return $conditions;
    }

    /**
     * @return array
     * @throws BadRequestHttpException
     */
    protected function getRequestFilter()
    {
        $request = Yii::$app->getRequest();
        $params = $request instanceof Request ? $request->getQueryParams() : [];
        $filter = [];

        if (isset($params[$this->filterParam])) {
            try {
                $filter = (array) Json::decode($params[$this->filterParam]);
            } catch (InvalidParamException $e) {
                throw new BadRequestHttpException('Syntax error in filter.', 0, $e);
            }
        }

        return $filter;
    }

    /**
     * @param string $field
     * @return array
     */
    private function prepareField($field)
    {
        foreach ($this->builders as $operator => $builder) {
            if (strpos($field, $operator) === 0) {
                return [$operator, substr($field, strlen($operator))];
            }
        }
        
        return [null, $field];
    }

    /**
     * @param string $field
     * @return bool
     */
    protected function isAcceptableField($field)
    {
        if (in_array('!' . $field, $this->fields)) {
            return false;
        } elseif (!in_array('*', $this->fields) && !in_array($field, $this->fields)) {
            return false;
        }
        
        return true;
    }

    public function buildCondition($operator = null, $field, $value)
    {
        if ($operator !== null && isset($this->builders[$operator])) {
            $method = $this->builders[$operator];
        } elseif (is_string($value) && (substr($value, 0, 1) === '%' || substr($value, -1, 1) === '%')) {
            $method = $this->builders['like'];
        } else {
            $method = $this->builders['simple'];
        }

        return $this->$method($operator, $field, $value);
    }

    protected function buildSimpleCondition($operator, $field, $value)
    {
        if ($operator) {
            return [$operator, $field, $value];
        } else {
            return [$field => $value];
        }
    }

    protected function buildNeqCondition($operator, $field, $value)
    {
        if (is_array($value) && !empty($value)) {
            return ['NOT IN', $field, $value];
        } else {
            return ['NOT', [$field => $value]];
        }
    }
    
    protected function buildLikeCondition($operator, $field, $value)
    {
        return ['like', $field, $value, false];
    }
}
