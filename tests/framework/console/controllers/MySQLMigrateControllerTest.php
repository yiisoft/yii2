<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\console\controllers;

use Yii;
use yii\console\ExitCode;
use yii\db\Connection;
use yii\helpers\FileHelper;
use yiiunit\TestCase;

/**
 * Tests [[\yii\console\controllers\MigrateController]] against a MySQL database.
 *
 * @group console
 * @group db
 * @group mysql
 */
class MySQLMigrateControllerTest extends TestCase
{
    /**
     * @var string|null
     */
    private $migrationPath;

    protected function setUp(): void
    {
        parent::setUp();

        if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('pdo and pdo_mysql extensions are required.');
        }

        $database = self::getParam('databases')['mysql'];

        $this->mockApplication([
            'components' => [
                'db' => [
                    'class' => Connection::class,
                    'dsn' => $database['dsn'],
                    'username' => $database['username'],
                    'password' => $database['password'],
                ],
            ],
        ]);

        $this->migrationPath = Yii::getAlias('@yiiunit/runtime/test_migrations');

        FileHelper::createDirectory($this->migrationPath);
    }

    protected function tearDown(): void
    {
        if ($this->migrationPath !== null) {
            FileHelper::removeDirectory($this->migrationPath);
        }

        parent::tearDown();
    }

    public function testRefreshMigration(): void
    {
        $db = Yii::$app->db;

        $db->createCommand('create table hall_of_fame(id int, string varchar(255))')->execute();
        $db->createCommand("insert into hall_of_fame values(1, 'Qiang Xue');")->execute();
        $db->createCommand("insert into hall_of_fame values(2, 'Alexander Makarov');")->execute();
        $db->createCommand('create view view_hall_of_fame as select * from hall_of_fame')->execute();

        $controller = new EchoMigrateController('migrate', Yii::$app);

        $controller->interactive = false;
        $controller->migrationPath = $this->migrationPath;

        ob_start();
        ob_implicit_flush(false);

        $exitCode = $controller->run('fresh');
        $result = ob_get_clean();

        $this->assertSame(
            ExitCode::OK,
            $exitCode,
            'Command must exit successfully.',
        );
        $this->assertStringContainsString(
            'Table hall_of_fame dropped.',
            $result,
            'Existing table must be dropped.',
        );
        $this->assertStringContainsString(
            'View view_hall_of_fame dropped.',
            $result,
            'Existing view must be dropped.',
        );
        $this->assertStringContainsString(
            'No new migrations found. Your system is up-to-date.',
            $result,
            'Migrations must be applied again.',
        );
    }
}
