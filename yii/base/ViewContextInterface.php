<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ViewContextInterface is the interface that should implemented by classes who want to support relative view names.
 *
 * The method [[findViewFile()]] should be implemented to convert a relative view name into a file path.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
interface ViewContextInterface
{
	/**
	 * Finds the view file corresponding to the specified relative view name.
	 * @param string $view a relative view name. The name does NOT start with a slash.
	 * @return string the view file path. Note that the file may not exist.
	 */
	public function findViewFile($view);
}
