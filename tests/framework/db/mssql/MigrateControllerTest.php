<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

use PHPUnit\Framework\Attributes\Group;
use Yii;
use yii\console\ExitCode;
use yii\db\Connection;
use yii\helpers\FileHelper;
use yiiunit\framework\console\controllers\EchoMigrateController;
use yiiunit\framework\db\DatabaseTestCase;
use yiiunit\support\DbHelper;

use function file_put_contents;
use function ob_get_clean;
use function ob_implicit_flush;
use function ob_start;

/**
 * Console migration tests for {@see \yii\console\controllers\MigrateController} against the MSSQL driver.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('mssql')]
#[Group('console')]
final class MigrateControllerTest extends DatabaseTestCase
{
    protected $driverName = 'sqlsrv';

    public function testMigrateUpCreatesTableInConfiguredSchema(): void
    {
        $db = $this->getConnection();

        $migrationPath = Yii::getAlias('@yiiunit/runtime/mssql_migrations');

        $artifacts = [
            'migration_test.T_18318',
            'migration_test.migration_18318',
            'dbo.T_18318',
            'dbo.migration_18318',
        ];

        DbHelper::createSchemaIfNotExist($db, 'migration_test');
        DbHelper::dropTablesIfExist($db, $artifacts);

        $db->getSchema()->defaultSchema = 'migration_test';

        FileHelper::createDirectory($migrationPath);

        $this->writeMigrationFile($migrationPath);

        self::assertSame(
            ExitCode::OK,
            $this->runMigrateAction($db, $migrationPath, 'up'),
            'Migrate up must complete successfully.',
        );

        $migratedTable = $db->getTableSchema('migration_test.T_18318', true);

        self::assertNotNull(
            $migratedTable,
            'Migrated table must exist in the configured schema.',
        );
        self::assertSame(
            'migration_test',
            $migratedTable->schemaName,
            'Table must reside in the configured schema, not `dbo`.',
        );
        self::assertNull(
            $db->getTableSchema('dbo.T_18318', true),
            "Table must not leak into the 'dbo' schema.",
        );
        self::assertNotNull(
            $db->getTableSchema('migration_test.migration_18318', true),
            'History table must reside in the configured schema.',
        );
        self::assertNull(
            $db->getTableSchema('dbo.migration_18318', true),
            "History table must not leak into the 'dbo' schema.",
        );
        self::assertSame(
            ExitCode::OK,
            $this->runMigrateAction($db, $migrationPath, 'down'),
            'Migrate down must complete successfully.',
        );
        self::assertNull(
            $db->getTableSchema('migration_test.T_18318', true),
            'Reverted table must be removed from the configured schema.',
        );

        DbHelper::dropTablesIfExist($db, $artifacts);
        DbHelper::dropSchemaIfExist($db, 'migration_test');
        FileHelper::removeDirectory($migrationPath);
    }

    private function runMigrateAction(Connection $db, string $migrationPath, string $action, array $args = []): int
    {
        Yii::$app->controllerMap['migrate'] = [
            'class' => EchoMigrateController::class,
            'db' => $db,
            'interactive' => false,
            'migrationPath' => $migrationPath,
            'migrationTable' => '{{%migration_18318}}',
        ];

        ob_start();
        ob_implicit_flush(false);

        try {
            return Yii::$app->runAction("migrate/$action", $args);
        } finally {
            ob_get_clean();
        }
    }

    private function writeMigrationFile(string $migrationPath): void
    {
        $code = <<<CODE
        <?php

        class m000000_000000_create_t18318 extends \\yii\\db\\Migration
        {
            public function safeUp()
            {
                \$this->createTable('T_18318', ['id' => \$this->primaryKey(), 'name' => \$this->string()]);
            }

            public function safeDown()
            {
                \$this->dropTable('T_18318');
            }
        }
        CODE;

        file_put_contents($migrationPath . DIRECTORY_SEPARATOR . 'm000000_000000_create_t18318.php', $code);
    }
}
