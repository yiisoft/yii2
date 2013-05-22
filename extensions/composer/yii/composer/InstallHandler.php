<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\composer;

use Composer\Script\CommandEvent;

/**
 * InstallHandler is called by Composer after it installs/updates the current package.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InstallHandler
{
	/**
	 * Sets the correct permissions of files and directories.
	 * @param CommandEvent $event
	 */
	public static function setPermissions($event)
	{
		$options = array_merge(array(
			'writable' => array(),
			'executable' => array(),
		), $event->getComposer()->getPackage()->getExtra());

		foreach ((array)$options['writable'] as $path) {
			echo "Setting writable: $path ...";
			if (is_dir($path)) {
				chmod($path, 0777);
				echo "done\n";
			} else {
				echo "The directory was not found: " . getcwd() . DIRECTORY_SEPARATOR . $path;
				return;
			}
		}

		foreach ((array)$options['executable'] as $path) {
			echo "Setting executable: $path ...";
			if (is_file($path)) {
				chmod($path, 0755);
				echo "done\n";
			} else {
				echo "\n\tThe file was not found: " . getcwd() . DIRECTORY_SEPARATOR . $path . "\n";
				return;
			}
		}
	}
}
