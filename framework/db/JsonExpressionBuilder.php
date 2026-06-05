<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\helpers\Json;

/**
 * Class JsonExpressionBuilder builds [[JsonExpression]] for DBMS that don't provide
 * a vendor-specific solution.
 *
 * @author WarLikeLaux
 * @since 2.0.55
 */
class JsonExpressionBuilder implements ExpressionBuilderInterface
{
    use ExpressionBuilderTrait;

    /**
     * {@inheritdoc}
     * @param JsonExpression|ExpressionInterface $expression the expression to be built
     */
    public function build(ExpressionInterface $expression, array &$params = [])
    {
        $value = $expression->getValue();

        if ($value instanceof Query) {
            list($sql, $params) = $this->queryBuilder->build($value, $params);
            return "($sql)";
        }

        return $this->queryBuilder->bindParam(Json::encode($value), $params);
    }
}
