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
        $migrationName = 'DefaultTest';
        $class = 'm' . gmdate('ymd_His') . '_' . $migrationName;
        $this->runMigrateControllerAction('create', [$migrationName]);
        $file = $this->parseNameClassMigration($class);

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
        $this->assertEqualsWithoutLE($code, $file);
    }

    public function testGenerateCreateMigration()
    {
        $migrationName = 'create_test';
        $class = 'm' . gmdate('ymd_His') . '_' . $migrationName;
        $this->runMigrateControllerAction('create', [
            $migrationName,
            'fields' => 'title:string(10):notNull:unique:defaultValue("test"),body:text:notNull,price:money(11,2):notNull'
        ]);
        $file = $this->parseNameClassMigration($class);

        $code = <<<CODE
<?php

use yii\db\Migration;

/**
 * Handles the creation for table `test`.
 */
class {$class} extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        \$this->createTable('test', [
            'id' => \$this->primaryKey(),
            'title' => \$this->string(10)->notNull()->unique()->defaultValue("test"),
            'body' => \$this->text()->notNull(),
            'price' => \$this->money(11,2)->notNull(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        \$this->dropTable('test');
    }
}

CODE;
        $this->assertEqualsWithoutLE($code, $file);

        $class = 'm' . gmdate('ymd_His') . '_' . $migrationName;
        $this->runMigrateControllerAction('create', [
            $migrationName,
            'fields' => 'title:primaryKey,body:text:notNull,price:money(11,2)',
        ]);
        $file = $this->parseNameClassMigration($class);
        $code = <<<CODE
<?php

use yii\db\Migration;

/**
 * Handles the creation for table `test`.
 */
class {$class} extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        \$this->createTable('test', [
            'title' => \$this->primaryKey(),
            'body' => \$this->text()->notNull(),
            'price' => \$this->money(11,2),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        \$this->dropTable('test');
    }
}

CODE;
        $this->assertEqualsWithoutLE($code, $file);

        $class = 'm' . gmdate('ymd_His') . '_' . $migrationName;
        $this->runMigrateControllerAction('create', [
            $migrationName,
        ]);
        $file = $this->parseNameClassMigration($class);
        $code = <<<CODE
<?php

use yii\db\Migration;

/**
 * Handles the creation for table `test`.
 */
class {$class} extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        \$this->createTable('test', [
            'id' => \$this->primaryKey(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        \$this->dropTable('test');
    }
}

CODE;
        $this->assertEqualsWithoutLE($code, $file);

        $class = 'm' . gmdate('ymd_His') . '_' . $migrationName;
        $this->runMigrateControllerAction('create', [
            $migrationName,
            'fields' => 'id:primaryKey,address:string,address2:string,email:string',
        ]);
        $file = $this->parseNameClassMigration($class);
        $code = <<<CODE
<?php

use yii\db\Migration;

/**
 * Handles the creation for table `test`.
 */
class {$class} extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        \$this->createTable('test', [
            'id' => \$this->primaryKey(),
            'address' => \$this->string(),
            'address2' => \$this->string(),
            'email' => \$this->string(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        \$this->dropTable('test');
    }
}

CODE;
        $this->assertEqualsWithoutLE($code, $file);

        $class = 'm' . gmdate('ymd_His') . '_' . $migrationName;
        $this->runMigrateControllerAction('create', [
            $migrationName,
            'fields' => 'user_id:integer:foreignKey,
                product_id:foreignKey:integer:unsigned:notNull,
                order_id:integer:foreignKey(user_order):notNull,
                created_at:dateTime:notNull',
        ]);
        $file = $this->parseNameClassMigration($class);
        $code = <<<CODE
<?php

use yii\db\Migration;

/**
 * Handles the creation for table `test`.
 * Has foreign keys to the tables:
 *
 * - `user`
 * - `product`
 * - `user_order`
 */
class {$class} extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        \$this->createTable('test', [
            'id' => \$this->primaryKey(),
            'user_id' => \$this->integer(),
            'product_id' => \$this->integer()->unsigned()->notNull(),
            'order_id' => \$this->integer()->notNull(),
            'created_at' => \$this->dateTime()->notNull(),
        ]);

        // creates index for column `user_id`
        \$this->createIndex(
            'idx-test-user_id',
            'test',
            'user_id'
        );

        // add foreign key for table `user`
        \$this->addForeignKey(
            'fk-test-user_id',
            'test',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );

        // creates index for column `product_id`
        \$this->createIndex(
            'idx-test-product_id',
            'test',
            'product_id'
        );

        // add foreign key for table `product`
        \$this->addForeignKey(
            'fk-test-product_id',
            'test',
            'product_id',
            'product',
            'id',
            'CASCADE'
        );

        // creates index for column `order_id`
        \$this->createIndex(
            'idx-test-order_id',
            'test',
            'order_id'
        );

        // add foreign key for table `user_order`
        \$this->addForeignKey(
            'fk-test-order_id',
            'test',
            'order_id',
            'user_order',
            'id',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        // drops foreign key for table `user`
        \$this->dropForeignKey(
            'fk-test-user_id',
            'test'
        );

        // drops index for column `user_id`
        \$this->dropIndex(
            'idx-test-user_id',
            'test'
        );

        // drops foreign key for table `product`
        \$this->dropForeignKey(
            'fk-test-product_id',
            'test'
        );

        // drops index for column `product_id`
        \$this->dropIndex(
            'idx-test-product_id',
            'test'
        );

        // drops foreign key for table `user_order`
        \$this->dropForeignKey(
            'fk-test-order_id',
            'test'
        );

        // drops index for column `order_id`
        \$this->dropIndex(
            'idx-test-order_id',
            'test'
        );

        \$this->dropTable('test');
    }
}

CODE;
        $this->assertEqualsWithoutLE($code, $file);

        $class = 'm' . gmdate('ymd_His') . '_' . $migrationName;
        $this->runMigrateControllerAction('create', [
            $migrationName,
            'useTablePrefix' => true,
            'fields' => 'user_id:integer:foreignKey,
                product_id:foreignKey:integer:unsigned:notNull,
                order_id:integer:foreignKey(user_order):notNull,
                created_at:dateTime:notNull',
        ]);
        $file = $this->parseNameClassMigration($class);
        $code = <<<CODE
<?php

use yii\db\Migration;

/**
 * Handles the creation for table `{{%test}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%product}}`
 * - `{{%user_order}}`
 */
class {$class} extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        \$this->createTable('{{%test}}', [
            'id' => \$this->primaryKey(),
            'user_id' => \$this->integer(),
            'product_id' => \$this->integer()->unsigned()->notNull(),
            'order_id' => \$this->integer()->notNull(),
            'created_at' => \$this->dateTime()->notNull(),
        ]);

        // creates index for column `user_id`
        \$this->createIndex(
            '{{%idx-test-user_id}}',
            '{{%test}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        \$this->addForeignKey(
            '{{%fk-test-user_id}}',
            '{{%test}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `product_id`
        \$this->createIndex(
            '{{%idx-test-product_id}}',
            '{{%test}}',
            'product_id'
        );

        // add foreign key for table `{{%product}}`
        \$this->addForeignKey(
            '{{%fk-test-product_id}}',
            '{{%test}}',
            'product_id',
            '{{%product}}',
            'id',
            'CASCADE'
        );

        // creates index for column `order_id`
        \$this->createIndex(
            '{{%idx-test-order_id}}',
            '{{%test}}',
            'order_id'
        );

        // add foreign key for table `{{%user_order}}`
        \$this->addForeignKey(
            '{{%fk-test-order_id}}',
            '{{%test}}',
            'order_id',
            '{{%user_order}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        // drops foreign key for table `{{%user}}`
        \$this->dropForeignKey(
            '{{%fk-test-user_id}}',
            '{{%test}}'
        );

        // drops index for column `user_id`
        \$this->dropIndex(
            '{{%idx-test-user_id}}',
            '{{%test}}'
        );

        // drops foreign key for table `{{%product}}`
        \$this->dropForeignKey(
            '{{%fk-test-product_id}}',
            '{{%test}}'
        );

        // drops index for column `product_id`
        \$this->dropIndex(
            '{{%idx-test-product_id}}',
            '{{%test}}'
        );

        // drops foreign key for table `{{%user_order}}`
        \$this->dropForeignKey(
            '{{%fk-test-order_id}}',
            '{{%test}}'
        );

        // drops index for column `order_id`
        \$this->dropIndex(
            '{{%idx-test-order_id}}',
            '{{%test}}'
        );

        \$this->dropTable('{{%test}}');
    }
}

CODE;
        $this->assertEqualsWithoutLE($code, $file);
    }

    public function testGenerateDropMigration()
    {
        $migrationName = 'drop_test';
        $class = 'm' . gmdate('ymd_His') . '_' . $migrationName;
        $this->runMigrateControllerAction('create', [
            $migrationName
        ]);
        $file = $this->parseNameClassMigration($class);

        $code = <<<CODE
<?php

use yii\db\Migration;

/**
 * Handles the dropping for table `test`.
 */
class {$class} extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        \$this->dropTable('test');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        \$this->createTable('test', [
            'id' => \$this->primaryKey(),
        ]);
    }
}

CODE;
        $this->assertEqualsWithoutLE($code, $file);

        $class = 'm' . gmdate('ymd_His') . '_' . $migrationName;
        $this->runMigrateControllerAction('create', [
            $migrationName,
            'fields' => 'body:text:notNull,price:money(11,2)'
        ]);
        $file = $this->parseNameClassMigration($class);
        $code = <<<CODE
<?php

use yii\db\Migration;

/**
 * Handles the dropping for table `test`.
 */
class {$class} extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        \$this->dropTable('test');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        \$this->createTable('test', [
            'id' => \$this->primaryKey(),
            'body' => \$this->text()->notNull(),
            'price' => \$this->money(11,2),
        ]);
    }
}

