<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\build\controllers;

use Yii;
use yii\console\Controller;
use yii\helpers\FileHelper;

/**
 * AppController will link the yii2 dev installation to the containted applications vendor dirs
 * to help working on yii using the application to test it.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class AppController extends Controller
{
    public $defaultAction = 'link';

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
     * This command runs the following shell commands in the dev repo root:
     *
     * - Run `composer update`
     * - `rm -rf apps/basic/vendor/yiisoft/yii2`
     * - `rm -rf apps/basic/vendor/yiisoft/yii2-*`
     *
     * And replaces them with symbolic links to the extensions and framework path in the dev repo.
     * @param string $app the application name `basic` or `advanced`.
     */
    public function actionLink($app)
    {
        // root of the dev repo
        $base = dirname(dirname(__DIR__));
        $appDir = "$base/apps/$app";

        // cleanup
        if (is_link($link = "$appDir/vendor/yiisoft/yii2")) {
            $this->stdout("Removing symlink $link.\n");
            $this->unlink($link);
        }
        $extensions = $this->findDirs("$appDir/vendor/yiisoft");
        foreach($extensions as $ext) {
            if (is_link($link = "$appDir/vendor/yiisoft/yii2-$ext")) {
                $this->stdout("Removing symlink $link.\n");
                $this->unlink($link);
            }
        }

        // composer update
        chdir($appDir);
        passthru('composer update --prefer-dist');

        // link directories
        if (is_dir($link = "$appDir/vendor/yiisoft/yii2")) {
            $this->stdout("Removing dir $link.\n");
            FileHelper::removeDirectory($link);
            $this->stdout("Creating symlink for $link.\n");
            symlink("$base/framework", $link);
        }
        $extensions = $this->findDirs("$appDir/vendor/yiisoft");
        foreach($extensions as $ext) {
            if (is_dir($link = "$appDir/vendor/yiisoft/yii2-$ext")) {
                $this->stdout("Removing dir $link.\n");
                FileHelper::removeDirectory($link);
                $this->stdout("Creating symlink for $link.\n");
                symlink("$base/extensions/$ext", $link);
            }
        }

        $this->stdout("done.\n");
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

        foreach($list as $i => $e) {
            if ($e == 'composer') { // skip composer to not break composer update
                unset($list[$i]);
            }
        }

        return $list;
    }
}
