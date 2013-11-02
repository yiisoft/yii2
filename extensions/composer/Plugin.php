<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

/**
 * Plugin is the composer plugin that registers the Yii composer installer.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Plugin implements PluginInterface
{
	/**
	 * @inheritdoc
	 */
	public function activate(Composer $composer, IOInterface $io)
	{
		$installer = new Installer($io, $composer);
		$composer->getInstallationManager()->addInstaller($installer);
		$file = rtrim($composer->getConfig()->get('vendor-dir'), '/') . '/yiisoft/extensions.php';
		if (!is_file($file)) {
			@mkdir(dirname($file));
			file_put_contents($file, "<?php\nreturn [];\n");
		}
	}
}
