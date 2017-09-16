<?php

namespace yii\db;

/**
 * Interface ExpressionInterface provides API for a DB expression
 * that does not need additional escaping or quoting.
 *
 * The database abstraction layer of Yii framework supports objects that implement this
 * interface and will use them as for passing raw SQL expressions.
 *
 * The default implementation is a class [[Expression]].
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.13
 */
interface ExpressionInterface
{
    /**
     * @return string the expression that will be put in SQL without any escaping or quoting.
     */
    public function __toString();

    /**
     * @return array list of parameters that should be bound for this expression.
     * The keys are placeholders appearing in [[__toString()]] result, and the values
     * are the corresponding parameter values.
     */
    public function getParams();
}
