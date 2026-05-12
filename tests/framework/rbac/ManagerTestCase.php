<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\rbac;

use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use stdClass;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\base\InvalidValueException;
use yii\rbac\BaseManager;
use yii\rbac\Item;
use yii\rbac\ManagerInterface;
use yii\rbac\Permission;
use yii\rbac\Role;
use yii\rbac\Rule;
use yiiunit\framework\rbac\stub\ActionRule;
use yiiunit\framework\rbac\stub\AuthorRule;
use yiiunit\TestCase;

use function count;

/**
 * Base class for testing {@see ManagerInterface}.
 *
 * @template TManager of BaseManager
 */
abstract class ManagerTestCase extends TestCase
{
    /**
     * @var TManager
     */
    protected $auth;

    /**
     * @return TManager
     */
    abstract protected function createManager();

    public function testCreateRole(): void
    {
        $role = $this->auth->createRole('admin');

        self::assertInstanceOf(
            Role::class,
            $role,
            'Returned object must be a Role.',
        );
        self::assertSame(
            Item::TYPE_ROLE,
            $role->type,
            "Type must be 'TYPE_ROLE'.",
        );
        self::assertSame(
            'admin',
            $role->name,
            'Name must round-trip from the factory argument.',
        );
    }

    public function testCreatePermission(): void
    {
        $permission = $this->auth->createPermission('edit post');

        self::assertInstanceOf(
            Permission::class,
            $permission,
            'Returned object must be a Permission.',
        );
        self::assertSame(
            Item::TYPE_PERMISSION,
            $permission->type,
            "Type must be 'TYPE_PERMISSION'.",
        );
        self::assertSame(
            'edit post',
            $permission->name,
            'Name must round-trip from the factory argument.',
        );
    }

    public function testAdd(): void
    {
        $role = $this->auth->createRole('admin');

        $role->description = 'administrator';

        self::assertTrue(
            $this->auth->add($role),
            "Adding a Role must report 'true'.",
        );

        $permission = $this->auth->createPermission('edit post');

        $permission->description = 'edit a post';

        self::assertTrue(
            $this->auth->add($permission),
            "Adding a Permission must report 'true'.",
        );

        $rule = new AuthorRule(['name' => 'is author', 'reallyReally' => true]);

        self::assertTrue(
            $this->auth->add($rule),
            "Adding a Rule must report 'true'.",
        );
    }

    public function testGetChildren(): void
    {
        $user = $this->auth->createRole('user');

        $this->auth->add($user);

        self::assertCount(
            0,
            $this->auth->getChildren($user->name),
            'Fresh role must have no children.',
        );

        $changeName = $this->auth->createPermission('changeName');

        $this->auth->add($changeName);
        $this->auth->addChild($user, $changeName);

        self::assertCount(
            1,
            $this->auth->getChildren($user->name),
            "Children count must reflect a single 'addChild'.",
        );
    }

    public function testGetRule(): void
    {
        $this->prepareData();

        $rule = $this->auth->getRule('isAuthor');

        self::assertInstanceOf(
            Rule::class,
            $rule,
            'Existing rule must be returned as a Rule instance.',
        );
        self::assertSame(
            'isAuthor',
            $rule->name,
            'Rule name must match the persisted value.',
        );

        $rule = $this->auth->getRule('nonExisting');

        self::assertNull(
            $rule,
            "Unknown rule name must yield 'null'.",
        );
    }

    public function testAddRule(): void
    {
        $this->prepareData();

        $ruleName = 'isReallyReallyAuthor';

        $rule = new AuthorRule(['name' => $ruleName, 'reallyReally' => true]);

        $this->auth->add($rule);

        $rule = $this->auth->getRule($ruleName);

        self::assertInstanceOf(
            AuthorRule::class,
            $rule,
            'Rule must round-trip as the original subclass.',
        );
        self::assertSame(
            $ruleName,
            $rule->name,
            'Rule name must round-trip after add.',
        );
        self::assertTrue(
            $rule->reallyReally,
            'Custom property must be persisted.',
        );
    }

