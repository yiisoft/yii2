<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\rbac;

use PHPUnit\Framework\Attributes\DataProvider;
use yii\db\Exception;
use yii\base\InvalidConfigException;
use yii\rbac\ManagerInterface;
use Yii;
use yii\base\InvalidArgumentException;
use yii\caching\ArrayCache;
use yii\console\Application;
use yii\console\ExitCode;
use yii\db\Connection;
use yii\log\Logger;
use yii\rbac\Assignment;
use yii\rbac\DbManager;
use yii\rbac\Permission;
use yii\rbac\Role;
use yiiunit\data\rbac\UserID;
use yiiunit\framework\console\controllers\EchoMigrateController;
use yiiunit\framework\log\ArrayTarget;

/**
 * Base class for testing {@see DbManager}.
 */
abstract class DbManagerTestCase extends ManagerTestCase
{
    protected static $database;
    protected static $driverName;

    /**
     * @var Connection
     */
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->auth = $this->createManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->auth->removeAll();

        if ($this->db && static::$driverName !== 'sqlite') {
            $this->db->close();
        }

        $this->db = null;
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $databases = static::getParam('databases');

        static::$database = $databases[static::$driverName];

        $pdo_database = 'pdo_' . static::$driverName;

        if (!extension_loaded('pdo') || !extension_loaded($pdo_database)) {
            static::markTestSkipped("pdo and '{$pdo_database}' extension are required.");
        }

