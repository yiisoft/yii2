<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\base\InvalidConfigException;

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
class JsonExpression implements ExpressionInterface, \JsonSerializable
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
        if ($value instanceof self) {
            $value = $value->getValue();
        }

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

    /**
     * Specify data which should be serialized to JSON
     *
     * @link https://www.php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 2.0.14.2
     * @throws InvalidConfigException when JsonExpression contains QueryInterface object
     */
    public function jsonSerialize()
    {
        $value = $this->getValue();
        if ($value instanceof QueryInterface) {
            throw new InvalidConfigException('The JsonExpression class can not be serialized to JSON when the value is a QueryInterface object');
        }

        return $value;
    }
}
