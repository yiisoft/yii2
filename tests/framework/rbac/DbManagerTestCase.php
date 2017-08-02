<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\rbac;

use Yii;
use yii\console\Application;
use yii\console\ExitCode;
use yii\db\Connection;
use yii\rbac\Assignment;
use yii\rbac\DbManager;
use yii\rbac\Permission;
use yii\rbac\Role;
use yiiunit\data\rbac\UserID;
use yiiunit\framework\console\controllers\EchoMigrateController;

/**
 * DbManagerTestCase
 * @group db
 * @group rbac
 * @group mysql
 */
abstract class DbManagerTestCase extends ManagerTestCase
{
    protected static $database;
    protected static $driverName = 'mysql';

    /**
     * @var Connection
     */
    protected $db;

    protected static function runConsoleAction($route, $params = [])
    {
        if (Yii::$app === null) {
            new Application([
                'id' => 'Migrator',
                'basePath' => '@yiiunit',
                'controllerMap' => [
                    'migrate' => EchoMigrateController::className(),
                ],
                'components' => [
                    'db' => static::createConnection(),
                    'authManager' => '\yii\rbac\DbManager',
                ],
            ]);
        }

        ob_start();
        $result = Yii::$app->runAction($route, $params);
        echo 'Result is ' . $result;
        if ($result !== ExitCode::OK) {
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
        Yii::$app = null;
        parent::tearDownAfterClass();
    }

    protected function setUp()
    {
        if (defined('HHVM_VERSION') && static::$driverName === 'pgsql') {
            static::markTestSkipped('HHVM PDO for pgsql does not work with binary columns, which are essential for rbac schema. See https://github.com/yiisoft/yii2/issues/14244');
        }
        parent::setUp();
        $this->auth = $this->createManager();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->auth->removeAll();
        if ($this->db && static::$driverName !== 'sqlite') {
            $this->db->close();
        }
        $this->db = null;
    }

    /**
     * @throws \yii\base\InvalidParamException
     * @throws \yii\db\Exception
     * @throws \yii\base\InvalidConfigException
     * @return \yii\db\Connection
     */
    public function getConnection()
    {
        if ($this->db === null) {
            $this->db = static::createConnection();
        }
        return $this->db;
    }

    public static function createConnection()
    {
        $db = new Connection();
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
        return $db;
    }

    /**
     * @return \yii\rbac\ManagerInterface
     */
    protected function createManager()
    {
        return new DbManager(['db' => $this->getConnection(), 'defaultRoles' => ['myDefaultRole']]);
    }

    private function prepareRoles($userId)
    {
        $this->auth->removeAll();

        $author = $this->auth->createRole('Author');
        $this->auth->add($author);
        $this->auth->assign($author, $userId);

        $createPost = $this->auth->createPermission('createPost');
        $this->auth->add($createPost);
        $this->auth->assign($createPost, $userId);

        $updatePost = $this->auth->createPermission('updatePost');
        $this->auth->add($updatePost);
        $this->auth->assign($updatePost, $userId);
    }

    public function emptyValuesProvider()
    {
        return [
            [0, 0, true],
            [0, new UserID(0), true],
            ['', '', false]
        ];
    }

    /**
     * @dataProvider emptyValuesProvider
     */
    public function testGetPermissionsByUserWithEmptyValue($userId, $searchUserId, $isValid)
    {
        $this->prepareRoles($userId);

        $permissions = $this->auth->getPermissionsByUser($searchUserId);

        if ($isValid) {
            $this->assertTrue(isset($permissions['createPost']));
            $this->assertInstanceOf(Permission::className(), $permissions['createPost']);
        } else {
            $this->assertEmpty($permissions);
        }
    }

    /**
     * @dataProvider emptyValuesProvider
     */
    public function testGetRolesByUserWithEmptyValue($userId, $searchUserId, $isValid)
    {
        $this->prepareRoles($userId);

        $roles = $this->auth->getRolesByUser($searchUserId);

        if ($isValid) {
            $this->assertTrue(isset($roles['Author']));
            $this->assertInstanceOf(Role::className(), $roles['Author']);
        } else {
            $this->assertEmpty($roles);
        }
    }

    /**
     * @dataProvider emptyValuesProvider
     */
    public function testGetAssignmentWithEmptyValue($userId, $searchUserId, $isValid)
    {
        $this->prepareRoles($userId);

        $assignment = $this->auth->getAssignment('createPost', $searchUserId);

        if ($isValid) {
            $this->assertInstanceOf(Assignment::className(), $assignment);
            $this->assertEquals($userId, $assignment->userId);
        } else {
            $this->assertEmpty($assignment);
        }
    }

    /**
     * @dataProvider emptyValuesProvider
     */
    public function testGetAssignmentsWithEmptyValue($userId, $searchUserId, $isValid)
    {
        $this->prepareRoles($userId);

        $assignments = $this->auth->getAssignments($searchUserId);

        if ($isValid) {
            $this->assertNotEmpty($assignments);
            $this->assertInstanceOf(Assignment::className(), $assignments['createPost']);
            $this->assertInstanceOf(Assignment::className(), $assignments['updatePost']);
        } else {
            $this->assertEmpty($assignments);
        }
    }

    /**
     * @dataProvider emptyValuesProvider
     */
    public function testRevokeWithEmptyValue($userId, $searchUserId, $isValid)
    {
        $this->prepareRoles($userId);
        $role = $this->auth->getRole('Author');

        $result = $this->auth->revoke($role, $searchUserId);

        if ($isValid) {
            $this->assertTrue($result);
        } else {
            $this->assertFalse($result);
        }
    }

    /**
     * @dataProvider emptyValuesProvider
     */
    public function testRevokeAllWithEmptyValue($userId, $searchUserId, $isValid)
    {
        $this->prepareRoles($userId);

        $result = $this->auth->revokeAll($searchUserId);

        if ($isValid) {
            $this->assertTrue($result);
        } else {
            $this->assertFalse($result);
        }
    }
}
