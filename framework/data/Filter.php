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

class Filter extends Object
{
    /**
     * @var string the name of the parameter that specifies which attributes to be sorted
     * in which direction. Defaults to 'filter'.
     */
    public $filterParam = 'filter';
    /**
     * @var array
     */
    public $operators = ['>=', '<=', '>', '<', '!='];
    /**
     * @var array
     */
    public $fields = ['*'];

    /**
     * @param string $modelClass
     * @return array
     * @throws NotAcceptableHttpException
     */
    public function getConditions($modelClass)
    {
        $requestFilter = $this->getRequestFilter();
        $filter = [];

        if (!empty($requestFilter)) {
            /** @var $model ActiveRecord */
            $model = new $modelClass;

            foreach ($requestFilter as $field => $value) {
                list($operator, $field) = $this->prepareField($field);

                if (!$this->isAcceptableField($field) || !$model->hasAttribute($field)) {
                    throw new NotAcceptableHttpException('Filter by field "' . $field . '" unsupported.');
                }
                                
                if ($operator) {
                    if (is_array($value) && !empty($value) && $operator === '!=') {
                        $operator = 'NOT IN';
                    }
                    $filter[] = [$operator, $field, $value];
                } elseif (substr($value, 0, 1) === '%' || substr($value, -1, 1) === '%') {
                    $filter[] = ['like', $field, $value, false];
                } else {
                    $filter[] = [$field => $value];
                }
            }
        }
        
        return $filter;
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
        foreach ($this->operators as $operator) {
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
}