    public function testUpdateRule(): void
    {
        $this->prepareData();

        $rule = $this->auth->getRule('isAuthor');

        self::assertInstanceOf(
            AuthorRule::class,
            $rule,
            'Existing rule must load as the original subclass.',
        );

        $rule->name = 'newName';
        $rule->reallyReally = false;

        $this->auth->update('isAuthor', $rule);

        $rule = $this->auth->getRule('isAuthor');

        self::assertNull(
            $rule,
            'Old rule name must no longer resolve after rename.',
        );

        $rule = $this->auth->getRule('newName');

        self::assertInstanceOf(
            AuthorRule::class,
            $rule,
            'Renamed rule must remain the original subclass.',
        );
        self::assertSame(
            'newName',
            $rule->name,
            'New rule name must resolve after rename.',
        );
        self::assertFalse(
            $rule->reallyReally,
            'Custom property update must persist.',
        );

        $rule->reallyReally = true;

        $this->auth->update('newName', $rule);

        $rule = $this->auth->getRule('newName');

        self::assertInstanceOf(
            AuthorRule::class,
            $rule,
            'Subsequent reload must keep the subclass.',
        );
        self::assertTrue(
            $rule->reallyReally,
            'Subsequent property update must persist.',
        );

        $item = $this->auth->getPermission('createPost');

        $item->name = 'new createPost';

        $this->auth->update('createPost', $item);

        $item = $this->auth->getPermission('createPost');

        self::assertNull(
            $item,
            'Old permission name must no longer resolve after rename.',
        );

        $item = $this->auth->getPermission('new createPost');

        self::assertSame(
            'new createPost',
            $item->name,
            'New permission name must resolve after rename.',
        );
    }

    public function testGetRules(): void
    {
        $this->prepareData();

        $rule = new AuthorRule(['name' => 'isReallyReallyAuthor', 'reallyReally' => true]);

        $this->auth->add($rule);

        $rules = $this->auth->getRules();

        $ruleNames = [];

        foreach ($rules as $rule) {
            $ruleNames[] = $rule->name;
        }

        self::assertContains(
            'isReallyReallyAuthor',
            $ruleNames,
            'Newly added rule must appear in the list.',
        );
        self::assertContains(
            'isAuthor',
            $ruleNames,
            'Pre-existing fixture rule must remain in the list.',
        );
    }

    public function testRemoveRule(): void
    {
        $this->prepareData();

        $this->auth->remove($this->auth->getRule('isAuthor'));

        $rules = $this->auth->getRules();

        self::assertEmpty(
            $rules,
            'Removing the only rule must leave the rule set empty.',
        );

        $this->auth->remove($this->auth->getPermission('createPost'));

        $item = $this->auth->getPermission('createPost');

        self::assertNull(
            $item,
            'Removed permission must no longer resolve.',
        );
    }

    public function testCheckAccess(): void
    {
        $this->prepareData();

        $testSuites = [
            'reader A' => [
                'createPost' => false,
                'readPost' => true,
                'updatePost' => false,
                'updateAnyPost' => false,
            ],
            'author B' => [
                'createPost' => true,
                'readPost' => true,
                'updatePost' => true,
                'deletePost' => true,
                'updateAnyPost' => false,
            ],
            'admin C' => [
                'createPost' => true,
                'readPost' => true,
                'updatePost' => false,
                'updateAnyPost' => true,
                'blablabla' => false,
            ],
            'guest' => [
                // all actions denied for guest (user not exists)
                'createPost' => false,
                'readPost' => false,
                'updatePost' => false,
                'deletePost' => false,
                'updateAnyPost' => false,
                'blablabla' => false,
            ],
        ];

        // using null as an array key is deprecated in PHP 8.5
        $invalidKey = PHP_VERSION_ID >= 80500 ? '' : null;
        $testSuites['admin C'][$invalidKey] = false;
        $testSuites['guest'][$invalidKey] = false;
        $params = ['authorID' => 'author B'];

        foreach ($testSuites as $user => $tests) {
            foreach ($tests as $permission => $result) {
                self::assertSame(
                    $result,
                    $this->auth->checkAccess($user, $permission, $params),
                    "Access decision must match expected for '{$user}' / '{$permission}'.",
                );
            }
        }
    }

    public function testGetPermissionsByRole(): void
    {
        $this->prepareData();

        $permissions = $this->auth->getPermissionsByRole('admin');
        $expectedPermissions = ['createPost', 'updatePost', 'readPost', 'updateAnyPost'];

        self::assertCount(
            count($expectedPermissions),
            $permissions,
            'Permission count must match the role hierarchy.',
        );

        foreach ($expectedPermissions as $permissionName) {
            self::assertInstanceOf(
                Permission::class,
                $permissions[$permissionName],
                "Entry '{$permissionName}' must be a Permission instance.",
            );
        }
    }

