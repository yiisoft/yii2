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
    public $defaultAction = 'release';

    /**
     * @var string base path to use for releases.
     */
    public $basePath;
    /**
     * @var bool whether to make actual changes. If true, it will run without changing or pushing anything.
     */
    public $dryRun = false;
    /**
     * @var bool whether to fetch latest tags.
     */
    public $update = false;


    public function options($actionID)
    {
        $options = ['basePath'];
        if ($actionID === 'release') {
            $options[] = 'dryRun';
        } elseif ($actionID === 'info') {
            $options[] = 'update';
        }
        return array_merge(parent::options($actionID), $options);
    }


    public function beforeAction($action)
    {
        if (!$this->interactive) {
            throw new Exception('Sorry, but releases should be run interactively to ensure you actually verify what you are doing ;)');
        }
        if ($this->basePath === null) {
            $this->basePath = dirname(dirname(__DIR__));
        }
        $this->basePath = rtrim($this->basePath, '\\/');
        return parent::beforeAction($action);
    }

    /**
     * Shows information about current framework and extension versions.
     */
    public function actionInfo()
    {
        $extensions = [
            'framework',
        ];
        $extensionPath = "{$this->basePath}/extensions";
        foreach (scandir($extensionPath) as $extension) {
            if (ctype_alpha($extension) && is_dir($extensionPath . '/' . $extension)) {
                $extensions[] = $extension;
            }
        }

        if ($this->update) {
            foreach($extensions as $extension) {
                if ($extension === 'framework') {
                    continue;
                }
                $this->stdout("fetching tags for $extension...");
                $this->gitFetchTags("{$this->basePath}/extensions/$extension");
                $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);
            }
        } else {
            $this->stdout("\nInformation may be outdated, re-run with `--update` to fetch latest tags.\n\n");
        }

        $versions = $this->getCurrentVersions($extensions);
        $nextVersions = $this->getNextVersions($versions, self::PATCH);

        // print version table
        $w = $this->minWidth(array_keys($versions));
        $this->stdout(str_repeat(' ', $w + 2) . "Current Version  Next Version\n", Console::BOLD);
        foreach($versions as $ext => $version) {
            $this->stdout($ext . str_repeat(' ', $w + 3 - mb_strlen($ext)) . $version . "");
            $this->stdout(str_repeat(' ', 17 - mb_strlen($version)) . $nextVersions[$ext] . "\n");
        }

    }

    private function minWidth($a)
    {
        $w = 1;
        foreach($a as $s) {
            if (($l = mb_strlen($s)) > $w) {
                $w = $l;
            }
        }
        return $w;
    }

    /**
     * Automation tool for making Yii framework and official extension releases.
     *
     * Usage:
     *
     * To make a release, make sure your git is clean (no uncommitted changes) and run the following command in
     * the yii dev repo root:
     *
     * ```
     * ./build/build release framework
     * ```
     *
     * or
     *
     * ```
     * ./build/build release redis,bootstrap,apidoc
     * ```
     *
     * You may use the `--dryRun` switch to test the command without changing or pushing anything:
     *
     * ```
     * ./build/build release redis --dryRun
     * ```
     *
     * The command will guide you through the complete release process including changing of files,
     * committing and pushing them. Each git command must be confirmed and can be skipped individually.
     * You may adjust changes in a separate shell or your IDE while the command is waiting for confirmation.
     *
     * @param array $what what do you want to release? this can either be an extension name such as `redis` or `bootstrap`,
     * or `framework` if you want to release a new version of the framework itself.
     * @return int
     */
    public function actionRelease(array $what)
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

        $this->stdout("Before you make a release briefly go over the changes and check if you spot obvious mistakes:\n\n", Console::BOLD);
        $this->stdout("- no accidentally added CHANGELOG lines for other versions than this one?\n");
        $this->stdout("- are all new `@since` tags for this relase version?\n");
        $travisUrl = reset($what) === 'framework' ? '' : '-'.reset($what);
        $this->stdout("- are unit tests passing on travis? https://travis-ci.org/yiisoft/yii2$travisUrl/builds\n");
        $this->stdout("- other issues with code changes?\n");
        $this->stdout("- also make sure the milestone on github is complete and no issues or PRs are left open.\n\n");
        $this->printWhatUrls($what, $versions);
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

    protected function printWhat(array $what, $newVersions, $versions)
    {
        foreach($what as $ext) {
            if ($ext === 'framework') {
                $this->stdout(" - Yii Framework version ");
            } else {
                $this->stdout(" - ");
                $this->stdout($ext, Console::FG_RED);
                $this->stdout(" extension version ");
            }
            $this->stdout($newVersions[$ext], Console::BOLD);
            $this->stdout(", last release was {$versions[$ext]}\n");
        }
    }

    protected function printWhatUrls(array $what, $oldVersions)
    {
        foreach($what as $ext) {
            if ($ext === 'framework') {
                $this->stdout("framework:    https://github.com/yiisoft/yii2-framework/compare/{$oldVersions[$ext]}...master\n");
                $this->stdout("app-basic:    https://github.com/yiisoft/yii2-app-basic/compare/{$oldVersions[$ext]}...master\n");
                $this->stdout("app-advanced: https://github.com/yiisoft/yii2-app-advanced/compare/{$oldVersions[$ext]}...master\n");
            } else {
                $this->stdout($ext, Console::FG_RED);
                $this->stdout(": https://github.com/yiisoft/yii2-$ext/compare/{$oldVersions[$ext]}...master\n");
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


    protected function releaseFramework($frameworkPath, $version)
    {
        throw new Exception('NOT IMPLEMENTED COMPLETELY YET');

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


        // if done:
        //     * ./build/build release/done framework 2.0.0-dev 2.0.0-rc
        //     * ./build/build release/done redis 2.0.0-dev 2.0.0-rc
//            $this->openChangelogs($what, $nextVersion);
//            $this->composerSetStability($what, 'dev');
//            if (in_array('framework', $what)) {
//                $this->updateYiiVersion($devVersion);
//            }

    }

    protected function releaseExtension($name, $path, $version)
    {
        $this->stdout("\n");
        $this->stdout($h = "Preparing release for extension  $name  version $version", Console::BOLD);
        $this->stdout("\n" . str_repeat('-', strlen($h)) . "\n\n", Console::BOLD);

        $this->runGit('git checkout master', $path); // TODO add compatibility for other release branches
        $this->runGit('git pull', $path); // TODO add compatibility for other release branches

        // adjustments

        $this->stdout("fixing various PHPdoc style issues...\n", Console::BOLD);
        $this->dryRun || Yii::$app->runAction('php-doc/fix', [$path]);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $this->stdout("updating PHPdoc @property annotations...\n", Console::BOLD);
        $this->dryRun || Yii::$app->runAction('php-doc/property', [$path]);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $this->stdout('sorting changelogs...', Console::BOLD);
        $this->dryRun || $this->resortChangelogs([$name], $version);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $this->stdout('closing changelogs...', Console::BOLD);
        $this->dryRun || $this->closeChangelogs([$name], $version);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $this->stdout("\nIn the following you can check the above changes using git diff.\n\n");
        do {
            $this->runGit("git diff --color", $path);
            $this->stdout("\n\n\nCheck whether the above diff is okay, if not you may change things as needed before continuing.\n");
            $this->stdout("You may abort the program with Ctrl + C and reset the changes by running `git checkout -- .` in the repo.\n\n");
        } while(!$this->confirm("Type `yes` to continue, `no` to view git diff again. Continue?"));

        $this->stdout("\n\n");
        $this->stdout("    ****          RELEASE TIME!         ****\n", Console::FG_YELLOW, Console::BOLD);
        $this->stdout("    ****    Commit, Tag and Push it!    ****\n", Console::FG_YELLOW, Console::BOLD);
        $this->stdout("\n\nHint: if you decide 'no' for any of the following, the command will not be executed. You may manually run them later if needed. E.g. try the release locally without pushing it.\n\n");

        $this->runGit("git commit -a -m \"release version $version\"", $path);
        $this->runGit("git tag -a $version -m\"version $version\"", $path);
        $this->runGit("git push origin master", $path);
        $this->runGit("git push --tags", $path);

        $this->stdout("\n\n");
        $this->stdout("CONGRATULATIONS! You have just released extension ", Console::FG_YELLOW, Console::BOLD);
        $this->stdout($name, Console::FG_RED, Console::BOLD);
        $this->stdout(" version ", Console::FG_YELLOW, Console::BOLD);
        $this->stdout($version, Console::BOLD);
        $this->stdout("!\n\n", Console::FG_YELLOW, Console::BOLD);

        // prepare next release

        $this->stdout("Time to prepare the next release...\n\n", Console::FG_YELLOW, Console::BOLD);

        $this->stdout('opening changelogs...', Console::BOLD);
        $nextVersion = $this->getNextVersions([$name => $version], self::PATCH); // TODO support other versions
        $this->dryRun || $this->openChangelogs([$name], $nextVersion[$name]);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $this->stdout("\n");
        $this->runGit("git diff --color", $path);
        $this->stdout("\n\n");
        $this->runGit("git commit -a -m \"prepare for next release\"", $path);
        $this->runGit("git push origin master", $path);

        $this->stdout("\n\nDONE!", Console::FG_YELLOW, Console::BOLD);

        $this->stdout("\n\nThe following steps are left for you to do manually:\n\n");
        $nextVersion2 = $this->getNextVersions($nextVersion, self::PATCH); // TODO support other versions
        $this->stdout("- close the $version milestone on github and open new ones for {$nextVersion[$name]} and {$nextVersion2[$name]}: https://github.com/yiisoft/yii2-$name/milestones\n");
        $this->stdout("- release news and announcement.\n");
        $this->stdout("- update the website (will be automated soon and is only relevant for the new website).\n");

        $this->stdout("\n");
    }


    protected function runGit($cmd, $path)
    {
        if ($this->confirm("Run `$cmd`?", true)) {
            if ($this->dryRun) {
                $this->stdout("dry run, command `$cmd` not executed.\n");
                return;
            }
            chdir($path);
            exec($cmd, $output, $ret);
            echo implode("\n", $output);
            if ($ret != 0) {
                throw new Exception("Command \"$cmd\" failed with code " . $ret);
            }
            echo "\n";
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

    protected function gitFetchTags($path)
    {
        chdir($path);
        exec('git fetch --tags', $output, $ret);
        if ($ret != 0) {
            throw new Exception('Command "git fetch --tags" failed with code ' . $ret);
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
        $headline .= str_repeat('-', strlen($headline) - 2) . "\n\n- no changes in this release.\n";
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
            $tags = [];
            exec('git tag', $tags, $ret);
            if ($ret != 0) {
                throw new Exception('Command "git tag" failed with code ' . $ret);
            }
            rsort($tags, SORT_NATURAL); // TODO this can not deal with alpha/beta/rc...
            $versions[$ext] = reset($tags);
        }
        return $versions;
    }

    const MINOR = 'minor';
    const PATCH = 'patch';

    protected function getNextVersions(array $versions, $type)
    {
        foreach($versions as $k => $v) {
            if (empty($v)) {
                $versions[$k] = '2.0.0';
                continue;
            }
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
