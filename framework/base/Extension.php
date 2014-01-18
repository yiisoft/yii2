<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Extension is the base class that may be extended by individual extensions.
 *
 * Extension serves as the bootstrap class for extensions. When an extension
 * is installed via composer, the [[init()]] method of its Extension class (if any)
 * will be invoked during the application initialization stage.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Extension
{
	/**
	 * Initializes the extension.
	 * This method is invoked at the end of [[Application::init()]].
	 */
	public static function init()
	{
	}
}
