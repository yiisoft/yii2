<?php

namespace yii\db\mysql;

use yii\db\ExpressionBuilderInterface;
use yii\db\ExpressionBuilderTrait;
use yii\db\ExpressionInterface;
use yii\db\JsonExpression;
use yii\db\Query;
use yii\helpers\Json;

/**
 * Class JsonExpressionBuilder builds [[JsonExpression]] for MySQL DBMS.
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class JsonExpressionBuilder implements ExpressionBuilderInterface
{
    use ExpressionBuilderTrait;

    const PARAM_PREFIX = ':qp';

    /**
     * {@inheritdoc}
     * @param JsonExpression|ExpressionInterface $expression the expression to be built
     */
    public function build(ExpressionInterface $expression, array &$params = [])
    {
        $value = $expression->getValue();

        if ($value instanceof Query) {
            return $this->queryBuilder->buildExpression($value, $params);
        }

        $placeholder = static::PARAM_PREFIX . count($params);
        $params[$placeholder] = Json::encode($value);

        return $placeholder;
    }
}
