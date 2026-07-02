<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\console\controllers;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\console\controllers\BaseMigrateController;
use yii\console\Exception;
use yii\console\ExitCode;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;
use yiiunit\TestCase;

/**
 * This trait provides unit tests shared by the different migration controllers implementations.
 * @see BaseMigrateController
 *
 * @template TMigrateController of BaseMigrateController
 * @phpstan-require-extends TestCase
 */
trait MigrateControllerTestTrait
{
    /**
     * @var class-string<TMigrateController> name of the migration controller class, which is under test.
     */
    protected $migrateControllerClass;
    /**
     * @var string name of the migration base class.
     */
    protected $migrationBaseClass;
    /**
     * @var string test migration path.
     */
    protected $migrationPath;
    /**
     * @var string test migration namespace
     */
    protected $migrationNamespace;
    /**
     * @var int|null migration controller exit code
     */
    protected $migrationExitCode;

    public function getExitCode()
    {
        return $this->migrationExitCode;
    }

    public function setUpMigrationPath(): void
    {
        $this->migrationNamespace = 'yiiunit\runtime\test_migrations';
        $this->migrationPath = Yii::getAlias('@yiiunit/runtime/test_migrations');
        FileHelper::createDirectory($this->migrationPath);
        if (!file_exists($this->migrationPath)) {
            $this->markTestIncomplete('Unit tests runtime directory should have writable permissions!');
        }
    }

    public function tearDownMigrationPath(): void
    {
        FileHelper::removeDirectory($this->migrationPath);
        FileHelper::removeDirectory(Yii::getAlias('@yiiunit/runtime/app_migrations'));
        FileHelper::removeDirectory(Yii::getAlias('@yiiunit/runtime/extension_migrations'));
    }

    /**
     * @return array applied migration entries
     */
    abstract protected function getMigrationHistory();

    /**
     * Creates test migrate controller instance.
     * @param array $config controller configuration.
     * @return TMigrateController migrate command instance.
     */
    protected function createMigrateController(array $config = [])
    {
        $module = $this->getMockBuilder(Module::class)
            ->addMethods(['fake'])
            ->setConstructorArgs(['console'])
            ->getMock();

        $class = $this->migrateControllerClass;
        $migrateController = new $class('migrate', $module);
        $migrateController->interactive = false;
        $migrateController->migrationPath = $this->migrationPath;

        return Yii::configure($migrateController, $config);
    }

    /**
     * Emulates running of the migrate controller action.
     * @param string $actionID id of action to be run.
     * @param array $args action arguments.
     * @param array $config controller configuration.
     * @return string command output.
     */
    protected function runMigrateControllerAction($actionID, array $args = [], array $config = [])
    {
        $controller = $this->createMigrateController($config);
        ob_start();
        ob_implicit_flush(false);
        $this->migrationExitCode = $controller->run($actionID, $args);

        return ob_get_clean();
    }

    /**
     * @param string $name
     * @param string|null $date
     * @param string|null $path
     * @return string generated class name
     */
    protected function createMigration($name, $date = null, $path = null)
    {
        if ($date === null) {
            $date = gmdate('ymd_His');
        }
        $class = 'm' . $date . '_' . $name;
        $baseClass = $this->migrationBaseClass;

        $code = <<<CODE
<?php

class {$class} extends {$baseClass}
{
    public function up()
    {
    }

    public function down()
    {
    }
}
CODE;
        file_put_contents(($path ? Yii::getAlias($path) : $this->migrationPath) . DIRECTORY_SEPARATOR . $class . '.php', $code);
        return $class;
    }

    /**
     * @param string $name
     * @param string|null $date
     * @return string generated class name
     */
    protected function createNamespaceMigration($name, $date = null)
    {
        if ($date === null) {
            $date = gmdate('ymdHis');
        }
        $class = 'M' . $date . ucfirst($name);
        $baseClass = $this->migrationBaseClass;
        $namespace = $this->migrationNamespace;

        $code = <<<CODE
<?php

namespace {$namespace};

class {$class} extends \\{$baseClass}
{
    public function up()
    {
    }

    public function down()
    {
    }
}
CODE;
        file_put_contents($this->migrationPath . DIRECTORY_SEPARATOR . $class . '.php', $code);
        return $class;
    }

    protected function createFailingUpMigration($name, $date = null)
    {
        if ($date === null) {
            $date = gmdate('ymd_His');
        }
        $class = 'm' . $date . '_' . $name;
        $baseClass = $this->migrationBaseClass;

        $code = <<<CODE
<?php

class {$class} extends {$baseClass}
{
    public function up()
    {
        return false;
    }

    public function down()
    {
    }
}
CODE;
        file_put_contents($this->migrationPath . DIRECTORY_SEPARATOR . $class . '.php', $code);
        return $class;
    }

