<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\build\controllers;

use Yii;
use yii\base\Exception;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

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
     * ./build/build release/prepare framework 2.0.0-beta
     * ./build/build release/prepare redis 2.0.0-beta
     * ```
     *
     */
    public function actionPrepare(array $what, $version)
    {
        $this->resortChangelogs($what, $version);
        $this->closeChangelogs($what, $version);
        $this->composerSetStability($what, $version);
        if (in_array('framework', $what)) {
            $this->updateYiiVersion($version);
        }
    }

    /**
     * Usage:
     *
     * ```
     * ./build/build release/done framework 2.0.0-dev 2.0.0-rc
     * ./build/build release/done redis 2.0.0-dev 2.0.0-rc
     * ```
     */
    public function actionDone(array $what, $devVersion, $nextVersion)
    {
        $this->openChangelogs($what, $nextVersion);
        $this->composerSetStability($what, 'dev');
        if (in_array('framework', $what)) {
            $this->updateYiiVersion($devVersion);
        }
    }

    protected function closeChangelogs($what, $version)
    {
        $v = str_replace('\\-', '[\\- ]', preg_quote($version, '/'));
        $headline = $version . ' ' . date('F d, Y');
        $this->sed(
            '/'.$v.' under development\n(-+?)\n/',
            $headline . "\n" . str_repeat('-', strlen($headline)) . "\n",
            $this->getChangelogs($what)
        );
    }

    protected function openChangelogs($what, $version)
    {
        $headline = "\n$version under development\n";
        $headline .= str_repeat('-', strlen($headline) - 2) . "\n\n";
        foreach($this->getChangelogs($what) as $file) {
            $lines = explode("\n", file_get_contents($file));
            $hl = [
                array_shift($lines),
                array_shift($lines),
            ];
            array_unshift($lines, $headline);

            file_put_contents($file, implode("\n", array_merge($hl, $lines)));
        }
    }

    protected function resortChangelogs($what, $version)
    {
        foreach($this->getChangelogs($what) as $file) {
            // split the file into relevant parts
            list($start, $changelog, $end) = $this->splitChangelog($file, $version);
            $changelog = $this->resortChangelog($changelog);
            file_put_contents($file, implode("\n", array_merge($start, $changelog, $end)));
        }
    }

    /**
     * Extract changelog content for a specific version
     */
    protected function splitChangelog($file, $version)
    {
        $lines = explode("\n", file_get_contents($file));

        // split the file into relevant parts
        $start = [];
        $changelog = [];
        $end = [];

        $state = 'start';
        foreach($lines as $l => $line) {
            // starting from the changelogs headline
            if (isset($lines[$l-2]) && strpos($lines[$l-2], $version) !== false &&
                isset($lines[$l-1]) && strncmp($lines[$l-1], '---', 3) === 0) {
                $state = 'changelog';
            }
            if ($state === 'changelog' && isset($lines[$l+1]) && strncmp($lines[$l+1], '---', 3) === 0) {
                $state = 'end';
            }
            ${$state}[] = $line;
        }
        return [$start, $changelog, $end];
    }

    /**
     * Ensure sorting of the changelog lines
     */
    protected function resortChangelog($changelog)
    {
        // cleanup whitespace
        foreach($changelog as $i => $line) {
            $changelog[$i] = rtrim($line);
        }
        $changelog = array_filter($changelog);

        $i = 0;
        ArrayHelper::multisort($changelog, function($line) use (&$i) {
            if (preg_match('/^- (Chg|Enh|Bug)( #\d+(, #\d+)*)?: .+$/', $line, $m)) {
                $o = ['Bug' => 'C', 'Enh' => 'D', 'Chg' => 'E'];
                return $o[$m[1]] . ' ' . (!empty($m[2]) ? $m[2] : 'AAAA' . $i++);
            }
            return 'B' . $i++;
        }, SORT_ASC, SORT_NATURAL);

        // re-add leading and trailing lines
        array_unshift($changelog, '');
        $changelog[] = '';
        $changelog[] = '';

        return $changelog;
    }

    protected function getChangelogs($what)
    {
        $changelogs = [];
        if (in_array('framework', $what)) {
            $changelogs[] = $this->getFrameworkChangelog();
        }

        return array_merge($changelogs, $this->getExtensionChangelogs($what));
    }

    protected function getFrameworkChangelog()
    {
        return YII2_PATH . '/CHANGELOG.md';
    }

    protected function getExtensionChangelogs($what)
    {
        return array_filter(glob(dirname(YII2_PATH) . '/extensions/*/CHANGELOG.md'), function($elem) use ($what) {
            foreach($what as $ext) {
                if (strpos($elem, "extensions/$ext/CHANGELOG.md") !== false) {
                    return true;
                }
            }
            return false;
        });
    }

    protected function composerSetStability($what, $version)
    {
        $apps = [];
        if (in_array('app-advanced', $what)) {
            $apps[] = dirname(YII2_PATH) . '/apps/advanced/composer.json';
        }
        if (in_array('app-basic', $what)) {
            $apps[] = dirname(YII2_PATH) . '/apps/basic/composer.json';
        }
        if (in_array('app-benchmark', $what)) {
            $apps[] = dirname(YII2_PATH) . '/apps/benchmark/composer.json';
        }
        if (empty($apps)) {
            return;
        }

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
            $apps
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
