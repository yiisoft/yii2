<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\base\InvalidConfigException;

/**
 * JsonExpression 类表示应编码为 JSON 的数据。
 *
 * 例如：
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
     * @var mixed 要编码为 JSON 的值。
     * 该值必须与 [\yii\helpers\Json::encode()|Json::encode()]] 输入要求兼容。
     */
    protected $value;
    /**
     * @var string|null 表达式应该被转为的 JSON 的类型。默认为 `null`，
     * 表示不会执行显式转换。
     * 只有支持不同类型 JSON 的 DBMS 才会遇到此属性。
     * 例如，PostgreSQL 有 `json` 和 `jsonb` 类型。
     */
    protected $type;


    /**
     * JsonExpression 构造函数。
     *
     * @param mixed $value 要编码为 JSON 的值。
     * 该值必须与 [\yii\helpers\Json::encode()|Json::encode()]] 要求兼容。
     * @param string|null $type JSON 的类型。请参阅 [[JsonExpression::type]]
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
     * @return null|string JSON 的类型
     * @see type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * 指定应序列化为 JSON 的数据
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed 可以通过 <b>json_encode</b> 序列化的数据，
     * 它是除资源之外的任何类型的值。
     * @since 2.0.14.2
     * @throws InvalidConfigException 当 JsonExpression 包含 QueryInterface 对象时抛出的异常
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