    protected function createFailingDownMigration($name, $date = null)
    {
        if ($date === null) {
            $date = gmdate('ymd_His');
        }
        $class = 'm' . $date . '_' . $name;
        $baseClass = $this->migrationBaseClass;

        $code = <<<CODE
<?php

class {$class} extends {$baseClass}
{
    public function up()
    {
    }

    public function down()
    {
        return false;
    }
}
CODE;
        file_put_contents($this->migrationPath . DIRECTORY_SEPARATOR . $class . '.php', $code);
        return $class;
    }

    /**
     * Change class name migration to $class.
     * @param string $class name class
     * @return string content generated class migration
     * @see https://github.com/yiisoft/yii2/pull/10213
     */
    protected function parseNameClassMigration($class)
    {
        $files = FileHelper::findFiles($this->migrationPath);
        $file = file_get_contents($files[0]);
        if (preg_match('/class (m\d+_?\d+_?.*) extends Migration/i', $file, $match)) {
            $file = str_replace($match[1], $class, $file);
        }
        $this->tearDownMigrationPath();
        return $file;
    }

    /**
     * Checks if applied migration history matches expected one.
     * @param array $expectedMigrations migration names in expected order
     * @param string $message failure message
     */
    protected function assertMigrationHistory(array $expectedMigrations, $message = '')
    {
        $success = true;
        $migrationHistory = $this->getMigrationHistory();
        $appliedMigrations = $migrationHistory;
        foreach ($expectedMigrations as $expectedMigrationName) {
            $appliedMigration = array_shift($appliedMigrations);
            if (!StringHelper::matchWildcard(strtr($expectedMigrationName, ['\\' => DIRECTORY_SEPARATOR]), strtr($appliedMigration['version'], ['\\' => DIRECTORY_SEPARATOR]))) {
                $success = false;
                break;
            }
        }
        if (!$success) {
            $message .= "\n";
            $message .= 'Expected: ' . var_export($expectedMigrations, true) . "\n";

            $actualMigrations = [];
            foreach ($migrationHistory as $row) {
                $actualMigrations[] = $row['version'];
            }
            $message .= 'Actual: ' . var_export($actualMigrations, true) . "\n";
        }
        $this->assertTrue($success, $message);
    }

    // Tests :

    public function testCreate(): void
    {
        $migrationName = 'test_migration';
        $this->runMigrateControllerAction('create', [$migrationName]);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $files = FileHelper::findFiles($this->migrationPath);
        $this->assertCount(1, $files, 'Unable to create new migration!');
        $this->assertStringContainsString($migrationName, basename($files[0]), 'Wrong migration name!');
    }

    public function testUp(): void
    {
        $this->createMigration('test_up1');
        $this->createMigration('test_up2');

        $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $this->assertMigrationHistory(['m*_base', 'm*_test_up1', 'm*_test_up2']);
    }

    /**
     * @depends testUp
     */
    public function testUpCount(): void
    {
        $this->createMigration('test_down1');
        $this->createMigration('test_down2');

        $this->runMigrateControllerAction('up', [1]);
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $this->assertMigrationHistory(['m*_base', 'm*_test_down1']);
    }

    /**
     * @depends testUp
     */
    public function testDownCount(): void
    {
        $this->createMigration('test_down_count1');
        $this->createMigration('test_down_count2');

        $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->runMigrateControllerAction('down', [1]);
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $this->assertMigrationHistory(['m*_base', 'm*_test_down_count1']);
    }

    /**
     * @depends testDownCount
     */
    public function testDownAll(): void
    {
        $this->createMigration('test_down_all1');
        $this->createMigration('test_down_all2');

        $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->runMigrateControllerAction('down', ['all']);
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $this->assertMigrationHistory(['m*_base']);
    }

    /**
     * @depends testUp
     */
    public function testHistory(): void
    {
        $output = $this->runMigrateControllerAction('history');
        $this->assertStringContainsString('No migration', $output);

        $this->createMigration('test_history1');
        $this->createMigration('test_history2');
        $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $output = $this->runMigrateControllerAction('history');
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertStringContainsString('_test_history1', $output);
        $this->assertStringContainsString('_test_history2', $output);
    }

    /**
     * @depends testUp
     */
    public function testNew(): void
    {
        $this->createMigration('test_new1');

        $output = $this->runMigrateControllerAction('new');
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertStringContainsString('_test_new1', $output);

        $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $output = $this->runMigrateControllerAction('new');
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertStringNotContainsString('_test_new1', $output);
    }

