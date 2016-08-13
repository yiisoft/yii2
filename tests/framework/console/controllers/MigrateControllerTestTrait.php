<?php

namespace yiiunit\framework\console\controllers;

use Yii;
use yii\console\controllers\BaseMigrateController;
use yii\helpers\FileHelper;
use yiiunit\TestCase;

/**
 * This trait provides unit tests shared by the different migration controllers implementations
 * @see BaseMigrateController
 */
trait MigrateControllerTestTrait
{
    /* @var $this TestCase */

    /**
     * @var string name of the migration controller class, which is under test.
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

    public function setUpMigrationPath()
    {
        $this->migrationPath = Yii::getAlias('@yiiunit/runtime/test_migrations');
        FileHelper::createDirectory($this->migrationPath);
        if (!file_exists($this->migrationPath)) {
            $this->markTestIncomplete('Unit tests runtime directory should have writable permissions!');
        }
    }

    public function tearDownMigrationPath()
    {
        FileHelper::removeDirectory($this->migrationPath);
    }

    public function assertFileContent($expectedFile, $class)
    {
        $this->assertEqualsWithoutLE(
            include Yii::getAlias(
                "@yiiunit/data/console/migrate_create/$expectedFile.php"
            ),
            $this->parseNameClassMigration($class)
        );
    }

    public function assertCommandCreatedFile(
        $expectedFile,
        $migrationName,
        $params = []
    ) {
        $class = 'm' . gmdate('ymd_His') . '_' . $migrationName;
        $params[0] = $migrationName;
        $this->runMigrateControllerAction('create', $params);
        $this->assertFileContent($expectedFile, $class);
    }

    /**
     * @return array applied migration entries
     */
    abstract protected function getMigrationHistory();

    /**
     * Creates test migrate controller instance.
     * @return BaseMigrateController migrate command instance.
     */
    protected function createMigrateController()
    {
        $module = $this->getMock('yii\\base\\Module', ['fake'], ['console']);
        $class = $this->migrateControllerClass;
        $migrateController = new $class('migrate', $module);
        $migrateController->interactive = false;
        $migrateController->migrationPath = $this->migrationPath;
        return $migrateController;
    }

    /**
     * Emulates running of the migrate controller action.
     * @param  string $actionID id of action to be run.
     * @param  array  $args     action arguments.
     * @return string command output.
     */
    protected function runMigrateControllerAction($actionID, array $args = [])
    {
        $controller = $this->createMigrateController();
        ob_start();
        ob_implicit_flush(false);
        $controller->run($actionID, $args);

        return ob_get_clean();
    }

