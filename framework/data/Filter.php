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
use yii\helpers\Json;
use yii\web\BadRequestHttpException;

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
    const FORMAT_ARRAY = 'array';
    const FORMAT_JSON = 'json';
    
    /**
     * @var string the name of the parameter that specifies which attributes to be sorted
     * in which direction. Defaults to 'filter'.
     */
    public $filterParam = 'filter';
    /**
     * @var string
     */
    public $format = self::FORMAT_ARRAY;
    protected $queryParams;
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
     * @param bool $recalculate
     * @return array
     */
    public function getConditions()
    {
        $filter = $this->getParams();
        $conditions = [];

        if (!empty($filter)) {

            foreach ($filter as $field => $value) {
                list($operator, $field) = $this->prepareField($field);
                
                $conditions[] = $this->buildCondition($operator, $field, $value);
            }
        }
        
        return $conditions;
    }
    
    public function getData()
    {
        $filter = $this->getParams();
        
        // @todo clear from the operators
        
        return $filter;
    }

    protected function getParams($recalculate = false)
    {
        if ($this->queryParams === null || $recalculate) {
            $this->queryParams = Yii::$app->request->getQueryParam($this->filterParam) ?: [];

            // @todo create formatters
            if (!empty($this->queryParams) && $this->format === static::FORMAT_JSON) {
                try {
                    $this->queryParams = (array) Json::decode($this->queryParams);
                } catch (InvalidParamException $e) {
                    throw new BadRequestHttpException('Syntax error in filter.', 0, $e);
                }
            }
        }

        return $this->queryParams;
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
