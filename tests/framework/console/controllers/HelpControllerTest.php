<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\console\controllers;

use Yii;
use yii\base\Module;
use yii\console\controllers\HelpController;
use yii\console\Exception;
use yii\helpers\Console;
use yiiunit\TestCase;

/**
 * Unit test for [[\yii\console\controllers\HelpController]].
 * @see HelpController
 * @group console
 */
class HelpControllerTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->mockApplication();
    }

    /**
     * Creates controller instance.
     * @return BufferedHelpController
     */
    protected function createController()
    {
        $module = $this->getMockBuilder(Module::class)
            ->addMethods(['fake'])
            ->setConstructorArgs(['console'])
            ->getMock();

        return new BufferedHelpController('help', $module);
    }

    /**
     * Emulates running controller action.
     * @param string $actionID id of action to be run.
     * @param array $actionParams action arguments.
     * @return string command output.
     */
    protected function runControllerAction($actionID, $actionParams = [])
    {
        $controller = $this->createController();
        $action = $controller->createAction($actionID);
        $action->runWithParams($actionParams);
        return $controller->flushStdOutBuffer();
    }

    public function testModuleControllersList(): void
    {
        $this->mockApplication([
            'enableCoreCommands' => false,
            'modules' => [
                'magic' => 'yiiunit\data\modules\magic\Module',
            ],
        ]);
        $result = Console::stripAnsiFormat($this->runControllerAction('list'));
        $this->assertEqualsWithoutLE(<<<'STRING'
help
help/index
help/list
help/list-action-options
help/usage
magic/e-tag/delete
magic/e-tag/list-e-tags
magic/subFolder/sub/test

STRING
            , $result);
    }

    public function testActionList(): void
    {
        $this->mockApplication([
            'enableCoreCommands' => false,
            'controllerMap' => [
                'migrate' => 'yii\console\controllers\MigrateController',
                'cache' => 'yii\console\controllers\CacheController',
            ],
        ]);
        $result = Console::stripAnsiFormat($this->runControllerAction('list'));
        $this->assertEqualsWithoutLE(<<<'STRING'
cache
cache/flush
cache/flush-all
cache/flush-schema
cache/index
help
help/index
help/list
help/list-action-options
help/usage
migrate
migrate/create
migrate/down
migrate/fresh
migrate/history
migrate/mark
migrate/new
migrate/redo
migrate/to
migrate/up

STRING
            , $result);
    }

    public function testActionListActionOptions(): void
    {
        $this->mockApplication([
            'enableCoreCommands' => false,
            'controllerMap' => [
                'migrate' => 'yii\console\controllers\MigrateController',
                'cache' => 'yii\console\controllers\CacheController',
            ],
        ]);
        $result = Console::stripAnsiFormat($this->runControllerAction('list-action-options', ['action' => 'help/list-action-options']));
        $this->assertEqualsWithoutLE(<<<'STRING'
action: route to action

--interactive: whether to run the command interactively.
--color: whether to enable ANSI color in the output.If not set, ANSI color will only be enabled for terminals that support it.
--help: whether to display help information about current command.
--silent-exit-on-exception: if true - script finish with `ExitCode\:\:OK` in case of exception.false - `ExitCode\:\:UNSPECIFIED_ERROR`.Default\: `YII_ENV_TEST`

STRING
            , $result);
    }

    public function testActionUsage(): void
    {
        $this->mockApplication([
            'enableCoreCommands' => false,
            'controllerMap' => [
                'migrate' => 'yii\console\controllers\MigrateController',
                'cache' => 'yii\console\controllers\CacheController',
            ],
        ]);
        $result = Console::stripAnsiFormat($this->runControllerAction('usage', ['action' => 'help/list-action-options']));
        $this->assertEqualsWithoutLE(<<<'STRING'
bootstrap.php help/list-action-options <action>

STRING
            , $result);
    }

    public function testActionIndex(): void
    {
        $result = Console::stripAnsiFormat($this->runControllerAction('index'));
        $this->assertStringContainsString('This is Yii version ', $result);
        $this->assertStringContainsString('The following commands are available:', $result);
        $this->assertStringContainsString('To see the help of each command, enter:', $result);
        $this->assertStringContainsString('bootstrap.php help', $result);
    }

    public function testActionIndexWithHelpCommand(): void
    {
        $result = Console::stripAnsiFormat($this->runControllerAction('index', ['command' => 'help/index']));
        $this->assertStringContainsString('Displays available commands or the detailed information', $result);
        $this->assertStringContainsString('bootstrap.php help [command] [...options...]', $result);
        $this->assertStringContainsString('--appconfig: string', $result);
        $this->assertStringContainsString('- command: string', $result);
        $this->assertStringContainsString('--color: boolean, 0 or 1', $result);
        $this->assertStringContainsString('--help, -h: boolean, 0 or 1', $result);
        $this->assertStringContainsString('--interactive: boolean, 0 or 1 (defaults to 1)', $result);
    }

    public function testActionIndexWithServeCommand(): void
    {
        $result = Console::stripAnsiFormat($this->runControllerAction('index', ['command' => 'serve']));
        $this->assertStringContainsString('Runs PHP built-in web server', $result);
        $this->assertStringContainsString('bootstrap.php serve [address] [...options...]', $result);
        $this->assertStringContainsString('- address: string (defaults to \'localhost\')', $result);
        $this->assertStringContainsString('--appconfig: string', $result);
        $this->assertStringContainsString('--color: boolean, 0 or 1', $result);
        $this->assertStringContainsString('--docroot, -t: string (defaults to \'@app/web\')', $result);
        $this->assertStringContainsString('--help, -h: boolean, 0 or 1', $result);
        $this->assertStringContainsString('--interactive: boolean, 0 or 1 (defaults to 1)', $result);
        $this->assertStringContainsString('--port, -p: int (defaults to 8080)', $result);
        $this->assertStringContainsString('--router, -r: string', $result);
    }

    public function testActionListContainsNoEmptyCommands(): void
    {
        $this->mockApplication([
            'enableCoreCommands' => false,
            'controllerNamespace' => 'yiiunit\data\console\controllers',
        ]);
        $result = Console::stripAnsiFormat($this->runControllerAction('list'));
        $this->assertStringNotContainsString("fake-empty\n", $result);
        $this->assertStringNotContainsString("fake-no-default\n", $result);
        $this->assertStringContainsString("fake-no-default/index\n", $result);
    }

    public function testActionIndexContainsNoEmptyCommands(): void
    {
        $this->mockApplication([
            'enableCoreCommands' => false,
            'controllerNamespace' => 'yiiunit\data\console\controllers',
        ]);
        $result = Console::stripAnsiFormat($this->runControllerAction('index'));
        $this->assertStringNotContainsString('- fake-empty', $result);
        $this->assertStringContainsString('- fake-no-default', $result);
        $this->assertStringContainsString('    fake-no-default/index', $result);
        $this->assertStringNotContainsString('    fake-no-default/index (default)', $result);
    }

    public function testActionIndexWithUnknownCommandThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No help for unknown command');

        $this->runControllerAction('index', ['command' => 'unknown-nonexistent']);
    }

    public function testActionIndexWithCommandNameShowsCommandHelp(): void
    {
        $this->mockApplication([
            'enableCoreCommands' => false,
            'controllerMap' => [
                'cache' => 'yii\console\controllers\CacheController',
            ],
        ]);
        $result = Console::stripAnsiFormat($this->runControllerAction('index', ['command' => 'cache']));
        $this->assertStringContainsString('DESCRIPTION', $result);
        $this->assertStringContainsString('SUB-COMMANDS', $result);
        $this->assertStringContainsString('cache/flush', $result);
        $this->assertStringContainsString('cache/flush-all', $result);
        $this->assertStringContainsString('cache/flush-schema', $result);
        $this->assertStringContainsString('cache/index', $result);
        $this->assertStringContainsString('(default)', $result);
        $this->assertStringContainsString('To see the detailed information about individual sub-commands, enter:', $result);
    }

    public function testActionIndexWithNonDefaultSubCommand(): void
    {
        $result = Console::stripAnsiFormat($this->runControllerAction('index', ['command' => 'help/list']));
        $this->assertStringContainsString('USAGE', $result);
        $this->assertStringContainsString('bootstrap.php help/list', $result);
        $this->assertStringNotContainsString('bootstrap.php help [', $result);
    }

    public function testActionIndexWithSubCommandHavingRequiredArg(): void
    {
        $result = Console::stripAnsiFormat($this->runControllerAction('index', ['command' => 'help/list-action-options']));
        $this->assertStringContainsString('<action>', $result);
        $this->assertStringContainsString('(required)', $result);
    }

    public function testActionIndexWithUnknownSubCommandThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No help for unknown sub-command');

        $this->runControllerAction('index', ['command' => 'help/nonexistent-action']);
    }

    public function testActionIndexNoCommandsAvailable(): void
    {
        $emptyDir = sys_get_temp_dir() . '/yii2_test_empty_controllers_' . getmypid();
        $previousEmptyNsAlias = Yii::getAlias('@emptyNs', false);

        try {
            @mkdir($emptyDir, 0777, true);
            Yii::setAlias('@emptyNs', $emptyDir);
            $this->mockApplication([
                'enableCoreCommands' => false,
                'controllerNamespace' => 'emptyNs',
            ]);
            Yii::$app->controllerMap = [];

            $controller = new BufferedHelpController('help', Yii::$app);
            $controller->runAction('index');
            $result = Console::stripAnsiFormat($controller->flushStdOutBuffer());

            $this->assertStringContainsString('No commands are found.', $result);
            $this->assertStringNotContainsString('The following commands are available:', $result);
        } finally {
            Yii::setAlias('@emptyNs', $previousEmptyNsAlias === false ? null : $previousEmptyNsAlias);
            @rmdir($emptyDir);
        }
    }

    public function testActionListActionOptionsWithUnknownCommand(): void
    {
        $result = $this->runControllerAction('list-action-options', ['action' => 'unknown-nonexistent']);
        $this->assertSame('', $result);
    }

    public function testActionListActionOptionsWithNonExistentAction(): void
    {
        $result = $this->runControllerAction('list-action-options', ['action' => 'help/nonexistent-action']);
        $this->assertSame('', $result);
    }

    public function testActionUsageWithUnknownCommand(): void
    {
        $result = $this->runControllerAction('usage', ['action' => 'unknown-nonexistent']);
        $this->assertSame('', $result);
    }

    public function testActionUsageWithNonExistentAction(): void
    {
        $result = $this->runControllerAction('usage', ['action' => 'help/nonexistent-action']);
        $this->assertSame('', $result);
    }

    public function testActionUsageWithDefaultAction(): void
    {
        $result = Console::stripAnsiFormat($this->runControllerAction('usage', ['action' => 'help']));
        $this->assertStringContainsString('bootstrap.php help', $result);
        $this->assertStringNotContainsString('bootstrap.php help/index', $result);
    }

    public function testActionUsageWithOptionalArgument(): void
    {
        $result = Console::stripAnsiFormat($this->runControllerAction('usage', ['action' => 'help/index']));
        $this->assertStringContainsString('[command]', $result);
        $this->assertStringNotContainsString('<command>', $result);
    }

    public function testValidateControllerClassWithNonExistingClass(): void
    {
        $controller = $this->createController();
        $method = new \ReflectionMethod($controller, 'validateControllerClass');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $this->assertFalse($method->invoke($controller, 'non\existent\ClassName'));
    }

    public function testValidateControllerClassWithAbstractClass(): void
    {
        $controller = $this->createController();
        $method = new \ReflectionMethod($controller, 'validateControllerClass');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $this->assertFalse($method->invoke($controller, 'yii\console\Controller'));
    }

    public function testValidateControllerClassWithValidConsoleController(): void
    {
        $controller = $this->createController();
        $method = new \ReflectionMethod($controller, 'validateControllerClass');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $this->assertTrue($method->invoke($controller, 'yii\console\controllers\HelpController'));
    }

    public function testValidateControllerClassWithNonControllerClass(): void
    {
        $controller = $this->createController();
        $method = new \ReflectionMethod($controller, 'validateControllerClass');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $this->assertFalse($method->invoke($controller, 'yii\base\Component'));
    }

    public function testGetDefaultHelpHeader(): void
    {
        $controller = $this->createController();
        $method = new \ReflectionMethod($controller, 'getDefaultHelpHeader');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $result = $method->invoke($controller);
        $this->assertStringContainsString('This is Yii version ', $result);
        $this->assertStringContainsString(\Yii::getVersion(), $result);
    }

    public function testFormatOptionHelpWithEmptyDocAndComment(): void
    {
        $controller = $this->createController();
        $method = new \ReflectionMethod($controller, 'formatOptionHelp');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $result = $method->invoke($controller, '--name', false, '', null, '');
        $this->assertSame('--name', $result);
    }

    public function testFormatOptionHelpWithEmptyTypeAndComment(): void
    {
        $controller = $this->createController();
        $method = new \ReflectionMethod($controller, 'formatOptionHelp');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $result = $method->invoke($controller, '--name', false, '', null, 'some description');
        $this->assertSame('--name: some description', $result);
    }

    public function testFormatOptionHelpWithArrayDefault(): void
    {
        $controller = $this->createController();
        $method = new \ReflectionMethod($controller, 'formatOptionHelp');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $result = $method->invoke($controller, '--items', false, 'array', [], 'list of items');
        $this->assertStringContainsString('array', $result);
        $this->assertStringContainsString('list of items', $result);
        $this->assertStringNotContainsString('defaults to', $result);
    }

    public function testFormatOptionHelpRequired(): void
    {
        $controller = $this->createController();
        $method = new \ReflectionMethod($controller, 'formatOptionHelp');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $result = $method->invoke($controller, '--name', true, 'string', null, '');
        $this->assertStringContainsString('(required)', $result);
    }

    public function testFormatOptionAliasesWithAlias(): void
    {
        $controller = $this->createController();
        $method = new \ReflectionMethod($controller, 'formatOptionAliases');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $serveController = new \yii\console\controllers\ServeController('serve', Yii::$app);
        $result = $method->invoke($controller, $serveController, 'port');
        $this->assertSame(', -p', $result);
    }

    public function testFormatOptionAliasesWithNoAlias(): void
    {
        $controller = $this->createController();
        $method = new \ReflectionMethod($controller, 'formatOptionAliases');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $serveController = new \yii\console\controllers\ServeController('serve', Yii::$app);
        $result = $method->invoke($controller, $serveController, 'color');
        $this->assertSame('', $result);
    }

    public function testGetCommandsFiltersOutNonConsoleControllers(): void
    {
        $this->mockApplication([
            'enableCoreCommands' => false,
            'controllerMap' => [
                'web-only' => 'yii\web\Controller',
            ],
        ]);
        $controller = $this->createController();
        $commands = $controller->getCommands();
        $this->assertNotContains('web-only', $commands);
    }

    public function testGetActionsIncludesActionMethodsAndExternalActions(): void
    {
        $controller = $this->createController();
        $actions = $controller->getActions($controller);

        $this->assertSame(['index', 'list', 'list-action-options', 'usage'], $actions);
    }

    public function testGetModuleCommandsSkipsUnavailableModule(): void
    {
        $this->mockApplication([
            'enableCoreCommands' => false,
            'modules' => [
                'broken' => null,
                'magic' => [
                    'class' => 'yiiunit\data\modules\magic\Module',
                ],
            ],
        ]);

        $result = Console::stripAnsiFormat($this->runControllerAction('list'));

        $this->assertStringContainsString('magic/e-tag/delete', $result);
        $this->assertStringNotContainsString("broken\n", $result);
    }

    public function testGetModuleCommandsWithNestedModules(): void
    {
        $this->mockApplication([
            'enableCoreCommands' => false,
            'modules' => [
                'magic' => [
                    'class' => 'yiiunit\data\modules\magic\Module',
                ],
            ],
        ]);
        $result = Console::stripAnsiFormat($this->runControllerAction('list'));
        $this->assertStringContainsString('magic/e-tag/', $result);
        $this->assertStringContainsString('magic/subFolder/sub/', $result);
    }
}


class BufferedHelpController extends HelpController
{
    use StdOutBufferControllerTrait;
}
