<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\composer;

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Script\CommandEvent;
use Composer\Util\Filesystem;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Installer extends LibraryInstaller
{
	const EXTRA_BOOTSTRAP = 'bootstrap';
	const EXTRA_WRITABLE = 'writable';
	const EXTRA_EXECUTABLE = 'executable';

	const EXTENSION_FILE = 'yiisoft/extensions.php';

	/**
	 * @inheritdoc
	 */
	public function supports($packageType)
	{
		return $packageType === 'yii2-extension';
	}

	/**
	 * @inheritdoc
	 */
	public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
	{
		parent::install($repo, $package);
		$this->addPackage($package);
	}

	/**
	 * @inheritdoc
	 */
	public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
	{
		parent::update($repo, $initial, $target);
		$this->removePackage($initial);
		$this->addPackage($target);
	}

	/**
	 * @inheritdoc
	 */
	public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
	{
		parent::uninstall($repo, $package);
		$this->removePackage($package);
	}

	protected function addPackage(PackageInterface $package)
	{
		$extension = [
			'name' => $package->getName(),
			'version' => $package->getVersion(),
		];

		$alias = $this->generateDefaultAlias($package);
		if (!empty($alias)) {
			$extension['alias'] = $alias;
		}
		$extra = $package->getExtra();
		if (isset($extra[self::EXTRA_BOOTSTRAP]) && is_string($extra[self::EXTRA_BOOTSTRAP])) {
			$extension['bootstrap'] = $extra[self::EXTRA_BOOTSTRAP];
		}

		$extensions = $this->loadExtensions();
		$extensions[$package->getName()] = $extension;
		$this->saveExtensions($extensions);
	}

	protected function generateDefaultAlias(PackageInterface $package)
	{
		$autoload = $package->getAutoload();
		if (!empty($autoload['psr-0'])) {
			$fs = new Filesystem;
			$vendorDir = $fs->normalizePath($this->vendorDir);
			foreach ($autoload['psr-0'] as $name => $path) {
				$name = str_replace('\\', '/', trim($name, '\\'));
				if (!$fs->isAbsolutePath($path)) {
					$path = $this->vendorDir . '/' . $path;
				}
				$path = $fs->normalizePath($path);
				if (strpos($path . '/', $vendorDir . '/') === 0) {
					return ["@$name" => '<vendor-dir>' . substr($path, strlen($vendorDir)) . '/' . $name];
				} else {
					return ["@$name" => $path . '/' . $name];
				}
			}
		}
		return false;
	}

	protected function removePackage(PackageInterface $package)
	{
		$packages = $this->loadExtensions();
		unset($packages[$package->getName()]);
		$this->saveExtensions($packages);
	}

	protected function loadExtensions()
	{
		$file = $this->vendorDir . '/' . self::EXTENSION_FILE;
		if (!is_file($file)) {
			return [];
		}
		$extensions = require($file);

		$vendorDir = str_replace('\\', '/', $this->vendorDir);
		$n = strlen($vendorDir);

		foreach ($extensions as &$extension) {
			if (isset($extension['alias'])) {
				foreach ($extension['alias'] as $alias => $path) {
					$path = str_replace('\\', '/', $path);
					if (strpos($path . '/', $vendorDir . '/') === 0) {
						$extension['alias'][$alias] = '<vendor-dir>' . substr($path, $n);
					}
				}
			}
		}

		return $extensions;
	}

	protected function saveExtensions(array $extensions)
	{
		$file = $this->vendorDir . '/' . self::EXTENSION_FILE;
		$array = str_replace("'<vendor-dir>", '$vendorDir . \'', var_export($extensions, true));
		file_put_contents($file, "<?php\n\n\$vendorDir = dirname(__DIR__);\n\nreturn $array;\n");
	}


	/**
	 * Sets the correct permission for the files and directories listed in the extra section.
	 * @param CommandEvent $event
	 */
	public static function setPermission($event)
	{
		$options = array_merge([
			self::EXTRA_WRITABLE => [],
			self::EXTRA_EXECUTABLE => [],
		], $event->getComposer()->getPackage()->getExtra());

		foreach ((array)$options[self::EXTRA_WRITABLE] as $path) {
			echo "Setting writable: $path ...";
			if (is_dir($path)) {
				chmod($path, 0777);
				echo "done\n";
			} else {
				echo "The directory was not found: " . getcwd() . DIRECTORY_SEPARATOR . $path;
				return;
			}
		}

		foreach ((array)$options[self::EXTRA_EXECUTABLE] as $path) {
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
