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
        // install the package the normal composer way
        parent::install($repo, $package);
        // add the package to yiisoft/extensions.php
        $this->addPackage($package);
        // ensure the yii2-dev package also provides Yii.php in the same place as yii2 does
        if ($package->getName() == 'yiisoft/yii2-dev') {
            $this->linkBaseYiiFiles();
        }
    }

    /**
     * @inheritdoc
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        parent::update($repo, $initial, $target);
        $this->removePackage($initial);
        $this->addPackage($target);
        // ensure the yii2-dev package also provides Yii.php in the same place as yii2 does
        if ($initial->getName() == 'yiisoft/yii2-dev') {
            $this->linkBaseYiiFiles();
        }
    }

    /**
     * @inheritdoc
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        // uninstall the package the normal composer way
        parent::uninstall($repo, $package);
        // remove the package from yiisoft/extensions.php
        $this->removePackage($package);
        // remove links for Yii.php
        if ($package->getName() == 'yiisoft/yii2-dev') {
            $this->removeBaseYiiFiles();
        }
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
        if (isset($extra[self::EXTRA_BOOTSTRAP])) {
            $extension['bootstrap'] = $extra[self::EXTRA_BOOTSTRAP];
        }

        $extensions = $this->loadExtensions();
        $extensions[$package->getName()] = $extension;
        $this->saveExtensions($extensions);
    }

    protected function generateDefaultAlias(PackageInterface $package)
    {
        $fs = new Filesystem;
        $vendorDir = $fs->normalizePath($this->vendorDir);
        $autoload = $package->getAutoload();

        $aliases = [];

        if (!empty($autoload['psr-0'])) {
            foreach ($autoload['psr-0'] as $name => $path) {
                $name = str_replace('\\', '/', trim($name, '\\'));
                if (!$fs->isAbsolutePath($path)) {
                    $path = $this->vendorDir . '/' . $package->getPrettyName() . '/' . $path;
                }
                $path = $fs->normalizePath($path);
                if (strpos($path . '/', $vendorDir . '/') === 0) {
                    $aliases["@$name"] = '<vendor-dir>' . substr($path, strlen($vendorDir)) . '/' . $name;
                } else {
                    $aliases["@$name"] = $path . '/' . $name;
                }
            }
        }

        if (!empty($autoload['psr-4'])) {
            foreach ($autoload['psr-4'] as $name => $path) {
                $name = str_replace('\\', '/', trim($name, '\\'));
                if (!$fs->isAbsolutePath($path)) {
                    $path = $this->vendorDir . '/' . $package->getPrettyName() . '/' . $path;
                }
                $path = $fs->normalizePath($path);
                if (strpos($path . '/', $vendorDir . '/') === 0) {
                    $aliases["@$name"] = '<vendor-dir>' . substr($path, strlen($vendorDir));
                } else {
                    $aliases["@$name"] = $path;
                }
            }
        }

        return $aliases;
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
        // invalidate opcache of extensions.php if exists
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($file, true);
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
        // invalidate opcache of extensions.php if exists
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($file, true);
        }
    }

    protected function linkBaseYiiFiles()
    {
        $yiiDir = $this->vendorDir . '/yiisoft/yii2';
        if (!file_exists($yiiDir)) {
            mkdir($yiiDir, 0777, true);
        }
        foreach (['Yii.php', 'BaseYii.php', 'classes.php'] as $file) {
            file_put_contents($yiiDir . '/' . $file, <<<EOF
<?php
/**
 * This is a link provided by the yiisoft/yii2-dev package via yii2-composer plugin.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

return require(__DIR__ . '/../yii2-dev/framework/$file');

EOF
            );
        }
    }

    protected function removeBaseYiiFiles()
    {
        $yiiDir = $this->vendorDir . '/yiisoft/yii2';
        foreach (['Yii.php', 'BaseYii.php', 'classes.php'] as $file) {
            if (file_exists($yiiDir . '/' . $file)) {
                unlink($yiiDir . '/' . $file);
            }
        }
        if (file_exists($yiiDir)) {
            rmdir($yiiDir);
        }
    }
    
    public static function postCreateProject($event)
    {
        $params = $event->getComposer()->getPackage()->getExtra();
        if (isset($params[__METHOD__]) && is_array($params[__METHOD__])) {
            foreach ($params[__METHOD__] as $method => $args) {
                call_user_func_array([__CLASS__, $method], (array) $args);
            }
        }
    }

    /**
     * Sets the correct permission for the files and directories listed in the extra section.
     * @param array $paths the paths (keys) and the corresponding permission octal strings (values)
     */
    public static function setPermission(array $paths)
    {
        foreach ($paths as $path => $permission) {
            echo "chmod('$path', $permission)...";
            if (is_dir($path) || is_file($path)) {
                chmod($path, octdec($permission));
                echo "done.\n";
            } else {
                echo "file not found.\n";
            }
        }
    }

    /**
     * Generates a cookie validation key for every app config listed in "config" in extra section.
     * You can provide one or multiple parameters as the configuration files which need to have validation key inserted.
     */
    public static function generateCookieValidationKey()
    {
        $configs = func_get_args();
        $key = self::generateRandomString();
        foreach ($configs as $config) {
            if (is_file($config)) {
                $content = preg_replace('/(("|\')cookieValidationKey("|\')\s*=>\s*)(""|\'\')/', "\\1'$key'", file_get_contents($config));
                file_put_contents($config, $content);
            }
        }
    }

    protected static function generateRandomString()
    {
        if (!extension_loaded('mcrypt')) {
            throw new \Exception('The mcrypt PHP extension is required by Yii2.');
        }
        $length = 32;
        $bytes = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
        return strtr(substr(base64_encode($bytes), 0, $length), '+/=', '_-.');
    }
}