        static::runConsoleAction(
            'migrate/up',
            ['migrationPath' => '@yii/rbac/migrations/', 'interactive' => false],
        );
    }

    public static function tearDownAfterClass(): void
    {
        static::runConsoleAction(
            'migrate/down',
            ['all', 'migrationPath' => '@yii/rbac/migrations/', 'interactive' => false],
        );

        parent::tearDownAfterClass();
    }

    public function testGetAssignmentsByRole(): void
    {
        $this->prepareData();

        $reader = $this->auth->getRole('reader');

        $this->auth->assign($reader, 123);

        $this->auth = $this->createManager();

        self::assertSame(
            [],
            $this->auth->getUserIdsByRole('nonexisting'),
            'Unknown role must yield an empty array.',
        );
        self::assertEqualsCanonicalizing(
            ['123', 'reader A'],
            $this->auth->getUserIdsByRole('reader'),
            'User ids must include both string and numeric ids.',
        );
        self::assertSame(
            ['author B'],
            $this->auth->getUserIdsByRole('author'),
            'Author must list a single user.',
        );
        self::assertSame(
            ['admin C'],
            $this->auth->getUserIdsByRole('admin'),
            'Admin must list a single user.',
        );
    }

    #[DataProvider('emptyValuesProvider')]
    public function testGetPermissionsByUserWithEmptyValue(
        int|string $userId,
        int|UserID|string $searchUserId,
        bool $isValid,
    ): void {
        $this->prepareRoles($userId);

        $permissions = $this->auth->getPermissionsByUser($searchUserId);

        if ($isValid) {
            self::assertTrue(
                isset($permissions['createPost']),
                'Valid id must resolve permission entry.',
            );
            self::assertInstanceOf(
                Permission::class,
                $permissions['createPost'],
                'Resolved entry must be a Permission instance.',
            );
        } else {
            self::assertEmpty(
                $permissions,
                'Empty-string id must yield no permissions.',
            );
        }
    }

    #[DataProvider('emptyValuesProvider')]
    public function testGetRolesByUserWithEmptyValue(
        int|string $userId,
        int|UserID|string $searchUserId,
        bool $isValid,
    ): void {
        $this->prepareRoles($userId);

        $roles = $this->auth->getRolesByUser($searchUserId);

        if ($isValid) {
            self::assertTrue(
                isset($roles['Author']),
                'Valid id must resolve role entry.',
            );
            self::assertInstanceOf(
                Role::class,
                $roles['Author'],
                'Resolved entry must be a Role instance.',
            );
        } else {
            self::assertEmpty(
                $roles,
                'Empty-string id must yield no roles.',
            );
        }
    }

    public function testGetCachedRolesByUserId(): void
    {
        $this->auth->removeAll();

        $this->auth->cache = new ArrayCache();

        $admin = $this->auth->createRole('Admin');

        $this->auth->add($admin);

        $manager = $this->auth->createRole('Manager');

        $this->auth->add($manager);

        $adminUserRoles = $this->auth->getRolesByUser(1);

        self::assertArrayHasKey(
            'myDefaultRole',
            $adminUserRoles,
            'Default role must always be present.',
        );
        self::assertArrayNotHasKey(
            'Admin',
            $adminUserRoles,
            'Admin must be absent before assignment.',
        );

        $this->auth->assign($admin, 1);

        $managerUserRoles = $this->auth->getRolesByUser(2);

        self::assertArrayHasKey(
            'myDefaultRole',
            $managerUserRoles,
            'Default role must always be present.',
        );
        self::assertArrayNotHasKey(
            'Manager',
            $managerUserRoles,
            'Manager must be absent before assignment.',
        );

        $this->auth->assign($manager, 2);

        $adminUserRoles = $this->auth->getRolesByUser(1);

        self::assertArrayHasKey(
            'myDefaultRole',
            $adminUserRoles,
            'Default role must remain after assignment.',
        );
        self::assertArrayHasKey(
            'Admin',
            $adminUserRoles,
            'Admin must appear after assignment.',
        );
        self::assertSame(
            $admin->name,
            $adminUserRoles['Admin']->name,
            'Cached role name must match original.',
        );

        $managerUserRoles = $this->auth->getRolesByUser(2);

        self::assertArrayHasKey(
            'myDefaultRole',
            $managerUserRoles,
            'Default role must remain after assignment.',
        );
        self::assertArrayHasKey(
            'Manager',
            $managerUserRoles,
            'Manager must appear after assignment.',
        );
        self::assertSame(
            $manager->name,
            $managerUserRoles['Manager']->name,
            'Cached role name must match original.',
        );
    }

    #[DataProvider('emptyValuesProvider')]
    public function testGetAssignmentWithEmptyValue(
        int|string $userId,
        int|UserID|string $searchUserId,
        bool $isValid,
    ): void {
        $this->prepareRoles($userId);

        $assignment = $this->auth->getAssignment('createPost', $searchUserId);

        if ($isValid) {
            self::assertInstanceOf(
                Assignment::class,
                $assignment,
                'Valid id must resolve an Assignment instance.',
            );
            self::assertEquals(
                $userId,
                $assignment->userId,
                'Resolved userId must match the original.',
            );
        } else {
            self::assertEmpty(
                $assignment,
                'Empty-string id must yield no assignment.',
            );
        }
    }

    #[DataProvider('emptyValuesProvider')]
    public function testGetAssignmentsWithEmptyValue(
        int|string $userId,
        int|UserID|string $searchUserId,
        bool $isValid,
    ): void {
        $this->prepareRoles($userId);

        $assignments = $this->auth->getAssignments($searchUserId);

        if ($isValid) {
            self::assertNotEmpty(
                $assignments,
                'Valid id must yield at least one assignment.',
            );
            self::assertInstanceOf(
                Assignment::class,
                $assignments['createPost'],
                'createPost entry must be an Assignment instance.',
            );
            self::assertInstanceOf(
                Assignment::class,
                $assignments['updatePost'],
                'updatePost entry must be an Assignment instance.',
            );
        } else {
            self::assertEmpty(
                $assignments,
                'Empty-string id must yield no assignments.',
            );
        }
    }

    #[DataProvider('emptyValuesProvider')]
    public function testRevokeWithEmptyValue(
        int|string $userId,
        int|UserID|string $searchUserId,
        bool $isValid,
    ): void {
        $this->prepareRoles($userId);

        $role = $this->auth->getRole('Author');
        $result = $this->auth->revoke($role, $searchUserId);

        if ($isValid) {
            self::assertTrue(
                $result,
                'Valid id must report a successful revoke.',
            );
        } else {
            self::assertFalse(
                $result,
                "Empty-string id must short-circuit and report 'false'.",
            );
        }
    }

    #[DataProvider('emptyValuesProvider')]
    public function testRevokeAllWithEmptyValue(
        int|string $userId,
        int|UserID|string $searchUserId,
        bool $isValid,
    ): void {
        $this->prepareRoles($userId);

        $result = $this->auth->revokeAll($searchUserId);

        if ($isValid) {
            self::assertTrue(
                $result,
                "Valid id must report a successful 'revokeAll'.",
            );
        } else {
            self::assertFalse(
                $result,
                "Empty-string id must short-circuit and report 'false'.",
            );
        }
    }

    /**
     * Ensure assignments are read from DB only once on subsequent tests.
     */
    public function testCheckAccessCache(): void
    {
        $this->mockApplication();
        $this->prepareData();

        // warm up item cache, so only assignment queries are sent to DB
        $this->auth->cache = new ArrayCache();
        $this->auth->checkAccess('author B', 'readPost');
        $this->auth->checkAccess(new UserID('author B'), 'createPost');

        // track db queries
        Yii::$app->log->flushInterval = 1;
        Yii::$app->log->getLogger()->messages = [];
        Yii::$app->log->targets['rbacqueries'] = $logTarget = new ArrayTarget(
            [
                'categories' => ['yii\\db\\Command::query'],
                'levels' => Logger::LEVEL_INFO,
            ],
        );

        self::assertCount(
            0,
            $logTarget->messages,
            'Log target must start empty.',
        );

        // testing access on two different permissons for the same user should only result in one DB query for user assignments
        foreach (['readPost' => true, 'createPost' => false] as $permission => $result) {
            self::assertSame(
                $result,
                $this->auth->checkAccess('reader A', $permission),
                "Access decision must match expected for permission '{$permission}'.",
            );
        }

        $this->assertSingleQueryToAssignmentsTable($logTarget);

        // verify cache is flushed on assign (createPost is now true)
        $this->auth->assign($this->auth->getRole('admin'), 'reader A');

        foreach (['readPost' => true, 'createPost' => true] as $permission => $result) {
            self::assertSame(
                $result,
                $this->auth->checkAccess('reader A', $permission),
                "Access decision must reflect new assignment for '{$permission}'.",
            );
        }

        $this->assertSingleQueryToAssignmentsTable($logTarget);

        // verify cache is flushed on revoke (createPost is now false again)
        $this->auth->revoke($this->auth->getRole('admin'), 'reader A');
        foreach (['readPost' => true, 'createPost' => false] as $permission => $result) {
            self::assertSame(
                $result,
                $this->auth->checkAccess('reader A', $permission),
                "Access decision must reflect revoke for '{$permission}'.",
            );
        }

        $this->assertSingleQueryToAssignmentsTable($logTarget);

        // verify cache is flushed on revokeall
        $this->auth->revokeAll('reader A');

        foreach (['readPost' => false, 'createPost' => false] as $permission => $result) {
            self::assertSame(
                $result,
                $this->auth->checkAccess('reader A', $permission),
                "Access decision must reflect revokeAll for '{$permission}'.",
            );
        }

        $this->assertSingleQueryToAssignmentsTable($logTarget);

        // verify cache is flushed on removeAllAssignments
        $this->auth->assign($this->auth->getRole('admin'), 'reader A');
        foreach (['readPost' => true, 'createPost' => true] as $permission => $result) {
            self::assertSame(
                $result,
                $this->auth->checkAccess('reader A', $permission),
                "Access decision must reflect re-assignment for '{$permission}'.",
            );
        }

        $this->assertSingleQueryToAssignmentsTable($logTarget);

        $this->auth->removeAllAssignments();

        foreach (['readPost' => false, 'createPost' => false] as $permission => $result) {
            self::assertSame(
                $result,
                $this->auth->checkAccess('reader A', $permission),
                "Access decision must reflect removeAllAssignments for '{$permission}'.",
            );
        }

        $this->assertSingleQueryToAssignmentsTable($logTarget);
    }

    public function testGetRulesUsesInMemoryCacheOnSecondCall(): void
    {
        $this->prepareData();

        // trigger loadFromCache so $this->rules is populated.
        $this->auth->checkAccess('reader A', 'readPost');

        $first = $this->auth->getRules();
        $second = $this->auth->getRules();

        self::assertSame(
            array_keys($first),
            array_keys($second),
            'Second call must return the same in-memory cached set.',
        );
    }

    public function testGetRolesByUserReturnsSameResultAcrossCalls(): void
    {
        $this->prepareData();

        $first = $this->auth->getRolesByUser('reader A');
        $second = $this->auth->getRolesByUser('reader A');

        self::assertEquals(
            array_keys($first),
            array_keys($second),
            'Cache hit and cache miss paths must yield the same role names.',
        );
    }

    public function testLoadFromCachePopulatesItemsFromCachedSnapshot(): void
    {
        $this->prepareData();

        // first checkAccess populates the cache snapshot if cache is enabled.
        $this->auth->checkAccess('reader A', 'readPost');

        // reset the in-memory snapshot to force the cached path on the next read.
        $reflection = new \ReflectionClass($this->auth);

        foreach (['items', 'rules', 'parents'] as $prop) {
            $property = $reflection->getProperty($prop);

            $property->setValue($this->auth, null);
        }

        self::assertTrue(
            $this->auth->checkAccess('reader A', 'readPost'),
            'Cached snapshot must support a subsequent access check.',
        );
    }

    public function testGetRuleReturnsNullForRowWithEmptyData(): void
    {
        $this->auth->removeAll();

        $this->auth->db->createCommand()
            ->insert(
                $this->auth->ruleTable,
                ['name' => 'corrupt-rule', 'data' => null, 'created_at' => time(), 'updated_at' => time()],
            )
            ->execute();

        self::assertNull(
            $this->auth->getRule('corrupt-rule'),
            'Rule row with empty data must be treated as absent.',
        );
    }

    public static function emptyValuesProvider(): array
    {
        return [
            [0, 0, true],
            [0, new UserID(0), true],
            ['', '', false],
        ];
    }

    protected static function createConnection(): Connection
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

    protected function createManager(): ManagerInterface
    {
        return new DbManager(['db' => $this->getConnection(), 'defaultRoles' => ['myDefaultRole']]);
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     * @throws InvalidConfigException
     * @return Connection
     */
    protected function getConnection()
    {
        if ($this->db === null) {
            $this->db = static::createConnection();
        }

        return $this->db;
    }

    protected static function runConsoleAction($route, $params = []): void
    {
        if (Yii::$app === null) {
            new Application(
                [
                    'id' => 'Migrator',
                    'basePath' => '@yiiunit',
                    'controllerMap' => ['migrate' => EchoMigrateController::class],
                    'components' => [
                        'db' => static::createConnection(),
                        'authManager' => DbManager::class,
                    ],
                ],
            );
        }

        Yii::$app->setComponents(
            [
                'db' => static::createConnection(),
                'authManager' => DbManager::class,
            ],
        );

        self::assertSame(
            static::$driverName,
            Yii::$app->db->getDriverName(),
            'Connection represents the same DB driver, as is tested',
        );

        ob_start();

        $result = Yii::$app->runAction($route, $params);

        echo "Result is '{$result}'";

        if ($result !== ExitCode::OK) {
            ob_end_flush();
        } else {
            ob_end_clean();
        }
    }

    private function assertSingleQueryToAssignmentsTable(ArrayTarget $logTarget): void
    {
        $messages = array_filter(
            $logTarget->messages,
            static fn($message): bool => str_contains((string) $message[0], 'auth_assignment'),
        );

        self::assertCount(
            1,
            $messages,
            'Exactly one assignments query is expected. Got logs: ' . print_r($logTarget->messages, true),
        );
        self::assertStringContainsString(
            'auth_assignment',
            $messages[0][0],
            'Logged query must target the assignments table.',
        );

        $logTarget->messages = [];
    }

    private function prepareRoles(int|string $userId): void
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
}
