<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\console\controllers;

use Yii;
use yii\console\controllers\FixtureController;
use yii\console\ExitCode;
use yii\helpers\Console;
use yiiunit\data\console\controllers\fixtures\FixtureStorage;
use yiiunit\TestCase;

/**
 * @see FixtureController
 * @group console
 */
class FixtureControllerSqliteTest extends TestCase
{
    private static $activeFixtureExclusions = [
        '-DependentActive',
        '-FirstIndependentActive',
        '-SecondIndependentActive',
    ];

    /**
     * @var BufferedFixtureController|null
     */
    private $_controller;

    protected function setUp(): void
    {
        $this->mockApplication();

        $this->_controller = new BufferedFixtureController('fixture', Yii::$app);
        $this->_controller->interactive = false;
        $this->_controller->globalFixtures = [];
        $this->_controller->namespace = 'yiiunit\data\console\controllers\fixtures';
    }

    protected function tearDown(): void
    {
        $this->_controller = null;
        FixtureStorage::clear();

        parent::tearDown();
    }

    public function testActionLoadEmptyInputPrintsHelp(): void
    {
        $result = $this->_controller->actionLoad([]);

        $output = Console::stripAnsiFormat($this->_controller->flushStdOutBuffer());

        $this->assertSame(ExitCode::OK, $result);
        $this->assertStringContainsString('yii help fixture', $output);
    }

    public function testActionLoadSpecificFixture(): void
    {
        $this->_controller->actionLoad(['First']);

        $this->assertCount(1, FixtureStorage::$firstFixtureData);
        $this->assertEmpty(FixtureStorage::$secondFixtureData);
    }

    public function testActionLoadAllFixtures(): void
    {
        $this->_controller->actionLoad(array_merge(['*'], self::$activeFixtureExclusions));

        $this->assertCount(1, FixtureStorage::$firstFixtureData);
        $this->assertCount(1, FixtureStorage::$secondFixtureData);
        $this->assertCount(1, FixtureStorage::$globalFixturesData);
        $this->assertCount(1, FixtureStorage::$subdirFirstFixtureData);
        $this->assertCount(1, FixtureStorage::$subdirSecondFixtureData);
    }

    public function testActionLoadAllExceptSpecific(): void
    {
        $this->_controller->actionLoad(array_merge(
            ['*', '-Second', '-subdir/Second'],
            self::$activeFixtureExclusions
        ));

        $this->assertCount(1, FixtureStorage::$firstFixtureData);
        $this->assertEmpty(FixtureStorage::$secondFixtureData);
        $this->assertCount(1, FixtureStorage::$globalFixturesData);
        $this->assertEmpty(FixtureStorage::$subdirSecondFixtureData);
    }

    public function testActionLoadNothingToLoadWhenAllExcluded(): void
    {
        $result = $this->_controller->actionLoad(['Global', '-Global']);

        $output = Console::stripAnsiFormat($this->_controller->flushStdOutBuffer());

        $this->assertSame(ExitCode::OK, $result);
        $this->assertStringContainsString('could not be found according given conditions', $output);
        $this->assertEmpty(FixtureStorage::$globalFixturesData);
    }

    public function testActionLoadNonExistentFixtureThrowsException(): void
    {
        $this->expectException('yii\console\Exception');
        $this->expectExceptionMessage('No files were found for');

        $this->_controller->actionLoad(['NonExistent']);
    }

    public function testActionUnloadEmptyInputPrintsHelp(): void
    {
        $result = $this->_controller->actionUnload([]);

        $output = Console::stripAnsiFormat($this->_controller->flushStdOutBuffer());

        $this->assertSame(ExitCode::OK, $result);
        $this->assertStringContainsString('yii help fixture', $output);
    }