CODE;
        $this->assertEqualsWithoutLE($code, $file);
    }

    public function testGenerateAddColumnMigration()
    {
        $migrationName = 'add_columns_to_test';
        $class = 'm' . gmdate('ymd_His') . '_' . $migrationName;
        $this->runMigrateControllerAction('create', [
            $migrationName,
            'fields' => 'title:string(10):notNull,
                body:text:notNull,
                price:money(11,2):notNull,
                created_at:dateTime'
        ]);
        $file = $this->parseNameClassMigration($class);

        $code = <<<CODE
<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `test`.
 */
class {$class} extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        \$this->addColumn('test', 'title', \$this->string(10)->notNull());
        \$this->addColumn('test', 'body', \$this->text()->notNull());
        \$this->addColumn('test', 'price', \$this->money(11,2)->notNull());
        \$this->addColumn('test', 'created_at', \$this->dateTime());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        \$this->dropColumn('test', 'title');
        \$this->dropColumn('test', 'body');
        \$this->dropColumn('test', 'price');
        \$this->dropColumn('test', 'created_at');
    }
}

CODE;
        $this->assertEqualsWithoutLE($code, $file);

        $migrationName = 'add_columns_to_test';
        $class = 'm' . gmdate('ymd_His') . '_' . $migrationName;
        $this->runMigrateControllerAction('create', [
            $migrationName,
            'fields' => 'user_id:integer:foreignKey,
                product_id:foreignKey:integer:unsigned:notNull,
                order_id:integer:foreignKey(user_order):notNull,
                created_at:dateTime:notNull',
        ]);
        $file = $this->parseNameClassMigration($class);

        $code = <<<CODE
