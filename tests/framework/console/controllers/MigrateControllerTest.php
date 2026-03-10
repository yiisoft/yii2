<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\console\controllers;

use yii\base\NotSupportedException;
use yii\db\Exception;
use Yii;
use yii\console\controllers\BaseMigrateController;
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
    /** @use MigrateControllerTestTrait<EchoMigrateController> */
    use MigrateControllerTestTrait;

    protected function setUp(): void
    {
        $this->migrateControllerClass = EchoMigrateController::class;
        $this->migrationBaseClass = Migration::class;

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

    public function assertFileContent($expectedFile, $class, $table, $namespace = null): void
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

    public function assertFileContentJunction($expectedFile, $class, $junctionTable, $firstTable, $secondTable, $namespace = null): void
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
    public static function generateMigrationDataProvider(): array
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
    public function testGenerateMigration($expectedFile, $migrationName, $table, $params): void
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
    public static function generateJunctionMigrationDataProvider(): array
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
    public function testGenerateJunctionMigration($migrationName, $junctionTable, $firstTable, $secondTable): void
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

    public function testUpdatingLongNamedMigration(): void
    {
        $this->createMigration(str_repeat('a', 180));

        $result = $this->runMigrateControllerAction('up');
        $this->assertSame(ExitCode::UNSPECIFIED_ERROR, $this->getExitCode());

        $this->assertStringContainsString('The migration name', $result);
        $this->assertStringContainsString('is too long. Its not possible to apply this migration.', $result);
    }

    public function testNamedMigrationWithCustomLimit(): void
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

    public function testCreateLongNamedMigration(): void
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
     * @throws Exception
     */
    public function testRefreshMigration($db): void
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

    public static function refreshMigrationDataProvider(): array
    {
        return [
            ['default'],
            ['mysql'],
        ];
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/12980
     */
    public function testGetMigrationHistory(): void
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

    public function testGetMigrationHistoryWithoutNamespaces(): void
    {
        $controller = $this->createMigrateController([]);
        $controller->db = Yii::$app->db;

        $this->runMigrateControllerAction('history');

        $rows = $this->invokeMethod($controller, 'getMigrationHistory', [10]);
        $this->assertSame([], $rows);

        $this->createMigration('hist_no_ns1', '010101_000001');
        $this->createMigration('hist_no_ns2', '010101_000002');
        $this->runMigrateControllerAction('up');

        $rows = $this->invokeMethod($controller, 'getMigrationHistory', [10]);
        $this->assertSame([
            'm010101_000002_hist_no_ns2',
            'm010101_000001_hist_no_ns1',
        ], array_keys($rows));
    }

    public function testGetMigrationHistoryWithoutNamespacesAndLimit(): void
    {
        $controller = $this->createMigrateController([]);
        $controller->db = Yii::$app->db;

        $this->runMigrateControllerAction('history');

        $this->createMigration('hist_limit1', '010101_000001');
        $this->createMigration('hist_limit2', '010101_000002');
        $this->createMigration('hist_limit3', '010101_000003');
        $this->runMigrateControllerAction('up');

        $rows = $this->invokeMethod($controller, 'getMigrationHistory', [2]);
        $this->assertSame([
            'm010101_000003_hist_limit3',
            'm010101_000002_hist_limit2',
        ], array_keys($rows));
    }

    public function testMigrateControllerOptionsForCreate(): void
    {
        $controller = $this->createMigrateController([]);
        $options = $controller->options('create');
        $this->assertContains('fields', $options);
        $this->assertContains('useTablePrefix', $options);
        $this->assertContains('comment', $options);
        $this->assertContains('templateFile', $options);
        $this->assertContains('migrationTable', $options);
        $this->assertContains('db', $options);
    }

    public function testMigrateControllerOptionsForUp(): void
    {
        $controller = $this->createMigrateController([]);
        $options = $controller->options('up');
        $this->assertNotContains('fields', $options);
        $this->assertNotContains('useTablePrefix', $options);
        $this->assertNotContains('comment', $options);
        $this->assertContains('migrationTable', $options);
        $this->assertContains('db', $options);
    }

    public function testOptionAliases(): void
    {
        $controller = $this->createMigrateController([]);
        $aliases = $controller->optionAliases();
        $this->assertSame('comment', $aliases['C']);
        $this->assertSame('fields', $aliases['f']);
        $this->assertSame('migrationPath', $aliases['p']);
        $this->assertSame('migrationTable', $aliases['t']);
        $this->assertSame('templateFile', $aliases['F']);
        $this->assertSame('useTablePrefix', $aliases['P']);
        $this->assertSame('compact', $aliases['c']);
    }

    public function testTruncateDatabaseDropsTablesAndViews(): void
    {
        Yii::$app->db->createCommand('CREATE TABLE test_table1(id INTEGER PRIMARY KEY, name TEXT)')->execute();
        Yii::$app->db->createCommand('CREATE TABLE test_table2(id INTEGER PRIMARY KEY, value TEXT)')->execute();
        Yii::$app->db->createCommand('CREATE VIEW test_view1 AS SELECT * FROM test_table1')->execute();

        $controller = $this->createMigrateController([]);
        $controller->db = Yii::$app->db;

        ob_start();
        $this->invokeMethod($controller, 'truncateDatabase');
        $output = ob_get_clean();

        $this->assertStringContainsString('dropped.', $output);

        $tables = Yii::$app->db->schema->getTableNames('', true);
        $this->assertNotContains('test_table1', $tables);
        $this->assertNotContains('test_table2', $tables);
        $this->assertNotContains('test_view1', $tables);
    }

    public function testGetMigrationNameLimitReturnsColumnSize(): void
    {
        $this->runMigrateControllerAction('history');

        $controller = $this->createMigrateController([]);
        $controller->db = Yii::$app->db;

        $limit = $this->invokeMethod($controller, 'getMigrationNameLimit');
        $this->assertSame(MigrateController::MAX_NAME_LENGTH, $limit);
    }

    public function testGetMigrationNameLimitReturnsCachedValue(): void
    {
        $this->runMigrateControllerAction('history');

        $controller = $this->createMigrateController([]);
        $controller->db = Yii::$app->db;

        $limit1 = $this->invokeMethod($controller, 'getMigrationNameLimit');
        $limit2 = $this->invokeMethod($controller, 'getMigrationNameLimit');
        $this->assertSame($limit1, $limit2);
    }

    public function testGenerateTableNameWithPrefix(): void
    {
        $controller = $this->createMigrateController(['useTablePrefix' => true]);
        $result = $this->invokeMethod($controller, 'generateTableName', ['my_table']);
        $this->assertSame('{{%my_table}}', $result);
    }

    public function testGenerateTableNameWithoutPrefix(): void
    {
        $controller = $this->createMigrateController(['useTablePrefix' => false]);
        $result = $this->invokeMethod($controller, 'generateTableName', ['my_table']);
        $this->assertSame('my_table', $result);
    }

    public function testAddDefaultPrimaryKeyWhenNoIdField(): void
    {
        $controller = $this->createMigrateController([]);
        $fields = [
            ['property' => 'name', 'decorators' => 'string()'],
        ];
        $this->invokeMethod($controller, 'addDefaultPrimaryKey', [&$fields]);
        $this->assertSame('id', $fields[0]['property']);
        $this->assertSame('primaryKey()', $fields[0]['decorators']);
        $this->assertCount(2, $fields);
    }

    public function testAddDefaultPrimaryKeySkipsWhenIdExists(): void
    {
        $controller = $this->createMigrateController([]);
        $fields = [
            ['property' => 'id', 'decorators' => 'integer()'],
            ['property' => 'name', 'decorators' => 'string()'],
        ];
        $this->invokeMethod($controller, 'addDefaultPrimaryKey', [&$fields]);
        $this->assertCount(2, $fields);
        $this->assertSame('integer()', $fields[0]['decorators']);
    }

    public function testAddDefaultPrimaryKeySkipsWhenPrimaryKeyDecorator(): void
    {
        $controller = $this->createMigrateController([]);
        $fields = [
            ['property' => 'custom_id', 'decorators' => 'primaryKey()'],
            ['property' => 'name', 'decorators' => 'string()'],
        ];
        $this->invokeMethod($controller, 'addDefaultPrimaryKey', [&$fields]);
        $this->assertCount(2, $fields);
        $this->assertSame('custom_id', $fields[0]['property']);
    }

    public function testParseFieldsWithForeignKey(): void
    {
        $controller = $this->createMigrateController([
            'fields' => ['user_id:integer:foreignKey'],
        ]);
        $result = $this->invokeMethod($controller, 'parseFields');
        $this->assertArrayHasKey('user_id', $result['foreignKeys']);
        $this->assertSame('user', $result['foreignKeys']['user_id']['table']);
        $this->assertNull($result['foreignKeys']['user_id']['column']);
    }

    public function testParseFieldsWithForeignKeyAndTable(): void
    {
        $controller = $this->createMigrateController([
            'fields' => ['order_id:integer:foreignKey(user_order)'],
        ]);
        $result = $this->invokeMethod($controller, 'parseFields');
        $this->assertArrayHasKey('order_id', $result['foreignKeys']);
        $this->assertSame('user_order', $result['foreignKeys']['order_id']['table']);
    }

    public function testParseFieldsWithoutForeignKey(): void
    {
        $controller = $this->createMigrateController([
            'fields' => ['name:string(255):notNull'],
        ]);
        $result = $this->invokeMethod($controller, 'parseFields');
        $this->assertEmpty($result['foreignKeys']);
        $this->assertCount(1, $result['fields']);
        $this->assertSame('name', $result['fields'][0]['property']);
    }

    public function testSplitFieldIntoChunksWithDefaultValue(): void
    {
        $controller = $this->createMigrateController([]);
        $result = $this->invokeMethod($controller, 'splitFieldIntoChunks', ['field_1:string:defaultValue(\'val:ue\')']);
        $this->assertSame('field_1', $result[0]);
        $this->assertSame('string', $result[1]);
        $this->assertSame("defaultValue('val:ue')", $result[2]);
    }

    public function testSplitFieldIntoChunksWithoutDefaultValue(): void
    {
        $controller = $this->createMigrateController([]);
        $result = $this->invokeMethod($controller, 'splitFieldIntoChunks', ['name:string(255):notNull']);
        $this->assertSame('name', $result[0]);
        $this->assertSame('string(255)', $result[1]);
        $this->assertSame('notNull', $result[2]);
    }

    public function testBaseMigrateControllerTruncateDatabaseThrowsNotSupportedException(): void
    {
        $this->expectException(NotSupportedException::class);

        $controller = $this->createMigrateController([]);
        $parent = new \ReflectionClass(BaseMigrateController::class);
        $method = $parent->getMethod('truncateDatabase');

        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }
        $method->invoke($controller);
    }

    public function testCreateMigrationHistoryTable(): void
    {
        $tableNames = Yii::$app->db->schema->getTableNames('', true);
        $this->assertNotContains('migration', $tableNames);

        $this->runMigrateControllerAction('history');

        $tableNames = Yii::$app->db->schema->getTableNames('', true);
        $this->assertContains('migration', $tableNames);
    }

    public function testNewWithNamespacedMigrations(): void
    {
        $controllerConfig = [
            'migrationPath' => null,
            'migrationNamespaces' => [$this->migrationNamespace],
        ];

        $this->createNamespaceMigration('nsNew1');
        $this->createNamespaceMigration('nsNew2');

        $output = $this->runMigrateControllerAction('new', [], $controllerConfig);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertStringContainsString('Found 2 new migrations:', $output);
    }

    public function testHistoryWithNamespacesShowsAll(): void
    {
        $controllerConfig = [
            'migrationPath' => null,
            'migrationNamespaces' => [$this->migrationNamespace],
        ];

        $this->createNamespaceMigration('histAll1');
        $this->createNamespaceMigration('histAll2');
        $this->runMigrateControllerAction('up', [], $controllerConfig);
        $this->assertSame(ExitCode::OK, $this->getExitCode());

        $output = $this->runMigrateControllerAction('history', ['all'], $controllerConfig);
        $this->assertSame(ExitCode::OK, $this->getExitCode());
        $this->assertStringContainsString('Total 2 migrations have been applied before:', $output);
    }

    public function testNormalizeTableNameTrailingUnderscore(): void
    {
        $controller = $this->createMigrateController([]);
        $result = $this->invokeMethod($controller, 'normalizeTableName', ['test_']);
        $this->assertSame('test', $result);
    }

    public function testNormalizeTableNameLeadingUnderscore(): void
    {
        $controller = $this->createMigrateController([]);
        $result = $this->invokeMethod($controller, 'normalizeTableName', ['_Test']);
        $this->assertSame('Test', $result);
    }

    public function testNormalizeTableNameCamelCase(): void
    {
        $controller = $this->createMigrateController([]);
        $result = $this->invokeMethod($controller, 'normalizeTableName', ['MyTable']);
        $this->assertSame('my_table', $result);
    }

    public function testBeforeActionReturnsFalseWhenEventCancelled(): void
    {
        $controller = $this->createMigrateController([]);
        $controller->on(\yii\base\Controller::EVENT_BEFORE_ACTION, function ($event) {
            $event->isValid = false;
        });

        ob_start();
        $exitCode = $controller->runAction('up');
        ob_end_clean();

        $this->assertNull($exitCode);
    }

    public function testGetMigrationHistoryWithNonMatchingVersion(): void
    {
        $controllerConfig = [
            'migrationPath' => null,
            'migrationNamespaces' => [$this->migrationNamespace],
        ];

        $this->runMigrateControllerAction('history', [], $controllerConfig);

        Yii::$app->db->createCommand()->insert('migration', [
            'version' => 'custom_no_timestamp',
            'apply_time' => time(),
        ])->execute();

        $controller = $this->createMigrateController($controllerConfig);
        $controller->db = Yii::$app->db;

        $rows = $this->invokeMethod($controller, 'getMigrationHistory', [10]);
        $this->assertArrayHasKey('custom_no_timestamp', $rows);
    }

    public function testIsViewRelatedReturnsFalseForUnrelatedError(): void
    {
        $controller = $this->createMigrateController([]);
        $result = $this->invokeMethod($controller, 'isViewRelated', ['Some random database error']);
        $this->assertFalse($result);
    }

    public function testParseFieldsWithForeignKeyAndColumnSpec(): void
    {
        $controller = $this->createMigrateController([
            'fields' => ['user_id:integer:foreignKey(users user_pk)'],
        ]);
        $result = $this->invokeMethod($controller, 'parseFields');
        $this->assertSame('user_pk', $result['foreignKeys']['user_id']['column']);
        $this->assertSame('users', $result['foreignKeys']['user_id']['table']);
    }

    public function testFkResolvesColumnFromSinglePkTable(): void
    {
        Yii::$app->db->createCommand('CREATE TABLE fk_user(pk_col INTEGER PRIMARY KEY, name TEXT)')->execute();

        $controller = $this->createMigrateController([
            'fields' => ['fk_user_id:integer:foreignKey(fk_user)'],
        ]);
        $controller->db = Yii::$app->db;

        ob_start();
        $result = $this->invokeMethod($controller, 'generateMigrationSourceCode', [[
            'name' => 'create_fk_test_table',
            'className' => 'm000000_000000_create_fk_test_table',
            'namespace' => null,
        ]]);
        ob_end_clean();

        $this->assertStringContainsString("'pk_col'", $result);
    }

    public function testFkCompositePkUsesDefaultColumn(): void
    {
        Yii::$app->db->createCommand('CREATE TABLE fk_order_item(order_id INTEGER, product_id INTEGER, PRIMARY KEY(order_id, product_id))')->execute();

        $controller = $this->createMigrateController([
            'fields' => ['fk_order_item_id:integer:foreignKey(fk_order_item)'],
        ]);
        $controller->db = Yii::$app->db;

        ob_start();
        $result = $this->invokeMethod($controller, 'generateMigrationSourceCode', [[
            'name' => 'create_fk_comp_table',
            'className' => 'm000000_000000_create_fk_comp_table',
            'namespace' => null,
        ]]);
        $output = ob_get_clean();

        $this->assertStringContainsString('primary key is composite', $output);
        $this->assertStringContainsString("'id'", $result);
    }

    public function testFkNoPkUsesDefaultColumn(): void
    {
        Yii::$app->db->createCommand('CREATE TABLE fk_tag(name TEXT, value TEXT)')->execute();

        $controller = $this->createMigrateController([
            'fields' => ['fk_tag_id:integer:foreignKey(fk_tag)'],
        ]);
        $controller->db = Yii::$app->db;

        ob_start();
        $result = $this->invokeMethod($controller, 'generateMigrationSourceCode', [[
            'name' => 'create_fk_nopk_table',
            'className' => 'm000000_000000_create_fk_nopk_table',
            'namespace' => null,
        ]]);
        $output = ob_get_clean();

        $this->assertStringContainsString('does not have a primary key', $output);
        $this->assertStringContainsString("'id'", $result);
    }

    public function testCreateMigrationWithUnknownNamespaceThrowsException(): void
    {
        $this->setOutputCallback(function ($output) {
            return null;
        });

        $this->expectException(\yii\console\Exception::class);
        $this->expectExceptionMessage('not found in `migrationNamespaces`');

        $controller = $this->createMigrateController([
            'migrationPath' => null,
            'migrationNamespaces' => [$this->migrationNamespace],
        ]);
        $controller->run('create', ['unknown\\SomeMigration']);
    }

    public function testActionFreshConfirmDenied(): void
    {
        $this->runMigrateControllerAction('history');

        $module = $this->getMockBuilder(\yii\base\Module::class)
            ->addMethods(['fake'])
            ->setConstructorArgs(['console'])
            ->getMock();
        $controller = new DenyingEchoMigrateController('migrate', $module);
        $controller->interactive = false;
        $controller->migrationPath = $this->migrationPath;

        ob_start();
        $exitCode = $controller->run('fresh');
        $output = ob_get_clean();

        $this->assertSame(ExitCode::OK, $exitCode);
        $this->assertStringContainsString('Action was cancelled by user.', $output);
    }
}

class DenyingEchoMigrateController extends EchoMigrateController
{
    public function confirm($message, $default = false)
    {
        return false;
    }
}