    public function testActionUnloadSpecificFixture(): void
    {
        FixtureStorage::$firstFixtureData[] = 'seeded data';
        FixtureStorage::$secondFixtureData[] = 'seeded data';

        $this->_controller->actionUnload(['First']);

        $this->assertEmpty(FixtureStorage::$firstFixtureData);
        $this->assertNotEmpty(FixtureStorage::$secondFixtureData);
    }

    public function testActionUnloadAllFixtures(): void
    {
        FixtureStorage::$firstFixtureData[] = 'seeded data';
        FixtureStorage::$secondFixtureData[] = 'seeded data';
        FixtureStorage::$globalFixturesData[] = 'seeded data';
        FixtureStorage::$subdirFirstFixtureData[] = 'seeded data';
        FixtureStorage::$subdirSecondFixtureData[] = 'seeded data';

        $this->_controller->actionUnload(array_merge(['*'], self::$activeFixtureExclusions));

        $this->assertEmpty(FixtureStorage::$firstFixtureData);
        $this->assertEmpty(FixtureStorage::$secondFixtureData);
        $this->assertEmpty(FixtureStorage::$globalFixturesData);
        $this->assertEmpty(FixtureStorage::$subdirFirstFixtureData);
        $this->assertEmpty(FixtureStorage::$subdirSecondFixtureData);
    }

    public function testActionUnloadAllExceptSpecific(): void
    {
        FixtureStorage::$firstFixtureData[] = 'seeded data';
        FixtureStorage::$secondFixtureData[] = 'seeded data';
        FixtureStorage::$globalFixturesData[] = 'seeded data';

        $this->_controller->actionUnload(array_merge(
            ['*', '-Second', '-subdir/Second'],
            self::$activeFixtureExclusions
        ));

        $this->assertEmpty(FixtureStorage::$firstFixtureData);
        $this->assertNotEmpty(FixtureStorage::$secondFixtureData);
        $this->assertEmpty(FixtureStorage::$globalFixturesData);
    }

    public function testActionUnloadNothingToUnloadWhenAllExcluded(): void
    {
        FixtureStorage::$globalFixturesData[] = 'seeded data';

        $result = $this->_controller->actionUnload(['Global', '-Global']);

        $output = Console::stripAnsiFormat($this->_controller->flushStdOutBuffer());

        $this->assertSame(ExitCode::OK, $result);
        $this->assertStringContainsString('could not be found according to given conditions', $output);
        $this->assertNotEmpty(FixtureStorage::$globalFixturesData);
    }

    public function testActionUnloadNonExistentFixtureThrowsException(): void
    {
        $this->expectException('yii\console\Exception');
        $this->expectExceptionMessage('No files were found for');

        $this->_controller->actionUnload(['NonExistent']);
    }

    public function testOptionsReturnsNamespaceAndGlobalFixtures(): void
    {
        $options = $this->_controller->options('load');

        $this->assertContains('namespace', $options);
        $this->assertContains('globalFixtures', $options);
        $this->assertContains('color', $options);
        $this->assertContains('interactive', $options);
    }

    public function testOptionAliasesContainsGlobalFixturesAndNamespace(): void
    {
        $aliases = $this->_controller->optionAliases();

        $this->assertSame('globalFixtures', $aliases['g']);
        $this->assertSame('namespace', $aliases['n']);
        $this->assertArrayHasKey('h', $aliases);
    }

    /**
     * @dataProvider needToApplyAllProvider
     */
    public function testNeedToApplyAll(string $fixture, bool $expected): void
    {
        $this->assertSame($expected, $this->_controller->needToApplyAll($fixture));
    }

    public static function needToApplyAllProvider(): array
    {
        return [
            'wildcard' => ['*', true],
            'specific fixture' => ['User', false],
            'empty string' => ['', false],
        ];
    }

