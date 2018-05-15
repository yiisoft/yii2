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
use yii\helpers\FileHelper;

/**
 * ReleaseController is there to help preparing releases.
 *
 * Get a version overview:
 *
 *     ./build release/info
 *
 * run it with `--update` to fetch tags for all repos:
 *
 *     ./build release/info --update
 *
 * Make a framework release (apps are always in line with framework):
 *
 *     ./build release framework
 *     ./build release app-basic
 *     ./build release app-advanced
 *
 * Make an extension release (e.g. for redis):
 *
 *     ./build release redis
 *
 * Be sure to check the help info for individual sub-commands:
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
    /**
     * @var string override the default version. e.g. for major or patch releases.
     */
    public $version;


    public function options($actionID)
    {
        $options = ['basePath'];
        if ($actionID === 'release') {
            $options[] = 'dryRun';
            $options[] = 'version';
        } elseif ($actionID === 'sort-changelog') {
            $options[] = 'version';
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
            $this->basePath = \dirname(\dirname(__DIR__));
        }
        $this->basePath = rtrim($this->basePath, '\\/');
        return parent::beforeAction($action);
    }

    /**
     * Shows information about current framework and extension versions.
     */
    public function actionInfo()
    {
        $items = [
            'framework',
            'app-basic',
            'app-advanced',
        ];
        $extensionPath = "{$this->basePath}/extensions";
        foreach (scandir($extensionPath) as $extension) {
            if (ctype_alpha($extension) && is_dir($extensionPath . '/' . $extension)) {
                $items[] = $extension;
            }
        }

        if ($this->update) {
            foreach ($items as $item) {
                $this->stdout("fetching tags for $item...");
                if ($item === 'framework') {
                    $this->gitFetchTags((string)$this->basePath);
                } elseif (strncmp('app-', $item, 4) === 0) {
                    $this->gitFetchTags("{$this->basePath}/apps/" . substr($item, 4));
                } else {
                    $this->gitFetchTags("{$this->basePath}/extensions/$item");
                }
                $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);
            }
        } else {
            $this->stdout("\nInformation may be outdated, re-run with `--update` to fetch latest tags.\n\n");
        }

        $versions = $this->getCurrentVersions($items);
        $nextVersions = $this->getNextVersions($versions, self::PATCH);

        // print version table
        $w = $this->minWidth(array_keys($versions));
        $this->stdout(str_repeat(' ', $w + 2) . "Current Version  Next Version\n", Console::BOLD);
        foreach ($versions as $ext => $version) {
            $this->stdout($ext . str_repeat(' ', $w + 3 - mb_strlen($ext)) . $version . '');
            $this->stdout(str_repeat(' ', 17 - mb_strlen($version)) . $nextVersions[$ext] . "\n");
        }
    }

    private function minWidth($a)
    {
        $w = 1;
        foreach ($a as $s) {
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
     * @param array $what what do you want to release? this can either be:
     *
     * - an extension name such as `redis` or `bootstrap`,
     * - an application indicated by prefix `app-`, e.g. `app-basic`,
     * - or `framework` if you want to release a new version of the framework itself.
     *
     * @return int
     */
    public function actionRelease(array $what)
    {
        if (\count($what) > 1) {
            $this->stdout("Currently only one simultaneous release is supported.\n");
            return 1;
        }

        $this->stdout("This is the Yii release manager\n\n", Console::BOLD);

        if ($this->dryRun) {
            $this->stdout("Running in \"dry-run\" mode, nothing will actually be changed.\n\n", Console::BOLD, Console::FG_GREEN);
        }

        $this->validateWhat($what);
        $versions = $this->getCurrentVersions($what);

        if ($this->version !== null) {
            // if a version is explicitly given
            $newVersions = [];
            foreach ($versions as $k => $v) {
                $newVersions[$k] = $this->version;
            }
        } else {
            // otherwise get next patch or minor
            $newVersions = $this->getNextVersions($versions, self::PATCH);
        }

        $this->stdout("You are about to prepare a new release for the following things:\n\n");
        $this->printWhat($what, $newVersions, $versions);
        $this->stdout("\n");

        $this->stdout("Before you make a release briefly go over the changes and check if you spot obvious mistakes:\n\n", Console::BOLD);
        $gitDir = reset($what) === 'framework' ? 'framework/' : '';
        $gitVersion = $versions[reset($what)];
        if (strncmp('app-', reset($what), 4) !== 0) {
            $this->stdout("- no accidentally added CHANGELOG lines for other versions than this one?\n\n    git diff $gitVersion.. ${gitDir}CHANGELOG.md\n\n");
            $this->stdout("- are all new `@since` tags for this release version?\n");
        }
        $this->stdout("- other issues with code changes?\n\n    git diff -w $gitVersion.. ${gitDir}\n\n");
        $travisUrl = reset($what) === 'framework' ? '' : '-' . reset($what);
        $this->stdout("- are unit tests passing on travis? https://travis-ci.org/yiisoft/yii2$travisUrl/builds\n");
        $this->stdout("- also make sure the milestone on github is complete and no issues or PRs are left open.\n\n");
        $this->printWhatUrls($what, $versions);
        $this->stdout("\n");

        if (!$this->confirm('When you continue, this tool will run cleanup jobs and update the changelog as well as other files (locally). Continue?', false)) {
            $this->stdout("Canceled.\n");
            return 1;
        }

        foreach ($what as $ext) {
            if ($ext === 'framework') {
                $this->releaseFramework("{$this->basePath}/framework", $newVersions['framework']);
            } elseif (strncmp('app-', $ext, 4) === 0) {
                $this->releaseApplication(substr($ext, 4), "{$this->basePath}/apps/" . substr($ext, 4), $newVersions[$ext]);
            } else {
                $this->releaseExtension($ext, "{$this->basePath}/extensions/$ext", $newVersions[$ext]);
            }
        }

        return 0;
    }

    /**
     * This will generate application packages for download page.
     *
     * Usage:
     *
     * ```
     * ./build/build release/package app-basic
     * ```
     *
     * @param array $what what do you want to package? this can either be:
     *
     * - an application indicated by prefix `app-`, e.g. `app-basic`,
     *
     * @return int
     */
    public function actionPackage(array $what)
    {
        $this->validateWhat($what, ['app']);
        $versions = $this->getCurrentVersions($what);

        $this->stdout("You are about to generate packages for the following things:\n\n");
        foreach ($what as $ext) {
            if (strncmp('app-', $ext, 4) === 0) {
                $this->stdout(' - ');
                $this->stdout(substr($ext, 4), Console::FG_RED);
                $this->stdout(' application version ');
            } elseif ($ext === 'framework') {
                $this->stdout(' - Yii Framework version ');
            } else {
                $this->stdout(' - ');
                $this->stdout($ext, Console::FG_RED);
                $this->stdout(' extension version ');
            }
            $this->stdout($versions[$ext], Console::BOLD);
            $this->stdout("\n");
        }
        $this->stdout("\n");

        $packagePath = "{$this->basePath}/packages";
        $this->stdout("Packages will be stored in $packagePath\n\n");

        if (!$this->confirm('Continue?', false)) {
            $this->stdout("Canceled.\n");
            return 1;
        }

        foreach ($what as $ext) {
            if ($ext === 'framework') {
                throw new Exception('Can not package framework.');
            } elseif (strncmp('app-', $ext, 4) === 0) {
                $this->packageApplication(substr($ext, 4), $versions[$ext], $packagePath);
            } else {
                throw new Exception('Can not package extension.');
            }
        }

        $this->stdout("\ndone. verify the versions composer installed above and push it to github!\n\n");

        return 0;
    }

    /**
     * Sorts CHANGELOG for framework or extension.
     *
     * @param array $what what do you want to resort changelog for? this can either be:
     *
     * - an extension name such as `redis` or `bootstrap`,
     * - or `framework` if you want to release a new version of the framework itself.
     */
    public function actionSortChangelog(array $what)
    {
        if (\count($what) > 1) {
            $this->stdout("Currently only one simultaneous release is supported.\n");
            return 1;
        }
        $this->validateWhat($what, ['framework', 'ext'], false);

        $version = $this->version ?: array_values($this->getNextVersions($this->getCurrentVersions($what), self::PATCH))[0];
        $this->stdout('sorting CHANGELOG of ');
        $this->stdout(reset($what), Console::BOLD);
        $this->stdout(' for version ');
        $this->stdout($version, Console::BOLD);
        $this->stdout('...');

        $this->resortChangelogs($what, $version);

        $this->stdout("done.\n", Console::BOLD, Console::FG_GREEN);
    }

    protected function printWhat(array $what, $newVersions, $versions)
    {
        foreach ($what as $ext) {
            if (strncmp('app-', $ext, 4) === 0) {
                $this->stdout(' - ');
                $this->stdout(substr($ext, 4), Console::FG_RED);
                $this->stdout(' application version ');
            } elseif ($ext === 'framework') {
                $this->stdout(' - Yii Framework version ');
            } else {
                $this->stdout(' - ');
                $this->stdout($ext, Console::FG_RED);
                $this->stdout(' extension version ');
            }
            $this->stdout($newVersions[$ext], Console::BOLD);
            $this->stdout(", last release was {$versions[$ext]}\n");
        }
    }

    protected function printWhatUrls(array $what, $oldVersions)
    {
        foreach ($what as $ext) {
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

    /**
     * @param array $what list of items
     * @param array $limit list of things to allow, or empty to allow any, can be `app`, `framework`, `extension`
     * @param bool $ensureGitClean
     * @throws \yii\base\Exception
     */
    protected function validateWhat(array $what, $limit = [], $ensureGitClean = true)
    {
        foreach ($what as $w) {
            if (strncmp('app-', $w, 4) === 0) {
                if (!empty($limit) && !\in_array('app', $limit)) {
                    throw new Exception('Only the following types are allowed: ' . implode(', ', $limit) . "\n");
                }
                if (!is_dir($appPath = "{$this->basePath}/apps/" . substr($w, 4))) {
                    throw new Exception("Application path does not exist: \"{$appPath}\"\n");
                }
                if ($ensureGitClean) {
                    $this->ensureGitClean($appPath);
                }
            } elseif ($w === 'framework') {
                if (!empty($limit) && !\in_array('framework', $limit)) {
                    throw new Exception('Only the following types are allowed: ' . implode(', ', $limit) . "\n");
                }
                if (!is_dir($fwPath = "{$this->basePath}/framework")) {
                    throw new Exception("Framework path does not exist: \"{$this->basePath}/framework\"\n");
                }
                if ($ensureGitClean) {
                    $this->ensureGitClean($fwPath);
                }
            } else {
                if (!empty($limit) && !\in_array('ext', $limit)) {
                    throw new Exception('Only the following types are allowed: ' . implode(', ', $limit) . "\n");
                }
                if (!is_dir($extPath = "{$this->basePath}/extensions/$w")) {
                    throw new Exception("Extension path for \"$w\" does not exist: \"{$this->basePath}/extensions/$w\"\n");
                }
                if ($ensureGitClean) {
                    $this->ensureGitClean($extPath);
                }
            }
        }
    }


    protected function releaseFramework($frameworkPath, $version)
    {
        $this->stdout("\n");
        $this->stdout($h = "Preparing framework release version $version", Console::BOLD);
        $this->stdout("\n" . str_repeat('-', \strlen($h)) . "\n\n", Console::BOLD);

        if (!$this->confirm('Make sure you are on the right branch for this release and that it tracks the correct remote branch! Continue?')) {
            exit(1);
        }
        $this->runGit('git pull', $frameworkPath);

        // checks

        $this->stdout('check if framework composer.json matches yii2-dev composer.json...');
        $this->checkComposer($frameworkPath);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        // adjustments

        $this->stdout('prepare classmap...', Console::BOLD);
        $this->dryRun || Yii::$app->runAction('classmap', [$frameworkPath]);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $this->stdout('updating mimetype magic file and mime aliases...', Console::BOLD);
        $this->dryRun || Yii::$app->runAction('mime-type', ["$frameworkPath/helpers/mimeTypes.php"], ["$frameworkPath/helpers/mimeAliases.php"]);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $this->stdout("fixing various PHPDoc style issues...\n", Console::BOLD);
        $this->dryRun || Yii::$app->runAction('php-doc/fix', [$frameworkPath]);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $this->stdout("updating PHPDoc @property annotations...\n", Console::BOLD);
        $this->dryRun || Yii::$app->runAction('php-doc/property', [$frameworkPath]);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $this->stdout('sorting changelogs...', Console::BOLD);
        $this->dryRun || $this->resortChangelogs(['framework'], $version);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $this->stdout('closing changelogs...', Console::BOLD);
        $this->dryRun || $this->closeChangelogs(['framework'], $version);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $this->stdout('updating Yii version...');
        $this->dryRun || $this->updateYiiVersion($frameworkPath, $version);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $this->stdout("\nIn the following you can check the above changes using git diff.\n\n");
        do {
            $this->runGit('git diff --color', $frameworkPath);
            $this->stdout("\n\n\nCheck whether the above diff is okay, if not you may change things as needed before continuing.\n");
            $this->stdout("You may abort the program with Ctrl + C and reset the changes by running `git checkout -- .` in the repo.\n\n");
        } while (!$this->confirm('Type `yes` to continue, `no` to view git diff again. Continue?'));

        $this->stdout("\n\n");
        $this->stdout("    ****          RELEASE TIME!         ****\n", Console::FG_YELLOW, Console::BOLD);
        $this->stdout("    ****    Commit, Tag and Push it!    ****\n", Console::FG_YELLOW, Console::BOLD);
        $this->stdout("\n\nHint: if you decide 'no' for any of the following, the command will not be executed. You may manually run them later if needed. E.g. try the release locally without pushing it.\n\n");

        $this->stdout("Make sure to have your git set up for GPG signing. The following tag and commit should be signed.\n\n");

        $this->runGit("git commit -S -a -m \"release version $version\"", $frameworkPath);
        $this->runGit("git tag -s $version -m \"version $version\"", $frameworkPath);
        $this->runGit('git push', $frameworkPath);
        $this->runGit('git push --tags', $frameworkPath);

        $this->stdout("\n\n");
        $this->stdout('CONGRATULATIONS! You have just released ', Console::FG_YELLOW, Console::BOLD);
        $this->stdout('framework', Console::FG_RED, Console::BOLD);
        $this->stdout(' version ', Console::FG_YELLOW, Console::BOLD);
        $this->stdout($version, Console::BOLD);
        $this->stdout("!\n\n", Console::FG_YELLOW, Console::BOLD);

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



        // prepare next release

        $this->stdout("Time to prepare the next release...\n\n", Console::FG_YELLOW, Console::BOLD);

        $this->stdout('opening changelogs...', Console::BOLD);
        $nextVersion = $this->getNextVersions(['framework' => $version], self::PATCH); // TODO support other versions
        $this->dryRun || $this->openChangelogs(['framework'], $nextVersion['framework']);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $this->stdout('updating Yii version...');
        $this->dryRun || $this->updateYiiVersion($frameworkPath, $nextVersion['framework'] . '-dev');
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);


        $this->stdout("\n");
        $this->runGit('git diff --color', $frameworkPath);
        $this->stdout("\n\n");
        $this->runGit('git commit -a -m "prepare for next release"', $frameworkPath);
        $this->runGit('git push', $frameworkPath);

        $this->stdout("\n\nDONE!", Console::FG_YELLOW, Console::BOLD);

        $this->stdout("\n\nThe following steps are left for you to do manually:\n\n");
        $nextVersion2 = $this->getNextVersions($nextVersion, self::PATCH); // TODO support other versions
        $this->stdout("- wait for your changes to be propagated to the repo and create a tag $version on  https://github.com/yiisoft/yii2-framework\n\n");
        $this->stdout("    git clone git@github.com:yiisoft/yii2-framework.git\n");
        $this->stdout("    cd yii2-framework/\n");
        $this->stdout("    export RELEASECOMMIT=$(git log --oneline |grep $version |grep -Po \"^[0-9a-f]+\")\n");
        $this->stdout("    git tag -s $version -m \"version $version\" \$RELEASECOMMIT\n");
        $this->stdout("    git tag --verify $version\n");
        $this->stdout("    git push --tags\n\n");
        $this->stdout("- close the $version milestone on github and open new ones for {$nextVersion['framework']} and {$nextVersion2['framework']}: https://github.com/yiisoft/yii2/milestones\n");
        $this->stdout("- create a release on github.\n");
        $this->stdout("- release news and announcement.\n");
        $this->stdout("- update the website (will be automated soon and is only relevant for the new website).\n");
        $this->stdout("\n");
        $this->stdout("- release applications: ./build/build release app-basic\n");
        $this->stdout("- release applications: ./build/build release app-advanced\n");

        $this->stdout("\n");
    }

    protected function releaseApplication($name, $path, $version)
    {
        $this->stdout("\n");
        $this->stdout($h = "Preparing release for application  $name  version $version", Console::BOLD);
        $this->stdout("\n" . str_repeat('-', \strlen($h)) . "\n\n", Console::BOLD);

        if (!$this->confirm('Make sure you are on the right branch for this release and that it tracks the correct remote branch! Continue?')) {
            exit(1);
        }
        $this->runGit('git pull', $path);

        // adjustments

        $this->stdout("fixing various PHPDoc style issues...\n", Console::BOLD);
        $this->setAppAliases($name, $path);
        $this->dryRun || Yii::$app->runAction('php-doc/fix', [$path, 'skipFrameworkRequirements' => true]);
        $this->resetAppAliases();
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $this->stdout("updating PHPDoc @property annotations...\n", Console::BOLD);
        $this->setAppAliases($name, $path);
        $this->dryRun || Yii::$app->runAction('php-doc/property', [$path, 'skipFrameworkRequirements' => true]);
        $this->resetAppAliases();
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $this->stdout("updating composer stability...\n", Console::BOLD);
        $this->dryRun || $this->composerSetStability(["app-$name"], $version);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $this->stdout("\nIn the following you can check the above changes using git diff.\n\n");
        do {
            $this->runGit('git diff --color', $path);
            $this->stdout("\n\n\nCheck whether the above diff is okay, if not you may change things as needed before continuing.\n");
            $this->stdout("You may abort the program with Ctrl + C and reset the changes by running `git checkout -- .` in the repo.\n\n");
        } while (!$this->confirm('Type `yes` to continue, `no` to view git diff again. Continue?'));

        $this->stdout("\n\n");
        $this->stdout("    ****          RELEASE TIME!         ****\n", Console::FG_YELLOW, Console::BOLD);
        $this->stdout("    ****    Commit, Tag and Push it!    ****\n", Console::FG_YELLOW, Console::BOLD);
        $this->stdout("\n\nHint: if you decide 'no' for any of the following, the command will not be executed. You may manually run them later if needed. E.g. try the release locally without pushing it.\n\n");

        $this->stdout("Make sure to have your git set up for GPG signing. The following tag and commit should be signed.\n\n");

        $this->runGit("git commit -S -a -m \"release version $version\"", $path);
        $this->runGit("git tag -s $version -m \"version $version\"", $path);
        $this->runGit('git push', $path);
        $this->runGit('git push --tags', $path);

        $this->stdout("\n\n");
        $this->stdout('CONGRATULATIONS! You have just released application ', Console::FG_YELLOW, Console::BOLD);
        $this->stdout($name, Console::FG_RED, Console::BOLD);
        $this->stdout(' version ', Console::FG_YELLOW, Console::BOLD);
        $this->stdout($version, Console::BOLD);
        $this->stdout("!\n\n", Console::FG_YELLOW, Console::BOLD);

        // prepare next release

        $this->stdout("Time to prepare the next release...\n\n", Console::FG_YELLOW, Console::BOLD);

        $this->stdout("updating composer stability...\n", Console::BOLD);
        $this->dryRun || $this->composerSetStability(["app-$name"], 'dev');
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $nextVersion = $this->getNextVersions(["app-$name" => $version], self::PATCH); // TODO support other versions

        $this->stdout("\n");
        $this->runGit('git diff --color', $path);
        $this->stdout("\n\n");
        $this->runGit('git commit -a -m "prepare for next release"', $path);
        $this->runGit('git push', $path);

        $this->stdout("\n\nDONE!", Console::FG_YELLOW, Console::BOLD);

        $this->stdout("\n\nThe following steps are left for you to do manually:\n\n");
        $nextVersion2 = $this->getNextVersions($nextVersion, self::PATCH); // TODO support other versions
        $this->stdout("- close the $version milestone on github and open new ones for {$nextVersion["app-$name"]} and {$nextVersion2["app-$name"]}: https://github.com/yiisoft/yii2-app-$name/milestones\n");
        $this->stdout("- Create Application packages and upload them to github:  ./build release/package app-$name\n");

        $this->stdout("\n");
    }

    private $_oldAlias;

    protected function setAppAliases($app, $path)
    {
        $this->_oldAlias = Yii::getAlias('@app');
        switch ($app) {
            case 'basic':
                Yii::setAlias('@app', $path);
                break;
            case 'advanced':
                // setup @frontend, @backend etc...
                require "$path/common/config/bootstrap.php";
                break;
        }
    }

    protected function resetAppAliases()
    {
        Yii::setAlias('@app', $this->_oldAlias);
    }

    protected function packageApplication($name, $version, $packagePath)
    {
        FileHelper::createDirectory($packagePath);

        $this->runCommand("composer create-project yiisoft/yii2-app-$name $name $version", $packagePath);
        // clear cookie validation key in basic app
        if (is_file($configFile = "$packagePath/$name/config/web.php")) {
            $this->sed(
                "/'cookieValidationKey' => '.*?',/",
                "'cookieValidationKey' => '',",
                $configFile
            );
        }
        $this->runCommand("tar zcf yii-$name-app-$version.tgz $name", $packagePath);
    }

    protected function releaseExtension($name, $path, $version)
    {
        $this->stdout("\n");
        $this->stdout($h = "Preparing release for extension  $name  version $version", Console::BOLD);
        $this->stdout("\n" . str_repeat('-', \strlen($h)) . "\n\n", Console::BOLD);

        if (!$this->confirm('Make sure you are on the right branch for this release and that it tracks the correct remote branch! Continue?')) {
            exit(1);
        }
        $this->runGit('git pull', $path);

        // adjustments

        $this->stdout("fixing various PHPDoc style issues...\n", Console::BOLD);
        $this->dryRun || Yii::$app->runAction('php-doc/fix', [$path]);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $this->stdout("updating PHPDoc @property annotations...\n", Console::BOLD);
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
            $this->runGit('git diff --color', $path);
            $this->stdout("\n\n\nCheck whether the above diff is okay, if not you may change things as needed before continuing.\n");
            $this->stdout("You may abort the program with Ctrl + C and reset the changes by running `git checkout -- .` in the repo.\n\n");
        } while (!$this->confirm('Type `yes` to continue, `no` to view git diff again. Continue?'));

        $this->stdout("\n\n");
        $this->stdout("    ****          RELEASE TIME!         ****\n", Console::FG_YELLOW, Console::BOLD);
        $this->stdout("    ****    Commit, Tag and Push it!    ****\n", Console::FG_YELLOW, Console::BOLD);
        $this->stdout("\n\nHint: if you decide 'no' for any of the following, the command will not be executed. You may manually run them later if needed. E.g. try the release locally without pushing it.\n\n");

        $this->stdout("Make sure to have your git set up for GPG signing. The following tag and commit should be signed.\n\n");

        $this->runGit("git commit -S -a -m \"release version $version\"", $path);
        $this->runGit("git tag -s $version -m \"version $version\"", $path);
        $this->runGit('git push', $path);
        $this->runGit('git push --tags', $path);

        $this->stdout("\n\n");
        $this->stdout('CONGRATULATIONS! You have just released extension ', Console::FG_YELLOW, Console::BOLD);
        $this->stdout($name, Console::FG_RED, Console::BOLD);
        $this->stdout(' version ', Console::FG_YELLOW, Console::BOLD);
        $this->stdout($version, Console::BOLD);
        $this->stdout("!\n\n", Console::FG_YELLOW, Console::BOLD);

        // prepare next release

        $this->stdout("Time to prepare the next release...\n\n", Console::FG_YELLOW, Console::BOLD);

        $this->stdout('opening changelogs...', Console::BOLD);
        $nextVersion = $this->getNextVersions([$name => $version], self::PATCH); // TODO support other versions
        $this->dryRun || $this->openChangelogs([$name], $nextVersion[$name]);
        $this->stdout("done.\n", Console::FG_GREEN, Console::BOLD);

        $this->stdout("\n");
        $this->runGit('git diff --color', $path);
        $this->stdout("\n\n");
        $this->runGit('git commit -a -m "prepare for next release"', $path);
        $this->runGit('git push', $path);

        $this->stdout("\n\nDONE!", Console::FG_YELLOW, Console::BOLD);

        $this->stdout("\n\nThe following steps are left for you to do manually:\n\n");
        $nextVersion2 = $this->getNextVersions($nextVersion, self::PATCH); // TODO support other versions
        $this->stdout("- close the $version milestone on github and open new ones for {$nextVersion[$name]} and {$nextVersion2[$name]}: https://github.com/yiisoft/yii2-$name/milestones\n");
        $this->stdout("- release news and announcement.\n");
        $this->stdout("- update the website (will be automated soon and is only relevant for the new website).\n");

        $this->stdout("\n");
    }


    protected function runCommand($cmd, $path)
    {
        $this->stdout("running  $cmd  ...", Console::BOLD);
        if ($this->dryRun) {
            $this->stdout("dry run, command `$cmd` not executed.\n");
            return;
        }
        chdir($path);
        exec($cmd, $output, $ret);
        if ($ret != 0) {
            echo implode("\n", $output);
            throw new Exception("Command \"$cmd\" failed with code " . $ret);
        }
        $this->stdout("\ndone.\n", Console::BOLD, Console::FG_GREEN);
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
        try {
            chdir($path);
        } catch (\yii\base\ErrorException $e) {
            throw new Exception('Failed to getch git tags in ' . $path . ': ' . $e->getMessage());
        }
        exec('git fetch --tags', $output, $ret);
        if ($ret != 0) {
            throw new Exception('Command "git fetch --tags" failed with code ' . $ret);
        }
    }


    protected function checkComposer($fwPath)
    {
        if (!$this->confirm("\nNot yet automated: Please check if composer.json dependencies in framework dir match the one in repo root. Continue?", false)) {
            exit;
        }
    }


    protected function closeChangelogs($what, $version)
    {
        $v = str_replace('\\-', '[\\- ]', preg_quote($version, '/'));
        $headline = $version . ' ' . date('F d, Y');
        $this->sed(
            '/' . $v . ' under development\n(-+?)\n/',
            $headline . "\n" . str_repeat('-', \strlen($headline)) . "\n",
            $this->getChangelogs($what)
        );
    }

    protected function openChangelogs($what, $version)
    {
        $headline = "\n$version under development\n";
        $headline .= str_repeat('-', \strlen($headline) - 2) . "\n\n- no changes in this release.\n";
        foreach ($this->getChangelogs($what) as $file) {
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
        foreach ($this->getChangelogs($what) as $file) {
            // split the file into relevant parts
            [$start, $changelog, $end] = $this->splitChangelog($file, $version);
            $changelog = $this->resortChangelog($changelog);
            file_put_contents($file, implode("\n", array_merge($start, $changelog, $end)));
        }
    }

    /**
     * Extract changelog content for a specific version.
     * @param string $file
     * @param string $version
     * @return array
     */
    protected function splitChangelog($file, $version)
    {
        $lines = explode("\n", file_get_contents($file));

        // split the file into relevant parts
        $start = [];
        $changelog = [];
        $end = [];

        $state = 'start';
        foreach ($lines as $l => $line) {
            // starting from the changelogs headline
            if (isset($lines[$l - 2]) && strpos($lines[$l - 2], $version) !== false &&
                isset($lines[$l - 1]) && strncmp($lines[$l - 1], '---', 3) === 0) {
                $state = 'changelog';
            }
            if ($state === 'changelog' && isset($lines[$l + 1]) && strncmp($lines[$l + 1], '---', 3) === 0) {
                $state = 'end';
            }
            // add continued lines to the last item to keep them together
            if (!empty(${$state}) && trim($line) !== '' && strncmp($line, '- ', 2) !== 0) {
                end(${$state});
                ${$state}[key(${$state})] .= "\n" . $line;
            } else {
                ${$state}[] = $line;
            }
        }

        return [$start, $changelog, $end];
    }

    /**
     * Ensure sorting of the changelog lines.
     * @param string[] $changelog
     * @return string[]
     */
    protected function resortChangelog($changelog)
    {
        // cleanup whitespace
        foreach ($changelog as $i => $line) {
            $changelog[$i] = rtrim($line);
        }
        $changelog = array_filter($changelog);

        $i = 0;
        ArrayHelper::multisort($changelog, function ($line) use (&$i) {
            if (preg_match('/^- (Chg|Enh|Bug|New)( #\d+(, #\d+)*)?: .+/', $line, $m)) {
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
        if (\in_array('framework', $what)) {
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
        return array_filter(glob($this->basePath . '/extensions/*/CHANGELOG.md'), function ($elem) use ($what) {
            foreach ($what as $ext) {
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
        if (\in_array('app-advanced', $what)) {
            $apps[] = $this->basePath . '/apps/advanced/composer.json';
        }
        if (\in_array('app-basic', $what)) {
            $apps[] = $this->basePath . '/apps/basic/composer.json';
        }
        if (\in_array('app-benchmark', $what)) {
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
        foreach ((array) $files as $file) {
            file_put_contents($file, preg_replace($pattern, $replace, file_get_contents($file)));
        }
    }

    protected function getCurrentVersions(array $what)
    {
        $versions = [];
        foreach ($what as $ext) {
            if ($ext === 'framework') {
                chdir("{$this->basePath}/framework");
            } elseif (strncmp('app-', $ext, 4) === 0) {
                chdir("{$this->basePath}/apps/" . substr($ext, 4));
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
        foreach ($versions as $k => $v) {
            if (empty($v)) {
                $versions[$k] = '2.0.0';
                continue;
            }
            $parts = explode('.', $v);
            switch ($type) {
                case self::MINOR:
                    $parts[1]++;
                    $parts[2] = 0;
                    if (isset($parts[3])) {
                        unset($parts[3]);
                    }
                    break;
                case self::PATCH:
                    $parts[2]++;
                    if (isset($parts[3])) {
                        unset($parts[3]);
                    }
                    break;
                default:
                    throw new Exception('Unknown version type.');
            }
            $versions[$k] = implode('.', $parts);
        }

        return $versions;
    }
}
