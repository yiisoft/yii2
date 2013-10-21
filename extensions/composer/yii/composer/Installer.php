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
use yii\console\Application;
use yii\console\Exception;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Installer extends LibraryInstaller
{
	const EXTRA_WRITABLE = 'writable';
	const EXTRA_EXECUTABLE = 'executable';
	const EXTRA_CONFIG = 'yii-config';
	const EXTRA_COMMANDS = 'yii-commands';
	const EXTRA_BOOTSTRAP = 'bootstrap';

	/**
	 * @inheritdoc
	 */
	public function supports($packageType)
	{
		return $packageType === 'yii-extension';
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
			'name' => $package->getPrettyName(),
			'version' => $package->getVersion(),
		];

		$extra = $package->getExtra();

		if (isset($extra[self::EXTRA_BOOTSTRAP]) && is_string($extra[self::EXTRA_BOOTSTRAP])) {
			$extension['bootstrap'] = $extra[self::EXTRA_BOOTSTRAP];
		}

		$extensions = $this->loadExtensions();
		$extensions[$package->getUniqueName()] = $extension;
		$this->saveExtensions($extensions);
	}

	protected function removePackage(PackageInterface $package)
	{
		$packages = $this->loadExtensions();
		unset($packages[$package->getUniqueName()]);
		$this->saveExtensions($packages);
	}

	protected function loadExtensions()
	{
		$file = $this->vendorDir . '/yii-extensions.php';
		return is_file($file) ? require($file) : [];
	}

	protected function saveExtensions(array $extensions)
	{
		$file = $this->vendorDir . '/yii-extensions.php';
		file_put_contents($file, "<?php\nreturn " . var_export($extensions, true) . ";\n");
	}


	/**
	 * Sets the correct permissions of files and directories.
	 * @param CommandEvent $event
	 */
	public static function setPermissions($event)
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

	/**
	 * Executes a yii command.
	 * @param CommandEvent $event
	 */
	public static function run($event)
	{
		$options = array_merge([
			self::EXTRA_COMMANDS => [],
		], $event->getComposer()->getPackage()->getExtra());

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