    public function testGetPermissionsByUser(): void
    {
        $this->prepareData();

        $permissions = $this->auth->getPermissionsByUser('author B');

        $expectedPermissions = ['deletePost', 'createPost', 'updatePost', 'readPost'];

        self::assertCount(
            count($expectedPermissions),
            $permissions,
            'Permission count must match the user assignments.',
        );

        foreach ($expectedPermissions as $permissionName) {
            self::assertInstanceOf(
                Permission::class,
                $permissions[$permissionName],
                "Entry '{$permissionName}' must be a Permission instance.",
            );
        }
    }

    public function testGetRole(): void
    {
        $this->prepareData();

        $author = $this->auth->getRole('author');

        self::assertEquals(
            Item::TYPE_ROLE,
            $author->type,
            "Type must be 'TYPE_ROLE'.",
        );
        self::assertSame(
            'author',
            $author->name,
            'Name must round-trip from storage.',
        );
        self::assertSame(
            'authorData',
            $author->data,
            'Custom data must round-trip from storage.',
        );
    }

    public function testGetPermission(): void
    {
        $this->prepareData();
        $createPost = $this->auth->getPermission('createPost');

        self::assertEquals(
            Item::TYPE_PERMISSION,
            $createPost->type,
            "Type must be `TYPE_PERMISSION'.",
        );
        self::assertSame(
            'createPost',
            $createPost->name,
            'Name must round-trip from storage.',
        );
        self::assertSame(
            'createPostData',
            $createPost->data,
            'Custom data must round-trip from storage.',
        );
    }

    public function testGetRolesByUser(): void
    {
        $this->prepareData();

        $reader = $this->auth->getRole('reader');

        $this->auth->assign($reader, 0);
        $this->auth->assign($reader, 123);

        $roles = $this->auth->getRolesByUser('reader A');

        self::assertInstanceOf(
            Role::class,
            reset($roles),
            'String user id must yield Role instances.',
        );
        self::assertSame(
            'reader',
            $roles['reader']->name,
            'Role name must match assignment.',
        );

        $roles = $this->auth->getRolesByUser(0);

        self::assertInstanceOf(
            Role::class,
            reset($roles),
            "User id '0' must yield Role instances.",
        );
        self::assertSame(
            'reader',
            $roles['reader']->name,
            "Role name must match assignment for '0'.",
        );

        $roles = $this->auth->getRolesByUser(123);

        self::assertInstanceOf(
            Role::class,
            reset($roles),
            "User id '123' must yield Role instances.",
        );
        self::assertSame(
            'reader',
            $roles['reader']->name,
            "Role name must match assignment for '123'.",
        );
        self::assertContains(
            'myDefaultRole',
            array_keys($roles),
            'Default role must be merged into the result.',
        );
    }

    public function testGetChildRoles(): void
    {
        $this->prepareData();

        $roles = $this->auth->getChildRoles('withoutChildren');

        self::assertCount(
            1,
            $roles,
            'Childless role must produce a single-entry result.',
        );
        self::assertInstanceOf(
            Role::class,
            reset($roles),
            'Entry must be a Role instance.',
        );
        self::assertSame(
            'withoutChildren',
            reset($roles)->name,
            'Single entry must be the role itself.',
        );

        $roles = $this->auth->getChildRoles('reader');

        self::assertCount(
            1,
            $roles,
            'Reader has no child roles, only itself.',
        );
        self::assertInstanceOf(
            Role::class,
            reset($roles),
            'Entry must be a Role instance.',
        );
        self::assertSame(
            'reader',
            reset($roles)->name,
            'Single entry must be the role itself.',
        );

        $roles = $this->auth->getChildRoles('author');

        self::assertCount(
            2,
            $roles,
            "Author must include itself and 'reader'.",
        );
        self::assertArrayHasKey(
            'author',
            $roles,
            'Result must include the queried role.',
        );
        self::assertArrayHasKey(
            'reader',
            $roles,
            'Result must include nested child role.',
        );

        $roles = $this->auth->getChildRoles('admin');

        self::assertCount(
            3,
            $roles,
            "Admin must include itself, 'author' and 'reader'.",
        );
        self::assertArrayHasKey(
            'admin',
            $roles,
            'Result must include the queried role.',
        );
        self::assertArrayHasKey(
            'author',
            $roles,
            'Result must include direct child role.',
        );
        self::assertArrayHasKey(
            'reader',
            $roles,
            'Result must include transitive child role.',
        );
    }