    public function testMark(): void
    {
        $version = '010101_000001';
        $this->createMigration('test_mark1', $version);

        $this->runMigrateControllerAction('mark', [$version]);
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $this->assertMigrationHistory(['m*_base', 'm*_test_mark1']);
    }

    public function testMarkBase(): void
    {
        $version = '010101_000001';
        $this->createMigration('test_mark1', $version);

        $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertMigrationHistory(['m*_base', 'm*_test_mark1']);

        $this->runMigrateControllerAction('mark', [BaseMigrateController::BASE_MIGRATION]);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertMigrationHistory(['m*_base']);
    }

    public function testTo(): void
    {
        $version = '020202_000001';
        $this->createMigration('to1', $version);

        $this->runMigrateControllerAction('to', [$version]);
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $this->assertMigrationHistory(['m*_base', 'm*_to1']);
    }

    /**
     * @depends testUp
     */
    public function testRedo(): void
    {
        $this->createMigration('test_redo1');
        $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $this->runMigrateControllerAction('redo');
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $this->assertMigrationHistory(['m*_base', 'm*_test_redo1']);
    }

    // namespace :

    /**
     * @depends testCreate
     */
    public function testNamespaceCreate(): void
    {
        // default namespace apply :
        $migrationName = 'testDefaultNamespace';
        $this->runMigrateControllerAction('create', [$migrationName], [
            'migrationPath' => null,
            'migrationNamespaces' => [$this->migrationNamespace],
        ]);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $files = FileHelper::findFiles($this->migrationPath);
        $fileContent = file_get_contents($files[0]);
        $this->assertStringContainsString("namespace {$this->migrationNamespace};", $fileContent);
        $this->assertMatchesRegularExpression('/class M[0-9]{12}' . ucfirst($migrationName) . '/s', $fileContent);
        unlink($files[0]);

        // namespace specify :
        $migrationName = 'test_namespace_specify';
        $this->runMigrateControllerAction('create', [$this->migrationNamespace . '\\' . $migrationName], [
            'migrationPath' => $this->migrationPath,
            'migrationNamespaces' => [$this->migrationNamespace],
        ]);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $files = FileHelper::findFiles($this->migrationPath);
        $fileContent = file_get_contents($files[0]);
        $this->assertStringContainsString("namespace {$this->migrationNamespace};", $fileContent);
        unlink($files[0]);

        // no namespace:
        $migrationName = 'test_no_namespace';
        $this->runMigrateControllerAction('create', [$migrationName], [
            'migrationPath' => $this->migrationPath,
            'migrationNamespaces' => [$this->migrationNamespace],
        ]);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $files = FileHelper::findFiles($this->migrationPath);
        $fileContent = file_get_contents($files[0]);
        $this->assertStringNotContainsString("namespace {$this->migrationNamespace};", $fileContent);
    }

    /**
     * @depends testUp
     */
    public function testNamespaceUp(): void
    {
        $this->createNamespaceMigration('nsTest1');
        $this->createNamespaceMigration('nsTest2');

        $this->runMigrateControllerAction('up', [], [
            'migrationPath' => null,
            'migrationNamespaces' => [$this->migrationNamespace],
        ]);
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $this->assertMigrationHistory([
            'm*_*_base',
            $this->migrationNamespace . '\\M*NsTest1',
            $this->migrationNamespace . '\\M*NsTest2',
        ]);
    }

    /**
     * @depends testNamespaceUp
     * @depends testDownCount
     */
    public function testNamespaceDownCount(): void
    {
        $this->createNamespaceMigration('down1');
        $this->createNamespaceMigration('down2');

        $controllerConfig = [
            'migrationPath' => null,
            'migrationNamespaces' => [$this->migrationNamespace],
        ];
        $this->runMigrateControllerAction('up', [], $controllerConfig);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->runMigrateControllerAction('down', [1], $controllerConfig);
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $this->assertMigrationHistory([
            'm*_*_base',
            $this->migrationNamespace . '\\M*Down1',
        ]);
    }

    /**
     * @depends testNamespaceUp
     * @depends testHistory
     */
    public function testNamespaceHistory(): void
    {
        $controllerConfig = [
            'migrationPath' => null,
            'migrationNamespaces' => [$this->migrationNamespace],
        ];

        $output = $this->runMigrateControllerAction('history', [], $controllerConfig);
        $this->assertStringContainsString('No migration', $output);

        $this->createNamespaceMigration('history1');
        $this->createNamespaceMigration('history2');
        $this->runMigrateControllerAction('up', [], $controllerConfig);
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $output = $this->runMigrateControllerAction('history', [], $controllerConfig);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertMatchesRegularExpression('/' . preg_quote($this->migrationNamespace) . '.*History1/s', $output);
        $this->assertMatchesRegularExpression('/' . preg_quote($this->migrationNamespace) . '.*History2/s', $output);
    }

