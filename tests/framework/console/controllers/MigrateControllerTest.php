<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

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
 * @group db
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

    public function assertFileContent($expectedFile, $class, $table)
    {
        $expected = include Yii::getAlias("@yiiunit/data/console/migrate_create/$expectedFile.php");
        $expected = str_replace('{table}', $table, $expected);
        $this->assertEqualsWithoutLE($expected, $this->parseNameClassMigration($class, $table));
    }

    protected function assertCommandCreatedFile($expectedFile, $migrationName, $table, $params = [])
    {
        $class = 'm' . gmdate('ymd_His') . '_' . $migrationName;
        $params[0] = $migrationName;
        $this->runMigrateControllerAction('create', $params);
        $this->assertFileContent($expectedFile, $class, $table);
    }

    // Tests :

    public function testGenerateDefaultMigration()
    {
        $this->assertCommandCreatedFile('default', 'DefaultTest', 'default');
    }

    public function testGenerateCreateMigration()
    {
        $tables = [
            'test',
            'TEST',
        ];
        foreach ($tables as $table) {
            $migrationName = 'create_' . $table . '_table';

            $this->assertCommandCreatedFile('create_test', $migrationName, $table);

            $this->assertCommandCreatedFile('create_fields', $migrationName, $table, [
                'fields' => 'title:string(10):notNull:unique:defaultValue("test"),
                    body:text:notNull,
                    price:money(11,2):notNull,
                    parenthesis_in_comment:string(255):notNull:comment(\'Name of set (RU)\')',
            ]);

            $this->assertCommandCreatedFile('create_title_pk', $migrationName, $table, [
                'fields' => 'title:primaryKey,body:text:notNull,price:money(11,2)',
            ]);

            $this->assertCommandCreatedFile('create_unsigned_pk', $migrationName, $table, [
                'fields' => 'brand_id:primaryKey:unsigned',
            ]);

            $this->assertCommandCreatedFile('create_unsigned_big_pk', $migrationName, $table, [
                'fields' => 'brand_id:bigPrimaryKey:unsigned',
            ]);

            $this->assertCommandCreatedFile('create_id_pk', $migrationName, $table, [
                'fields' => 'id:primaryKey,
                    address:string,
                    address2:string,
                    email:string',
            ]);

            $this->assertCommandCreatedFile('create_foreign_key', $migrationName, $table, [
                'fields' => 'user_id:integer:foreignKey,
                    product_id:foreignKey:integer:unsigned:notNull,
                    order_id:integer:foreignKey(user_order):notNull,
                    created_at:dateTime:notNull',
            ]);

            $this->assertCommandCreatedFile('create_prefix', $migrationName, $table, [
                'useTablePrefix' => true,
                'fields' => 'user_id:integer:foreignKey,
                    product_id:foreignKey:integer:unsigned:notNull,
                    order_id:integer:foreignKey(user_order):notNull,
                    created_at:dateTime:notNull',
            ]);
        }

        // @see https://github.com/yiisoft/yii2/issues/10876
        foreach (['products_from_store', 'products_FROM_store'] as $table) {
            $this->assertCommandCreatedFile('drop_products_from_store_table', 'drop_' . $table . '_table', $table);
        }
        // @see https://github.com/yiisoft/yii2/issues/11461
        $this->assertCommandCreatedFile('create_title_with_comma_default_values', 'create_test_table', 'test', [
            'fields' => 'title:string(10):notNull:unique:defaultValue(",te,st"),
             body:text:notNull:defaultValue(",test"),
             test:custom(11,2,"s"):notNull',
        ]);
    }

    public function testGenerateDropMigration()
    {
        $tables = [
            'test',
            'TEST',
        ];
        foreach ($tables as $table) {
            $migrationName = 'drop_' . $table . '_table';

            $this->assertCommandCreatedFile('drop_test', $migrationName, $table);

            $this->assertCommandCreatedFile('drop_fields', $migrationName, $table, [
                'fields' => 'body:text:notNull,price:money(11,2)',
            ]);
        }

        // @see https://github.com/yiisoft/yii2/issues/10876
        foreach (['products_from_store', 'products_FROM_store'] as $table) {
            $this->assertCommandCreatedFile('drop_products_from_store_table', 'drop_' . $table . '_table', $table);
        }
    }

    public function testGenerateAddColumnMigration()
    {
        $tables = [
            'test',
            'TEST',
        ];
        foreach ($tables as $table) {
            $migrationName = 'add_columns_column_to_' . $table . '_table';

            $this->assertCommandCreatedFile('add_columns_test', $migrationName, $table, [
                'fields' => 'title:string(10):notNull,
                    body:text:notNull,
                    price:money(11,2):notNull,
                    created_at:dateTime',
            ]);

            $this->assertCommandCreatedFile('add_columns_fk', $migrationName, $table, [
                'fields' => 'user_id:integer:foreignKey,
                    product_id:foreignKey:integer:unsigned:notNull,
                    order_id:integer:foreignKey(user_order):notNull,
                    created_at:dateTime:notNull',
            ]);

            $this->assertCommandCreatedFile('add_columns_prefix', $migrationName, $table, [
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
        $tables = [
            'test',
            'TEST',
        ];
        foreach ($tables as $table) {
            $migrationNames = [
                'drop_columns_column_from_' . $table . '_table',
                'drop_columns_columns_from_' . $table . '_table',
            ];
            foreach ($migrationNames as $migrationName) {
                $this->assertCommandCreatedFile('drop_columns_test', $migrationName, $table, [
                    'fields' => 'title:string(10):notNull,body:text:notNull,
                    price:money(11,2):notNull,
                    created_at:dateTime',
                ]);
            }
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
            $this->assertCommandCreatedFile('junction_test', $migrationName, 'post_tag');
        }
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/12980
     */
    public function testGetMigrationHistory()
    {
        $controllerConfig = [
            'migrationPath' => null,
            'migrationNamespaces' => [$this->migrationNamespace],
        ];
        $this->runMigrateControllerAction('history', [], $controllerConfig);

        $controller = $this->createMigrateController($controllerConfig);
        $controller->db = Yii::$app->db;

        Yii::$app->db->createCommand()
            ->batchInsert(
                'migration',
                ['version', 'apply_time'],
                [
                    ['app\migrations\M140506102106One', 10],
                    ['app\migrations\M160909083544Two', 10],
                    ['app\modules\foo\migrations\M161018124749Three', 10],
                    ['app\migrations\M160930135248Four', 20],
                    ['app\modules\foo\migrations\M161025123028Five', 20],
                    ['app\migrations\M161110133341Six', 20],
                ]
            )
            ->execute();

        $rows = $this->invokeMethod($controller, 'getMigrationHistory', [10]);

        $this->assertSame(
            [
                'app\migrations\M161110133341Six',
                'app\modules\foo\migrations\M161025123028Five',
                'app\migrations\M160930135248Four',
                'app\modules\foo\migrations\M161018124749Three',
                'app\migrations\M160909083544Two',
                'app\migrations\M140506102106One',
            ],
            array_keys($rows)
        );

        $rows = $this->invokeMethod($controller, 'getMigrationHistory', [4]);

        $this->assertSame(
            [
                'app\migrations\M161110133341Six',
                'app\modules\foo\migrations\M161025123028Five',
                'app\migrations\M160930135248Four',
                'app\modules\foo\migrations\M161018124749Three',
            ],
            array_keys($rows)
        );
    }
}