    public function testNotifyNothingToLoadOutputsFoundAndExcept(): void
    {
        $this->_controller->notifyNothingToLoad(['First', 'Second'], ['First']);

        $output = Console::stripAnsiFormat($this->_controller->flushStdOutBuffer());

        $this->assertStringContainsString('could not be found according given conditions', $output);
        $this->assertStringContainsString($this->_controller->namespace, $output);
        $this->assertStringContainsString('First', $output);
        $this->assertStringContainsString('Second', $output);
        $this->assertStringContainsString('will NOT be loaded', $output);
    }

    public function testNotifyNothingToLoadWithEmptyArrays(): void
    {
        $this->_controller->notifyNothingToLoad([], []);

        $output = Console::stripAnsiFormat($this->_controller->flushStdOutBuffer());

        $this->assertStringContainsString('could not be found according given conditions', $output);
        $this->assertStringNotContainsString('will NOT be loaded', $output);
        $this->assertStringNotContainsString('founded under the namespace', $output);
    }

    public function testNotifyNothingToUnloadOutputsFoundAndExcept(): void
    {
        $this->_controller->notifyNothingToUnload(['First', 'Second'], ['First']);

        $output = Console::stripAnsiFormat($this->_controller->flushStdOutBuffer());

        $this->assertStringContainsString('could not be found according to given conditions', $output);
        $this->assertStringContainsString($this->_controller->namespace, $output);
        $this->assertStringContainsString('First', $output);
        $this->assertStringContainsString('Second', $output);
        $this->assertStringContainsString('will NOT be unloaded', $output);
    }

    public function testNotifyNothingToUnloadWithEmptyArrays(): void
    {
        $this->_controller->notifyNothingToUnload([], []);

        $output = Console::stripAnsiFormat($this->_controller->flushStdOutBuffer());

        $this->assertStringContainsString('could not be found according to given conditions', $output);
        $this->assertStringNotContainsString('will NOT be unloaded', $output);
        $this->assertStringNotContainsString('found under the namespace', $output);
    }

    public function testActionLoadWithGlobalFixture(): void
    {
        $this->_controller->globalFixtures = [
            '\yiiunit\data\console\controllers\fixtures\GlobalFixture',
        ];

        $this->_controller->actionLoad(['First']);

        $this->assertCount(1, FixtureStorage::$globalFixturesData);
        $this->assertCount(1, FixtureStorage::$firstFixtureData);
    }

    public function testActionLoadWithGlobalFixtureWithoutSuffix(): void
    {
        $this->_controller->globalFixtures = [
            '\yiiunit\data\console\controllers\fixtures\Global',
        ];

        $this->_controller->actionLoad(['First']);

        $this->assertCount(1, FixtureStorage::$globalFixturesData);
        $this->assertCount(1, FixtureStorage::$firstFixtureData);
    }

    public function testActionUnloadWithGlobalFixture(): void
    {
        $this->_controller->globalFixtures = [
            '\yiiunit\data\console\controllers\fixtures\GlobalFixture',
        ];

        FixtureStorage::$globalFixturesData[] = 'seeded data';
        FixtureStorage::$firstFixtureData[] = 'seeded data';

        $this->_controller->actionUnload(['First']);

        $this->assertEmpty(FixtureStorage::$globalFixturesData);
        $this->assertEmpty(FixtureStorage::$firstFixtureData);
    }

    public function testActionLoadOutputsLoadedFixtures(): void
    {
        $this->_controller->actionLoad(['First']);

        $output = Console::stripAnsiFormat($this->_controller->flushStdOutBuffer());

        $this->assertStringContainsString('Fixtures were successfully loaded', $output);
        $this->assertStringContainsString('FirstFixture', $output);
    }

    public function testActionUnloadOutputsUnloadedFixtures(): void
    {
        FixtureStorage::$firstFixtureData[] = 'seeded data';

        $this->_controller->actionUnload(['First']);

        $output = Console::stripAnsiFormat($this->_controller->flushStdOutBuffer());

        $this->assertStringContainsString('Fixtures were successfully unloaded', $output);
    }

