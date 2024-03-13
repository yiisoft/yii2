<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\conditions;

use yii\db\ExpressionBuilderInterface;
use yii\db\ExpressionBuilderTrait;
use yii\db\ExpressionInterface;

/**
 * Class ExistsConditionBuilder builds objects of [[ExistsCondition]]
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class ExistsConditionBuilder implements ExpressionBuilderInterface
{
    use ExpressionBuilderTrait;


    /**
     * Method builds the raw SQL from the $expression that will not be additionally
     * escaped or quoted.
     *
     * @param ExpressionInterface|ExistsCondition $expression the expression to be built.
     * @param array $params the binding parameters.
     * @return string the raw SQL that will not be additionally escaped or quoted.
     */
    public function build(ExpressionInterface $expression, array &$params = [])
    {
        $operator = $expression->getOperator();
        $query = $expression->getQuery();

        $sql = $this->queryBuilder->buildExpression($query, $params);

        return "$operator $sql";
    }
}