    /**
     * @depends testMark
     */
    public function testNamespaceMark(): void
    {
        $controllerConfig = [
            'migrationPath' => null,
            'migrationNamespaces' => [$this->migrationNamespace],
        ];

        $version = '010101000001';
        $this->createNamespaceMigration('mark1', $version);

        $this->runMigrateControllerAction('mark', [$this->migrationNamespace . '\\M' . $version], $controllerConfig);
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $this->assertMigrationHistory(['m*_base', $this->migrationNamespace . '\\M*Mark1']);
    }

    /**
     * @depends testTo
     */
    public function testNamespaceTo(): void
    {
        $controllerConfig = [
            'migrationPath' => null,
            'migrationNamespaces' => [$this->migrationNamespace],
        ];

        $version = '020202000020';
        $this->createNamespaceMigration('to1', $version);

        $this->runMigrateControllerAction('to', [$this->migrationNamespace . '\\M' . $version], $controllerConfig);
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $this->assertMigrationHistory(['m*_base', $this->migrationNamespace . '\\M*To1']);
    }

    /**
     * Test migration with using multiple migration paths and namespaces.
     */
    public function testCombinedMigrationProcess(): void
    {
        FileHelper::createDirectory(Yii::getAlias('@yiiunit/runtime/app_migrations'));
        FileHelper::createDirectory(Yii::getAlias('@yiiunit/runtime/extension_migrations'));
        $controllerConfig = [
            'migrationPath' => [$appPath = '@yiiunit/runtime/app_migrations', $extensionPath = '@yiiunit/runtime/extension_migrations'],
            'migrationNamespaces' => [$this->migrationNamespace],
        ];

        $this->createMigration('app_migration1', '010101_000001', $appPath);
        $this->createMigration('ext_migration1', '010101_000002', $extensionPath);
        $this->createMigration('app_migration2', '010101_000003', $appPath);
        $this->createNamespaceMigration('NsMigration', '010101000004');

        // yii migrate/up 1
        $this->runMigrateControllerAction('up', [1], $controllerConfig);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertMigrationHistory(['m*_base', 'm010101_000001_app_migration1']);

        // yii migrate/up
        $this->runMigrateControllerAction('up', [], $controllerConfig);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertMigrationHistory([
            'm*_base',
            'm010101_000001_app_migration1',
            'm010101_000002_ext_migration1',
            'm010101_000003_app_migration2',
            $this->migrationNamespace . '\\M010101000004NsMigration',
        ]);

        // yii migrate/to m010101_000002_ext_migration1
        $this->runMigrateControllerAction('to', ['m010101_000002_ext_migration1'], $controllerConfig);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertMigrationHistory([
            'm*_base',
            'm010101_000001_app_migration1',
        ]);

        // yii migrate/mark M010101000004NsMigration
        $this->runMigrateControllerAction('mark', ['m010101_000003_app_migration2'], $controllerConfig);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertMigrationHistory([
            'm*_base',
            'm010101_000001_app_migration1',
            'm010101_000002_ext_migration1',
            'm010101_000003_app_migration2',
        ]);

        // yii migrate/up
        $this->runMigrateControllerAction('up', [], $controllerConfig);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertMigrationHistory([
            'm*_base',
            'm010101_000001_app_migration1',
            'm010101_000002_ext_migration1',
            'm010101_000003_app_migration2',
            $this->migrationNamespace . '\\M010101000004NsMigration',
        ]);

        // yii migrate/redo 2
        $this->runMigrateControllerAction('redo', [2], $controllerConfig);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertMigrationHistory([
            'm*_base',
            'm010101_000001_app_migration1',
            'm010101_000002_ext_migration1',
            'm010101_000003_app_migration2',
            $this->migrationNamespace . '\\M010101000004NsMigration',
        ]);

        // yii migrate/down
        $this->runMigrateControllerAction('down', [], $controllerConfig);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertMigrationHistory([
            'm*_base',
            'm010101_000001_app_migration1',
            'm010101_000002_ext_migration1',
            'm010101_000003_app_migration2',
        ]);

        // yii migrate/redo
        $this->runMigrateControllerAction('redo', [], $controllerConfig);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertMigrationHistory([
            'm*_base',
            'm010101_000001_app_migration1',
            'm010101_000002_ext_migration1',
            'm010101_000003_app_migration2',
        ]);

        // yii migrate/down 2
        $this->runMigrateControllerAction('down', [2], $controllerConfig);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertMigrationHistory([
            'm*_base',
            'm010101_000001_app_migration1',
        ]);

        // yii migrate/create app_migration3
        $this->runMigrateControllerAction('create', ['app_migration3'], $controllerConfig);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertMigrationHistory([
            'm*_base',
            'm010101_000001_app_migration1',
        ]);

        // yii migrate/up
        $this->runMigrateControllerAction('up', [], $controllerConfig);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertMigrationHistory([
            'm*_base',
            'm010101_000001_app_migration1',
            'm010101_000002_ext_migration1',
            'm010101_000003_app_migration2',
            $this->migrationNamespace . '\\M010101000004NsMigration',
            'm*_app_migration3',
        ]);
        $this->assertCount(1, FileHelper::findFiles(Yii::getAlias($appPath), ['only' => ['m*_app_migration3.php']]));
    }

