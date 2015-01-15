<?php
namespace yiiunit\framework\rbac;

use Yii;
use yii\console\Application;
use yii\console\Controller;
use yii\db\Connection;
use yii\rbac\DbManager;
use yii\rbac\models\Assignment;
use yii\rbac\models\Item;

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

    protected static function runConsoleAction($route, $params = [])
    {
        if (Yii::$app === null) {
            new Application([
                'id' => 'Migrator',
                'basePath' => '@yiiunit',
                'components' => [
                    'db' => static::getConnection(),
                    'authManager' => '\yii\rbac\DbManager',
                ],
            ]);
        }

        ob_start();
        $result = Yii::$app->runAction('migrate/up', ['migrationPath' => '@yii/rbac/migrations/', 'interactive' => false]);
        echo "Result is ".$result;
        if ($result !== Controller::EXIT_CODE_NORMAL) {
            ob_end_flush();
        } else {
            ob_end_clean();
        }
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

        static::runConsoleAction('migrate/up', ['migrationPath' => '@yii/rbac/migrations/', 'interactive' => false]);
    }

    public static function tearDownAfterClass()
    {
        static::runConsoleAction('migrate/down', ['migrationPath' => '@yii/rbac/migrations/', 'interactive' => false]);
        if (static::$db) {
            static::$db->close();
        }
        Yii::$app = null;
        parent::tearDownAfterClass();
    }

    protected function setUp()
    {
        parent::setUp();
        if (Yii::$app === null) {
            $this->mockApplication([
                'components' => [
                    'db' => static::getConnection(),
                    'authManager' => $this->createManager(),
                ],
            ]);
        }
        $this->auth = $this->createManager();
    }

    public function testCreateRole()
    {
        $role = $this->auth->createRole('admin');
        $this->assertTrue($role instanceof Item);
        $this->assertEquals(Item::TYPE_ROLE, $role->type);
        $this->assertEquals('admin', $role->name);
    }

    public function testCreatePermission()
    {
        $permission = $this->auth->createPermission('edit post');
        $this->assertTrue($permission instanceof Item);
        $this->assertEquals(Item::TYPE_PERMISSION, $permission->type);
        $this->assertEquals('edit post', $permission->name);
    }

    public function testGetRolesByUser()
    {
        $this->prepareData();
        $roles = $this->auth->getRolesByUser('reader A');
        $this->assertTrue(reset($roles) instanceof Item);
        $this->assertEquals($roles['reader']->name, 'reader');
    }

    public function testGetPermissionsByRole()
    {
        $this->prepareData();
        $roles = $this->auth->getPermissionsByRole('admin');
        $expectedPermissions = ['createPost', 'updatePost', 'readPost', 'updateAnyPost'];
        $this->assertEquals(count($roles), count($expectedPermissions));
        foreach ($expectedPermissions as $permission) {
            $this->assertTrue($roles[$permission] instanceof Item);
        }
    }

    public function testGetPermissionsByUser()
    {
        $this->prepareData();
        $roles = $this->auth->getPermissionsByUser('author B');
        $expectedPermissions = ['createPost', 'updatePost', 'readPost'];
        $this->assertEquals(count($roles), count($expectedPermissions));
        foreach ($expectedPermissions as $permission) {
            $this->assertTrue($roles[$permission] instanceof Item);
        }
    }
    protected function tearDown()
    {
        $this->auth->removeAll();
        parent::tearDown();
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

    /**
     * @return \yii\rbac\ManagerInterface
     */
    protected function createManager()
    {
        return new DbManager(['db' => $this->getConnection()]);
    }
}
