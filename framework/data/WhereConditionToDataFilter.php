<?php

namespace yii\data;

use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\db\ExpressionInterface;
use yii\helpers\ArrayHelper;

/**
 * WhereConditionToDataFilter is a converter to transform the Query->where structure to yii\data\DataFilter format.
 * Example:
 * ```php
 * // on client side
 * $query = new yii\db\Query();
 * $query->where(['not in', 'id', [1, 2, 3]]);
 * $converted = (new WhereConditionToDataFilter())->convert($where);  // will return ['id' => ['not in' => [1, 2, 3]]]
 * $jsonCondition = json_encode($converted);
 * $curlManager->send($url, $jsonCondition);
 * $data = $curlManager->response();
 *
 * // on server side
 * $jsonCondition = Yii::$app->request->getBodyParams();
 * $dataFilter = new DataFilter();
 * $condition = $dataFilter->load($jsonCondition)->build();
 * $data = MyModel::find()->where($condition)->asArray()->all();
 * echo json_encode($data):
 * ```
 */
class WhereConditionToDataFilter extends Component
{
    /**
     * @var string[]
     */
    public $operatorList = [];

    /**
     * @var string[]
     */
    public $builtInOperatorList = [
        'in',
        'not in',
        '>',
        '<',
        '<=',
        '>=',
        'between',
        'not between',
        'like',
        'or like',
        'not like',
        'or not like',
        'ilike',
        'or ilike',
        'not ilike',
        'or not ilike',
    ];

    /**
     * @param string|array|ExpressionInterface $where
     *
     * @return array
     * @throws InvalidConfigException
     */
    public function convert($where)
    {
        if (!is_array($where)) {
            return $where;
        }

        $this->validateWhere($where);

        if ($this->containsOperatorAndOr($where)) {
            // Converts: ['and', [['id' => 1], ['name' => 'name1']]] to ['and' => [['id' => 1], ['name' => 'name1']]]
            $where = [$where[0] => array_slice($where, 1)];
        }

        if ($this->containsOperatorWith2Params($where)) {
            $where = [$where[1] => [$where[0] => $where[2]]];
        } elseif ($this->containsOperatorWith3PlusParams($where)) {
            // todo: this format is not supported now
            // Converts: ['like', 'title', 'value', false] to ['title' => ['like' => ['value', false]]]
            // Converts: ['between', 'created_at', '2000-01-01', '3000-01-01'] to ['created_at' => ['between' => ['2000-01-01', '3000-01-01']]]
            $where = [$where[1] => [$where[0] => array_slice($where, 2)]];
        } else {
            foreach ($where as $key => $value) {
                $where[$key] = $this->convert($value);
            }
        }

        return $where;
    }

    /**
     * @param string $operator
     *
     * @return bool
     */
    private function containsAndOr($operator)
    {
        return $this->containsOperatorByList($operator, ['and', 'or']);
    }

    /**
     * @param string $operator
     *
     * @return bool
     */
    private function containsOperator($operator)
    {
        $operators = ArrayHelper::merge($this->builtInOperatorList, $this->operatorList);

        return $this->containsOperatorByList($operator, $operators);
    }

    /**
     * @param string   $operator
     * @param string[] $list
     *
     * @return bool
     */
    private function containsOperatorByList($operator, $list)
    {
        if (!is_string($operator)) {
            return false;
        }

        $operator = trim($operator);
        $operator = preg_replace('/\s+/u', ' ', $operator);
        $operator = mb_strtolower($operator);

        return in_array($operator, $list, true);
    }

    /**
     * @param string|array|ExpressionInterface $where
     *
     * @return bool
     */
    private function containsOperatorWith2Params($where)
    {
        return ArrayHelper::isIndexed($where)
            && $this->containsOperator($where[0])
            && count($where) === 3 // first item is operator
            && is_string($where[1]);
    }

    /**
     * @param string|array|ExpressionInterface $where
     *
     * @return bool
     */
    private function containsOperatorWith3PlusParams($where)
    {
        return ArrayHelper::isIndexed($where)
            && $this->containsOperator($where[0])
            && count($where) > 4 // first item is operator
            && is_string($where[1]);
    }

    /**
     * @param string|array|ExpressionInterface $where
     * @return bool
     */
    private function containsOperatorAndOr($where)
    {
        return ArrayHelper::isIndexed($where) && $this->containsAndOr($where[0]);
    }

    /**
     * @param string|array|ExpressionInterface $where
     *
     * @throws InvalidConfigException
     */
    private function validateWhere($where)
    {
        $isNotValid = ArrayHelper::isIndexed($where)
            && is_string($where[0])
            && $this->containsOperator($where[0])
            && ! is_string($where[1]);

        if ($isNotValid) {
            $given = print_r($where[1], true);
            throw new InvalidConfigException("Condition contains operator, but the parameter name is not string, {$given} given");
        }
    }
}
