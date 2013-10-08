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

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Installer extends LibraryInstaller
{
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
		$extension = array('name' => $package->getPrettyName());

		$root = $package->getPrettyName();
		if ($targetDir = $package->getTargetDir()) {
			$root .= '/' . trim($targetDir, '/');
		}
		$root = trim($root, '/');

		$extra = $package->getExtra();

		if (isset($extra['preinit']) && is_string($extra['preinit'])) {
			$extension['preinit'] = "<vendor-dir>/$root/" . ltrim(str_replace('\\', '/', $extra['preinit']), '/');
		}
		if (isset($extra['init']) && is_string($extra['init'])) {
			$extension['init'] = "<vendor-dir>/$root/" . ltrim(str_replace('\\', '/', $extra['init']), '/');
		}

		if (isset($extra['aliases']) && is_array($extra['aliases'])) {
			foreach ($extra['aliases'] as $alias => $path) {
				$extension['aliases']['@' . ltrim($alias, '@')] = "<vendor-dir>/$root/" . ltrim(str_replace('\\', '/', $path), '/');
			}
		}

		if (!empty($aliases)) {
			foreach ($aliases as $alias => $path) {
				if (strncmp($alias, '@', 1) !== 0) {
					$alias = '@' . $alias;
				}
				$path = trim(str_replace('\\', '/', $path), '/');
				$extension['aliases'][$alias] = $root . '/' . $path;
			}
		}

		$extensions = $this->loadExtensions();
		$extensions[$package->getId()] = $extension;
		$this->saveExtensions($extensions);
	}

	protected function removePackage(PackageInterface $package)
	{
		$packages = $this->loadExtensions();
		unset($packages[$package->getId()]);
		$this->saveExtensions($packages);
	}

	protected function loadExtensions()
	{
		$file = $this->vendorDir . '/yii-extensions.php';
		if (!is_file($file)) {
			return array();
		}
		$extensions = require($file);
		/** @var string $vendorDir defined in yii-extensions.php */
		$n = strlen($vendorDir);
		foreach ($extensions as &$extension) {
			if (isset($extension['aliases'])) {
				foreach ($extension['aliases'] as $alias => $path) {
					$extension['aliases'][$alias] = '<vendor-dir>' . substr($path, $n);
				}
			}
			if (isset($extension['preinit'])) {
				$extension['preinit'] = '<vendor-dir>' . substr($extension['preinit'], $n);
			}
			if (isset($extension['init'])) {
				$extension['init'] = '<vendor-dir>' . substr($extension['init'], $n);
			}
		}
		return $extensions;
	}

	protected function saveExtensions(array $extensions)
	{
		$file = $this->vendorDir . '/yii-extensions.php';
		$array = str_replace("'<vendor-dir>", '$vendorDir . \'', var_export($extensions, true));
		file_put_contents($file, "<?php\n\$vendorDir = __DIR__;\n\nreturn $array;\n");
	}
}