<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `test`.
 * Has foreign keys to the tables:
 *
 * - `user`
 * - `product`
 * - `user_order`
 */
class {$class} extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        \$this->addColumn('test', 'user_id', \$this->integer());
        \$this->addColumn('test', 'product_id', \$this->integer()->unsigned()->notNull());
        \$this->addColumn('test', 'order_id', \$this->integer()->notNull());
        \$this->addColumn('test', 'created_at', \$this->dateTime()->notNull());

        // creates index for column `user_id`
        \$this->createIndex(
            'idx-test-user_id',
            'test',
            'user_id'
        );

        // add foreign key for table `user`
        \$this->addForeignKey(
            'fk-test-user_id',
            'test',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );

        // creates index for column `product_id`
        \$this->createIndex(
            'idx-test-product_id',
            'test',
            'product_id'
        );

        // add foreign key for table `product`
        \$this->addForeignKey(
            'fk-test-product_id',
            'test',
            'product_id',
            'product',
            'id',
            'CASCADE'
        );

        // creates index for column `order_id`
        \$this->createIndex(
            'idx-test-order_id',
            'test',
            'order_id'
        );

        // add foreign key for table `user_order`
        \$this->addForeignKey(
            'fk-test-order_id',
            'test',
            'order_id',
            'user_order',
            'id',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        // drops foreign key for table `user`
        \$this->dropForeignKey(
            'fk-test-user_id',
            'test'
        );

        // drops index for column `user_id`
        \$this->dropIndex(
            'idx-test-user_id',
            'test'
        );

        // drops foreign key for table `product`
        \$this->dropForeignKey(
            'fk-test-product_id',
            'test'
        );

        // drops index for column `product_id`
        \$this->dropIndex(
            'idx-test-product_id',
            'test'
        );

        // drops foreign key for table `user_order`
        \$this->dropForeignKey(
            'fk-test-order_id',
            'test'
        );

        // drops index for column `order_id`
        \$this->dropIndex(
            'idx-test-order_id',
            'test'
        );

        \$this->dropColumn('test', 'user_id');
        \$this->dropColumn('test', 'product_id');
        \$this->dropColumn('test', 'order_id');
        \$this->dropColumn('test', 'created_at');
    }
}

