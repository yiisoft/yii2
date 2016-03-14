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

    public function testSessionMigration()
    {
        $this->mockWebApplication([
            'components' => [
                'db' => [
                    'class' => 'yii\db\Connection',
                    'dsn' => 'sqlite::memory:',
                ],
                'session' => [
                    'class' => 'yii\web\DbSession'
                ]
            ],
        ]);
        $this->runMigrateControllerAction('down',['all']);

        $this->migrationPath = '@yii/web/migrations';
        $this->runMigrateControllerAction('up');
        $this->assertMigrationHistory(['base', 'session_init']);
        
        $table = Yii::$app->db->schema->getTableSchema('{{%session}}');
        $this->assertNotNull($table);
        $this->assertEquals(array_keys($table->columns), ['id', 'expire', 'data']);

        $this->runMigrateControllerAction('down');
        $this->assertMigrationHistory(['base']);

        $table = Yii::$app->db->schema->getTableSchema('{{%session}}');
        $this->assertNull($table);
    }
}