<?php

namespace yii\db;

/**
 * Class JsonExpression represents data that should be encoded to JSON
 *
 * // TODO: docs, examples
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class JsonExpression implements ExpressionInterface
{
    const TYPE_JSON = 'json';
    const TYPE_JSONB = 'jsonb';

    /**
     * @var object|array|QueryInterface|\Traversable the value to be encoded to JSON
     */
    protected $value;

    /**
     * @var string|null Type of JSON, expression should be casted to. Defaults to `null`, meaning
     * no explicit casting will be performed.
     * This is applicable for some DBMSs for example, PostgreSQL, that has `json` and `jsonb` types.
     */
    protected $type;

    /**
     * JsonExpression constructor.
     *
     * @param object|array|QueryInterface|\Traversable $value the value to be encoded to JSON
     * @param string|null $type the type of the JSON. See [[JsonExpression::type]]
     *
     * @see type
     */
    public function __construct($value, $type = null)
    {
        $this->value = $value;
    }

    /**
     * @return object|array|QueryInterface|\Traversable
     * @see value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return null|string the type of JSON
     * @see type
     */
    public function getType()
    {
        return $this->type;
    }
}
