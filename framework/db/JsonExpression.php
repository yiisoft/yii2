<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * Class JsonExpression represents data that should be encoded to JSON.
 *
 * For example:
 *
 * ```php
 * new JsonExpression(['a' => 1, 'b' => 2]); // will be encoded to '{"a": 1, "b": 2}'
 * ```
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class JsonExpression implements ExpressionInterface
{
    const TYPE_JSON = 'json';
    const TYPE_JSONB = 'jsonb';

    /**
     * @var mixed the value to be encoded to JSON.
     * The value must be compatible with [\yii\helpers\Json::encode()|Json::encode()]] input requirements.
     */
    protected $value;
    /**
     * @var string|null Type of JSON, expression should be casted to. Defaults to `null`, meaning
     * no explicit casting will be performed.
     * This property will be encountered only for DBMSs that support different types of JSON.
     * For example, PostgreSQL has `json` and `jsonb` types.
     */
    protected $type;


    /**
     * JsonExpression constructor.
     *
     * @param mixed $value the value to be encoded to JSON.
     * The value must be compatible with [\yii\helpers\Json::encode()|Json::encode()]] requirements.
     * @param string|null $type the type of the JSON. See [[JsonExpression::type]]
     *
     * @see type
     */
    public function __construct($value, $type = null)
    {
        $this->value = $value;
        $this->type = $type;
    }

    /**
     * @return mixed
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
