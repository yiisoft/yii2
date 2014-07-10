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
        $this->migrateControllerClass = MigrateController::className();
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
}