    public function testBeforeActionThrowsExceptionWhenBothPathAndNamespacesEmpty(): void
    {
        $this->setOutputCallback(function ($output) {
            return null;
        });

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('At least one of `migrationPath` or `migrationNamespaces` should be specified.');

        $controller = $this->createMigrateController([
            'migrationPath' => null,
            'migrationNamespaces' => [],
        ]);
        $controller->run('up');
    }

    public function testBeforeActionThrowsExceptionWhenPathNotExistsForNonCreateAction(): void
    {
        $this->setOutputCallback(function ($output) {
            return null;
        });

        $nonExistentPath = Yii::getAlias('@yiiunit/runtime/non_existent_path_' . uniqid());

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage("Migration failed. Directory specified in migrationPath doesn't exist");

        $controller = $this->createMigrateController([
            'migrationPath' => $nonExistentPath,
        ]);
        $controller->run('up');
    }

    public function testBeforeActionCreatesDirectoryForCreateAction(): void
    {
        $newPath = Yii::getAlias('@yiiunit/runtime/auto_created_path_' . uniqid());

        try {
            $this->runMigrateControllerAction('create', ['test_auto_dir'], [
                'migrationPath' => $newPath,
            ]);
            $this->assertSame(ExitCode::OK, $this->getExitCode());
            $this->assertDirectoryExists($newPath);
        } finally {
            FileHelper::removeDirectory($newPath);
        }
    }

    public function testDownStepLessThanOneThrowsException(): void
    {
        $this->setOutputCallback(function ($output) {
            return null;
        });

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The step argument must be greater than 0.');

        $controller = $this->createMigrateController([]);
        $controller->run('down', [0]);
    }

    public function testDownWhenNoMigrationApplied(): void
    {
        $output = $this->runMigrateControllerAction('down');
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertStringContainsString('No migration has been done before.', $output);
    }

    public function testRedoStepLessThanOneThrowsException(): void
    {
        $this->setOutputCallback(function ($output) {
            return null;
        });

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The step argument must be greater than 0.');

        $controller = $this->createMigrateController([]);
        $controller->run('redo', [0]);
    }

    public function testRedoWhenNoMigrationApplied(): void
    {
        $output = $this->runMigrateControllerAction('redo');
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertStringContainsString('No migration has been done before.', $output);
    }

    public function testRedoAll(): void
    {
        $this->createMigration('test_redo_all1', '010101_000001');
        $this->createMigration('test_redo_all2', '010101_000002');

        $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $output = $this->runMigrateControllerAction('redo', ['all']);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertStringContainsString('2 migrations were redone.', $output);

        $this->assertMigrationHistory(['m*_base', 'm*_test_redo_all1', 'm*_test_redo_all2']);
    }

    public function testHistoryAll(): void
    {
        $this->createMigration('test_history_all1', '010101_000001');
        $this->createMigration('test_history_all2', '010101_000002');

        $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $output = $this->runMigrateControllerAction('history', ['all']);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertStringContainsString('_test_history_all1', $output);
        $this->assertStringContainsString('_test_history_all2', $output);
        $this->assertStringContainsString('Total 2 migrations have been applied before:', $output);
    }

    public function testHistoryLimitLessThanOneThrowsException(): void
    {
        $this->setOutputCallback(function ($output) {
            return null;
        });

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The limit must be greater than 0.');

        $controller = $this->createMigrateController([]);
        $controller->run('history', [0]);
    }

