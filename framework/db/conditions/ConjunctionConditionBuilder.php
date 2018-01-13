<?php

namespace yii\db\conditions;

use yii\db\ExpressionBuilderInterface;
use yii\db\ExpressionBuilderTrait;
use yii\db\ExpressionInterface;

/**
 * Class ConjunctionConditionBuilder builds objects of abstract class [[ConjunctionCondition]]
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class ConjunctionConditionBuilder implements ExpressionBuilderInterface
{
    use ExpressionBuilderTrait;

    /**
     * Method builds the raw SQL from the $expression that will not be additionally
     * escaped or quoted.
     *
     * @param ExpressionInterface|ConjunctionCondition $expression the expression to be built.
     * @param array $params the binding parameters.
     * @return string the raw SQL that will not be additionally escaped or quoted.
     */
    public function build(ExpressionInterface $expression, array &$params = [])
    {
        $parts = [];
        foreach ($expression->getExpressions() as $expression) {
            if (is_array($expression)) {
                $expression = $this->queryBuilder->buildCondition($expression, $params);
            }
            if ($expression instanceof ExpressionInterface) {
                $expression = $this->queryBuilder->buildExpression($expression, $params);
            }
            if ($expression !== '') {
                $parts[] = $expression;
            }
        }
        if (!empty($parts)) {
            return '(' . implode(") {$expression->getOperator()} (", $parts) . ')';
        }

        return '';
    }
}
