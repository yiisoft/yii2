<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

/**
 * ResponseFormatter specifies the interface needed to format data for a Web response object.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface ResponseFormatter
{
	/**
	 * Formats the given data for the response.
	 * @param Response $response the response object that will accept the formatted result
	 * @param mixed $data the data to be formatted
	 * @return string the formatted result
	 */
	function format($response, $data);
}