    public function testHistoryWithLimit(): void
    {
        $this->createMigration('test_hist_lim1', '010101_000001');
        $this->createMigration('test_hist_lim2', '010101_000002');
        $this->createMigration('test_hist_lim3', '010101_000003');

        $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $output = $this->runMigrateControllerAction('history', [2]);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertStringContainsString('Showing the last 2 applied migrations:', $output);
        $this->assertStringContainsString('_test_hist_lim3', $output);
        $this->assertStringContainsString('_test_hist_lim2', $output);
    }

    public function testNewAll(): void
    {
        $this->createMigration('test_new_all1', '010101_000001');
        $this->createMigration('test_new_all2', '010101_000002');

        $output = $this->runMigrateControllerAction('new', ['all']);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertStringContainsString('Found 2 new migrations:', $output);
        $this->assertStringContainsString('_test_new_all1', $output);
        $this->assertStringContainsString('_test_new_all2', $output);
    }

    public function testNewLimitLessThanOneThrowsException(): void
    {
        $this->setOutputCallback(function ($output) {
            return null;
        });

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The limit must be greater than 0.');

        $controller = $this->createMigrateController([]);
        $controller->run('new', [0]);
    }

    public function testNewWithLimitShowsPartialList(): void
    {
        $this->createMigration('test_new_lim1', '010101_000001');
        $this->createMigration('test_new_lim2', '010101_000002');
        $this->createMigration('test_new_lim3', '010101_000003');

        $output = $this->runMigrateControllerAction('new', [2]);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertStringContainsString('Showing 2 out of 3 new migrations:', $output);
        $this->assertStringContainsString('_test_new_lim1', $output);
        $this->assertStringContainsString('_test_new_lim2', $output);
        $this->assertStringNotContainsString('_test_new_lim3', $output);
    }

    public function testNewUpToDate(): void
    {
        $output = $this->runMigrateControllerAction('new');
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertStringContainsString('No new migrations found. Your system is up-to-date.', $output);
    }

    public function testToWithTimestampFormat(): void
    {
        $this->createMigration('to_ts1', '101129_185401');

        $output = $this->runMigrateControllerAction('to', ['101129_185401']);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertMigrationHistory(['m*_base', 'm*_to_ts1']);
    }

    public function testToWithUnixTimestamp(): void
    {
        $this->createMigration('to_unix1', '010101_000001');
        $this->createMigration('to_unix2', '010101_000002');

        $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $output = $this->runMigrateControllerAction('to', [(string) (time() + 86400)]);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertStringContainsString('Nothing needs to be done.', $output);
    }

    public function testToWithDatetimeString(): void
    {
        $this->createMigration('to_dt1', '010101_000001');
        $this->createMigration('to_dt2', '010101_000002');

        $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $futureDate = date('Y-m-d H:i:s', time() + 86400);
        $output = $this->runMigrateControllerAction('to', [$futureDate]);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertStringContainsString('Nothing needs to be done.', $output);
    }

    public function testToWithInvalidVersionThrowsException(): void
    {
        $this->setOutputCallback(function ($output) {
            return null;
        });

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The version argument must be either a timestamp');

        $controller = $this->createMigrateController([]);
        $controller->run('to', ['invalid!version']);
    }

    public function testMarkWithInvalidVersionThrowsException(): void
    {
        $this->setOutputCallback(function ($output) {
            return null;
        });

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The version argument must be either a timestamp');

        $controller = $this->createMigrateController([]);
        $controller->run('mark', ['invalid!version']);
    }

    public function testMarkAlreadyAtVersion(): void
    {
        $this->createMigration('test_mark_at1', '010101_000001');

        $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $output = $this->runMigrateControllerAction('mark', ['010101_000001']);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertStringContainsString("Already at '010101_000001'. Nothing needs to be done.", $output);
    }

    public function testMarkDown(): void
    {
        $this->createMigration('test_mark_down1', '010101_000001');
        $this->createMigration('test_mark_down2', '010101_000002');

        $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertMigrationHistory(['m*_base', 'm*_test_mark_down1', 'm*_test_mark_down2']);

        $output = $this->runMigrateControllerAction('mark', ['010101_000001']);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertStringContainsString('The migration history is set at 010101_000001.', $output);
        $this->assertMigrationHistory(['m*_base', 'm*_test_mark_down1']);
    }

    public function testMarkVersionNotFoundThrowsException(): void
    {
        $this->setOutputCallback(function ($output) {
            return null;
        });

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Unable to find the version '999999_999999'.");

        $controller = $this->createMigrateController([]);
        $controller->run('mark', ['999999_999999']);
    }

