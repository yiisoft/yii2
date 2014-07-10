<?php

namespace yiiunit\extensions\mongodb\console\controllers;

use yii\mongodb\Exception;
use yii\mongodb\Migration;
use yii\mongodb\Query;
use Yii;
use yiiunit\extensions\mongodb\MongoDbTestCase;
use yiiunit\framework\console\controllers\MigrateControllerTestTrait;
use yii\mongodb\console\controllers\MigrateController;

/**
 * Unit test for [[\yii\mongodb\console\controllers\MigrateController]].
 * @see MigrateController
 *
 * @group mongodb
 * @group console
 */
class MigrateControllerTest extends MongoDbTestCase
{
    use MigrateControllerTestTrait;

    public function setUp()
    {
        $this->migrateControllerClass = MigrateController::className();
        $this->migrationBaseClass = Migration::className();

        parent::setUp();

        $this->setUpMigrationPath();
        Yii::$app->setComponents(['mongodb' => $this->getConnection()]);
    }

    public function tearDown()
    {
        parent::tearDown();
        try {
            $this->getConnection()->getCollection('migration')->drop();
        } catch (Exception $e) {
            // shutdown exception
        }
        $this->tearDownMigrationPath();
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