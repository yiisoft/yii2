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
	const EXTRA_WRITABLES = 'yii-writables';
	const EXTRA_EXECUTABLES = 'yii-executables';
	const EXTRA_CONFIG = 'yii-config';
	const EXTRA_COMMANDS = 'yii-commands';
	const EXTRA_ALIASES = 'yii-aliases';
	const EXTRA_PREINIT = 'yii-preinit';
	const EXTRA_INIT = 'yii-init';

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

		if (isset($extra[self::EXTRA_PREINIT]) && is_string($extra[self::EXTRA_PREINIT])) {
			$extension[self::EXTRA_PREINIT] = "<vendor-dir>/$root/" . ltrim(str_replace('\\', '/', $extra[self::EXTRA_PREINIT]), '/');
		}
		if (isset($extra[self::EXTRA_INIT]) && is_string($extra[self::EXTRA_INIT])) {
			$extension[self::EXTRA_INIT] = "<vendor-dir>/$root/" . ltrim(str_replace('\\', '/', $extra[self::EXTRA_INIT]), '/');
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
			if (isset($extension[self::EXTRA_PREINIT])) {
				$extension[self::EXTRA_PREINIT] = '<vendor-dir>' . substr($extension[self::EXTRA_PREINIT], $n);
			}
			if (isset($extension[self::EXTRA_INIT])) {
				$extension[self::EXTRA_INIT] = '<vendor-dir>' . substr($extension[self::EXTRA_INIT], $n);
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


	/**
	 * Sets the correct permissions of files and directories.
	 * @param CommandEvent $event
	 */
	public static function setPermissions($event)
	{
		$options = array_merge(array(
			self::EXTRA_WRITABLES => array(),
			self::EXTRA_EXECUTABLES => array(),
		), $event->getComposer()->getPackage()->getExtra());

		foreach ((array)$options[self::EXTRA_WRITABLES] as $path) {
			echo "Setting writable: $path ...";
			if (is_dir($path)) {
				chmod($path, 0777);
				echo "done\n";
			} else {
				echo "The directory was not found: " . getcwd() . DIRECTORY_SEPARATOR . $path;
				return;
			}
		}

		foreach ((array)$options[self::EXTRA_EXECUTABLES] as $path) {
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

	/**
	 * Executes a yii command.
	 * @param CommandEvent $event
	 */
	public static function run($event)
	{
		$options = array_merge(array(
			self::EXTRA_COMMANDS => array(),
		), $event->getComposer()->getPackage()->getExtra());

		if (!isset($options[self::EXTRA_CONFIG])) {
			throw new Exception('Please specify the "' . self::EXTRA_CONFIG . '" parameter in composer.json.');
		}
		$configFile = getcwd() . '/' . $options[self::EXTRA_CONFIG];
		if (!is_file($configFile)) {
			throw new Exception("Config file does not exist: $configFile");
		}

		require_once(__DIR__ . '/../../../yii2/yii/Yii.php');
		$application = new Application(require($configFile));
		$request = $application->getRequest();

		foreach ((array)$options[self::EXTRA_COMMANDS] as $command) {
			$params = str_getcsv($command, ' '); // see http://stackoverflow.com/a/6609509/291573
			$request->setParams($params);
			list($route, $params) = $request->resolve();
			echo "Running command: yii {$command}\n";
			$application->runAction($route, $params);
		}
	}
}
