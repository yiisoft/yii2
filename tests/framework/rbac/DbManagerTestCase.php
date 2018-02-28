<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\rbac;

use Psr\Log\LogLevel;
use Yii;
use yii\caching\ArrayCache;
use yii\caching\Cache;
use yii\console\Application;
use yii\console\ExitCode;
use yii\db\Connection;
use yii\rbac\Assignment;
use yii\rbac\DbManager;
use yii\rbac\Permission;
use yii\rbac\Role;
use yiiunit\data\rbac\UserID;
use yiiunit\framework\console\controllers\EchoMigrateController;
use yiiunit\framework\log\ArrayTarget;

/**
 * DbManagerTestCase.
 * @group db
 * @group rbac
 */
abstract class DbManagerTestCase extends ManagerTestCase
{
    protected static $database;
    protected static $driverName;

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
                    'migrate' => EchoMigrateController::class,
                ],
                'components' => [
                    'db' => static::createConnection(),
                    'authManager' => '\yii\rbac\DbManager',
                ],
            ]);
        }

        Yii::$app->setComponents([
            'db' => static::createConnection(),
            'authManager' => '\yii\rbac\DbManager',
        ]);
        self::assertSame(static::$driverName, Yii::$app->db->getDriverName(), 'Connection represents the same DB driver, as is tested');
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
        static::runConsoleAction('migrate/down', ['all', 'migrationPath' => '@yii/rbac/migrations/', 'interactive' => false]);
        parent::tearDownAfterClass();
    }

    protected function setUp()
    {
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
     * @throws \yii\base\InvalidArgumentException
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
            ['', '', false],
        ];
    }

    /**
     * @dataProvider emptyValuesProvider
     * @param mixed $userId
     * @param mixed $searchUserId
     * @param mixed $isValid
     */
    public function testGetPermissionsByUserWithEmptyValue($userId, $searchUserId, $isValid)
    {
        $this->prepareRoles($userId);

        $permissions = $this->auth->getPermissionsByUser($searchUserId);

        if ($isValid) {
            $this->assertTrue(isset($permissions['createPost']));
            $this->assertInstanceOf(Permission::class, $permissions['createPost']);
        } else {
            $this->assertEmpty($permissions);
        }
    }

    /**
     * @dataProvider emptyValuesProvider
     * @param mixed $userId
     * @param mixed $searchUserId
     * @param mixed $isValid
     */
    public function testGetRolesByUserWithEmptyValue($userId, $searchUserId, $isValid)
    {
        $this->prepareRoles($userId);

        $roles = $this->auth->getRolesByUser($searchUserId);

        if ($isValid) {
            $this->assertTrue(isset($roles['Author']));
            $this->assertInstanceOf(Role::class, $roles['Author']);
        } else {
            $this->assertEmpty($roles);
        }
    }

    /**
     * @dataProvider emptyValuesProvider
     * @param mixed $userId
     * @param mixed $searchUserId
     * @param mixed $isValid
     */
    public function testGetAssignmentWithEmptyValue($userId, $searchUserId, $isValid)
    {
        $this->prepareRoles($userId);

        $assignment = $this->auth->getAssignment('createPost', $searchUserId);

        if ($isValid) {
            $this->assertInstanceOf(Assignment::class, $assignment);
            $this->assertEquals($userId, $assignment->userId);
        } else {
            $this->assertEmpty($assignment);
        }
    }

    /**
     * @dataProvider emptyValuesProvider
     * @param mixed $userId
     * @param mixed $searchUserId
     * @param mixed $isValid
     */
    public function testGetAssignmentsWithEmptyValue($userId, $searchUserId, $isValid)
    {
        $this->prepareRoles($userId);

        $assignments = $this->auth->getAssignments($searchUserId);

        if ($isValid) {
            $this->assertNotEmpty($assignments);
            $this->assertInstanceOf(Assignment::class, $assignments['createPost']);
            $this->assertInstanceOf(Assignment::class, $assignments['updatePost']);
        } else {
            $this->assertEmpty($assignments);
        }
    }

    /**
     * @dataProvider emptyValuesProvider
     * @param mixed $userId
     * @param mixed $searchUserId
     * @param mixed $isValid
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
     * @param mixed $userId
     * @param mixed $searchUserId
     * @param mixed $isValid
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

    /**
     * Ensure assignments are read from DB only once on subsequent tests.
     */
    public function testCheckAccessCache()
    {
        $this->mockApplication();
        $this->prepareData();

        // warm up item cache, so only assignment queries are sent to DB
        $this->auth->cache = new Cache(['handler' => new ArrayCache()]);
        $this->auth->checkAccess('author B', 'readPost');
        $this->auth->checkAccess(new UserID('author B'), 'createPost');

        // track db queries
        /* @var $logger \yii\log\Logger */
        $logger = Yii::$app->getLogger();

        $logger->flushInterval = 1;
        $logger->messages = [];
        $logTarget = new ArrayTarget([
            'categories' => ['yii\\db\\Command::query'],
            'levels' => [LogLevel::INFO],
        ]);
        $logger->addTarget($logTarget, 'rbacqueries');
        $this->assertCount(0, $logTarget->messages);

        // testing access on two different permissons for the same user should only result in one DB query for user assignments
        foreach (['readPost' => true, 'createPost' => false] as $permission => $result) {
            $this->assertEquals($result, $this->auth->checkAccess('reader A', $permission), "Checking $permission");
        }
        $this->assertSingleQueryToAssignmentsTable($logTarget);

        // verify cache is flushed on assign (createPost is now true)
        $this->auth->assign($this->auth->getRole('admin'), 'reader A');
        foreach (['readPost' => true, 'createPost' => true] as $permission => $result) {
            $this->assertEquals($result, $this->auth->checkAccess('reader A', $permission), "Checking $permission");
        }
        $this->assertSingleQueryToAssignmentsTable($logTarget);

        // verify cache is flushed on unassign (createPost is now false again)
        $this->auth->revoke($this->auth->getRole('admin'), 'reader A');
        foreach (['readPost' => true, 'createPost' => false] as $permission => $result) {
            $this->assertEquals($result, $this->auth->checkAccess('reader A', $permission), "Checking $permission");
        }
        $this->assertSingleQueryToAssignmentsTable($logTarget);

        // verify cache is flushed on revokeall
        $this->auth->revokeAll('reader A');
        foreach (['readPost' => false, 'createPost' => false] as $permission => $result) {
            $this->assertEquals($result, $this->auth->checkAccess('reader A', $permission), "Checking $permission");
        }
        $this->assertSingleQueryToAssignmentsTable($logTarget);

        // verify cache is flushed on removeAllAssignments
        $this->auth->assign($this->auth->getRole('admin'), 'reader A');
        foreach (['readPost' => true, 'createPost' => true] as $permission => $result) {
            $this->assertEquals($result, $this->auth->checkAccess('reader A', $permission), "Checking $permission");
        }
        $this->assertSingleQueryToAssignmentsTable($logTarget);
        $this->auth->removeAllAssignments();
        foreach (['readPost' => false, 'createPost' => false] as $permission => $result) {
            $this->assertEquals($result, $this->auth->checkAccess('reader A', $permission), "Checking $permission");
        }
        $this->assertSingleQueryToAssignmentsTable($logTarget);
    }

    private function assertSingleQueryToAssignmentsTable($logTarget)
    {
        $this->assertCount(1, $logTarget->messages, 'Only one query should have been performed, but there are the following logs: ' . print_r($logTarget->messages, true));
        $this->assertContains('auth_assignment', $logTarget->messages[0][1], 'Log message should be a query to auth_assignment table');
        $logTarget->messages = [];
    }
}