    public function testAssignMultipleRoles(): void
    {
        $this->prepareData();

        $reader = $this->auth->getRole('reader');
        $author = $this->auth->getRole('author');

        $this->auth->assign($reader, 'readingAuthor');
        $this->auth->assign($author, 'readingAuthor');
        $this->auth = $this->createManager();

        $roles = $this->auth->getRolesByUser('readingAuthor');

        $roleNames = [];

        foreach ($roles as $role) {
            $roleNames[] = $role->name;
        }

        self::assertContains(
            'reader',
            $roleNames,
            "Result must include 'reader'. Got: " . implode(', ', $roleNames),
        );
        self::assertContains(
            'author',
            $roleNames,
            "Result must include 'author'. Got: " . implode(', ', $roleNames),
        );
    }

    public function testAssignmentsToIntegerId(): void
    {
        $this->prepareData();

        $reader = $this->auth->getRole('reader');
        $author = $this->auth->getRole('author');

        $this->auth->assign($reader, 42);
        $this->auth->assign($author, 1337);
        $this->auth->assign($reader, 1337);

        $this->auth = $this->createManager();

        self::assertCount(
            0,
            $this->auth->getAssignments(0),
            'User without assignments must yield empty result.',
        );
        self::assertCount(
            1,
            $this->auth->getAssignments(42),
            'Single assignment must round-trip for integer id.',
        );
        self::assertCount(
            2,
            $this->auth->getAssignments(1337),
            'Two assignments must round-trip for integer id.',
        );
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
        self::assertSame(
            ['reader A', '123'],
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

    public function testCanAddChild(): void
    {
        $this->prepareData();

        $author = $this->auth->createRole('author');
        $reader = $this->auth->createRole('reader');

        self::assertTrue(
            $this->auth->canAddChild($author, $reader),
            'Author may legally adopt reader as child.',
        );
        self::assertFalse(
            $this->auth->canAddChild($reader, $author),
            'Reader adopting author would create a loop.',
        );
    }

    public function testRemoveAllRules(): void
    {
        $this->prepareData();

        $this->auth->removeAllRules();

        self::assertEmpty(
            $this->auth->getRules(),
            'Rule set must be cleared.',
        );
        self::assertNotEmpty(
            $this->auth->getRoles(),
            'Roles must remain untouched.',
        );
        self::assertNotEmpty(
            $this->auth->getPermissions(),
            'Permissions must remain untouched.',
        );
    }

    public function testRemoveAllRoles(): void
    {
        $this->prepareData();

        $this->auth->removeAllRoles();

        self::assertEmpty(
            $this->auth->getRoles(),
            'Role set must be cleared.',
        );
        self::assertNotEmpty(
            $this->auth->getRules(),
            'Rules must remain untouched.',
        );
        self::assertNotEmpty(
            $this->auth->getPermissions(),
            'Permissions must remain untouched.',
        );
    }

    public function testRemoveAllPermissions(): void
    {
        $this->prepareData();

        $this->auth->removeAllPermissions();

        self::assertEmpty(
            $this->auth->getPermissions(),
            'Permission set must be cleared.',
        );
        self::assertNotEmpty(
            $this->auth->getRules(),
            'Rules must remain untouched.',
        );
        self::assertNotEmpty(
            $this->auth->getRoles(),
            'Roles must remain untouched.',
        );
    }

    #[DataProvider('RBACItemsProvider')]
    public function testAssignRule(int $RBACItemType): void
    {
        $auth = $this->auth;

        $userId = 3;

        $auth->removeAll();

        $item = $this->createRBACItem($RBACItemType, 'Admin');

        $auth->add($item);
        $auth->assign($item, $userId);

        self::assertTrue(
            $auth->checkAccess($userId, 'Admin'),
            'Item without a rule must grant access.',
        );

        // with normal register rule
        $auth->removeAll();

        $rule = new ActionRule();

        $auth->add($rule);

        $item = $this->createRBACItem($RBACItemType, 'Reader');

        $item->ruleName = $rule->name;

        $auth->add($item);
        $auth->assign($item, $userId);

        self::assertTrue(
            $auth->checkAccess($userId, 'Reader', ['action' => 'read']),
            'Rule must allow `read` action.',
        );
        self::assertFalse(
            $auth->checkAccess($userId, 'Reader', ['action' => 'write']),
            'Rule must deny non-`read` action.',
        );

        // using rule class name
        $auth->removeAll();

        $item = $this->createRBACItem($RBACItemType, 'Reader');

        $item->ruleName = 'yiiunit\framework\rbac\stub\ActionRule';

        $auth->add($item);
        $auth->assign($item, $userId);

        self::assertTrue(
            $auth->checkAccess($userId, 'Reader', ['action' => 'read']),
            "Class-name rule must allow 'read' action.",
        );
        self::assertFalse(
            $auth->checkAccess($userId, 'Reader', ['action' => 'write']),
            "Class-name rule must deny 'write' action.",
        );

        // using DI
        Yii::$container->set(
            'write_rule',
            ['class' => 'yiiunit\framework\rbac\stub\ActionRule',
            'action' => 'write'],
        );
        Yii::$container->set(
            'delete_rule',
            ['class' => 'yiiunit\framework\rbac\stub\ActionRule',
            'action' => 'delete'],
        );
        Yii::$container->set(
            'all_rule',
            ['class' => 'yiiunit\framework\rbac\stub\ActionRule',
            'action' => 'all'],
        );

        $item = $this->createRBACItem($RBACItemType, 'Writer');

        $item->ruleName = 'write_rule';

        $auth->add($item);
        $auth->assign($item, $userId);

        self::assertTrue(
            $auth->checkAccess($userId, 'Writer', ['action' => 'write']),
            "DI-registered rule must allow 'write' action.",
        );
        self::assertFalse(
            $auth->checkAccess($userId, 'Writer', ['action' => 'update']),
            "DI-registered rule must deny 'update' action.",
        );

        $item = $this->createRBACItem($RBACItemType, 'Deleter');

        $item->ruleName = 'delete_rule';

        $auth->add($item);
        $auth->assign($item, $userId);

        self::assertTrue(
            $auth->checkAccess($userId, 'Deleter', ['action' => 'delete']),
            "DI-registered rule must allow 'delete' action.",
        );
        self::assertFalse(
            $auth->checkAccess($userId, 'Deleter', ['action' => 'update']),
            "DI-registered rule must deny 'update' action.",
        );

        $item = $this->createRBACItem($RBACItemType, 'Author');

        $item->ruleName = 'all_rule';

        $auth->add($item);
        $auth->assign($item, $userId);

        self::assertTrue(
            $auth->checkAccess($userId, 'Author', ['action' => 'update']),
            'Wildcard rule must allow any action.',
        );

        // update role and rule
        $item = $this->getRBACItem($RBACItemType, 'Reader');

        $item->name = 'AdminPost';
        $item->ruleName = 'all_rule';

        $auth->update('Reader', $item);

        self::assertTrue(
            $auth->checkAccess($userId, 'AdminPost', ['action' => 'print']),
            'Renamed item with wildcard rule must allow access.',
        );
    }

    #[DataProvider('RBACItemsProvider')]
    public function testRevokeRule(int $RBACItemType): void
    {
        $userId = 3;

        $auth = $this->auth;

        $auth->removeAll();

        $item = $this->createRBACItem($RBACItemType, 'Admin');

        $auth->add($item);
        $auth->assign($item, $userId);

        self::assertTrue(
            $auth->revoke($item, $userId),
            "Revoke of an existing assignment must report 'true'.",
        );
        self::assertFalse(
            $auth->checkAccess($userId, 'Admin'),
            'Access must be denied after revoke.',
        );

        $auth->removeAll();

        $rule = new ActionRule();

        $auth->add($rule);

        $item = $this->createRBACItem($RBACItemType, 'Reader');

        $item->ruleName = $rule->name;

        $auth->add($item);
        $auth->assign($item, $userId);

        self::assertTrue(
            $auth->revoke($item, $userId),
            "Revoke of a rule-bound assignment must report 'true'.",
        );
        self::assertFalse(
            $auth->checkAccess($userId, 'Reader', ['action' => 'read']),
            'Access must be denied after revoke even if the rule would allow.',
        );
        self::assertFalse(
            $auth->checkAccess($userId, 'Reader', ['action' => 'write']),
            'Access must be denied after revoke for any action.',
        );
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/10176
     * @see https://github.com/yiisoft/yii2/issues/12681
     */
    public function testRuleWithPrivateFields(): void
    {
        $auth = $this->auth;

        $auth->removeAll();

        $rule = new ActionRule();

        $auth->add($rule);

        /** @var ActionRule $rule */
        $rule = $this->auth->getRule('action_rule');

        self::assertInstanceOf(
            ActionRule::class,
            $rule,
            'Rule with private fields must round-trip via storage.',
        );
    }

    public function testDefaultRolesWithClosureReturningNonArrayValue(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(
            'Default roles closure must return an array',
        );

        $this->auth->defaultRoles = fn() => 'test';
    }

    public function testDefaultRolesWithNonArrayValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Default roles must be either an array or a callable',
        );

        $this->auth->defaultRoles = 'test';
    }

    public function testHasChild(): void
    {
        $this->prepareData();

        $author = $this->auth->getRole('author');
        $createPost = $this->auth->getPermission('createPost');
        $deletePost = $this->auth->getPermission('deletePost');

        self::assertTrue(
            $this->auth->hasChild($author, $createPost),
            'Existing parent-child link must be detected.',
        );
        self::assertFalse(
            $this->auth->hasChild($author, $deletePost),
            "Missing parent-child link must report 'false'.",
        );
    }

    public function testRemoveChild(): void
    {
        $this->prepareData();

        $author = $this->auth->getRole('author');
        $createPost = $this->auth->getPermission('createPost');

        self::assertTrue(
            $this->auth->removeChild($author, $createPost),
            "Existing pair must be removed and report 'true'.",
        );
        self::assertFalse(
            $this->auth->hasChild($author, $createPost),
            'Pair must no longer be present after removal.',
        );
        self::assertFalse(
            $this->auth->removeChild($author, $createPost),
            "Removing an already-removed pair must report 'false'.",
        );
    }

    public function testRemoveChildren(): void
    {
        $this->prepareData();

        $author = $this->auth->getRole('author');

        self::assertNotEmpty(
            $this->auth->getChildren($author->name),
            'Fixture must populate children.',
        );
        self::assertTrue(
            $this->auth->removeChildren($author),
            "Removing children must report 'true'.",
        );
        self::assertEmpty(
            $this->auth->getChildren($author->name),
            'Children must be cleared after removal.',
        );
        self::assertFalse(
            $this->auth->removeChildren($author),
            "Second call without children must report 'false'.",
        );
    }

    public function testRevokeAll(): void
    {
        $this->prepareData();

        self::assertTrue(
            $this->auth->revokeAll('author B'),
            "Revoking assignments of an assigned user must report 'true'.",
        );
        self::assertEmpty(
            $this->auth->getAssignments('author B'),
            'Assignments must be cleared after revokeAll.',
        );
        self::assertFalse(
            $this->auth->revokeAll('user-never-assigned'),
            "Revoking on a user that was never assigned must report 'false'.",
        );
    }

    public function testGetAssignmentReturnsNullForUnknownPair(): void
    {
        $this->prepareData();

        self::assertNull(
            $this->auth->getAssignment('admin', 'nobody'),
            "Unknown user must yield 'null'.",
        );
        self::assertNull(
            $this->auth->getAssignment('nonExistentRole', 'reader A'),
            "Unknown role must yield 'null'.",
        );
    }

    public function testRemoveAllAssignments(): void
    {
        $this->prepareData();

        $this->auth->removeAllAssignments();

        self::assertEmpty(
            $this->auth->getAssignments('reader A'),
            'No assignments must remain for any user.',
        );
        self::assertEmpty(
            $this->auth->getAssignments('author B'),
            'No assignments must remain for any user.',
        );
        self::assertEmpty(
            $this->auth->getAssignments('admin C'),
            'No assignments must remain for any user.',
        );
    }

    public function testCheckAccessReturnsFalseForUserWithoutAssignments(): void
    {
        $this->prepareData();

        $this->auth->defaultRoles = [];

        self::assertFalse(
            $this->auth->checkAccess('user-without-any-assignment', 'createPost'),
            'User without assignments and no default roles must be denied.',
        );
    }

    public function testGetPermissionsByRoleReturnsEmptyForRoleWithoutChildren(): void
    {
        $this->prepareData();

        self::assertSame(
            [],
            $this->auth->getPermissionsByRole('withoutChildren'),
            'Role without children must yield an empty array.',
        );
    }

    public function testGetPermissionsByUserReturnsEmptyForRoleWithoutChildren(): void
    {
        $this->prepareData();

        $this->auth->assign($this->auth->getRole('withoutChildren'), 'lonely-user');

        self::assertSame(
            [],
            $this->auth->getPermissionsByUser('lonely-user'),
            'User assigned only to a childless role must yield no permissions.',
        );
    }

    public function testGetUserIdsByRoleReturnsEmptyForBlankName(): void
    {
        $this->prepareData();

        self::assertSame(
            [],
            $this->auth->getUserIdsByRole(''),
            'Blank role name must yield an empty array.',
        );
    }

    public function testGetDefaultRolesReturnsConfiguredArray(): void
    {
        $this->auth->defaultRoles = ['guest', 'observer'];

        self::assertSame(
            ['guest', 'observer'],
            $this->auth->getDefaultRoles(),
            'Configured default roles must round-trip.',
        );
    }

    public function testSetDefaultRolesAcceptsClosureReturningArray(): void
    {
        $this->auth->defaultRoles = static fn(): array => ['viewer', 'editor'];

        self::assertSame(
            ['viewer', 'editor'],
            $this->auth->getDefaultRoles(),
            'Closure must populate default roles.',
        );
    }

    public function testGetRoleReturnsNullForPermissionName(): void
    {
        $this->prepareData();

        self::assertNull(
            $this->auth->getRole('createPost'),
            'Permission name must not resolve to a role.',
        );
    }

    public function testGetPermissionReturnsNullForRoleName(): void
    {
        $this->prepareData();

        self::assertNull(
            $this->auth->getPermission('admin'),
            'Role name must not resolve to a permission.',
        );
    }

    public function testThrowInvalidArgumentExceptionWhenAddChildIsItself(): void
    {
        $role = $this->auth->createRole('cycle-target');

        $this->auth->add($role);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Cannot add 'cycle-target' as a child of itself.",
        );

        $this->auth->addChild($role, $role);
    }

