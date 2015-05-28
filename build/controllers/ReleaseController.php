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
        $this->resortChangelogs($version);
        $this->mergeChangelogs($version);
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

    protected function resortChangelogs($version)
    {
        foreach($this->getChangelogs() as $file) {
            // split the file into relevant parts
            list($start, $changelog, $end) = $this->splitChangelog($file, $version);
            $changelog = $this->resortChangelog($changelog);
            file_put_contents($file, implode("\n", array_merge($start, $changelog, $end)));
        }
    }

    protected function mergeChangelogs($version)
    {
        $file = $this->getFrameworkChangelog();
        // split the file into relevant parts
        list($start, $changelog, $end) = $this->splitChangelog($file, $version);

        $changelog = $this->resortChangelog($changelog);

        $changelog[] = '';
        $extensions = $this->getExtensionChangelogs();
        asort($extensions);
        foreach($extensions as $changelogFile) {
            if (!preg_match('~extensions/([a-z]+)/CHANGELOG\\.md~', $changelogFile, $m)) {
                throw new Exception("Illegal extension changelog file: " . $changelogFile);
            }
            list( , $extensionChangelog, ) = $this->splitChangelog($changelogFile, $version);
            $name = $m[1];
            $ucname = ucfirst($name);
            $changelog[] = "### $ucname Extension (yii2-$name)";
            $changelog = array_merge($changelog, $extensionChangelog);
        }

        file_put_contents($file, implode("\n", array_merge($start, $changelog, $end)));
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

        // TODO sorting
        return $changelog;
    }

    protected function getChangelogs()
    {
        return array_merge([$this->getFrameworkChangelog()], $this->getExtensionChangelogs());
    }

    protected function getFrameworkChangelog()
    {
        return YII2_PATH . '/CHANGELOG.md';
    }

    protected function getExtensionChangelogs()
    {
        return glob(dirname(YII2_PATH) . '/extensions/*/CHANGELOG.md');
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
