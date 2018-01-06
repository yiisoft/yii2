<?php

namespace yii\db;

/**
 * Class FakeExpressionBuilder does not build the expression, but returns it unmodified
 * instead. This builder is mainly used for [[PdoValue]], but can be used for test purposes as well
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @internal
 */
class FakeExpressionBuilder implements ExpressionBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(ExpressionInterface $expression, &$params = [])
    {
        return $expression;
    }
}
