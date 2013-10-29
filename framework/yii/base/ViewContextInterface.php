<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ViewContextInterface represents possible context for the view rendering.
 * It determines the way the non-global view files are searched.
 * This interface introduces method [[findViewFile]], which will be used
 * at [[View::render()]] to determine the file by view name, which does
 * not match default format.
 *
 * @see View
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
interface ViewContextInterface
{
	/**
	 * Finds the view file based on the given view name.
	 * @param string $view the view name.
	 * @return string the view file path. Note that the file may not exist.
	 */
	public function findViewFile($view);
}