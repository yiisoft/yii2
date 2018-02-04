<?php

namespace yii\db;

/**
 * Interface ExpressionBuilderInterface is designed to build raw SQL from specific expression
 * objects that implement [[ExpressionInterface]].
 *
 * @author Dmitry Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
interface ExpressionBuilderInterface
{
    /**
     * Method builds the raw SQL from the $expression that will not be additionally
     * escaped or quoted.
     *
     * @param ExpressionInterface $expression the expression to be built.
     * @param array $params the binding parameters.
     * @return string the raw SQL that will not be additionally escaped or quoted.
     */
    public function build(ExpressionInterface $expression, array &$params = []);
}
