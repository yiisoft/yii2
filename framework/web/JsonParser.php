<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use yii\base\InvalidParamException;
use yii\helpers\Json;

/**
 * Parses a raw HTTP request using Json::encode()
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 * @since 2.0
 */
class JsonParser implements RequestParserInterface
{
	/**
	 * @var boolean whether to return objects in terms of associative arrays.
	 */
	public $asArray = true;

	/**
	 * @var boolean whether to throw an exception if the body is invalid json
	 */
	public $throwException = false;

	/**
	 * @param string $rawBody the raw HTTP request body
	 * @return array parameters parsed from the request body
	 */
	public function parse($rawBody)
	{
		try {
			return Json::decode($rawBody, $this->asArray);
		} catch (InvalidParamException $e) {
			if ($this->throwException) {
				throw $e;
			}
			return null;
		}
	}
}