    public function testThrowInvalidArgumentExceptionWhenAddChildPermissionParentsRole(): void
    {
        $perm = $this->auth->createPermission('do-stuff');
        $role = $this->auth->createRole('staff');

        $this->auth->add($perm);
        $this->auth->add($role);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Cannot add a role as a child of a permission.',
        );

        $this->auth->addChild($perm, $role);
    }

    public function testThrowInvalidCallExceptionWhenAddChildCreatesLoop(): void
    {
        $a = $this->auth->createRole('loop-a');
        $b = $this->auth->createRole('loop-b');

        $this->auth->add($a);
        $this->auth->add($b);
        $this->auth->addChild($a, $b);

        $this->expectException(InvalidCallException::class);
        $this->expectExceptionMessage(
            "Cannot add 'loop-a' as a child of 'loop-b'. A loop has been detected.",
        );

        $this->auth->addChild($b, $a);
    }

    public function testThrowInvalidArgumentExceptionWhenGetChildRolesUnknownRole(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Role "no-such-role" not found.',
        );

        $this->auth->getChildRoles('no-such-role');
    }

    public function testThrowInvalidArgumentExceptionWhenAddUnsupportedObject(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Adding unsupported object type.',
        );

        $this->auth->add(new stdClass());
    }

    public function testThrowInvalidArgumentExceptionWhenRemoveUnsupportedObject(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Removing unsupported object type.',
        );

        $this->auth->remove(new stdClass());
    }

    public function testThrowInvalidArgumentExceptionWhenUpdateUnsupportedObject(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Updating unsupported object type.',
        );

        $this->auth->update('any', new stdClass());
    }

    public function testRevokeReturnsFalseForUnassignedRole(): void
    {
        $this->prepareData();

        $admin = $this->auth->getRole('admin');

        self::assertFalse(
            $this->auth->revoke($admin, 'never-was-admin'),
            "Revoking a role from a user that was never assigned must report 'false'.",
        );
    }

    public function testRemoveAllRolesIsIdempotentOnEmptyManager(): void
    {
        $this->auth->removeAll();
        $this->auth->removeAllRoles();

        self::assertSame(
            [],
            $this->auth->getRoles(),
            "No roles must remain after empty 'removeAllRoles'.",
        );
    }

    public function testRemoveAllPermissionsIsIdempotentOnEmptyManager(): void
    {
        $this->auth->removeAll();
        $this->auth->removeAllPermissions();

        self::assertSame(
            [],
            $this->auth->getPermissions(),
            "No permissions must remain after empty 'removeAllPermissions'.",
        );
    }

    public function testUpdateItemRenameWithChildrenAndAssignments(): void
    {
        $this->prepareData();

        $author = $this->auth->getRole('author');

        $author->name = 'newAuthor';

        self::assertTrue(
            $this->auth->update('author', $author),
            'Rename of an item with children and assignments must succeed.',
        );
        self::assertNull(
            $this->auth->getRole('author'),
            'Old name must no longer resolve to a role.',
        );
        self::assertNotNull(
            $this->auth->getRole('newAuthor'),
            'New name must resolve to a role.',
        );
    }

    public function testUpdateAutoCreatesRuleWhenItemReferencesAnUnknownRuleClass(): void
    {
        $perm = $this->auth->createPermission('audited-action');

        $this->auth->add($perm);

        // Update with a ruleName that resolves to an instantiable Rule class but is not yet registered.
        $perm->ruleName = AuthorRule::class;
        $this->auth->update('audited-action', $perm);

        self::assertNotNull(
            $this->auth->getRule(AuthorRule::class),
            'Update must auto-register the rule referenced by the item.',
        );
    }

    public function testThrowInvalidConfigExceptionWhenExecuteRuleCannotResolveRuleName(): void
    {
        $rule = new AuthorRule();

        $perm = $this->auth->createPermission('rule-locked');

        $perm->ruleName = $rule->name;

        $this->auth->add($rule);
        $this->auth->add($perm);
        $this->auth->assign($perm, 'user-z');
        // warm up the in-memory snapshot so subsequent checkAccess() calls do not reload from cache.
        $this->auth->checkAccess('user-z', 'rule-locked', ['authorID' => 'user-z']);

        // force the in-memory rule registry to drop the rule while the item keeps its `ruleName`, simulating a corrupt
        // persisted state that bypasses the framework's normal cleanup.
        $reflection = new ReflectionClass($this->auth);

        $rulesProperty = $reflection->getProperty('rules');
        $rules = $rulesProperty->getValue($this->auth) ?? [];

        unset($rules[$rule->name]);

        $rulesProperty->setValue($this->auth, $rules);

        // reset the per-user assignment cache (DbManager only) so the next call re-reads assignments fresh.
        if ($reflection->hasProperty('checkAccessAssignments')) {
            $reflection->getProperty('checkAccessAssignments')->setValue($this->auth, []);
        }

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(
            "Rule not found: {$rule->name}",
        );

        $this->auth->checkAccess('user-z', 'rule-locked', ['authorID' => 'user-z']);
    }

    public static function RBACItemsProvider(): array
    {
        return [
            [Item::TYPE_ROLE],
            [Item::TYPE_PERMISSION],
        ];
    }

    protected function prepareData(): void
    {
        $rule = new AuthorRule();

        $this->auth->add($rule);

        $uniqueTrait = $this->auth->createPermission('Fast Metabolism');
        $uniqueTrait->description = 'Your metabolic rate is twice normal. This means that you are much less resistant to radiation and poison, but your body heals faster.';
        $this->auth->add($uniqueTrait);

        $createPost = $this->auth->createPermission('createPost');
        $createPost->data = 'createPostData';
        $createPost->description = 'create a post';
        $this->auth->add($createPost);

        $readPost = $this->auth->createPermission('readPost');
        $readPost->description = 'read a post';
        $this->auth->add($readPost);

        $deletePost = $this->auth->createPermission('deletePost');
        $deletePost->description = 'delete a post';
        $this->auth->add($deletePost);

        $updatePost = $this->auth->createPermission('updatePost');
        $updatePost->description = 'update a post';
        $updatePost->ruleName = $rule->name;
        $this->auth->add($updatePost);

        $updateAnyPost = $this->auth->createPermission('updateAnyPost');
        $updateAnyPost->description = 'update any post';
        $this->auth->add($updateAnyPost);

        $withoutChildren = $this->auth->createRole('withoutChildren');
        $this->auth->add($withoutChildren);

        $reader = $this->auth->createRole('reader');
        $this->auth->add($reader);
        $this->auth->addChild($reader, $readPost);

        $author = $this->auth->createRole('author');
        $author->data = 'authorData';
        $this->auth->add($author);
        $this->auth->addChild($author, $createPost);
        $this->auth->addChild($author, $updatePost);
        $this->auth->addChild($author, $reader);

        $admin = $this->auth->createRole('admin');
        $this->auth->add($admin);
        $this->auth->addChild($admin, $author);
        $this->auth->addChild($admin, $updateAnyPost);

        $this->auth->assign($uniqueTrait, 'reader A');

        $this->auth->assign($reader, 'reader A');
        $this->auth->assign($author, 'author B');
        $this->auth->assign($deletePost, 'author B');
        $this->auth->assign($admin, 'admin C');
    }

    /**
     * Create Role or Permission RBAC item.
     */
    private function createRBACItem(int $RBACItemType, string $name): Permission|Role
    {
        if ($RBACItemType === Item::TYPE_ROLE) {
            return $this->auth->createRole($name);
        }

        if ($RBACItemType === Item::TYPE_PERMISSION) {
            return $this->auth->createPermission($name);
        }

        throw new InvalidArgumentException(
            "Unsupported RBAC item type: '{$RBACItemType}'",
        );
    }

    /**
     * Get Role or Permission RBAC item.
     */
    private function getRBACItem(int $RBACItemType, string $name): Permission|Role|null
    {
        if ($RBACItemType === Item::TYPE_ROLE) {
            return $this->auth->getRole($name);
        }

        if ($RBACItemType === Item::TYPE_PERMISSION) {
            return $this->auth->getPermission($name);
        }

        throw new InvalidArgumentException(
            "Unsupported RBAC item type: '{$RBACItemType}'",
        );
    }
}