    /**
     * @param string $name
     * @param string|null $date
     * @return string generated class name
     */
    protected function createMigration($name, $date = null)
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
        file_put_contents($this->migrationPath . DIRECTORY_SEPARATOR . $class . '.php', $code);
        return $class;
    }

    /**
     * Change class name migration to $class
     * @param string $class name class
     * @return string content generated class migration
     * @see https://github.com/yiisoft/yii2/pull/10213
     */
    protected function parseNameClassMigration($class)
    {
        $files = FileHelper::findFiles($this->migrationPath);
        $file = file_get_contents($files[0]);
        if (preg_match('/class (m\d+_\d+_.*) extends Migration/', $file, $match)) {
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
            if (strpos($appliedMigration['version'], $expectedMigrationName) === false) {
                $success = false;
                break;
            }
        }
        if (!$success) {
            $message .= "\n";
            $message .= "Expected: " . var_export($expectedMigrations, true) . "\n";

            $actualMigrations = [];
            foreach ($migrationHistory as $row) {
                $actualMigrations[] = $row['version'];
            }
            $message .= "Actual: " . var_export($actualMigrations, true) . "\n";
        }
        $this->assertTrue($success, $message);
    }

    // Tests :

    public function testCreate()
    {
        $migrationName = 'test_migration';
        $this->runMigrateControllerAction('create', [$migrationName]);
        $files = FileHelper::findFiles($this->migrationPath);
        $this->assertCount(1, $files, 'Unable to create new migration!');
        $this->assertContains($migrationName, basename($files[0]), 'Wrong migration name!');
    }

    public function testGenerateDefaultMigration()
    {
        $this->assertCommandCreatedFile('default', 'DefaultTest');
    }

    public function testGenerateCreateMigration()
    {
        $migrationNames = [
            'create_test_table',
        ];
        foreach ($migrationNames as $migrationName) {
            $this->assertCommandCreatedFile('create_test', $migrationName);

            $this->assertCommandCreatedFile('create_fields', $migrationName, [
                'fields' => 'title:string(10):notNull:unique:defaultValue("test"),
                    body:text:notNull,
                    price:money(11,2):notNull'
            ]);

            $this->assertCommandCreatedFile('create_title_pk', $migrationName, [
                'fields' => 'title:primaryKey,body:text:notNull,price:money(11,2)',
            ]);

            $this->assertCommandCreatedFile('create_id_pk', $migrationName, [
                'fields' => 'id:primaryKey,
                    address:string,
                    address2:string,
                    email:string',
            ]);

            $this->assertCommandCreatedFile('create_foreign_key', $migrationName, [
                'fields' => 'user_id:integer:foreignKey,
                    product_id:foreignKey:integer:unsigned:notNull,
                    order_id:integer:foreignKey(user_order):notNull,
                    created_at:dateTime:notNull',
            ]);

            $this->assertCommandCreatedFile('create_prefix', $migrationName, [
                'useTablePrefix' => true,
                'fields' => 'user_id:integer:foreignKey,
                    product_id:foreignKey:integer:unsigned:notNull,
                    order_id:integer:foreignKey(user_order):notNull,
                    created_at:dateTime:notNull',
            ]);
        }

		// @see https://github.com/yiisoft/yii2/issues/10876
		$this->assertCommandCreatedFile('create_products_from_store_table', 'create_products_from_store_table');

        // @see https://github.com/yiisoft/yii2/issues/11461
        $this->assertCommandCreatedFile('create_title_with_comma_default_values', 'create_test_table', [
            'fields' => 'title:string(10):notNull:unique:defaultValue(",te,st"),
             body:text:notNull:defaultValue(",test"),
             test:custom(11,2,"s"):notNull',
        ]);
    }

    public function testGenerateDropMigration()
    {
        $migrationNames = [
            'drop_test_table',
        ];
        foreach ($migrationNames as $migrationName) {
            $this->assertCommandCreatedFile('drop_test', $migrationName);

            $this->assertCommandCreatedFile('drop_fields', $migrationName, [
                'fields' => 'body:text:notNull,price:money(11,2)'
            ]);
        }

		// @see https://github.com/yiisoft/yii2/issues/10876
		$this->assertCommandCreatedFile('drop_products_from_store_table', 'drop_products_from_store_table');
    }

    public function testGenerateAddColumnMigration()
    {
        $migrationNames = [
            'add_columns_column_to_test_table',
            'add_columns_columns_to_test_table',
        ];
        foreach ($migrationNames as $migrationName) {
            $this->assertCommandCreatedFile('add_columns_test', $migrationName, [
                'fields' => 'title:string(10):notNull,
                    body:text:notNull,
                    price:money(11,2):notNull,
                    created_at:dateTime'
            ]);

            $this->assertCommandCreatedFile('add_columns_fk', $migrationName, [
                'fields' => 'user_id:integer:foreignKey,
                    product_id:foreignKey:integer:unsigned:notNull,
                    order_id:integer:foreignKey(user_order):notNull,
                    created_at:dateTime:notNull',
            ]);

            $this->assertCommandCreatedFile('add_columns_prefix', $migrationName, [
                'useTablePrefix' => true,
                'fields' => 'user_id:integer:foreignKey,
                    product_id:foreignKey:integer:unsigned:notNull,
                    order_id:integer:foreignKey(user_order):notNull,
                    created_at:dateTime:notNull',
            ]);
        }
    }

    public function testGenerateDropColumnMigration()
    {
        $migrationNames = [
            'drop_columns_column_from_test_table',
            'drop_columns_columns_from_test_table',
        ];
        foreach ($migrationNames as $migrationName) {
            $this->assertCommandCreatedFile('drop_columns_test', $migrationName, [
                'fields' => 'title:string(10):notNull,body:text:notNull,
                    price:money(11,2):notNull,
                    created_at:dateTime'
            ]);
        }
    }

    public function testGenerateCreateJunctionMigration()
    {
        $migrationNames = [
            'create_junction_post_and_tag_tables',
            'create_junction_for_post_and_tag_tables',
            'create_junction_table_for_post_and_tag_tables',
            'create_junction_table_for_post_and_tag_table',
        ];
        foreach ($migrationNames as $migrationName) {
            $this->assertCommandCreatedFile('junction_test', $migrationName);
        }
    }

    public function testUp()
    {
        $this->createMigration('test1');
        $this->createMigration('test2');

        $this->runMigrateControllerAction('up');

        $this->assertMigrationHistory(['base', 'test1', 'test2']);
    }

    /**
     * @depends testUp
     */
    public function testUpCount()
    {
        $this->createMigration('test1');
        $this->createMigration('test2');

        $this->runMigrateControllerAction('up', [1]);

        $this->assertMigrationHistory(['base', 'test1']);
    }

    /**
     * @depends testUp
     */
    public function testDownCount()
    {
        $this->createMigration('test1');
        $this->createMigration('test2');

        $this->runMigrateControllerAction('up');
        $this->runMigrateControllerAction('down', [1]);

        $this->assertMigrationHistory(['base', 'test1']);
    }

    /**
     * @depends testDownCount
     */
    public function testDownAll()
    {
        $this->createMigration('test1');
        $this->createMigration('test2');

        $this->runMigrateControllerAction('up');
        $this->runMigrateControllerAction('down', ['all']);

        $this->assertMigrationHistory(['base']);
    }

    /**
     * @depends testUp
     */
    public function testHistory()
    {
        $output = $this->runMigrateControllerAction('history');
        $this->assertContains('No migration', $output);

        $this->createMigration('test1');
        $this->createMigration('test2');
        $this->runMigrateControllerAction('up');

        $output = $this->runMigrateControllerAction('history');
        $this->assertContains('_test1', $output);
        $this->assertContains('_test2', $output);
    }

    /**
     * @depends testUp
     */
    public function testNew()
    {
        $this->createMigration('test1');

        $output = $this->runMigrateControllerAction('new');
        $this->assertContains('_test1', $output);

        $this->runMigrateControllerAction('up');

        $output = $this->runMigrateControllerAction('new');
        $this->assertNotContains('_test1', $output);
    }

    public function testMark()
    {
        $version = '010101_000001';
        $this->createMigration('test1', $version);

        $this->runMigrateControllerAction('mark', [$version]);

        $this->assertMigrationHistory(['base', 'test1']);
    }

    /**
     * @depends testUp
     */
    public function testRedo()
    {
        $this->createMigration('test1');
        $this->runMigrateControllerAction('up');

        $this->runMigrateControllerAction('redo');

        $this->assertMigrationHistory(['base', 'test1']);
    }
}
