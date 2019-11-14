<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use yii\base\Arrayable;
use yii\base\InvalidArgumentException;
use yii\web\JsExpression;
use yii\base\Model;

/**
 * BaseJson 类为 [[Json]] 提供了具体的实现方法。
 *
 * 不要使用类 BaseJson。使用 [[Json]] 类来代替。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BaseJson
{
    /**
     * 为更好地处理版本差异而分配给常量名的 JSON 错误消息列表。
     * @var array
     * @since 2.0.7
     */
    public static $jsonErrorMessages = [
        'JSON_ERROR_DEPTH' => 'The maximum stack depth has been exceeded.',
        'JSON_ERROR_STATE_MISMATCH' => 'Invalid or malformed JSON.',
        'JSON_ERROR_CTRL_CHAR' => 'Control character error, possibly incorrectly encoded.',
        'JSON_ERROR_SYNTAX' => 'Syntax error.',
        'JSON_ERROR_UTF8' => 'Malformed UTF-8 characters, possibly incorrectly encoded.', // PHP 5.3.3
        'JSON_ERROR_RECURSION' => 'One or more recursive references in the value to be encoded.', // PHP 5.5.0
        'JSON_ERROR_INF_OR_NAN' => 'One or more NAN or INF values in the value to be encoded', // PHP 5.5.0
        'JSON_ERROR_UNSUPPORTED_TYPE' => 'A value of a type that cannot be encoded was given', // PHP 5.5.0
    ];


    /**
     * 将给定值编码为 JSON 字符串。
     *
     * 该方法通过支持 JavaScript 表达式来增强 `json_encode()`。
     * 特别地，该方法不会对以 [[JSExpression]]
     * 对象表示的 JavaScript 表达式进行编码。
     *
     * 这里需要注意编码为 JSON 的数据必须按照 JSON 规范进行 UTF-8 编码。
     * 你必须确保传递给此方法的字符串在传递之前具有正确的编码。
     *
     * @param mixed $value 即将编码的数据。
     * @param int $options 编码选项。
     * 有关详细信息，请参阅 <http://www.php.net/manual/en/function.json-encode.php>。默认值为 `JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE`。
     * @return string 编码结果。
     * @throws InvalidArgumentException 编码过程中发生的异常。
     */
    public static function encode($value, $options = 320)
    {
        $expressions = [];
        $value = static::processData($value, $expressions, uniqid('', true));
        set_error_handler(function () {
            static::handleJsonError(JSON_ERROR_SYNTAX);
        }, E_WARNING);
        $json = json_encode($value, $options);
        restore_error_handler();
        static::handleJsonError(json_last_error());

        return $expressions === [] ? $json : strtr($json, $expressions);
    }

    /**
     * 将给定的值编码为 JSON 字符串进行 HTML-escaping 转义实体，这样就可以安全地嵌入 HTML 代码中。
     *
     * 该方法通过支持 JavaScript 表达式来增强 `json_encode()`。
     * 特别地，该方法不会对以 [[JsExpression]]
     * 对象表示的 JavaScript 表达式进行编码。
     *
     * 这里注意的是按照 JSON 规范编码的数据必须是 UTF-8 编码。
     * 在传递字符串之前，必须确保传递给此方法的字符串具有适当的编码。
     *
     * @param mixed $value 传递被编码的数据
     * @return string 返回编码的结果
     * @since 2.0.4
     * @throws InvalidArgumentException 编码过程中发生的异常
     */
    public static function htmlEncode($value)
    {
        return static::encode($value, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }

    /**
     * 为给定的 JSON 字符串进行解码为 PHP 数据结构。
     * @param string $json 即将解码的数据
     * @param bool $asArray 是否以关联数组的形式返回对象。
     * @return mixed 返回 PHP 数据格式
     * @throws InvalidArgumentException 解码过程中发生的异常
     */
    public static function decode($json, $asArray = true)
    {
        if (is_array($json)) {
            throw new InvalidArgumentException('Invalid JSON data.');
        } elseif ($json === null || $json === '') {
            return null;
        }
        $decode = json_decode((string) $json, $asArray);
        static::handleJsonError(json_last_error());

        return $decode;
    }

    /**
     * 通过抛出异常错误与相应的错误消息来处理 [[encode()]] 和 [[decode()]]。
     *
     * @param int $lastError 错误代码来自 [json_last_error()](http://php.net/manual/en/function.json-last-error.php)。
     * @throws InvalidArgumentException encoding/decoding 过程中发生的异常错误。
     * @since 2.0.6
     */
    protected static function handleJsonError($lastError)
    {
        if ($lastError === JSON_ERROR_NONE) {
            return;
        }

        $availableErrors = [];
        foreach (static::$jsonErrorMessages as $const => $message) {
            if (defined($const)) {
                $availableErrors[constant($const)] = $message;
            }
        }

        if (isset($availableErrors[$lastError])) {
            throw new InvalidArgumentException($availableErrors[$lastError], $lastError);
        }

        throw new InvalidArgumentException('Unknown JSON encoding/decoding error.');
    }

    /**
     * 在将数据发送到 `json_encode()` 之前对其进行预处理。
     * @param mixed $data 要处理的数据
     * @param array $expressions JavaScript 表达式集合
     * @param string $expPrefix 内部用于处理 JS 表达式的前缀
     * @return mixed 返回处理后的数据
     */
    protected static function processData($data, &$expressions, $expPrefix)
    {
        if (is_object($data)) {
            if ($data instanceof JsExpression) {
                $token = "!{[$expPrefix=" . count($expressions) . ']}!';
                $expressions['"' . $token . '"'] = $data->expression;

                return $token;
            } elseif ($data instanceof \JsonSerializable) {
                return static::processData($data->jsonSerialize(), $expressions, $expPrefix);
            } elseif ($data instanceof Arrayable) {
                $data = $data->toArray();
            } elseif ($data instanceof \SimpleXMLElement) {
                $data = (array) $data;
            } else {
                $result = [];
                foreach ($data as $name => $value) {
                    $result[$name] = $value;
                }
                $data = $result;
            }

            if ($data === []) {
                return new \stdClass();
            }
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $data[$key] = static::processData($value, $expressions, $expPrefix);
                }
            }
        }

        return $data;
    }

    /**
     * 生成验证错误的摘要。
     * @param Model|Model[] $models 要显示其验证错误的 model(s)。
     * @param array $options 以键-值对表示的标记选项。以下是特殊处理的选项：
     *
     * - 显示错误：boolean，如果设置为 true 则显示每个属性的所有错误消息，
     *   否则只显示每个属性的第一条错误消息。默认 `false`。
     *
     * @return string 生成的错误摘要
     * @since 2.0.14
     */
    public static function errorSummary($models, $options = [])
    {
        $showAllErrors = ArrayHelper::remove($options, 'showAllErrors', false);
        $lines = self::collectErrors($models, $showAllErrors);

        return json_encode($lines);
    }

    /**
     * 返回验证错误数组
     * @param Model|Model[] $models 要显示其验证错误的 model(s)。
     * @param $showAllErrors boolean，如果设置为 true 将显示每个属性的所有错误信息，
     * 否则只显示每个属性的第一条错误消息。
     * @return 返回验证错误的数组
     * @since 2.0.14
     */
    private static function collectErrors($models, $showAllErrors)
    {
        $lines = [];
        if (!is_array($models)) {
            $models = [$models];
        }

        foreach ($models as $model) {
            $lines = array_unique(array_merge($lines, $model->getErrorSummary($showAllErrors)));
        }

        return $lines;
    }
}
