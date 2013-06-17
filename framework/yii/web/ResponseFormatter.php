<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

/**
 * ResponseFormatter specifies the interface needed to format a response before it is sent out.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface ResponseFormatter
{
	/**
	 * Formats the specified response.
	 * @param Response $response the response to be formatted.
	 */
	public function format($response);
}