    public function testActionLoadSubdirFixture(): void
    {
        $this->_controller->actionLoad(['subdir/First']);

        $this->assertCount(1, FixtureStorage::$subdirFirstFixtureData);
        $this->assertEmpty(FixtureStorage::$firstFixtureData);
    }

    public function testActionLoadMultipleFixturesWithExclusions(): void
    {
        $this->_controller->actionLoad(['First', 'subdir/First', '-Second', '-Global', '-subdir/Second']);

        $this->assertCount(1, FixtureStorage::$firstFixtureData);
        $this->assertCount(1, FixtureStorage::$subdirFirstFixtureData);
        $this->assertEmpty(FixtureStorage::$globalFixturesData);
        $this->assertEmpty(FixtureStorage::$secondFixtureData);
        $this->assertEmpty(FixtureStorage::$subdirSecondFixtureData);
    }

    public function testActionUnloadMultipleFixturesWithExclusions(): void
    {
        FixtureStorage::$firstFixtureData[] = 'seeded data';
        FixtureStorage::$secondFixtureData[] = 'seeded data';
        FixtureStorage::$globalFixturesData[] = 'seeded data';
        FixtureStorage::$subdirFirstFixtureData[] = 'seeded data';
        FixtureStorage::$subdirSecondFixtureData[] = 'seeded data';

        $this->_controller->actionUnload(['First', 'subdir/First', '-Second', '-Global', '-subdir/Second']);

        $this->assertEmpty(FixtureStorage::$firstFixtureData);
        $this->assertEmpty(FixtureStorage::$subdirFirstFixtureData);
        $this->assertNotEmpty(FixtureStorage::$globalFixturesData);
        $this->assertNotEmpty(FixtureStorage::$secondFixtureData);
        $this->assertNotEmpty(FixtureStorage::$subdirSecondFixtureData);
    }

    public function testActionLoadAllExceptSubdirFixtures(): void
    {
        $this->_controller->actionLoad(array_merge(
            ['*', '-subdir/First', '-subdir/Second'],
            self::$activeFixtureExclusions
        ));

        $this->assertCount(1, FixtureStorage::$firstFixtureData);
        $this->assertCount(1, FixtureStorage::$secondFixtureData);
        $this->assertCount(1, FixtureStorage::$globalFixturesData);
        $this->assertEmpty(FixtureStorage::$subdirFirstFixtureData);
        $this->assertEmpty(FixtureStorage::$subdirSecondFixtureData);
    }

    public function testActionLoadConfirmOutputWithGlobalFixtures(): void
    {
        $this->_controller->globalFixtures = [
            '\yiiunit\data\console\controllers\fixtures\GlobalFixture',
        ];

        $this->_controller->actionLoad(['First', '-Second']);

        $output = Console::stripAnsiFormat($this->_controller->flushStdOutBuffer());

        $this->assertStringContainsString('Global fixtures will be used', $output);
        $this->assertStringContainsString('Fixtures below will be loaded', $output);
        $this->assertStringContainsString('will NOT be loaded', $output);
    }

    public function testActionUnloadConfirmOutputWithGlobalFixtures(): void
    {
        $this->_controller->globalFixtures = [
            '\yiiunit\data\console\controllers\fixtures\GlobalFixture',
        ];

        FixtureStorage::$firstFixtureData[] = 'seeded data';

        $this->_controller->actionUnload(['First', '-Second']);

        $output = Console::stripAnsiFormat($this->_controller->flushStdOutBuffer());

        $this->assertStringContainsString('Global fixtures will be used', $output);
        $this->assertStringContainsString('Fixtures below will be unloaded', $output);
        $this->assertStringContainsString('will NOT be unloaded', $output);
    }

    public function testActionLoadWithNotFoundFixturesOutputsWarning(): void
    {
        $this->_controller->actionLoad(['First', 'DoesNotExist']);

        $output = Console::stripAnsiFormat($this->_controller->flushStdOutBuffer());

        $this->assertStringContainsString('Some fixtures were not found under path', $output);
        $this->assertStringContainsString('DoesNotExist', $output);
        $this->assertCount(1, FixtureStorage::$firstFixtureData);
    }