CODE;
        $this->assertEqualsWithoutLE($code, $file);

        $migrationName = 'add_columns_to_test';
        $class = 'm' . gmdate('ymd_His') . '_' . $migrationName;
        $this->runMigrateControllerAction('create', [
            $migrationName,
            'useTablePrefix' => true,
            'fields' => 'user_id:integer:foreignKey,
                product_id:foreignKey:integer:unsigned:notNull,
                order_id:integer:foreignKey(user_order):notNull,
                created_at:dateTime:notNull',
        ]);
        $file = $this->parseNameClassMigration($class);

        $code = <<<CODE
<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%test}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%product}}`
 * - `{{%user_order}}`
 */
class {$class} extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        \$this->addColumn('{{%test}}', 'user_id', \$this->integer());
        \$this->addColumn('{{%test}}', 'product_id', \$this->integer()->unsigned()->notNull());
        \$this->addColumn('{{%test}}', 'order_id', \$this->integer()->notNull());
        \$this->addColumn('{{%test}}', 'created_at', \$this->dateTime()->notNull());

        // creates index for column `user_id`
        \$this->createIndex(
            '{{%idx-test-user_id}}',
            '{{%test}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        \$this->addForeignKey(
            '{{%fk-test-user_id}}',
            '{{%test}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `product_id`
        \$this->createIndex(
            '{{%idx-test-product_id}}',
            '{{%test}}',
            'product_id'
        );

        // add foreign key for table `{{%product}}`
        \$this->addForeignKey(
            '{{%fk-test-product_id}}',
            '{{%test}}',
            'product_id',
            '{{%product}}',
            'id',
            'CASCADE'
        );

        // creates index for column `order_id`
        \$this->createIndex(
            '{{%idx-test-order_id}}',
            '{{%test}}',
            'order_id'
        );

        // add foreign key for table `{{%user_order}}`
        \$this->addForeignKey(
            '{{%fk-test-order_id}}',
            '{{%test}}',
            'order_id',
            '{{%user_order}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        // drops foreign key for table `{{%user}}`
        \$this->dropForeignKey(
            '{{%fk-test-user_id}}',
            '{{%test}}'
        );

        // drops index for column `user_id`
        \$this->dropIndex(
            '{{%idx-test-user_id}}',
            '{{%test}}'
        );

        // drops foreign key for table `{{%product}}`
        \$this->dropForeignKey(
            '{{%fk-test-product_id}}',
            '{{%test}}'
        );

        // drops index for column `product_id`
        \$this->dropIndex(
            '{{%idx-test-product_id}}',
            '{{%test}}'
        );

        // drops foreign key for table `{{%user_order}}`
        \$this->dropForeignKey(
            '{{%fk-test-order_id}}',
            '{{%test}}'
        );

        // drops index for column `order_id`
        \$this->dropIndex(
            '{{%idx-test-order_id}}',
            '{{%test}}'
        );

        \$this->dropColumn('{{%test}}', 'user_id');
        \$this->dropColumn('{{%test}}', 'product_id');
        \$this->dropColumn('{{%test}}', 'order_id');
        \$this->dropColumn('{{%test}}', 'created_at');
    }
}

CODE;
        $this->assertEqualsWithoutLE($code, $file);
    }

    public function testGenerateDropColumnMigration()
    {
        $migrationName = 'drop_columns_from_test';
        $class = 'm' . gmdate('ymd_His') . '_' . $migrationName;
        $this->runMigrateControllerAction('create', [
            $migrationName,
            'fields' => 'title:string(10):notNull,body:text:notNull,price:money(11,2):notNull,created_at:dateTime'
        ]);
        $file = $this->parseNameClassMigration($class);

        $code = <<<CODE
<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `test`.
 */
class {$class} extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        \$this->dropColumn('test', 'title');
        \$this->dropColumn('test', 'body');
        \$this->dropColumn('test', 'price');
        \$this->dropColumn('test', 'created_at');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        \$this->addColumn('test', 'title', \$this->string(10)->notNull());
        \$this->addColumn('test', 'body', \$this->text()->notNull());
        \$this->addColumn('test', 'price', \$this->money(11,2)->notNull());
        \$this->addColumn('test', 'created_at', \$this->dateTime());
    }
}

CODE;
        $this->assertEqualsWithoutLE($code, $file);
    }

    public function testGenerateCreateJunctionMigration()
    {
        $migrationName = 'create_junction_post_and_tag';
        $class = 'm' . gmdate('ymd_His') . '_' . $migrationName;
        $this->runMigrateControllerAction('create', [
            $migrationName,
        ]);
        $file = $this->parseNameClassMigration($class);

        $code = <<<CODE
<?php

use yii\db\Migration;

/**
 * Handles the creation for table `post_tag`.
 * Has foreign keys to the tables:
 *
 * - `post`
 * - `tag`
 */
class {$class} extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        \$this->createTable('post_tag', [
            'post_id' => \$this->integer(),
            'tag_id' => \$this->integer(),
            'PRIMARY KEY(post_id, tag_id)',
        ]);

        // creates index for column `post_id`
        \$this->createIndex(
            'idx-post_tag-post_id',
            'post_tag',
            'post_id'
        );

        // add foreign key for table `post`
        \$this->addForeignKey(
            'fk-post_tag-post_id',
            'post_tag',
            'post_id',
            'post',
            'id',
            'CASCADE'
        );

        // creates index for column `tag_id`
        \$this->createIndex(
            'idx-post_tag-tag_id',
            'post_tag',
            'tag_id'
        );

        // add foreign key for table `tag`
        \$this->addForeignKey(
            'fk-post_tag-tag_id',
            'post_tag',
            'tag_id',
            'tag',
            'id',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        // drops foreign key for table `post`
        \$this->dropForeignKey(
            'fk-post_tag-post_id',
            'post_tag'
        );

        // drops index for column `post_id`
        \$this->dropIndex(
            'idx-post_tag-post_id',
            'post_tag'
        );

        // drops foreign key for table `tag`
        \$this->dropForeignKey(
            'fk-post_tag-tag_id',
            'post_tag'
        );

        // drops index for column `tag_id`
        \$this->dropIndex(
            'idx-post_tag-tag_id',
            'post_tag'
        );

        \$this->dropTable('post_tag');
    }
}

CODE;
        $this->assertEqualsWithoutLE($code, $file);
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
