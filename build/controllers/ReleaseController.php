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
use yii\helpers\Console;

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
     * @var string base path to use for releases.
     */
    public $basePath;
    /**
     * @var bool whether to do actual changes. If true, it will run without changing or pushing anything.
     */
    public $dryRun = false;


    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['basePath', 'dryRun']);
    }


    public function beforeAction($action)
    {
        if (!$this->interactive) {
            throw new Exception('Sorry, but releases should be run interactive to ensure you actually verify what you are doing ;)');
        }
        if ($this->basePath === null) {
            $this->basePath = dirname(dirname(__DIR__));
        }
        $this->basePath = rtrim($this->basePath, '\\/');
        return parent::beforeAction($action);
    }

    /**
     * Usage:
     *
     * ```
     * ./build/build release/prepare framework
     * ./build/build release/prepare redis,bootstrap,apidoc
     * ```
     *
     */
    public function actionPrepare(array $what)
    {
        if (count($what) > 1) {
            $this->stdout("Currently only one simultaneous release is supported.\n");
            return 1;
        }

        $this->stdout("This is the Yii release manager\n\n", Console::BOLD);

        if ($this->dryRun) {
            $this->stdout("Running in \"dry-run\" mode, nothing will actually be changed.\n\n", Console::BOLD, Console::FG_GREEN);
        }

        $this->validateWhat($what);
        $versions = $this->getCurrentVersions($what);
        $newVersions = $this->getNextVersions($versions, self::PATCH);// TODO add support for minor

        $this->stdout("You are about to prepare a new release for the following things:\n\n");
        $this->printWhat($what, $newVersions, $versions);
        $this->stdout("\n");

        if (!$this->confirm('When you continue, this tool will run cleanup jobs and update the changelog as well as other files (locally). Continue?', false)) {
            $this->stdout("Canceled.\n");
            return 1;
        }

        foreach($what as $ext) {
            if ($ext === 'framework') {
                $this->releaseFramework("{$this->basePath}/framework", $newVersions['framework']);
            } else {
                $this->releaseExtension($ext, "{$this->basePath}/extensions/$ext", $newVersions[$ext]);
            }
        }

        return 0;
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

    protected function printWhat(array $what, $newVersions, $versions)
    {
        foreach($what as $w) {
            if ($w === 'framework') {
                $this->stdout(" - Yii Framework version {$newVersions[$w]}, current latest release is {$versions[$w]}\n");
            } else {
                $this->stdout(" - $w extension version {$newVersions[$w]}, current latest release is {$versions[$w]}\n");
            }
        }
    }

    protected function validateWhat(array $what)
    {
        foreach($what as $w) {
            if ($w === 'framework') {
                if (!is_dir($fwPath = "{$this->basePath}/framework")) {
                    throw new Exception("Framework path does not exist: \"{$this->basePath}/framework\"\n");
                }
                $this->ensureGitClean($fwPath);
            } else {
                if (!is_dir($extPath = "{$this->basePath}/extensions/$w")) {
                    throw new Exception("Extension path for \"$w\" does not exist: \"{$this->basePath}/extensions/$w\"\n");
                }
                $this->ensureGitClean($extPath);
            }
        }
    }

    protected function ensureGitClean($path)
    {
        chdir($path);
        exec('git status --porcelain -uno', $changes, $ret);
        if ($ret != 0) {
            throw new Exception('Command "git status --porcelain -uno" failed with code ' . $ret);
        }
        if (!empty($changes)) {
            throw new Exception("You have uncommitted changes in $path: " . print_r($changes, true));
        }
    }


    protected function releaseFramework($frameworkPath, $version)
    {
        $this->stdout("\n");
        $this->stdout($h = "Preparing framework release version $version", Console::BOLD);
        $this->stdout("\n" . str_repeat('-', strlen($h)) . "\n\n", Console::BOLD);

        if ($this->confirm('Run `git checkout master`?', true)) {
            chdir($frameworkPath);
            exec('git checkout master', $output, $ret); // TODO add compatibility for other release branches
            if ($ret != 0) {
                throw new Exception('Command "git checkout master" failed with code ' . $ret);
            }
        }

        if ($this->confirm('Run `git pull`?', true)) {
            chdir($frameworkPath);
            exec('git pull', $output, $ret);
            if ($ret != 0) {
                throw new Exception('Command "git pull" failed with code ' . $ret);
            }
        }

        // checks

        $this->stdout('check if framework composer.json matches yii2-dev composer.json...');
        $this->checkComposer($frameworkPath);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        // adjustments


        $this->stdout('prepare classmap...');
        $this->dryRun || Yii::$app->runAction('classmap', [$frameworkPath]);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $this->stdout('fixing various PHPdoc style issues...');
        $this->dryRun || Yii::$app->runAction('php-doc/fix', [$frameworkPath]);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $this->stdout('updating PHPdoc @property annotations...');
        $this->dryRun || Yii::$app->runAction('php-doc/property', [$frameworkPath]);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $this->stdout('updating mimetype magic file...');
        $this->dryRun || Yii::$app->runAction('mime-type', ["$frameworkPath/helpers/mimeTypes.php"]);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $this->stdout('sorting changelogs...');
        $this->dryRun || $this->resortChangelogs(['framework'], $version);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $this->stdout('closing changelogs...');
        $this->dryRun || $this->closeChangelogs(['framework'], $version);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $this->stdout('updating Yii version...');
        $this->dryRun || $this->updateYiiVersion($frameworkPath, $version);;
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        // TODO Commit and push

        // TODO tag and push

        // TODO release applications
        // $this->composerSetStability($what, $version);


//        $this->resortChangelogs($what, $version);
  //        $this->closeChangelogs($what, $version);
  //        $this->composerSetStability($what, $version);
  //        if (in_array('framework', $what)) {
  //            $this->updateYiiVersion($version);
  //        }
    }

    protected function releaseExtension($name, $path, $version)
    {
        $this->stdout("\n");
        $this->stdout($h = "Preparing release for extension  $name  version $version", Console::BOLD);
        $this->stdout("\n" . str_repeat('-', strlen($h)) . "\n\n", Console::BOLD);

        if ($this->confirm('Run `git checkout master`?', true)) {
            chdir($path);
            exec('git checkout master', $output, $ret); // TODO add compatibility for other release branches
            if ($ret != 0) {
                throw new Exception('Command "git checkout master" failed with code ' . $ret);
            }
        }

        if ($this->confirm('Run `git pull`?', true)) {
            chdir($path);
            exec('git pull', $output, $ret);
            if ($ret != 0) {
                throw new Exception('Command "git pull" failed with code ' . $ret);
            }
        }

        // adjustments

        $this->stdout('fixing various PHPdoc style issues...');
        $this->dryRun || Yii::$app->runAction('php-doc/fix', [$path]);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $this->stdout('updating PHPdoc @property annotations...');
        $this->dryRun || Yii::$app->runAction('php-doc/property', [$path]);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $this->stdout('sorting changelogs...');
        $this->dryRun || $this->resortChangelogs([$name], $version);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $this->stdout('closing changelogs...');
        $this->dryRun || $this->closeChangelogs([$name], $version);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        do {
            $this->runGit("git diff", $path);
        } while(!$this->confirm('continue?'));

        $this->runGit("git commit -a -m \"release version $version\"", $path);
        $this->runGit("git push", $path);
        $this->runGit("git tag -a $version -m\"version $version\"", $path);
        $this->runGit("git push --tags", $path);



    }


    protected function runGit($cmd, $path)
    {
        if ($this->confirm("Run `$cmd`?", true)) {
            chdir($path);
            exec($cmd, $output, $ret);
            echo implode("\n", $output);
            if ($ret != 0) {
                throw new Exception("Command \"$cmd\" failed with code " . $ret);
            }
        }
    }

    protected function checkComposer($fwPath)
    {
        if (!$this->confirm("\nNot yet automated: Please check if composer.json dependencies in framework dir match the on in repo root. Continue?", false)) {
            exit;
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
            if (preg_match('/^- (Chg|Enh|Bug|New)( #\d+(, #\d+)*)?: .+$/', $line, $m)) {
                $o = ['Bug' => 'C', 'Enh' => 'D', 'Chg' => 'E', 'New' => 'F'];
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
        return $this->basePath . '/framework/CHANGELOG.md';
    }

    protected function getExtensionChangelogs($what)
    {
        return array_filter(glob($this->basePath . '/extensions/*/CHANGELOG.md'), function($elem) use ($what) {
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
            $apps[] = $this->basePath . '/apps/advanced/composer.json';
        }
        if (in_array('app-basic', $what)) {
            $apps[] = $this->basePath . '/apps/basic/composer.json';
        }
        if (in_array('app-benchmark', $what)) {
            $apps[] = $this->basePath . '/apps/benchmark/composer.json';
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

    protected function updateYiiVersion($frameworkPath, $version)
    {
        $this->sed(
            '/function getVersion\(\)\n    \{\n        return \'(.+?)\';/',
            "function getVersion()\n    {\n        return '$version';",
            $frameworkPath . '/BaseYii.php');
    }

    protected function sed($pattern, $replace, $files)
    {
        foreach((array) $files as $file) {
            file_put_contents($file, preg_replace($pattern, $replace, file_get_contents($file)));
        }
    }

    protected function getCurrentVersions(array $what)
    {
        $versions = [];
        foreach($what as $ext) {
            if ($ext === 'framework') {
                chdir("{$this->basePath}/framework");
            } else {
                chdir("{$this->basePath}/extensions/$ext");
            }
            exec('git tag', $tags, $ret);
            if ($ret != 0) {
                throw new Exception('Command "git tag" failed with code ' . $ret);
            }
            rsort($tags, SORT_NATURAL); // TODO this can not deal with alpha/beta/rc...
            $versions[$ext] = reset($tags);
        }
        print_r($versions);
        return $versions;
    }

    const MINOR = 'minor';
    const PATCH = 'patch';

    protected function getNextVersions(array $versions, $type)
    {
        foreach($versions as $k => $v) {
            $parts = explode('.', $v);
            switch($type) {
                case self::MINOR:
                    $parts[1]++;
                    break;
                case self::PATCH:
                    $parts[2]++;
                    break;
                default:
                    throw new Exception('Unknown version type.');
            }
            $versions[$k] = implode('.', $parts);
        }
        return $versions;
    }
}
