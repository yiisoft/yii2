<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use yii\base\InvalidParamException;
use yii\base\Arrayable;
use yii\web\JsExpression;

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
	 * Encodes the given value into a JSON string.
	 * The method enhances `json_encode()` by supporting JavaScript expressions.
	 * In particular, the method will not encode a JavaScript expression that is
	 * represented in terms of a [[JsExpression]] object.
	 * @param mixed $value the data to be encoded
	 * @param integer $options the encoding options. For more details please refer to
	 * <http://www.php.net/manual/en/function.json-encode.php>
	 * @return string the encoding result
	 */
	public static function encode($value, $options = 0)
	{
		$expressions = [];
		$value = static::processData($value, $expressions, uniqid());
		$json = json_encode($value, $options);
		return empty($expressions) ? $json : strtr($json, $expressions);
	}

	/**
	 * Decodes the given JSON string into a PHP data structure.
	 * @param string $json the JSON string to be decoded
	 * @param boolean $asArray whether to return objects in terms of associative arrays.
	 * @return mixed the PHP data
	 * @throws InvalidParamException if there is any decoding error
	 */
	public static function decode($json, $asArray = true)
	{
		if (is_array($json)) {
			throw new InvalidParamException('Invalid JSON data.');
		}
		$decode = json_decode((string)$json, $asArray);
		switch (json_last_error()) {
			case JSON_ERROR_NONE:
				break;
			case JSON_ERROR_DEPTH:
				throw new InvalidParamException('The maximum stack depth has been exceeded.');
			case JSON_ERROR_CTRL_CHAR:
				throw new InvalidParamException('Control character error, possibly incorrectly encoded.');
			case JSON_ERROR_SYNTAX:
				throw new InvalidParamException('Syntax error.');
			case JSON_ERROR_STATE_MISMATCH:
				throw new InvalidParamException('Invalid or malformed JSON.');
			case JSON_ERROR_UTF8:
				throw new InvalidParamException('Malformed UTF-8 characters, possibly incorrectly encoded.');
			default:
				throw new InvalidParamException('Unknown JSON decoding error.');
		}

		return $decode;
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
				$token = "!{[$expPrefix=" . count($expressions) . ']}!';
				$expressions['"' . $token . '"'] = $data->expression;
				return $token;
			} elseif ($data instanceof \JsonSerializable) {
				$data = $data->jsonSerialize();
			} elseif ($data instanceof Arrayable) {
				$data = $data->toArray();
			} else {
				$data = get_object_vars($data);
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
}
