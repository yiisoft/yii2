<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\console\Controller;
use yii\console\Exception;
use yii\console\ExitCode;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\test\FixtureTrait;

/**
 * 管理 Fixture 数据的装载和卸载。
 *
 * ```
 * #load fixtures from UsersFixture class with default namespace "tests\unit\fixtures"
 * yii fixture/load User
 *
 * #also a short version of this command (generate action is default)
 * yii fixture User
 *
 * #load all fixtures
 * yii fixture "*"
 *
 * #load all fixtures except User
 * yii fixture "*, -User"
 *
 * #load fixtures with different namespace.
 * yii fixture/load User --namespace=alias\my\custom\namespace\goes\here
 * ```
 *
 * `unload` 子命令可用于卸载 fixtures。
 *
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
class FixtureController extends Controller
{
    use FixtureTrait;

    /**
     * @var string 控制器的 ID 的默认动作。
     */
    public $defaultAction = 'load';
    /**
     * @var string 搜索 fixtures 的默认命名空间
     */
    public $namespace = 'tests\unit\fixtures';
    /**
     * @var array 加载和卸载时应用的全局 fixtures。默认情况下它将设置 `InitDbFixture`
     * 禁用并启用完整性检查，以便可以安全地加载数据。
     */
    public $globalFixtures = [
        'yii\test\InitDbFixture',
    ];


    /**
     * {@inheritdoc}
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'namespace', 'globalFixtures',
        ]);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.8
     */
    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), [
            'g' => 'globalFixtures',
            'n' => 'namespace',
        ]);
    }

    /**
     * 加载指定的 fixture 数据。
     *
     * 例如，
     *
     * ```
     * # load the fixture data specified by User and UserProfile.
     * # any existing fixture data will be removed first
     * yii fixture/load "User, UserProfile"
     *
     * # load all available fixtures found under 'tests\unit\fixtures'
     * yii fixture/load "*"
     *
     * # load all fixtures except User and UserProfile
     * yii fixture/load "*, -User, -UserProfile"
     * ```
     *
     * @param array $fixturesInput
     * @return int 返回代码
     * @throws Exception 如果指定的 fixture 不存在。
     */
    public function actionLoad(array $fixturesInput = [])
    {
        if ($fixturesInput === []) {
            $this->stdout($this->getHelpSummary() . "\n");

            $helpCommand = Console::ansiFormat('yii help fixture', [Console::FG_CYAN]);
            $this->stdout("Use $helpCommand to get usage info.\n");

            return ExitCode::OK;
        }

        $filtered = $this->filterFixtures($fixturesInput);
        $except = $filtered['except'];

        if (!$this->needToApplyAll($fixturesInput[0])) {
            $fixtures = $filtered['apply'];

            $foundFixtures = $this->findFixtures($fixtures);
            $notFoundFixtures = array_diff($fixtures, $foundFixtures);

            if ($notFoundFixtures) {
                $this->notifyNotFound($notFoundFixtures);
            }
        } else {
            $foundFixtures = $this->findFixtures();
        }

        $fixturesToLoad = array_diff($foundFixtures, $except);

        if (!$foundFixtures) {
            throw new Exception(
                'No files were found for: "' . implode(', ', $fixturesInput) . "\".\n" .
                "Check that files exist under fixtures path: \n\"" . $this->getFixturePath() . '".'
            );
        }

        if (!$fixturesToLoad) {
            $this->notifyNothingToLoad($foundFixtures, $except);
            return ExitCode::OK;
        }

        if (!$this->confirmLoad($fixturesToLoad, $except)) {
            return ExitCode::OK;
        }

        $fixtures = $this->getFixturesConfig(array_merge($this->globalFixtures, $fixturesToLoad));

        if (!$fixtures) {
            throw new Exception('No fixtures were found in namespace: "' . $this->namespace . '"' . '');
        }

        $fixturesObjects = $this->createFixtures($fixtures);

        $this->unloadFixtures($fixturesObjects);
        $this->loadFixtures($fixturesObjects);
        $this->notifyLoaded($fixtures);

        return ExitCode::OK;
    }

    /**
     * 卸载指定的 fixtures。
     *
     * 例如，
     *
     * ```
     * # unload the fixture data specified by User and UserProfile.
     * yii fixture/unload "User, UserProfile"
     *
     * # unload all fixtures found under 'tests\unit\fixtures'
     * yii fixture/unload "*"
     *
     * # unload all fixtures except User and UserProfile
     * yii fixture/unload "*, -User, -UserProfile"
     * ```
     *
     * @param array $fixturesInput
     * @return int 返回代码
     * @throws Exception 如果指定的 fixture 不存在。
     */
    public function actionUnload(array $fixturesInput = [])
    {
        $filtered = $this->filterFixtures($fixturesInput);
        $except = $filtered['except'];

        if (!$this->needToApplyAll($fixturesInput[0])) {
            $fixtures = $filtered['apply'];

            $foundFixtures = $this->findFixtures($fixtures);
            $notFoundFixtures = array_diff($fixtures, $foundFixtures);

            if ($notFoundFixtures) {
                $this->notifyNotFound($notFoundFixtures);
            }
        } else {
            $foundFixtures = $this->findFixtures();
        }

        $fixturesToUnload = array_diff($foundFixtures, $except);

        if (!$foundFixtures) {
            throw new Exception(
                'No files were found for: "' . implode(', ', $fixturesInput) . "\".\n" .
                "Check that files exist under fixtures path: \n\"" . $this->getFixturePath() . '".'
            );
        }

        if (!$fixturesToUnload) {
            $this->notifyNothingToUnload($foundFixtures, $except);
            return ExitCode::OK;
        }

        if (!$this->confirmUnload($fixturesToUnload, $except)) {
            return ExitCode::OK;
        }

        $fixtures = $this->getFixturesConfig(array_merge($this->globalFixtures, $fixturesToUnload));

        if (!$fixtures) {
            throw new Exception('No fixtures were found in namespace: ' . $this->namespace . '".');
        }

        $this->unloadFixtures($this->createFixtures($fixtures));
        $this->notifyUnloaded($fixtures);
    }

    /**
     * 通知用户 fixtures 已成功加载。
     * @param array $fixtures
     */
    private function notifyLoaded($fixtures)
    {
        $this->stdout("Fixtures were successfully loaded from namespace:\n", Console::FG_YELLOW);
        $this->stdout("\t\"" . Yii::getAlias($this->namespace) . "\"\n\n", Console::FG_GREEN);
        $this->outputList($fixtures);
    }

    /**
     * 通知用户没有可根据输入条件加载的 fixtures。
     * @param array $foundFixtures 找到的 fixtures 数组
     * @param array $except 不应加载的 fixtures 的数组。
     */
    public function notifyNothingToLoad($foundFixtures, $except)
    {
        $this->stdout("Fixtures to load could not be found according given conditions:\n\n", Console::FG_RED);
        $this->stdout("Fixtures namespace is: \n", Console::FG_YELLOW);
        $this->stdout("\t" . $this->namespace . "\n", Console::FG_GREEN);

        if (count($foundFixtures)) {
            $this->stdout("\nFixtures founded under the namespace:\n\n", Console::FG_YELLOW);
            $this->outputList($foundFixtures);
        }

        if (count($except)) {
            $this->stdout("\nFixtures that will NOT be loaded: \n\n", Console::FG_YELLOW);
            $this->outputList($except);
        }
    }

    /**
     * 通知用户没有要根据输入条件卸载的 fixtures。
     * @param array $foundFixtures 找到的 fixtures 数组
     * @param array $except 不应加载的 fixtures 的数组。
     */
    public function notifyNothingToUnload($foundFixtures, $except)
    {
        $this->stdout("Fixtures to unload could not be found according to given conditions:\n\n", Console::FG_RED);
        $this->stdout("Fixtures namespace is: \n", Console::FG_YELLOW);
        $this->stdout("\t" . $this->namespace . "\n", Console::FG_GREEN);

        if (count($foundFixtures)) {
            $this->stdout("\nFixtures found under the namespace:\n\n", Console::FG_YELLOW);
            $this->outputList($foundFixtures);
        }

        if (count($except)) {
            $this->stdout("\nFixtures that will NOT be unloaded: \n\n", Console::FG_YELLOW);
            $this->outputList($except);
        }
    }

    /**
     * 通知用户 fixtures 已成功卸载。
     * @param array $fixtures
     */
    private function notifyUnloaded($fixtures)
    {
        $this->stdout("\nFixtures were successfully unloaded from namespace: ", Console::FG_YELLOW);
        $this->stdout(Yii::getAlias($this->namespace) . "\"\n\n", Console::FG_GREEN);
        $this->outputList($fixtures);
    }

    /**
     * 通知用户在 fixtures 路径下找不到 fixtures。
     * @param array $fixtures
     */
    private function notifyNotFound($fixtures)
    {
        $this->stdout("Some fixtures were not found under path:\n", Console::BG_RED);
        $this->stdout("\t" . $this->getFixturePath() . "\n\n", Console::FG_GREEN);
        $this->stdout("Check that they have correct namespace \"{$this->namespace}\" \n", Console::BG_RED);
        $this->outputList($fixtures);
        $this->stdout("\n");
    }

    /**
     * 提示用户确认是否应加载 fixtures。
     * @param array $fixtures
     * @param array $except
     * @return bool
     */
    private function confirmLoad($fixtures, $except)
    {
        $this->stdout("Fixtures namespace is: \n", Console::FG_YELLOW);
        $this->stdout("\t" . $this->namespace . "\n\n", Console::FG_GREEN);

        if (count($this->globalFixtures)) {
            $this->stdout("Global fixtures will be used:\n\n", Console::FG_YELLOW);
            $this->outputList($this->globalFixtures);
        }

        if (count($fixtures)) {
            $this->stdout("\nFixtures below will be loaded:\n\n", Console::FG_YELLOW);
            $this->outputList($fixtures);
        }

        if (count($except)) {
            $this->stdout("\nFixtures that will NOT be loaded: \n\n", Console::FG_YELLOW);
            $this->outputList($except);
        }

        $this->stdout("\nBe aware that:\n", Console::BOLD);
        $this->stdout("Applying leads to purging of certain data in the database!\n", Console::FG_RED);

        return $this->confirm("\nLoad above fixtures?");
    }

    /**
     * 提示用户确认应卸载的 fixtures。
     * @param array $fixtures
     * @param array $except
     * @return bool
     */
    private function confirmUnload($fixtures, $except)
    {
        $this->stdout("Fixtures namespace is: \n", Console::FG_YELLOW);
        $this->stdout("\t" . $this->namespace . "\n\n", Console::FG_GREEN);

        if (count($this->globalFixtures)) {
            $this->stdout("Global fixtures will be used:\n\n", Console::FG_YELLOW);
            $this->outputList($this->globalFixtures);
        }

        if (count($fixtures)) {
            $this->stdout("\nFixtures below will be unloaded:\n\n", Console::FG_YELLOW);
            $this->outputList($fixtures);
        }

        if (count($except)) {
            $this->stdout("\nFixtures that will NOT be unloaded:\n\n", Console::FG_YELLOW);
            $this->outputList($except);
        }

        return $this->confirm("\nUnload fixtures?");
    }

    /**
     * 将数据作为列表输出到控制台。
     * @param array $data
     */
    private function outputList($data)
    {
        foreach ($data as $index => $item) {
            $this->stdout("\t" . ($index + 1) . ". {$item}\n", Console::FG_GREEN);
        }
    }

    /**
     * 检查是否需要应用所有 fixtures。
     * @param string $fixture
     * @return bool
     */
    public function needToApplyAll($fixture)
    {
        return $fixture === '*';
    }

    /**
     * 找到要加载的 fixtures，例如 "User"，如果未指定任何 fixtures 然后他们
     * 都将以 "Fixture.php" 为后缀进行搜索。
     * @param array $fixtures 要加载的 fixtures
     * @return array 找到的 fixtures 数组。这些参数可能与输入参数不同，因为不是所有的 fixtures 都可能存在。
     */
    private function findFixtures(array $fixtures = [])
    {
        $fixturesPath = $this->getFixturePath();

        $filesToSearch = ['*Fixture.php'];
        $findAll = ($fixtures === []);

        if (!$findAll) {
            $filesToSearch = [];

            foreach ($fixtures as $fileName) {
                $filesToSearch[] = $fileName . 'Fixture.php';
            }
        }

        $files = FileHelper::findFiles($fixturesPath, ['only' => $filesToSearch]);
        $foundFixtures = [];

        foreach ($files as $fixture) {
            $foundFixtures[] = $this->getFixtureRelativeName($fixture);
        }

        return $foundFixtures;
    }

    /**
     * 计算 fixture 的名称
     * 基本上，从 fixture's 的完整路径中去掉 [[getFixturePath()]] 和 `Fixture.php' 后缀。
     * @see getFixturePath()
     * @param string $fullFixturePath 完整的 fixture 路径
     * @return string 相对 fixture 名称
     */
    private function getFixtureRelativeName($fullFixturePath)
    {
        $fixturesPath = FileHelper::normalizePath($this->getFixturePath());
        $fullFixturePath = FileHelper::normalizePath($fullFixturePath);

        $relativeName = substr($fullFixturePath, strlen($fixturesPath) + 1);
        $relativeDir = dirname($relativeName) === '.' ? '' : dirname($relativeName) . DIRECTORY_SEPARATOR;

        return $relativeDir . basename($fullFixturePath, 'Fixture.php');
    }

    /**
     * 返回可用于加载它们的有效 fixtures 配置。
     * @param array $fixtures 要配置的 fixtures
     * @return array
     */
    private function getFixturesConfig($fixtures)
    {
        $config = [];

        foreach ($fixtures as $fixture) {
            $isNamespaced = (strpos($fixture, '\\') !== false);
            // replace linux' path slashes to namespace backslashes, in case if $fixture is non-namespaced relative path
            $fixture = str_replace('/', '\\', $fixture);
            $fullClassName = $isNamespaced ? $fixture : $this->namespace . '\\' . $fixture;

            if (class_exists($fullClassName)) {
                $config[] = $fullClassName;
            } elseif (class_exists($fullClassName . 'Fixture')) {
                $config[] = $fullClassName . 'Fixture';
            }
        }

        return $config;
    }

    /**
     * 通过将 fixtures 分为两类进行过滤：一类是应该应用的，另一类不是。
     *
     * 如果 fixture 前缀为 "-"，例如 "-User"，这意味着不应该加载 fixture，
     * 如果它没有前缀则被认为是要加载的。返回数组：
     *
     * ```php
     * [
     *     'apply' => [
     *         'User',
     *         ...
     *     ],
     *     'except' => [
     *         'Custom',
     *         ...
     *     ],
     * ]
     * ```
     * @param array $fixtures
     * @return array fixtures 带有 'apply' 和 'except' 元素的数组。
     */
    private function filterFixtures($fixtures)
    {
        $filtered = [
            'apply' => [],
            'except' => [],
        ];

        foreach ($fixtures as $fixture) {
            if (mb_strpos($fixture, '-') !== false) {
                $filtered['except'][] = str_replace('-', '', $fixture);
            } else {
                $filtered['apply'][] = $fixture;
            }
        }

        return $filtered;
    }

    /**
     * 返回在 fixtures 命名空间上确定的 fixture 路径。
     * @throws InvalidConfigException 如果 fixture 命名空间无效
     * @return string fixture 路径
     */
    private function getFixturePath()
    {
        try {
            return Yii::getAlias('@' . str_replace('\\', '/', $this->namespace));
        } catch (InvalidParamException $e) {
            throw new InvalidConfigException('Invalid fixture namespace: "' . $this->namespace . '". Please, check your FixtureController::namespace parameter');
        }
    }
}
