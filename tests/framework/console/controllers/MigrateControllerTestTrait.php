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
        $migrationName = 'DefaultTest';
        $this->runMigrateControllerAction('create', [$migrationName]);
        $files = FileHelper::findFiles($this->migrationPath);

        $class = 'm' . gmdate('ymd_His') . '_' . $migrationName;
        $newLine = '\n';
        $code = <<<CODE
<?php

use yii\db\Migration;

class {$class} extends Migration
{
    public function up()
    {

    }

    public function down()
    {
        echo "{$class} cannot be reverted.{$newLine}";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}

CODE;

        $this->assertEqualsWithoutLE($code, file_get_contents($files[0]));
    }

    public function testGenerateCreateMigration()
    {
        $migrationName = 'create_test';
        $this->runMigrateControllerAction('create', [
            $migrationName,
            'fields' => [
                'title:string(10):notNull',
                'body:text:notNull'
            ]
        ]);
        $files = FileHelper::findFiles($this->migrationPath);

        $class = 'm' . gmdate('ymd_His') . '_' . $migrationName;
        $code = <<<CODE
<?php

use yii\db\Migration;

class {$class} extends Migration
{
    public function up()
    {
        \$this->createTable('test', [
            'id' => \$this->primaryKey(),
            'title' => \$this->string(10)->notNull(),
            'body' => \$this->text()->notNull()
        ]);
    }

    public function down()
    {
        \$this->dropTable('test');
    }
}

CODE;

        $this->assertEqualsWithoutLE($code, file_get_contents($files[0]));
        $class = 'm' . gmdate('ymd_His') . '_' . $migrationName;
        $this->runMigrateControllerAction('create', [
            $migrationName,
            'fields' => [
                'title:primaryKey',
                'body:text:notNull'
            ],

        ]);
        $files = FileHelper::findFiles($this->migrationPath);
        $code = <<<CODE
<?php

use yii\db\Migration;

class {$class} extends Migration
{
    public function up()
    {
        \$this->createTable('test', [
            'title' => \$this->primaryKey(),
            'body' => \$this->text()->notNull()
        ]);
    }

    public function down()
    {
        \$this->dropTable('test');
    }
}

CODE;

        $this->assertEqualsWithoutLE($code, file_get_contents($files[0]));
    }

    public function testGenerateDropMigration()
    {
        $migrationName = 'drop_test';
        $this->runMigrateControllerAction('create', [
            $migrationName
        ]);
        $files = FileHelper::findFiles($this->migrationPath);

        $class = 'm' . gmdate('ymd_His') . '_' . $migrationName;
        $code = <<<CODE
<?php

use yii\db\Migration;

class {$class} extends Migration
{
    public function up()
    {
        \$this->dropTable('test');
    }

    public function down()
    {
        \$this->createTable('test', [
        ]);
    }
}

CODE;

        $this->assertEqualsWithoutLE($code, file_get_contents($files[0]));
        $class = 'm' . gmdate('ymd_His') . '_' . $migrationName;
        $this->runMigrateControllerAction('create', [
            $migrationName,
            'fields' => [
                'title:primaryKey',
                'body:text:notNull'
            ],

        ]);
        $files = FileHelper::findFiles($this->migrationPath);
        $code = <<<CODE
<?php

use yii\db\Migration;

class {$class} extends Migration
{
    public function up()
    {
        \$this->dropTable('test');
    }

    public function down()
    {
        \$this->createTable('test', [
            'title' => \$this->primaryKey(),
            'body' => \$this->text()->notNull()
        ]);
    }
}

CODE;

        $this->assertEqualsWithoutLE($code, file_get_contents($files[0]));
    }

    public function testGenerateAddMigration()
    {
        $migrationName = 'add_columns_from_test';
        $this->runMigrateControllerAction('create', [
            $migrationName,
            'fields' => [
                'title:string(10):notNull',
                'body:text:notNull',
                'create_at:dateTime'
            ]
        ]);
        $files = FileHelper::findFiles($this->migrationPath);

        $class = 'm' . gmdate('ymd_His') . '_' . $migrationName;
        $code = <<<CODE
<?php

use yii\db\Migration;

class {$class} extends Migration
{
    public function up()
    {
        \$this->addColumn('test', 'title', \$this->string(10)->notNull());
        \$this->addColumn('test', 'body', \$this->text()->notNull());
        \$this->addColumn('test', 'create_at', \$this->dateTime());
    }

    public function down()
    {
        \$this->dropColumn('test', 'title');
        \$this->dropColumn('test', 'body');
        \$this->dropColumn('test', 'create_at');
    }
}

CODE;

        $this->assertEqualsWithoutLE($code, file_get_contents($files[0]));
    }

    public function testGenerateRemoveMigration()
    {
        $migrationName = 'remove_columns_from_test';
        $this->runMigrateControllerAction('create', [
            $migrationName,
            'fields' => [
                'title:string(10):notNull',
                'body:text:notNull',
                'create_at:dateTime'
            ]
        ]);
        $files = FileHelper::findFiles($this->migrationPath);

        $class = 'm' . gmdate('ymd_His') . '_' . $migrationName;
        $code = <<<CODE
<?php

use yii\db\Migration;

class {$class} extends Migration
{
    public function up()
    {
        \$this->dropColumn('test', 'title');
        \$this->dropColumn('test', 'body');
        \$this->dropColumn('test', 'create_at');
    }

    public function down()
    {
        \$this->addColumn('test', 'title', \$this->string(10)->notNull());
        \$this->addColumn('test', 'body', \$this->text()->notNull());
        \$this->addColumn('test', 'create_at', \$this->dateTime());
    }
}

CODE;

        $this->assertEqualsWithoutLE($code, file_get_contents($files[0]));
    }

    public function testGenerateCreateJoinMigration()
    {
        $migrationName = 'create_join_post_and_tag';
        $this->runMigrateControllerAction('create', [
            $migrationName,
        ]);
        $files = FileHelper::findFiles($this->migrationPath);

        $class = 'm' . gmdate('ymd_His') . '_' . $migrationName;
        $code = <<<CODE
<?php

use yii\db\Migration;

class {$class} extends Migration
{
    public function up()
    {
        \$this->createTable('post_tag', [
            'post_id' => \$this->integer(),
            'tag_id' => \$this->integer(),
            'PRIMARY KEY(post_id, tag_id)'
        ]);

        \$this->createIndex('post_id_index', 'post_tag', 'post_id');
        \$this->createIndex('tag_id_index', 'post_tag', 'tag_id');

        \$this->addForeignKey('fk_post_tag_post_id', 'post_tag', 'post_id', 'post', 'id', 'CASCADE');
        \$this->addForeignKey('fk_post_tag_tag_id', 'post_tag', 'tag_id', 'tag', 'id', 'CASCADE');
    }

    public function down()
    {
        \$this->dropTable('post_tag');
    }
}

CODE;

        $this->assertEqualsWithoutLE($code, file_get_contents($files[0]));
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
