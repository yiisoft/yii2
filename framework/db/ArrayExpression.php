<?php

namespace yii\db;

/**
 * Class ArrayExpression represents an array SQL expression.
 *
 * Expressions of this type can be used for example in conditions, like:
 *
 * ```php
 * $query->andWhere(['@>', 'items', new ArrayExpression([1, 2, 3], 'integer')])
 * ```
 *
 * which, depending on DBMS, will result in a well-prepared condition. For example, in
 * PostgreSQL it will be compiled to `WHERE "items" @> ARRAY[1, 2, 3]::integer[]`.
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class ArrayExpression implements ExpressionInterface
{
    /**
     * @var null|string the type of the array elements. Defaults to `null` which means the type is
     * not explicitly specified.
     *
     * Note that in case when type is not specified explicitly and DBMS can not guess it from the context,
     * SQL error will be raised.
     */
    protected $type;
    /**
     * @var array|QueryInterface|mixed the array content. Either represented as an array of values or a [[Query]] that
     * returns these values. A single value will be considered as an array containing one element.
     */
    protected $values;

    /**
     * ArrayExpression constructor.
     *
     * @param array|QueryInterface|mixed $values the array content. Either represented as an array of values or a Query that
     * returns these values. A single value will be considered as an array containing one element.
     * @param string|null $type the type of the array elements. Defaults to `null` which means the type is
     * not explicitly specified. In case when type is not specified explicitly and DBMS can not guess it from the context,
     * SQL error will be raised.
     */
    public function __construct($values, $type = null)
    {
        $this->values = $values;
        $this->type = $type;
    }

    /**
     * @return null|string
     * @see type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array|mixed|QueryInterface
     * @see values
     */
    public function getValues()
    {
        return $this->values;
    }
}
