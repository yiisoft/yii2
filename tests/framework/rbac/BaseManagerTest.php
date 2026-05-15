<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\rbac;

use yii\rbac\Assignment;
use yii\rbac\Permission;
use yii\rbac\Role;
use yiiunit\TestCase;

/**
 * @group rbac
 */
class BaseManagerTest extends TestCase
{
    /**
     * @var ExposedPhpManager
     */
    private $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();

        $runtimePath = \Yii::$app->getRuntimePath();
        $this->manager = new ExposedPhpManager([
            'itemFile' => $runtimePath . '/rbac-test-items.php',
            'assignmentFile' => $runtimePath . '/rbac-test-assignments.php',
            'ruleFile' => $runtimePath . '/rbac-test-rules.php',
        ]);
    }

    protected function tearDown(): void
    {
        $runtimePath = \Yii::$app->getRuntimePath();
        @unlink($runtimePath . '/rbac-test-items.php');
        @unlink($runtimePath . '/rbac-test-assignments.php');
        @unlink($runtimePath . '/rbac-test-rules.php');
        parent::tearDown();
    }

    public function testAddUnsupportedObjectThrowsException(): void
    {
        $this->expectException('yii\base\InvalidArgumentException');
        $this->expectExceptionMessage('Adding unsupported object type.');

        $this->manager->add(new Assignment());
    }

    public function testRemoveUnsupportedObjectThrowsException(): void
    {
        $this->expectException('yii\base\InvalidArgumentException');
        $this->expectExceptionMessage('Removing unsupported object type.');

        $this->manager->remove(new Assignment());
    }

    public function testUpdateUnsupportedObjectThrowsException(): void
    {
        $this->expectException('yii\base\InvalidArgumentException');
        $this->expectExceptionMessage('Updating unsupported object type.');

        $this->manager->update('test', new Assignment());
    }

    public function testGetRoleReturnsNullForPermission(): void
    {
        $permission = $this->manager->createPermission('editPost');
        $this->manager->add($permission);

        $this->assertNull($this->manager->getRole('editPost'));
    }

    public function testGetRoleReturnsNullForNonExistent(): void
    {
        $this->assertNull($this->manager->getRole('nonExistent'));
    }

    public function testGetPermissionReturnsNullForRole(): void
    {
        $role = $this->manager->createRole('admin');
        $this->manager->add($role);

        $this->assertNull($this->manager->getPermission('admin'));
    }

    public function testGetPermissionReturnsNullForNonExistent(): void
    {
        $this->assertNull($this->manager->getPermission('nonExistent'));
    }

    public function testSetDefaultRolesWithArray(): void
    {
        $this->manager->setDefaultRoles(['guest', 'user']);

        $this->assertSame(['guest', 'user'], $this->manager->getDefaultRoles());
    }

    public function testSetDefaultRolesWithClosure(): void
    {
        $this->manager->setDefaultRoles(function () {
            return ['guest', 'member'];
        });

        $this->assertSame(['guest', 'member'], $this->manager->getDefaultRoles());
    }

    public function testGetDefaultRoleInstances(): void
    {
        $this->manager->setDefaultRoles(['guest', 'user']);

        $instances = $this->manager->getDefaultRoleInstances();

        $this->assertCount(2, $instances);
        $this->assertArrayHasKey('guest', $instances);
        $this->assertArrayHasKey('user', $instances);
        $this->assertInstanceOf(Role::class, $instances['guest']);
        $this->assertInstanceOf(Role::class, $instances['user']);
        $this->assertSame('guest', $instances['guest']->name);
        $this->assertSame('user', $instances['user']->name);
    }

    public function testGetRoles(): void
    {
        $admin = $this->manager->createRole('admin');
        $this->manager->add($admin);
        $user = $this->manager->createRole('user');
        $this->manager->add($user);
        $permission = $this->manager->createPermission('edit');
        $this->manager->add($permission);

        $roles = $this->manager->getRoles();

        $this->assertCount(2, $roles);
        $this->assertArrayHasKey('admin', $roles);
        $this->assertArrayHasKey('user', $roles);
    }

    public function testGetPermissions(): void
    {
        $role = $this->manager->createRole('admin');
        $this->manager->add($role);
        $edit = $this->manager->createPermission('edit');
        $this->manager->add($edit);
        $delete = $this->manager->createPermission('delete');
        $this->manager->add($delete);

        $permissions = $this->manager->getPermissions();

        $this->assertCount(2, $permissions);
        $this->assertArrayHasKey('edit', $permissions);
        $this->assertArrayHasKey('delete', $permissions);
    }

    public function testAddItemWithRuleAutoCreatesRule(): void
    {
        $permission = $this->manager->createPermission('readPost');
        $permission->ruleName = 'yiiunit\framework\rbac\ActionRule';
        $this->manager->add($permission);

        $rule = $this->manager->getRule('yiiunit\framework\rbac\ActionRule');
        $this->assertInstanceOf(ActionRule::class, $rule);
        $this->assertSame('yiiunit\framework\rbac\ActionRule', $rule->name);
    }

    public function testUpdateItemWithRuleAutoCreatesRule(): void
    {
        $permission = $this->manager->createPermission('readPost');
        $this->manager->add($permission);

        $permission->ruleName = 'yiiunit\framework\rbac\ActionRule';
        $this->manager->update('readPost', $permission);

        $rule = $this->manager->getRule('yiiunit\framework\rbac\ActionRule');
        $this->assertInstanceOf(ActionRule::class, $rule);
        $this->assertSame('yiiunit\framework\rbac\ActionRule', $rule->name);
    }

    public function testAddRule(): void
    {
        $rule = new ActionRule();
        $this->assertTrue($this->manager->add($rule));

        $fetched = $this->manager->getRule('action_rule');
        $this->assertInstanceOf(ActionRule::class, $fetched);
    }

    public function testRemoveRule(): void
    {
        $rule = new ActionRule();
        $this->manager->add($rule);

        $this->assertTrue($this->manager->remove($rule));
        $this->assertNull($this->manager->getRule('action_rule'));
    }

    public function testUpdateRule(): void
    {
        $rule = new ActionRule();
        $this->manager->add($rule);

        $rule->name = 'updated_rule';
        $this->assertTrue($this->manager->update('action_rule', $rule));

        $this->assertNull($this->manager->getRule('action_rule'));
        $fetched = $this->manager->getRule('updated_rule');
        $this->assertNotNull($fetched);
        $this->assertSame('updated_rule', $fetched->name);
    }

    public function testExecuteRuleWithNoRule(): void
    {
        $item = $this->manager->createPermission('test');
        $item->ruleName = null;
        $this->manager->add($item);

        $this->manager->assign($item, 'user1');
        $this->assertTrue($this->manager->checkAccess('user1', 'test'));
    }

    public function testExecuteRuleWithInvalidRule(): void
    {
        $item = $this->manager->createPermission('test');
        $this->manager->add($item);
        $this->manager->assign($item, 'user1');

        $this->manager->items['test']->ruleName = 'nonExistentRule';

        $this->expectException('yii\base\InvalidConfigException');
        $this->expectExceptionMessage('Rule not found: nonExistentRule');

        $this->manager->checkAccess('user1', 'test');
    }

    public function testHasNoAssignments(): void
    {
        $this->assertFalse($this->manager->checkAccess('nobody', 'anything'));
    }

    public function testRemoveItem(): void
    {
        $role = $this->manager->createRole('admin');
        $this->manager->add($role);

        $this->assertTrue($this->manager->remove($role));
        $this->assertNull($this->manager->getRole('admin'));
    }

    public function testGetRoleReturnsRole(): void
    {
        $role = $this->manager->createRole('admin');
        $this->manager->add($role);

        $fetched = $this->manager->getRole('admin');
        $this->assertInstanceOf(Role::class, $fetched);
        $this->assertSame('admin', $fetched->name);
    }

    public function testGetPermissionReturnsPermission(): void
    {
        $permission = $this->manager->createPermission('editPost');
        $this->manager->add($permission);

        $fetched = $this->manager->getPermission('editPost');
        $this->assertInstanceOf(Permission::class, $fetched);
        $this->assertSame('editPost', $fetched->name);
    }

    public function testUpdateItemWithExistingRule(): void
    {
        $rule = new ActionRule(['action' => 'read']);
        $this->manager->add($rule);

        $permission = $this->manager->createPermission('readPost');
        $this->manager->add($permission);

        $permission->ruleName = 'action_rule';
        $this->manager->update('readPost', $permission);

        $fetched = $this->manager->getPermission('readPost');
        $this->assertSame('action_rule', $fetched->ruleName);
        $this->assertNotNull($this->manager->getRule('action_rule'));
    }

    public function testHasNoAssignmentsWithDefaultRoles(): void
    {
        $this->manager->setDefaultRoles(['guest']);

        $permission = $this->manager->createPermission('viewPage');
        $this->manager->add($permission);

        $role = $this->manager->createRole('guest');
        $this->manager->add($role);
        $this->manager->addChild($role, $permission);

        $this->assertTrue($this->manager->checkAccess('unassignedUser', 'viewPage'));
    }

    public function testHasNoAssignmentsWithAssignmentsButNoDefaultRoles(): void
    {
        $permission = $this->manager->createPermission('editPost');
        $this->manager->add($permission);
        $this->manager->assign($permission, 'user1');

        $this->assertTrue($this->manager->checkAccess('user1', 'editPost'));
    }

    public function testHasNoAssignmentsWithNeitherAssignmentsNorDefaultRoles(): void
    {
        $permission = $this->manager->createPermission('editPost');
        $this->manager->add($permission);

        $this->assertFalse($this->manager->checkAccess('user1', 'editPost'));
    }

    public function testHasNoAssignmentsWithBothAssignmentsAndDefaultRoles(): void
    {
        $this->manager->setDefaultRoles(['guest']);

        $permission = $this->manager->createPermission('editPost');
        $this->manager->add($permission);
        $this->manager->assign($permission, 'user1');

        $this->assertTrue($this->manager->checkAccess('user1', 'editPost'));
    }

    public function testExecuteRuleWithValidRule(): void
    {
        $rule = new ActionRule(['action' => 'read']);
        $this->manager->add($rule);

        $permission = $this->manager->createPermission('readPost');
        $permission->ruleName = 'action_rule';
        $this->manager->add($permission);
        $this->manager->assign($permission, 'user1');

        $this->assertTrue($this->manager->checkAccess('user1', 'readPost', ['action' => 'read']));
        $this->assertFalse($this->manager->checkAccess('user1', 'readPost', ['action' => 'write']));
    }
}