    public function testCreateWithInvalidNameThrowsException(): void
    {
        $this->setOutputCallback(function ($output) {
            return null;
        });

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The migration name should contain letters, digits, underscore and/or backslash characters only.');

        $controller = $this->createMigrateController([]);
        $controller->run('create', ['invalid-name!']);
    }

    public function testUpFailurePartialApply(): void
    {
        $this->createMigration('test_up_ok', '010101_000001');
        $this->createFailingUpMigration('test_up_fail', '010101_000002');
        $this->createMigration('test_up_skip', '010101_000003');

        $output = $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::UNSPECIFIED_ERROR, $this->getExitCode());
        $this->assertStringContainsString('1 from 3 migration was applied.', $output);
        $this->assertStringContainsString('Migration failed. The rest of the migrations are canceled.', $output);
        $this->assertMigrationHistory(['m*_base', 'm*_test_up_ok']);
    }

    public function testDownFailure(): void
    {
        $this->createMigration('test_down_ok', '010101_000001');
        $this->createFailingDownMigration('test_down_fail', '010101_000002');

        $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $output = $this->runMigrateControllerAction('down', ['all']);
        $this->assertSame(ExitCode::UNSPECIFIED_ERROR, $this->getExitCode());
        $this->assertStringContainsString('0 from 2 migrations were reverted.', $output);
        $this->assertStringContainsString('Migration failed. The rest of the migrations are canceled.', $output);
    }

    public function testUpFailureFirstMigration(): void
    {
        $this->createFailingUpMigration('test_up_fail_first', '010101_000001');
        $this->createMigration('test_up_skip_after', '010101_000002');

        $output = $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::UNSPECIFIED_ERROR, $this->getExitCode());
        $this->assertStringContainsString('0 from 2 migrations were applied.', $output);
        $this->assertMigrationHistory(['m*_base']);
    }

    public function testRedoFailureDuringDown(): void
    {
        $this->createFailingDownMigration('test_redo_down_fail', '010101_000001');

        $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $output = $this->runMigrateControllerAction('redo');
        $this->assertSame(ExitCode::UNSPECIFIED_ERROR, $this->getExitCode());
        $this->assertStringContainsString('Migration failed. The rest of the migrations are canceled.', $output);
    }

    public function testRedoFailureDuringUp(): void
    {
        $date = '010101_000001';
        $class = 'm' . $date . '_test_redo_up_fail';
        $baseClass = $this->migrationBaseClass;
        $code = <<<CODE
<?php

class {$class} extends {$baseClass}
{
    public static \$upCallCount = 0;

    public function up()
    {
        self::\$upCallCount++;
        if (self::\$upCallCount > 1) {
            return false;
        }
    }

    public function down()
    {
    }
}
CODE;
        file_put_contents($this->migrationPath . DIRECTORY_SEPARATOR . $class . '.php', $code);

        $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $output = $this->runMigrateControllerAction('redo');
        $this->assertSame(ExitCode::UNSPECIFIED_ERROR, $this->getExitCode());
        $this->assertStringContainsString('Migration failed. The rest of the migrations are canceled.', $output);
    }

    public function testOptionsIncludesTemplateFileForCreate(): void
    {
        $controller = $this->createMigrateController([]);
        $options = $controller->options('create');
        $this->assertContains('templateFile', $options);
        $this->assertContains('migrationPath', $options);
        $this->assertContains('migrationNamespaces', $options);
        $this->assertContains('compact', $options);
    }

    public function testOptionsExcludesTemplateFileForUp(): void
    {
        $controller = $this->createMigrateController([]);
        $options = $controller->options('up');
        $this->assertNotContains('templateFile', $options);
        $this->assertContains('migrationPath', $options);
    }

    public function testToMigrateDown(): void
    {
        $this->createMigration('to_down1', '010101_000001');
        $this->createMigration('to_down2', '010101_000002');
        $this->createMigration('to_down3', '010101_000003');

        $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $output = $this->runMigrateControllerAction('to', ['m010101_000001_to_down1']);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertMigrationHistory(['m*_base', 'm*_to_down1']);
    }

    public function testToAlreadyAtVersion(): void
    {
        $this->createMigration('to_already1', '010101_000001');

        $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $output = $this->runMigrateControllerAction('to', ['m010101_000001_to_already1']);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertStringContainsString('Nothing needs to be done.', $output);
    }

    public function testToVersionNotFoundThrowsException(): void
    {
        $this->setOutputCallback(function ($output) {
            return null;
        });

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Unable to find the version 'm999999_999999'.");

        $controller = $this->createMigrateController([]);
        $controller->run('to', ['m999999_999999_nonexistent']);
    }

    public function testUpNoNewMigrations(): void
    {
        $output = $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertStringContainsString('No new migrations found. Your system is up-to-date.', $output);
    }

    public function testUpShowsPartialCount(): void
    {
        $this->createMigration('test_partial1', '010101_000001');
        $this->createMigration('test_partial2', '010101_000002');
        $this->createMigration('test_partial3', '010101_000003');

        $output = $this->runMigrateControllerAction('up', [2]);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertStringContainsString('Total 2 out of 3 new migrations to be applied:', $output);
        $this->assertStringContainsString('2 migrations were applied.', $output);
    }

    public function testToMigrateToTimeWithDowngrade(): void
    {
        $this->createMigration('to_time1', '010101_000001');
        $this->createMigration('to_time2', '010101_000002');

        $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $output = $this->runMigrateControllerAction('to', ['1']);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertMigrationHistory(['m*_base']);
    }

    public function testDownPartialFailure(): void
    {
        $this->createMigration('test_down_pf_ok', '010101_000001');
        $this->createFailingDownMigration('test_down_pf_fail', '010101_000002');
        $this->createMigration('test_down_pf_ok2', '010101_000003');

        $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $output = $this->runMigrateControllerAction('down', ['all']);
        $this->assertSame(ExitCode::UNSPECIFIED_ERROR, $this->getExitCode());
        $this->assertStringContainsString('1 from 3 migration was reverted.', $output);
        $this->assertMigrationHistory(['m*_base', 'm*_test_down_pf_ok', 'm*_test_down_pf_fail']);
    }

    public function testUpSingularMessage(): void
    {
        $this->createMigration('test_up_single', '010101_000001');

        $output = $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertStringContainsString('Total 1 new migration to be applied:', $output);
        $this->assertStringContainsString('1 migration was applied.', $output);
    }

    public function testDownSingularMessage(): void
    {
        $this->createMigration('test_down_single', '010101_000001');

        $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $output = $this->runMigrateControllerAction('down', [1]);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertStringContainsString('Total 1 migration to be reverted:', $output);
        $this->assertStringContainsString('1 migration was reverted.', $output);
    }

    public function testRedoSingularMessage(): void
    {
        $this->createMigration('test_redo_single', '010101_000001');

        $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $output = $this->runMigrateControllerAction('redo', [1]);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertStringContainsString('Total 1 migration to be redone:', $output);
        $this->assertStringContainsString('1 migration was redone.', $output);
    }

    public function testHistorySingularMessage(): void
    {
        $this->createMigration('test_hist_single', '010101_000001');

        $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $output = $this->runMigrateControllerAction('history', [1]);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertStringContainsString('Showing the last 1 applied migration:', $output);
    }

    public function testHistoryAllSingularMessage(): void
    {
        $this->createMigration('test_hist_all_single', '010101_000001');

        $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $output = $this->runMigrateControllerAction('history', ['all']);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertStringContainsString('Total 1 migration has been applied before:', $output);
    }

    public function testNewSingularMessage(): void
    {
        $this->createMigration('test_new_single', '010101_000001');

        $output = $this->runMigrateControllerAction('new');
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertStringContainsString('Found 1 new migration:', $output);
    }

    public function testMigrateUpBaseMigrationReturnsTrue(): void
    {
        $controller = $this->createMigrateController([]);
        $result = $this->invokeMethod($controller, 'migrateUp', [BaseMigrateController::BASE_MIGRATION]);
        $this->assertTrue($result);
    }

    public function testMigrateDownBaseMigrationReturnsTrue(): void
    {
        $controller = $this->createMigrateController([]);
        $result = $this->invokeMethod($controller, 'migrateDown', [BaseMigrateController::BASE_MIGRATION]);
        $this->assertTrue($result);
    }

    public function testGetNewMigrationsSkipsNonExistentNamespacePath(): void
    {
        $previousNonexistentNsAlias = Yii::getAlias('@nonexistentNs', false);

        try {
            Yii::setAlias('@nonexistentNs', '/tmp/nonexistent_ns_' . uniqid());

            $output = $this->runMigrateControllerAction('new', [], [
                'migrationPath' => $this->migrationPath,
                'migrationNamespaces' => ['nonexistentNs\\migrations'],
            ]);
            $this->assertSame(ExitCode::OK, $this->getExitCode());
            $this->assertStringContainsString('No new migrations found.', $output);
        } finally {
            Yii::setAlias('@nonexistentNs', $previousNonexistentNsAlias === false ? null : $previousNonexistentNsAlias);
        }
    }
}
