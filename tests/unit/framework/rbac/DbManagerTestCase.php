<?php
namespace yiiunit\framework\rbac;

use yii\console\Application;
use yii\console\controllers\MigrateController;
use yii\db\Connection;
use yii\rbac\DbManager;

/**
 * DbManagerTestCase
 */
abstract class DbManagerTestCase extends ManagerTestCase
{
    protected static $database;
    protected static $driverName = 'mysql';

    /**
     * @var Connection
     */
    protected static $db;

    /**
     * @return MigrateController
     */
    protected static function getMigrator()
    {
        $app = new Application([
            'id' => 'Migrator',
            'basePath' => '@yiiunit',
            'components' => [
                'db' => static::getConnection(),
                'authManager' => '\yii\rbac\DbManager',
            ],
        ]);

        $migrator = new MigrateController('migrate', $app, []);
        $migrator->migrationPath = '@yii/rbac/migrations/';
        $migrator->interactive = false;
        return $migrator;
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $databases = static::getParam('databases');
        static::$database = $databases[static::$driverName];
        $pdo_database = 'pdo_' . static::$driverName;

        if (!extension_loaded('pdo') || !extension_loaded($pdo_database)) {
            static::markTestSkipped('pdo and ' . $pdo_database . ' extension are required.');
        }

        static::getMigrator()->run('up');
    }

    public static function tearDownAfterClass()
    {
        static::getMigrator()->run('down');
        if (static::$db) {
            static::$db->close();
        }
        \Yii::$app = null;
        parent::tearDownAfterClass();
    }

    protected function setUp()
    {
        parent::setUp();
        $this->auth = new DbManager(['db' => $this->getConnection()]);

    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->auth->removeAll();
    }

    /**
     * @throws \yii\base\InvalidParamException
     * @throws \yii\db\Exception
     * @throws \yii\base\InvalidConfigException
     * @return \yii\db\Connection
     */
    public static function getConnection()
    {
        if (static::$db == null) {
            $db = new Connection;
            $db->dsn = static::$database['dsn'];
            if (isset(static::$database['username'])) {
                $db->username = static::$database['username'];
                $db->password = static::$database['password'];
            }
            if (isset(static::$database['attributes'])) {
                $db->attributes = static::$database['attributes'];
            }
            if (!$db->isActive) {
                $db->open();
            }
            static::$db = $db;
        }
        return static::$db;
    }
}
