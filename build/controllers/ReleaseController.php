<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\build\controllers;

use Yii;
use yii\console\Controller;

/**
 * ReleaseController is there to help preparing releases
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ReleaseController extends Controller
{
    public $defaultAction = 'help';

    /**
     * Usage:
     *
     * ```
     * ./build/build release/prepare 2.0.0-beta
     * ```
     *
     */
    public function actionPrepare($version)
    {
        $this->closeChangelogs($version);
        $this->composerSetStability($version);
        $this->updateYiiVersion($version);
    }

    /**
     * Usage:
     *
     * ```
     * ./build/build release/done 2.0.0-dev 2.0.0-rc
     * ```
     */
    public function actionDone($devVersion, $nextVersion)
    {
        $this->openChangelogs($nextVersion);
        $this->composerSetStability('dev');
        $this->updateYiiVersion($devVersion);
    }

    protected function closeChangelogs($version)
    {
        $v = str_replace('\\-', '[\\- ]', preg_quote($version, '/'));
        $headline = $version . ' ' . date('F d, Y');
        $this->sed(
            '/'.$v.' under development\n(-+?)\n/',
            $headline . "\n" . str_repeat('-', strlen($headline)) . "\n",
            $this->getChangelogs()
        );
    }

    protected function openChangelogs($version)
    {
        $headline = "\n$version under development\n";
        $headline .= str_repeat('-', strlen($headline) - 2) . "\n\n";
        $headline .= "- no changes in this release.\n";
        foreach($this->getChangelogs() as $file) {
            $lines = explode("\n", file_get_contents($file));
            $hl = [
                array_shift($lines),
                array_shift($lines),
            ];
            array_unshift($lines, $headline);

            file_put_contents($file, implode("\n", array_merge($hl, $lines)));
        }
    }

    protected function getChangelogs()
    {
        return array_merge([YII2_PATH . '/CHANGELOG.md'], glob(dirname(YII2_PATH) . '/extensions/*/CHANGELOG.md'));
    }

    protected function composerSetStability($version)
    {
        $stability = 'stable';
        if (strpos($version, 'alpha') !== false) {
            $stability = 'alpha';
        } elseif (strpos($version, 'beta') !== false) {
            $stability = 'beta';
        } elseif (strpos($version, 'rc') !== false) {
            $stability = 'RC';
        } elseif (strpos($version, 'dev') !== false) {
            $stability = 'dev';
        }

        $this->sed(
            '/"minimum-stability": "(.+?)",/',
            '"minimum-stability": "' . $stability . '",',
            [
                dirname(YII2_PATH) . '/apps/advanced/composer.json',
                dirname(YII2_PATH) . '/apps/basic/composer.json',
                dirname(YII2_PATH) . '/apps/benchmark/composer.json',
            ]
        );
    }

    protected function updateYiiVersion($version)
    {
        $this->sed(
            '/function getVersion\(\)\n    \{\n        return \'(.+?)\';/',
            "function getVersion()\n    {\n        return '$version';",
            YII2_PATH . '/BaseYii.php');
    }

    protected function sed($pattern, $replace, $files)
    {
        foreach((array) $files as $file) {
            file_put_contents($file, preg_replace($pattern, $replace, file_get_contents($file)));
        }
    }
}