    public function testActionUnloadWithNotFoundFixturesOutputsWarning(): void
    {
        FixtureStorage::$firstFixtureData[] = 'seeded data';

        $this->_controller->actionUnload(['First', 'DoesNotExist']);

        $output = Console::stripAnsiFormat($this->_controller->flushStdOutBuffer());

        $this->assertStringContainsString('Some fixtures were not found under path', $output);
        $this->assertStringContainsString('DoesNotExist', $output);
        $this->assertEmpty(FixtureStorage::$firstFixtureData);
    }

    public function testActionLoadReturnsOkExitCode(): void
    {
        $this->assertSame(ExitCode::OK, $this->_controller->actionLoad(['First']));
    }

    public function testActionUnloadReturnsOkExitCode(): void
    {
        FixtureStorage::$firstFixtureData[] = 'seeded data';

        $this->assertSame(ExitCode::OK, $this->_controller->actionUnload(['First']));
    }

    public function testGetFixturePathWithInvalidNamespaceThrowsException(): void
    {
        $this->_controller->namespace = 'nonexistent\invalid\namespace';

        $this->expectException('yii\base\InvalidConfigException');
        $this->expectExceptionMessage('Invalid fixture namespace');

        $this->_controller->actionLoad(['Something']);
    }

    public function testActionLoadNotifiesWithMultipleFixtureClassNames(): void
    {
        $this->_controller->actionLoad(['First', 'Second']);

        $output = Console::stripAnsiFormat($this->_controller->flushStdOutBuffer());

        $this->assertStringContainsString('Fixtures were successfully loaded', $output);
        $this->assertStringContainsString('FirstFixture', $output);
        $this->assertStringContainsString('SecondFixture', $output);
    }

    public function testActionUnloadNotifiesWithMultipleFixtures(): void
    {
        FixtureStorage::$firstFixtureData[] = 'seeded data';
        FixtureStorage::$secondFixtureData[] = 'seeded data';

        $this->_controller->actionUnload(['First', 'Second']);

        $output = Console::stripAnsiFormat($this->_controller->flushStdOutBuffer());

        $this->assertStringContainsString('Fixtures were successfully unloaded', $output);
    }

    public function testActionLoadConfirmOutputWithoutExcluded(): void
    {
        $this->_controller->actionLoad(['Global']);

        $output = Console::stripAnsiFormat($this->_controller->flushStdOutBuffer());

        $this->assertStringContainsString('Fixtures below will be loaded', $output);
        $this->assertStringContainsString('Applying leads to purging', $output);
        $this->assertStringNotContainsString('will NOT be loaded', $output);
    }

    public function testActionUnloadConfirmOutputWithoutExcluded(): void
    {
        FixtureStorage::$globalFixturesData[] = 'seeded data';

        $this->_controller->actionUnload(['Global']);

        $output = Console::stripAnsiFormat($this->_controller->flushStdOutBuffer());

        $this->assertStringContainsString('Fixtures below will be unloaded', $output);
        $this->assertStringNotContainsString('will NOT be unloaded', $output);
    }

    public function testActionLoadConfirmOutputWithExcluded(): void
    {
        $this->_controller->actionLoad(array_merge(
            ['*', '-Second', '-subdir/Second'],
            self::$activeFixtureExclusions
        ));

        $output = Console::stripAnsiFormat($this->_controller->flushStdOutBuffer());

        $this->assertStringContainsString('will NOT be loaded', $output);
        $this->assertStringContainsString('Second', $output);
    }

