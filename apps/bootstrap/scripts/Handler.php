<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace scripts;

use Composer\Script\Event;

define('BASE_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR . '..');

/**
 * Class to handle composer scripts (eg. installation)
 *
 * @see http://getcomposer.org/doc/articles/scripts.md
 * @author Tobias Munk <schmunk@usrbin.de>
 * @since 2.0
 */
class Handler
{
	public static function postInstall(Event $event)
	{
		chmod(BASE_PATH . DIRECTORY_SEPARATOR . 'runtime', 0777);
		chmod(BASE_PATH . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'assets', 0777);
		echo "\n\nInstallation completed.\n\n";
	}
}
