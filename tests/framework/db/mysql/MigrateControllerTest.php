<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql;

use PHPUnit\Framework\Attributes\Group;
use Yii;
use yii\console\controllers\BaseMigrateController;
use yii\console\ExitCode;
use yii\db\Connection;
use yii\db\Query;
use yii\helpers\FileHelper;
use yiiunit\framework\console\controllers\EchoMigrateController;
use yiiunit\framework\db\DatabaseTestCase;
use yiiunit\support\DbHelper;

use function file_put_contents;
use function ob_get_clean;
use function ob_implicit_flush;
use function ob_start;

/**
 * Console migration tests for {@see \yii\console\controllers\MigrateController} against the MySQL driver.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('mysql')]
#[Group('console')]
final class MigrateControllerTest extends DatabaseTestCase
{
    protected $driverName = 'mysql';

    /**
     * @see https://github.com/yiisoft/yii2/issues/11191
     */
    public function testMigrationHistoryIsCommittedWhenAutocommitIsDisabled(): void
    {
        $db = $this->getConnection();

        $migrationPath = Yii::getAlias('@yiiunit/runtime/mysql_migrations_11191');

        FileHelper::createDirectory($migrationPath);

        $migrationTable = 'migration_11191';
        $migrations = [
            'm160325_000001_create_issue_11191_a' => 'issue_11191_a',
            'm160325_000002_create_issue_11191_b' => 'issue_11191_b',
        ];
        $tables = [...array_values($migrations), $migrationTable];

        DbHelper::dropTablesIfExist($db, $tables);

        foreach ($migrations as $class => $table) {
            $this->writeMigrationFile($migrationPath, $class, $table);
        }

        $db->createCommand('SET SESSION autocommit = 0')->execute();

        self::assertSame(
            ExitCode::OK,
            $this->runMigrateAction($db, $migrationPath, $migrationTable),
            'Migrate up must complete successfully.',
        );

        $db->close();
        $db->open();

        $versions = (new Query())
            ->select('version')
            ->from($migrationTable)
            ->orderBy(['version' => SORT_ASC])
            ->column($db);

        self::assertSame(
            [BaseMigrateController::BASE_MIGRATION, ...array_keys($migrations)],
            $versions,
            'Every applied migration must remain in history after the connection is closed.',
        );

        $db->createCommand('SET SESSION autocommit = 0')->execute();

        self::assertSame(
            ExitCode::OK,
            $this->runMigrateAction($db, $migrationPath, $migrationTable, 'down', [2]),
            'Migrate down must complete successfully.',
        );

        $db->close();
        $db->open();

        $versions = (new Query())
            ->select('version')
            ->from($migrationTable)
            ->orderBy(['version' => SORT_ASC])
            ->column($db);

        self::assertSame(
            [BaseMigrateController::BASE_MIGRATION],
            $versions,
            'Every reverted migration must remain removed from history after the connection is closed.',
        );

        $db->close();
        $db->open();
        $db->createCommand('SET SESSION autocommit = 1')->execute();

        DbHelper::dropTablesIfExist($db, $tables);
        FileHelper::removeDirectory($migrationPath);
    }

    private function runMigrateAction(
        Connection $db,
        string $migrationPath,
        string $migrationTable,
        string $action = 'up',
        array $args = [],
    ): int {
        Yii::$app->controllerMap['migrate'] = [
            'class' => EchoMigrateController::class,
            'db' => $db,
            'interactive' => false,
            'migrationPath' => $migrationPath,
            'migrationTable' => $migrationTable,
        ];

        ob_start();
        ob_implicit_flush(false);

        try {
            return Yii::$app->runAction("migrate/$action", $args);
        } finally {
            ob_get_clean();
        }
    }

    private function writeMigrationFile(string $migrationPath, string $class, string $table): void
    {
        $code = <<<CODE
        <?php

        class $class extends \yii\db\Migration
        {
            public function safeUp()
            {
                \$this->createTable('$table', ['id' => \$this->primaryKey()]);
            }

            public function safeDown()
            {
                \$this->dropTable('$table');
            }
        }
        CODE;

        file_put_contents($migrationPath . DIRECTORY_SEPARATOR . $class . '.php', $code);
    }
}
