<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\console\controllers;

use Yii;
use yii\console\controllers\MigrateController;
use yii\console\ExitCode;
use yii\db\Migration;
use yii\db\Query;
use yii\helpers\Inflector;
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

    protected function setUp(): void
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

    protected function tearDown(): void
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

    public function assertFileContent($expectedFile, $class, $table, $namespace = null)
    {
        if ($namespace) {
            $namespace = "namespace {$namespace};\n\n";
        }
        $expected = include Yii::getAlias("@yiiunit/data/console/migrate_create/$expectedFile.php");
        $expected = str_replace('{table}', $table, $expected);
        $this->assertEqualsWithoutLE($expected, $this->parseNameClassMigration($class));
    }

    protected function assertCommandCreatedFile($expectedFile, $migrationName, $table, $params = [])
    {
        $params[0] = $migrationName;
        list($config, $namespace, $class) = $this->prepareMigrationNameData($migrationName);

        $this->runMigrateControllerAction('create', $params, $config);
        $this->assertFileContent($expectedFile, $class, $table, $namespace);
    }

    /**
     * Check config namespace but without input namespace
     * @param mixed $expectedFile
     * @param mixed $migrationName
     * @param mixed $table
     * @param array $params
     */
    protected function assertCommandCreatedFileWithoutNamespaceInput($expectedFile, $migrationName, $table, $params = [])
    {
        $params[0] = $migrationName;
        list($config, $namespace, $class) = $this->prepareMigrationNameData($this->migrationNamespace . '\\' . $migrationName);

        $this->runMigrateControllerAction('create', $params, $config);
        $this->assertFileContent($expectedFile, $class, $table, $namespace);
    }

    public function assertFileContentJunction($expectedFile, $class, $junctionTable, $firstTable, $secondTable, $namespace = null)
    {
        if ($namespace) {
            $namespace = "namespace {$namespace};\n\n";
        }
        $expected = include Yii::getAlias("@yiiunit/data/console/migrate_create/$expectedFile.php");
        $expected = str_replace(
            ['{junctionTable}', '{firstTable}', '{secondTable}'],
            [$junctionTable, $firstTable, $secondTable],
            $expected
        );
        $this->assertEqualsWithoutLE($expected, $this->parseNameClassMigration($class));
    }

    protected function assertCommandCreatedJunctionFile($expectedFile, $migrationName, $junctionTable, $firstTable, $secondTable)
    {
        list($config, $namespace, $class) = $this->prepareMigrationNameData($migrationName);

        $this->runMigrateControllerAction('create', [$migrationName], $config);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertFileContentJunction($expectedFile, $class, $junctionTable, $firstTable, $secondTable, $namespace);
    }

    /**
     * Check config namespace but without input namespace
     * @param mixed $expectedFile
     * @param mixed $migrationName
     * @param mixed $junctionTable
     * @param mixed $firstTable
     * @param mixed $secondTable
     */
    protected function assertCommandCreatedJunctionFileWithoutNamespaceInput($expectedFile, $migrationName, $junctionTable, $firstTable, $secondTable)
    {
        list($config, $namespace, $class) = $this->prepareMigrationNameData($this->migrationNamespace . '\\' . $migrationName);

        $this->runMigrateControllerAction('create', [$migrationName], $config);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertFileContentJunction($expectedFile, $class, $junctionTable, $firstTable, $secondTable, $namespace);
    }

    protected function prepareMigrationNameData($migrationName)
    {
        $config = [];
        $namespace = null;

        $lastSlashPosition = strrpos($migrationName, '\\');
        if ($lastSlashPosition !== false) {
            $config = [
                'migrationPath' => null,
                'migrationNamespaces' => [$this->migrationNamespace],
            ];
            $class = 'M' . gmdate('ymdHis') . Inflector::camelize(substr($migrationName, $lastSlashPosition + 1));
            $namespace = substr($migrationName, 0, $lastSlashPosition);
        } else {
            $class = 'm' . gmdate('ymd_His') . '_' . $migrationName;
        }

        return [$config, $namespace, $class];
    }

    /**
     * @return array
     */
    public function generateMigrationDataProvider()
    {
        $params = [
            'create_fields' => [
                'fields' => 'title:string(10):notNull:unique:defaultValue("test"),
                    body:text:notNull,
                    price:money(11,2):notNull,
                    parenthesis_in_comment:string(255):notNull:comment(\'Name of set (RU)\')',
            ],
            'create_id_field_not_as_pk' => [
                'fields' => 'id:integer(11):notNull',
            ],
            'create_fields_with_col_method_after_default_value' => [
                'fields' => 'id:primaryKey,
                    title:string(10):notNull:unique:defaultValue("test"):after("id"),
                    body:text:notNull:defaultValue("test"):after("title"),
                    address:text:notNull:defaultValue("test"):after("body"),
                    address2:text:notNull:defaultValue(\'te:st\'):after("address"),
                    address3:text:notNull:defaultValue(\':te:st:\'):after("address2")',
            ],
            'create_title_pk' => [
                'fields' => 'title:primaryKey,body:text:notNull,price:money(11,2)',
            ],
            'create_unsigned_pk' => [
                'fields' => 'brand_id:primaryKey:unsigned',
            ],
            'create_unsigned_big_pk' => [
                'fields' => 'brand_id:bigPrimaryKey:unsigned',
            ],
            'create_id_pk' => [
                'fields' => 'id:primaryKey,
                    address:string,
                    address2:string,
                    email:string',
            ],
            'create_foreign_key' => [
                'fields' => 'user_id:integer:foreignKey,
                    product_id:foreignKey:integer:unsigned:notNull,
                    order_id:integer:foreignKey(user_order):notNull,
                    created_at:dateTime:notNull',
            ],
            'create_prefix' => [
                'useTablePrefix' => true,
                'fields' => 'user_id:integer:foreignKey,
                    product_id:foreignKey:integer:unsigned:notNull,
                    order_id:integer:foreignKey(user_order):notNull,
                    created_at:dateTime:notNull',
            ],
            'create_title_with_comma_default_values' => [
                'fields' => 'title:string(10):notNull:unique:defaultValue(",te,st"),
                    body:text:notNull:defaultValue(",test"),
                    test:custom(11,2,"s"):notNull',
            ],
            'create_field_with_colon_default_values' => [
                'fields' => 'field_1:dateTime:notNull:defaultValue(\'0000-00-00 00:00:00\'),
                    field_2:string:defaultValue(\'default:value\')',
            ],
            'drop_fields' => [
                'fields' => 'body:text:notNull,price:money(11,2)',
            ],
            'add_columns_test' => [
                'fields' => 'title:string(10):notNull,
                    body:text:notNull,
                    price:money(11,2):notNull,
                    created_at:dateTime',
            ],
            'add_columns_fk' => [
                'fields' => 'user_id:integer:foreignKey,
                    product_id:foreignKey:integer:unsigned:notNull,
                    order_id:integer:foreignKey(user_order):notNull,
                    created_at:dateTime:notNull',
            ],
            'add_columns_prefix' => [
                'useTablePrefix' => true,
                'fields' => 'user_id:integer:foreignKey,
                    product_id:foreignKey:integer:unsigned:notNull,
                    order_id:integer:foreignKey(user_order):notNull,
                    created_at:dateTime:notNull',
            ],
            'add_two_columns_test' => [
                'fields' => 'field_1:string(10):notNull,
                    field_2:text:notNull',
            ],
            'drop_columns_test' => [
                'fields' => 'title:string(10):notNull,body:text:notNull,
                    price:money(11,2):notNull,
                    created_at:dateTime',
            ],
        ];

        return [
            ['default', 'DefaultTest', 'default', []],

            // underscore + table name = case kept
            ['create_test', 'create_test_table', 'test', []],
            ['create_test', 'create_test__table', 'test_', []],
            ['create_test', 'create_TEST_table', 'TEST', []],
            ['create_test', 'Create_tEsTTable', 'tEsT', []],

            // no underscore + table name = camelcase converted to underscore
            ['create_test', 'CreateTestTable', 'test', []],
            ['create_test', 'createTest_table', 'test', []],
            ['create_test', 'createTe_st_table', 'te_st', []],
            ['create_test', 'createTest__table', 'test_', []],
            ['create_test', 'createTESTtable', 't_e_s_t', []],

            ['create_fields', 'create_test_table', 'test', $params['create_fields']],
            ['create_fields', 'create_TEST_table', 'TEST', $params['create_fields']],
            ['create_id_field_not_as_pk', 'create_test_table', 'test', $params['create_id_field_not_as_pk']],
            ['create_title_pk', 'create_test_table', 'test', $params['create_title_pk']],
            ['create_title_pk', 'create_TEST_table', 'TEST', $params['create_title_pk']],
            ['create_unsigned_pk', 'create_test_table', 'test', $params['create_unsigned_pk']],
            ['create_unsigned_pk', 'create_TEST_table', 'TEST', $params['create_unsigned_pk']],
            ['create_unsigned_big_pk', 'create_test_table', 'test', $params['create_unsigned_big_pk']],
            ['create_unsigned_big_pk', 'create_TEST_table', 'TEST', $params['create_unsigned_big_pk']],
            ['create_id_pk', 'create_test_table', 'test', $params['create_id_pk']],
            ['create_id_pk', 'create_TEST_table', 'TEST', $params['create_id_pk']],
            ['create_foreign_key', 'create_test_table', 'test', $params['create_foreign_key']],
            ['create_foreign_key', 'create_TEST_table', 'TEST', $params['create_foreign_key']],
            ['create_prefix', 'create_test_table', 'test', $params['create_prefix']],
            ['create_prefix', 'create_TEST_table', 'TEST', $params['create_prefix']],

            // @see https://github.com/yiisoft/yii2/issues/11461
            ['create_title_with_comma_default_values', 'create_test_table', 'test', $params['create_title_with_comma_default_values']],
            ['create_field_with_colon_default_values', 'create_test_table', 'test', $params['create_field_with_colon_default_values']],

            // @see https://github.com/yiisoft/yii2/issues/18303
            ['create_fields_with_col_method_after_default_value', 'create_test_table', 'test', $params['create_fields_with_col_method_after_default_value']],

            ['drop_test', 'drop_test_table', 'test', []],
            ['drop_test', 'drop_test__table', 'test_', []],
            ['drop_test', 'drop_TEST_table', 'TEST', []],
            ['drop_test', 'Drop_tEStTable', 'tESt', []],
            ['drop_test', 'DropTestTable', 'test', []],
            ['drop_test', 'DropTest_Table', 'test', []],
            ['drop_test', 'DropTest__Table', 'test_', []],
            ['drop_test', 'DropTESTtable', 't_e_s_t', []],

            ['drop_fields', 'drop_test_table', 'test', $params['drop_fields']],
            ['drop_fields', 'drop_TEST_table', 'TEST', $params['drop_fields']],

            // @see https://github.com/yiisoft/yii2/issues/10876
            ['drop_products_from_store_table', 'drop_products_from_store_table', 'products_from_store', []],
            ['drop_products_from_store_table', 'drop_products_FROM_store_table', 'products_FROM_store', []],

            ['add_columns_test', 'add_columns_column_to_test_table', 'test', $params['add_columns_test']],
            ['add_columns_test', 'add_columns_column_to_test__table', 'test_', $params['add_columns_test']],
            ['add_columns_test', 'add_columns_column_to_TEST_table', 'TEST', $params['add_columns_test']],
            ['add_columns_test', 'AddColumns_column_to_teSTtable', 'teST', $params['add_columns_test']],
            ['add_columns_test', 'AddColumnsColumnTo_tEstTable', 'tEst', $params['add_columns_test']],
            ['add_columns_test', 'addColumnsColumnToTestTable', 'test', $params['add_columns_test']],
            ['add_columns_test', 'AddColumnsColumnToTest_Table', 'test', $params['add_columns_test']],
            ['add_columns_test', 'AddCol__umnsColumnToTest__Table', 'test_', $params['add_columns_test']],
            ['add_columns_test', 'AddColumnsColumnToTESTTable', 't_e_s_t', $params['add_columns_test']],

            ['add_columns_fk', 'add_columns_column_to_test_table', 'test', $params['add_columns_fk']],
            ['add_columns_fk', 'add_columns_column_to_TEST_table', 'TEST', $params['add_columns_fk']],
            ['add_columns_prefix', 'add_columns_column_to_test_table', 'test', $params['add_columns_prefix']],
            ['add_columns_prefix', 'add_columns_column_to_TEST_table', 'TEST', $params['add_columns_prefix']],
            ['add_two_columns_test', 'add_field_1_column_field_2_column_to_test_table', 'test', $params['add_two_columns_test']],
            ['add_two_columns_test', 'add_field_1_column_field_2_column_to_TEST_table', 'TEST', $params['add_two_columns_test']],

            ['drop_columns_test', 'drop_columns_column_from_test_table', 'test', $params['add_columns_test']],
            ['drop_columns_test', 'drop_columns_columns_from_test_table', 'test', $params['add_columns_test']],
            ['drop_columns_test', 'drop_columns_column_from_test__table', 'test_', $params['add_columns_test']],
            ['drop_columns_test', 'drop_columns_column_from_TEST_table', 'TEST', $params['add_columns_test']],
            ['drop_columns_test', 'drop_columns_columns_from_TEST_table', 'TEST', $params['add_columns_test']],
            ['drop_columns_test', 'dropColumnsColumNSFrom_TEstTable', 'TEst', $params['add_columns_test']],
            ['drop_columns_test', 'DropFewColumnsFrom_Test_Table', 'Test', $params['add_columns_test']],
            ['drop_columns_test', 'DropFewColumnsFromTestTable', 'test', $params['add_columns_test']],
            ['drop_columns_test', 'DropFewColumnsFromTest_Table', 'test', $params['add_columns_test']],
            ['drop_columns_test', 'DropFewColumnsFromTest__Table', 'test_', $params['add_columns_test']],
            ['drop_columns_test', 'DropFewColumnsFromTeStTable', 'te_st', $params['add_columns_test']],
        ];
    }

    /**
     * @param string $expectedFile
     * @param string $migrationName
     * @param string $table
     * @param array $params
     * @dataProvider generateMigrationDataProvider
     */
    public function testGenerateMigration($expectedFile, $migrationName, $table, $params)
    {
        $this->migrationNamespace = 'yiiunit\runtime\test_migrations';

        $this->assertCommandCreatedFile($expectedFile, $migrationName, $table, $params);
        $this->assertCommandCreatedFile(
            $expectedFile,
            $this->migrationNamespace . '\\' . $migrationName,
            $table,
            $params
        );
        $this->assertCommandCreatedFileWithoutNamespaceInput($expectedFile, $migrationName, $table, $params);
    }

    /**
     * @return array
     */
    public function generateJunctionMigrationDataProvider()
    {
        return [
            ['create_junction_post_and_tag_tables', 'post_tag', 'post', 'tag'],
            ['create_junction_for_post_and_tag_tables', 'post_tag', 'post', 'tag'],
            ['create_junction_table_for_post_and_tag_tables', 'post_tag', 'post', 'tag'],
            ['create_junction_table_for_post_and_tag_table', 'post_tag', 'post', 'tag'],
            ['CreateJunction_postAnd_tagTables', 'post_tag', 'post', 'tag'],
            ['CreateJunctionFor_postAnd_tagTables', 'post_tag', 'post', 'tag'],
            ['CreateJunctionTableFor_postAnd_tagTables', 'post_tag', 'post', 'tag'],
            ['CreateJunctionTableFor_postAnd_tagTable', 'post_tag', 'post', 'tag'],
            ['CreateJunctionPostAndTagTables', 'post_tag', 'post', 'tag'],
            ['CreateJunctionPost_AndTag_Tables', 'post_tag', 'post', 'tag'],
            ['CreateJunctionPost__AndTag__Tables', 'post__tag_', 'post_', 'tag_'],
            ['CreateJunctionPost__AndTagTables', 'post__tag', 'post_', 'tag'],
            ['CreateJunctionPostAndTag__Tables', 'post_tag_', 'post', 'tag_'],
            ['CreateJunctionPostAndTaGTables', 'post_ta_g', 'post', 'ta_g'],
            ['CreateJunctionPoStAndTagTables', 'po_st_tag', 'po_st', 'tag'],
        ];
    }

    /**
     * @param string $migrationName
     * @param string $junctionTable
     * @param string $firstTable
     * @param string $secondTable
     * @dataProvider generateJunctionMigrationDataProvider
     */
    public function testGenerateJunctionMigration($migrationName, $junctionTable, $firstTable, $secondTable)
    {
        $this->migrationNamespace = 'yiiunit\runtime\test_migrations';

        $this->assertCommandCreatedJunctionFile(
            'junction_test',
            $migrationName,
            $junctionTable,
            $firstTable,
            $secondTable
        );
        $this->assertCommandCreatedJunctionFile(
            'junction_test',
            $this->migrationNamespace . '\\' . $migrationName,
            $junctionTable,
            $firstTable,
            $secondTable
        );
        $this->assertCommandCreatedJunctionFileWithoutNamespaceInput(
            'junction_test',
            $migrationName,
            $junctionTable,
            $firstTable,
            $secondTable
        );
    }

    public function testUpdatingLongNamedMigration()
    {
        $this->createMigration(str_repeat('a', 180));

        $result = $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::UNSPECIFIED_ERROR, $this->getExitCode());

        $this->assertStringContainsString('The migration name', $result);
        $this->assertStringContainsString('is too long. Its not possible to apply this migration.', $result);
    }

    public function testNamedMigrationWithCustomLimit()
    {
        Yii::$app->db->createCommand()->createTable('migration', [
            'version' => 'varchar(255) NOT NULL PRIMARY KEY', // varchar(255) is longer than the default of 180
            'apply_time' => 'integer',
        ])->execute();

        $this->createMigration(str_repeat('a', 180));

        $result = $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $this->assertStringContainsString('1 migration was applied.', $result);
        $this->assertStringContainsString('Migrated up successfully.', $result);
    }

    public function testCreateLongNamedMigration()
    {
        $this->setOutputCallback(function ($output) {
            return null;
        });

        $migrationName = str_repeat('a', 180);

        $this->expectException('yii\console\Exception');
        $this->expectExceptionMessage('The migration name is too long.');

        $controller = $this->createMigrateController([]);
        $params[0] = $migrationName;
        $controller->run('create', $params);
    }

    /**
     * Test the migrate:fresh command.
     * @dataProvider refreshMigrationDataProvider
     * @param $db
     * @throws \yii\db\Exception
     */
    public function testRefreshMigration($db)
    {
        if ($db !== 'default') {
            $this->switchDbConnection($db);
        }

        Yii::$app->db->createCommand('create table hall_of_fame(id int, string varchar(255))')->execute();

        Yii::$app->db->createCommand("insert into hall_of_fame values(1, 'Qiang Xue');")->execute();
        Yii::$app->db->createCommand("insert into hall_of_fame values(2, 'Alexander Makarov');")->execute();

        Yii::$app->db->createCommand('create view view_hall_of_fame as select * from hall_of_fame')->execute();

        $result = $this->runMigrateControllerAction('fresh');
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        // Drop worked
        $this->assertStringContainsString('Table hall_of_fame dropped.', $result);
        $this->assertStringContainsString('View view_hall_of_fame dropped.', $result);

        // Migration was restarted
        $this->assertStringContainsString('No new migrations found. Your system is up-to-date.', $result);
    }

    public function refreshMigrationDataProvider()
    {
        return [
            ['default'],
            ['mysql'],
        ];
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