    public function testActionUnloadConfirmOutputWithExcluded(): void
    {
        FixtureStorage::$firstFixtureData[] = 'seeded data';
        FixtureStorage::$secondFixtureData[] = 'seeded data';
        FixtureStorage::$globalFixturesData[] = 'seeded data';

        $this->_controller->actionUnload(array_merge(
            ['*', '-Second', '-subdir/Second'],
            self::$activeFixtureExclusions
        ));

        $output = Console::stripAnsiFormat($this->_controller->flushStdOutBuffer());

        $this->assertStringContainsString('will NOT be unloaded', $output);
        $this->assertStringContainsString('Second', $output);
    }

    public function testActionLoadUnloadsBeforeLoading(): void
    {
        FixtureStorage::$firstFixtureData[] = 'old data';

        $this->_controller->actionLoad(['First']);

        $this->assertCount(1, FixtureStorage::$firstFixtureData);
        $this->assertSame('some data set for first fixture', FixtureStorage::$firstFixtureData[0]);
    }

    public function testActionLoadConfirmOutputIncludesNamespace(): void
    {
        $this->_controller->actionLoad(['First']);

        $output = Console::stripAnsiFormat($this->_controller->flushStdOutBuffer());

        $this->assertStringContainsString('yiiunit\data\console\controllers\fixtures', $output);
    }

    public function testActionUnloadConfirmOutputIncludesNamespace(): void
    {
        FixtureStorage::$firstFixtureData[] = 'seeded data';

        $this->_controller->actionUnload(['First']);

        $output = Console::stripAnsiFormat($this->_controller->flushStdOutBuffer());

        $this->assertStringContainsString('yiiunit\data\console\controllers\fixtures', $output);
    }

    public function testActionLoadWithoutGlobalFixturesDoesNotOutputGlobalSection(): void
    {
        $this->_controller->actionLoad(['First']);

        $output = Console::stripAnsiFormat($this->_controller->flushStdOutBuffer());

        $this->assertStringNotContainsString('Global fixtures will be used', $output);
    }

    public function testActionUnloadWithoutGlobalFixturesDoesNotOutputGlobalSection(): void
    {
        FixtureStorage::$firstFixtureData[] = 'seeded data';

        $this->_controller->actionUnload(['First']);

        $output = Console::stripAnsiFormat($this->_controller->flushStdOutBuffer());

        $this->assertStringNotContainsString('Global fixtures will be used', $output);
    }

    public function testActionLoadReturnsOkWhenConfirmDenied(): void
    {
        $controller = new DenyingFixtureController('fixture', Yii::$app);
        $controller->interactive = false;
        $controller->globalFixtures = [];
        $controller->namespace = 'yiiunit\data\console\controllers\fixtures';

        $result = $controller->actionLoad(['First']);

        $this->assertSame(ExitCode::OK, $result);
        $this->assertEmpty(FixtureStorage::$firstFixtureData);
    }

    public function testActionUnloadReturnsOkWhenConfirmDenied(): void
    {
        $controller = new DenyingFixtureController('fixture', Yii::$app);
        $controller->interactive = false;
        $controller->globalFixtures = [];
        $controller->namespace = 'yiiunit\data\console\controllers\fixtures';

        FixtureStorage::$firstFixtureData[] = 'seeded data';

        $result = $controller->actionUnload(['First']);

        $this->assertSame(ExitCode::OK, $result);
        $this->assertNotEmpty(FixtureStorage::$firstFixtureData);
    }

    public function testGetFixturesConfigThrowsExceptionForUnresolvableClass(): void
    {
        $this->_controller->globalFixtures = [
            '\yiiunit\data\console\controllers\fixtures\CompletelyNonExistentClass',
        ];

        $this->expectException('yii\console\Exception');
        $this->expectExceptionMessage('Neither fixture');

        $this->_controller->actionLoad(['First']);
    }
}

class BufferedFixtureController extends FixtureController
{
    use StdOutBufferControllerTrait;
}

class DenyingFixtureController extends FixtureController
{
    use StdOutBufferControllerTrait;

    public function confirm($message, $default = false)
    {
        return false;
    }
}
