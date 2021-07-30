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
 * BaseJson provides concrete implementation for [[Json]].
 *
 * Do not use BaseJson. Use [[Json]] instead.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BaseJson
{
    /**
     * @var bool|null Enables human readable output a.k.a. Pretty Print.
     * This can useful for debugging during development but is not recommended in a production environment!
     * In case `prettyPrint` is `null` (default) the `options` passed to `encode` functions will not be changed.
     * @since 2.0.43
     */
    public static $prettyPrint = null;
    /**
     * @var array List of JSON Error messages assigned to constant names for better handling of version differences.
     * @since 2.0.7
     */
    public static $jsonErrorMessages = [
        'JSON_ERROR_DEPTH' => 'The maximum stack depth has been exceeded.',
        'JSON_ERROR_STATE_MISMATCH' => 'Invalid or malformed JSON.',
        'JSON_ERROR_CTRL_CHAR' => 'Control character error, possibly incorrectly encoded.',
        'JSON_ERROR_SYNTAX' => 'Syntax error.',
        'JSON_ERROR_UTF8' => 'Malformed UTF-8 characters, possibly incorrectly encoded.', // PHP 5.3.3
        'JSON_ERROR_RECURSION' => 'One or more recursive references in the value to be encoded.', // PHP 5.5.0
        'JSON_ERROR_INF_OR_NAN' => 'One or more NAN or INF values in the value to be encoded.', // PHP 5.5.0
        'JSON_ERROR_UNSUPPORTED_TYPE' => 'A value of a type that cannot be encoded was given.', // PHP 5.5.0
        'JSON_ERROR_INVALID_PROPERTY_NAME' => 'A property name that cannot be encoded was given.', // PHP 7.0.0
        'JSON_ERROR_UTF16' => 'Malformed UTF-16 characters, possibly incorrectly encoded.', // PHP 7.0.0
    ];


    /**
     * Encodes the given value into a JSON string.
     *
     * The method enhances `json_encode()` by supporting JavaScript expressions.
     * In particular, the method will not encode a JavaScript expression that is
     * represented in terms of a [[JsExpression]] object.
     *
     * Note that data encoded as JSON must be UTF-8 encoded according to the JSON specification.
     * You must ensure strings passed to this method have proper encoding before passing them.
     *
     * @param mixed $value the data to be encoded.
     * @param int $options the encoding options. For more details please refer to
     * <https://secure.php.net/manual/en/function.json-encode.php>. Default is `JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE`.
     * @return string the encoding result.
     * @throws InvalidArgumentException if there is any encoding error.
     */
    public static function encode($value, $options = 320)
    {
        $expressions = [];
        $value = static::processData($value, $expressions, uniqid('', true));
        set_error_handler(function () {
            static::handleJsonError(JSON_ERROR_SYNTAX);
        }, E_WARNING);

        if (static::$prettyPrint === true) {
            $options |= JSON_PRETTY_PRINT;
        } elseif (static::$prettyPrint === false) {
            $options &= ~JSON_PRETTY_PRINT;
        }

        $json = json_encode($value, $options);
        restore_error_handler();
        static::handleJsonError(json_last_error());

        return $expressions === [] ? $json : strtr($json, $expressions);
    }

    /**
     * Encodes the given value into a JSON string HTML-escaping entities so it is safe to be embedded in HTML code.
     *
     * The method enhances `json_encode()` by supporting JavaScript expressions.
     * In particular, the method will not encode a JavaScript expression that is
     * represented in terms of a [[JsExpression]] object.
     *
     * Note that data encoded as JSON must be UTF-8 encoded according to the JSON specification.
     * You must ensure strings passed to this method have proper encoding before passing them.
     *
     * @param mixed $value the data to be encoded
     * @return string the encoding result
     * @since 2.0.4
     * @throws InvalidArgumentException if there is any encoding error
     */
    public static function htmlEncode($value)
    {
        return static::encode($value, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }

    /**
     * Decodes the given JSON string into a PHP data structure.
     * @param string $json the JSON string to be decoded
     * @param bool $asArray whether to return objects in terms of associative arrays.
     * @return mixed the PHP data
     * @throws InvalidArgumentException if there is any decoding error
     */
    public static function decode($json, $asArray = true)
    {
        if (is_array($json) || (is_object($json) && !method_exists($json, '__toString'))) {
            throw new InvalidArgumentException('Invalid JSON data.');
        }
        if ($json === null || $json === '') {
            return null;
        }
        $decode = json_decode((string) $json, (bool) $asArray);
        static::handleJsonError(json_last_error());

        return $decode;
    }

    /**
     * Handles [[encode()]] and [[decode()]] errors by throwing exceptions with the respective error message.
     *
     * @param int $lastError error code from [json_last_error()](https://secure.php.net/manual/en/function.json-last-error.php).
     * @throws InvalidArgumentException if there is any encoding/decoding error.
     * @since 2.0.6
     */
    protected static function handleJsonError($lastError)
    {
        if ($lastError !== JSON_ERROR_NONE) {
            if (PHP_VERSION_ID >= 50500) {
                throw new InvalidArgumentException(json_last_error_msg(), $lastError);
            }

            foreach (static::$jsonErrorMessages as $const => $message) {
                if (defined($const) && constant($const) === $lastError) {
                    throw new InvalidArgumentException($message, $lastError);
                }
            }

            throw new InvalidArgumentException('Unknown JSON encoding/decoding error.');
        }
    }

    /**
     * Pre-processes the data before sending it to `json_encode()`.
     * @param mixed $data the data to be processed
     * @param array $expressions collection of JavaScript expressions
     * @param string $expPrefix a prefix internally used to handle JS expressions
     * @return mixed the processed data
     */
    protected static function processData($data, &$expressions, $expPrefix)
    {
        if (is_object($data)) {
            if ($data instanceof JsExpression) {
                $token = '!{[' . $expPrefix . '=' . count($expressions) . ']}!';
                $expressions['"' . $token . '"'] = (string) $data;

                return $token;
            }

            if ($data instanceof \JsonSerializable) {
                $data = $data->jsonSerialize();
            } elseif ($data instanceof Arrayable) {
                $data = $data->toArray();
            } elseif ($data instanceof \SimpleXMLElement || $data instanceof \DateTimeInterface) {
                $data = (array) $data;
            } elseif ($data instanceof \Traversable) {
                $data = iterator_to_array($data);
            }
            // to keep initial data type
            if (is_array($data)) {
                $data = (object) $data;
            }
        }

        if (is_array($data) || is_object($data)) {
            $arrayAccess = is_array($data) || $data instanceof \ArrayAccess;
            foreach ($data as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $value = static::processData($value, $expressions, $expPrefix);
                    if ($arrayAccess) {
                        $data[$key] = $value;
                    } else {
                        $data->{$key} = $value;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Generates a summary of the validation errors.
     * @param Model|Model[] $models the model(s) whose validation errors are to be displayed.
     * @param array $options the tag options in terms of name-value pairs. The following options are specially handled:
     *
     * - showAllErrors: boolean, if set to true every error message for each attribute will be shown otherwise
     *   only the first error message for each attribute will be shown. Defaults to `false`.
     *
     * @return string the generated error summary
     * @since 2.0.14
     */
    public static function errorSummary($models, $options = [])
    {
        $showAllErrors = ArrayHelper::remove($options, 'showAllErrors', false);
        $lines = static::collectErrors($models, $showAllErrors);

        return static::encode($lines);
    }

    /**
     * Return array of the validation errors.
     * @param Model|Model[] $models the model(s) whose validation errors are to be displayed.
     * @param bool $showAllErrors if set to true every error message for each attribute will be shown otherwise
     * only the first error message for each attribute will be shown.
     * @return array of the validation errors
     * @since 2.0.14
     */
    private static function collectErrors($models, $showAllErrors)
    {
        $lines = [];

        if (!is_array($models)) {
            $models = [$models];
        }
        foreach ($models as $model) {
            $lines[] = $model->getErrorSummary($showAllErrors);
        }

        return array_unique(call_user_func_array('array_merge', $lines));
    }
}
