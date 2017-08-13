<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\build\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\FileHelper;

/**
 * This command helps to set up a dev environment with all extensions and applications
 *
 * It will clone an extension or app repo and link the yii2 dev installation to the containted applications/extensions vendor dirs
 * to help working on yii using the application to test it.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class DevController extends Controller
{
    public $defaultAction = 'all';

    /**
     * @var bool whether to use HTTP when cloning github repositories
     */
    public $useHttp = false;

    public $apps = [
        'basic' => 'git@github.com:yiisoft/yii2-app-basic.git',
        'advanced' => 'git@github.com:yiisoft/yii2-app-advanced.git',
        'benchmark' => 'git@github.com:yiisoft/yii2-app-benchmark.git',
    ];

    public $extensions = [
        'apidoc' => 'git@github.com:yiisoft/yii2-apidoc.git',
        'authclient' => 'git@github.com:yiisoft/yii2-authclient.git',
        'bootstrap' => 'git@github.com:yiisoft/yii2-bootstrap.git',
        'codeception' => 'git@github.com:yiisoft/yii2-codeception.git',
        'composer' => 'git@github.com:yiisoft/yii2-composer.git',
        'debug' => 'git@github.com:yiisoft/yii2-debug.git',
        'elasticsearch' => 'git@github.com:yiisoft/yii2-elasticsearch.git',
        'faker' => 'git@github.com:yiisoft/yii2-faker.git',
        'gii' => 'git@github.com:yiisoft/yii2-gii.git',
        'httpclient' => 'git@github.com:yiisoft/yii2-httpclient.git',
        'imagine' => 'git@github.com:yiisoft/yii2-imagine.git',
        'jui' => 'git@github.com:yiisoft/yii2-jui.git',
        'mongodb' => 'git@github.com:yiisoft/yii2-mongodb.git',
        'queue' => 'git@github.com:yiisoft/yii2-queue.git',
        'redis' => 'git@github.com:yiisoft/yii2-redis.git',
        'shell' => 'git@github.com:yiisoft/yii2-shell.git',
        'smarty' => 'git@github.com:yiisoft/yii2-smarty.git',
        'sphinx' => 'git@github.com:yiisoft/yii2-sphinx.git',
        'swiftmailer' => 'git@github.com:yiisoft/yii2-swiftmailer.git',
        'twig' => 'git@github.com:yiisoft/yii2-twig.git',
    ];


    /**
     * Install all extensions and advanced + basic app
     */
    public function actionAll()
    {
        if (!$this->confirm('Install all applications and all extensions now?')) {
            return 1;
        }

        foreach ($this->extensions as $ext => $repo) {
            $ret = $this->actionExt($ext);
            if ($ret !== 0) {
                return $ret;
            }
        }

        foreach ($this->apps as $app => $repo) {
            $ret = $this->actionApp($app);
            if ($ret !== 0) {
                return $ret;
            }
        }

        return 0;
    }

    /**
     * Runs a command in all extension and application directories
     *
     * Can be used to run e.g. `git pull`.
     *
     *     ./build/build dev/run git pull
     *
     * @param string $command the command to run
     */
    public function actionRun($command)
    {
        $command = implode(' ', func_get_args());

        // root of the dev repo
        $base = dirname(dirname(__DIR__));
        $dirs = $this->listSubDirs("$base/extensions");
        $dirs = array_merge($dirs, $this->listSubDirs("$base/apps"));
        asort($dirs);

        $oldcwd = getcwd();
        foreach ($dirs as $dir) {
            $displayDir = substr($dir, strlen($base));
            $this->stdout("Running '$command' in $displayDir...\n", Console::BOLD);
            chdir($dir);
            passthru($command);
            $this->stdout("done.\n", Console::BOLD, Console::FG_GREEN);
        }
        chdir($oldcwd);
    }

    /**
     * This command installs a project template in the `apps` directory and links the framework and extensions
     *
     * It basically runs the following commands in the dev repo root:
     *
     * - Run `composer update`
     * - `rm -rf apps/basic/vendor/yiisoft/yii2`
     * - `rm -rf apps/basic/vendor/yiisoft/yii2-*`
     *
     * And replaces them with symbolic links to the extensions and framework path in the dev repo.
     *
     * Extensions required by the application are automatically installed using the `ext` action.
     *
     * @param string $app the application name e.g. `basic` or `advanced`.
     * @param string $repo url of the git repo to clone if it does not already exist.
     * @return int return code
     */
    public function actionApp($app, $repo = null)
    {
        // root of the dev repo
        $base = dirname(dirname(__DIR__));
        $appDir = "$base/apps/$app";

        if (!file_exists($appDir)) {
            if (empty($repo)) {
                if (isset($this->apps[$app])) {
                    $repo = $this->apps[$app];
                    if ($this->useHttp) {
                        $repo = str_replace('git@github.com:', 'https://github.com/', $repo);
                    }
                } else {
                    $this->stderr("Repo argument is required for app '$app'.\n", Console::FG_RED);
                    return 1;
                }
            }

            $this->stdout("cloning application repo '$app' from '$repo'...\n", Console::BOLD);
            passthru('git clone ' . escapeshellarg($repo) . ' ' . $appDir);
            $this->stdout("done.\n", Console::BOLD, Console::FG_GREEN);
        }

        // cleanup
        $this->stdout("cleaning up application '$app' vendor directory...\n", Console::BOLD);
        $this->cleanupVendorDir($appDir);
        $this->stdout("done.\n", Console::BOLD, Console::FG_GREEN);

        // composer update
        $this->stdout("updating composer for app '$app'...\n", Console::BOLD);
        chdir($appDir);
        passthru('composer update --prefer-dist');
        $this->stdout("done.\n", Console::BOLD, Console::FG_GREEN);

        // link directories
        $this->stdout("linking framework and extensions to '$app' app vendor dir...\n", Console::BOLD);
        $this->linkFrameworkAndExtensions($appDir, $base);
        $this->stdout("done.\n", Console::BOLD, Console::FG_GREEN);

        return 0;
    }

    /**
     * This command installs an extension in the `extensions` directory and links the framework and other extensions
     *
     * @param string $extension the application name e.g. `basic` or `advanced`.
     * @param string $repo url of the git repo to clone if it does not already exist.
     *
     * @return int
     */
    public function actionExt($extension, $repo = null)
    {
        // root of the dev repo
        $base = dirname(dirname(__DIR__));
        $extensionDir = "$base/extensions/$extension";

        if (!file_exists($extensionDir)) {
            if (empty($repo)) {
                if (isset($this->extensions[$extension])) {
                    $repo = $this->extensions[$extension];
                    if ($this->useHttp) {
                        $repo = str_replace('git@github.com:', 'https://github.com/', $repo);
                    }
                } else {
                    $this->stderr("Repo argument is required for extension '$extension'.\n", Console::FG_RED);
                    return 1;
                }
            }

            $this->stdout("cloning extension repo '$extension' from '$repo'...\n", Console::BOLD);
            passthru('git clone ' . escapeshellarg($repo) . ' ' . $extensionDir);
            $this->stdout("done.\n", Console::BOLD, Console::FG_GREEN);
        }

        // cleanup
        $this->stdout("cleaning up extension '$extension' vendor directory...\n", Console::BOLD);
        $this->cleanupVendorDir($extensionDir);
        $this->stdout("done.\n", Console::BOLD, Console::FG_GREEN);

        // composer update
        $this->stdout("updating composer for extension '$extension'...\n", Console::BOLD);
        chdir($extensionDir);
        passthru('composer update --prefer-dist');
        $this->stdout("done.\n", Console::BOLD, Console::FG_GREEN);

        // link directories
        $this->stdout("linking framework and extensions to '$extension' vendor dir...\n", Console::BOLD);
        $this->linkFrameworkAndExtensions($extensionDir, $base);
        $this->stdout("done.\n", Console::BOLD, Console::FG_GREEN);

        return 0;
    }

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        $options = parent::options($actionID);
        if (in_array($actionID, ['ext', 'app', 'all'], true)) {
            $options[] = 'useHttp';
        }
        return $options;
    }


    /**
     * Remove all symlinks in the vendor subdirectory of the directory specified
     * @param string $dir base directory
     */
    protected function cleanupVendorDir($dir)
    {
        if (is_link($link = "$dir/vendor/yiisoft/yii2")) {
            $this->stdout("Removing symlink $link.\n");
            $this->unlink($link);
        }
        $extensions = $this->findDirs("$dir/vendor/yiisoft");
        foreach ($extensions as $ext) {
            if (is_link($link = "$dir/vendor/yiisoft/yii2-$ext")) {
                $this->stdout("Removing symlink $link.\n");
                $this->unlink($link);
            }
        }
    }

    /**
     * Creates symlinks to framework and extension sources for the application
     * @param string $dir application directory
     * @param string $base Yii sources base directory
     *
     * @return int
     */
    protected function linkFrameworkAndExtensions($dir, $base)
    {
        if (is_dir($link = "$dir/vendor/yiisoft/yii2")) {
            $this->stdout("Removing dir $link.\n");
            FileHelper::removeDirectory($link);
            $this->stdout("Creating symlink for $link.\n");
            symlink("$base/framework", $link);
        }
        $extensions = $this->findDirs("$dir/vendor/yiisoft");
        foreach ($extensions as $ext) {
            if (is_dir($link = "$dir/vendor/yiisoft/yii2-$ext")) {
                $this->stdout("Removing dir $link.\n");
                FileHelper::removeDirectory($link);
                $this->stdout("Creating symlink for $link.\n");
                if (!file_exists("$base/extensions/$ext")) {
                    $ret = $this->actionExt($ext);
                    if ($ret !== 0) {
                        return $ret;
                    }
                }
                symlink("$base/extensions/$ext", $link);
            }
        }
    }

    /**
     * Properly removes symlinked directory under Windows, MacOS and Linux
     *
     * @param string $file path to symlink
     */
    protected function unlink($file)
    {
        if (is_dir($file) && DIRECTORY_SEPARATOR === '\\') {
            rmdir($file);
        } else {
            unlink($file);
        }
    }

    /**
     * Get a list of subdirectories for directory specified
     * @param string $dir directory to read
     *
     * @return array list of subdirectories
     */
    protected function listSubDirs($dir)
    {
        $list = [];
        $handle = opendir($dir);
        if ($handle === false) {
            throw new InvalidParamException("Unable to open directory: $dir");
        }
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            // ignore hidden directories
            if ($file[0] === '.') {
                continue;
            }
            if (is_dir("$dir/$file")) {
                $list[] = "$dir/$file";
            }
        }
        closedir($handle);
        return $list;
    }

    /**
     * Finds linkable applications
     *
     * @param string $dir directory to search in
     * @return array list of applications command can link
     */
    protected function findDirs($dir)
    {
        $list = [];
        $handle = @opendir($dir);
        if ($handle === false) {
            return [];
        }
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path) && preg_match('/^yii2-(.*)$/', $file, $matches)) {
                $list[] = $matches[1];
            }
        }
        closedir($handle);

        foreach ($list as $i => $e) {
            if ($e === 'composer') { // skip composer to not break composer update
                unset($list[$i]);
            }
        }

        return $list;
    }
}
