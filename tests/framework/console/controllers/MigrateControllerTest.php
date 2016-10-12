<?php

namespace yiiunit\framework\console\controllers;

use Yii;
use yii\console\controllers\MigrateController;
use yii\db\Migration;
use yii\db\Query;
use yiiunit\TestCase;

/**
 * Unit test for [[\yii\console\controllers\MigrateController]].
 * @see MigrateController
 *
 * @group console
 */
class MigrateControllerTest extends TestCase
{
    use MigrateControllerTestTrait;

    public function setUp()
    {
        $this->migrateControllerClass = EchoMigrateController::className();
        $this->migrationBaseClass = Migration::className();

        $this->mockApplication([
            'components' => [
                'db' => [
                    'class' => 'yii\db\Connection',
                    'dsn' => 'sqlite::memory:',
                ],
            ],
        ]);

        $this->setUpMigrationPath();
        parent::setUp();
    }

    public function tearDown()
    {
        $this->tearDownMigrationPath();
        parent::tearDown();
    }

    /**
     * @return array applied migration entries
     */
    protected function getMigrationHistory()
    {
        $query = new Query();
        return $query->from('migration')->all();
    }

    protected function assertFileContent($expectedFile, $class)
    {
        $this->assertEqualsWithoutLE(
            include Yii::getAlias("@yiiunit/data/console/migrate_create/$expectedFile.php"),
            $this->parseNameClassMigration($class)
        );
    }

    protected function assertCommandCreatedFile($expectedFile, $migrationName, $params = [])
    {
        $class = 'm' . gmdate('ymd_His') . '_' . $migrationName;
        $params[0] = $migrationName;
        $this->runMigrateControllerAction('create', $params);
        $this->assertFileContent($expectedFile, $class);
    }

    // Tests :

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
                    price:money(11,2):notNull,
                    parenthesis_in_comment:string(255):notNull:comment(\'Name of set (RU)\')'
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
}