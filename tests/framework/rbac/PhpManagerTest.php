<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\rbac;

use PHPUnit\Framework\Attributes\Group;
use Xepozz\InternalMocker\MockerState;
use Yii;
use yiiunit\framework\rbac\stub\AuthorRule;
use yiiunit\framework\rbac\stub\ExposedPhpManager;

/**
 * Unit test for {@see \yii\rbac\PhpManager}.
 *
 * @extends ManagerTestCase<ExposedPhpManager>
 */
#[Group('rbac')]
#[Group('rbac-php')]
final class PhpManagerTest extends ManagerTestCase
{
    protected function getItemFile()
    {
        return Yii::$app->getRuntimePath() . '/rbac-items.php';
    }

    protected function getAssignmentFile()
    {
        return Yii::$app->getRuntimePath() . '/rbac-assignments.php';
    }

    protected function getRuleFile()
    {
        return Yii::$app->getRuntimePath() . '/rbac-rules.php';
    }

    protected function removeDataFiles()
    {
        @unlink($this->getItemFile());
        @unlink($this->getAssignmentFile());
        @unlink($this->getRuleFile());
    }

    /**
     * {@inheritdoc}
     */
    protected function createManager()
    {
        return new ExposedPhpManager(
            [
                'itemFile' => $this->getItemFile(),
                'assignmentFile' => $this->getAssignmentFile(),
                'ruleFile' => $this->getRuleFile(),
                'defaultRoles' => ['myDefaultRole'],
            ],
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockApplication();
        $this->removeDataFiles();

        $this->auth = $this->createManager();
    }

    protected function tearDown(): void
    {
        $this->removeDataFiles();
        parent::tearDown();
    }

    public function testSaveLoad(): void
    {
        $now = \time();

        MockerState::addCondition('yii\rbac', 'time', [], $now, true);
        MockerState::addCondition('yii\rbac', 'filemtime', [], $now, true);

        $this->prepareData();

        $items = $this->auth->items;
        $children = $this->auth->children;
        $assignments = $this->auth->assignments;
        $rules = $this->auth->rules;

        $this->auth->save();

        $this->auth = $this->createManager();

        $this->auth->load();

        self::assertEquals(
            $items,
            $this->auth->items,
            'Items must round-trip identically.',
        );
        self::assertEquals(
            $children,
            $this->auth->children,
            'Children must round-trip identically.',
        );
        self::assertEquals(
            $assignments,
            $this->auth->assignments,
            'Assignments must round-trip identically.',
        );
        self::assertEquals(
            $rules,
            $this->auth->rules,
            'Rules must round-trip identically.',
        );
    }

    public function testUpdateItemName(): void
    {
        $this->prepareData();

        $name = 'readPost';

        $permission = $this->auth->getPermission($name);

        $permission->name = 'UPDATED-NAME';

        self::assertTrue(
            $this->auth->update($name, $permission),
            "Rename must report 'true'.",
        );
    }

    public function testUpdateDescription(): void
    {
        $this->prepareData();

        $name = 'readPost';

        $permission = $this->auth->getPermission($name);

        $permission->description = 'UPDATED-DESCRIPTION';

        self::assertTrue(
            $this->auth->update($name, $permission),
            "Description-only update must report 'true'.",
        );
    }

    public function testOverwriteName(): void
    {
        $this->prepareData();

        $name = 'readPost';

        $permission = $this->auth->getPermission($name);

        $permission->name = 'createPost';

        $this->expectException(\yii\base\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Unable to change the item name. The name 'createPost' is already used by another item.",
        );

        $this->auth->update($name, $permission);
    }

    public function testSaveAssignments(): void
    {
        $this->auth->removeAll();

        $role = $this->auth->createRole('Admin');

        $this->auth->add($role);
        $this->auth->assign($role, 13);

        self::assertStringContainsString(
            'Admin',
            file_get_contents($this->getAssignmentFile()),
            'Initial assignment must persist the role name.',
        );

        $role->name = 'NewAdmin';

        $this->auth->update('Admin', $role);

        self::assertStringContainsString(
            'NewAdmin',
            file_get_contents($this->getAssignmentFile()),
            'Renamed role must persist with the new name.',
        );

        $this->auth->remove($role);

        self::assertStringNotContainsString(
            'NewAdmin',
            file_get_contents($this->getAssignmentFile()),
            'Removed role must not appear in the persistence file.',
        );
    }

    public function testRemoveItemReturnsFalseForUnknownItem(): void
    {
        $orphan = $this->auth->createRole('never-added');

        self::assertFalse(
            $this->auth->remove($orphan),
            "Removing an item that was never added must report 'false'.",
        );
    }

    public function testRemoveRuleReturnsFalseForUnknownRule(): void
    {
        $rule = new AuthorRule(['name' => 'never-saved']);

        self::assertFalse(
            $this->auth->remove($rule),
            "Removing a rule that was never added must report 'false'.",
        );
    }

    public function testThrowInvalidArgumentExceptionWhenAddChildItemNotInItems(): void
    {
        $orphanParent = $this->auth->createRole('orphan-parent');
        $orphanChild = $this->auth->createPermission('orphan-child');

        $this->expectException(\yii\base\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Either 'orphan-parent' or 'orphan-child' does not exist.",
        );

        $this->auth->addChild($orphanParent, $orphanChild);
    }

    public function testThrowInvalidCallExceptionWhenAddChildAlreadyExists(): void
    {
        $role = $this->auth->createRole('manager');
        $perm = $this->auth->createPermission('act');

        $this->auth->add($role);
        $this->auth->add($perm);
        $this->auth->addChild($role, $perm);

        $this->expectException(\yii\base\InvalidCallException::class);
        $this->expectExceptionMessage(
            "The item 'manager' already has a child 'act'.",
        );

        $this->auth->addChild($role, $perm);
    }

    public function testThrowInvalidArgumentExceptionWhenAssignUnknownRole(): void
    {
        $role = $this->auth->createRole('ghost-role');

        $this->expectException(\yii\base\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Unknown role 'ghost-role'.",
        );

        $this->auth->assign($role, 'user-1');
    }

    public function testThrowInvalidArgumentExceptionWhenAssignAlreadyAssigned(): void
    {
        $role = $this->auth->createRole('member');
        $this->auth->add($role);
        $this->auth->assign($role, 'user-2');

        $this->expectException(\yii\base\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Authorization item 'member' has already been assigned to user 'user-2'.",
        );

        $this->auth->assign($role, 'user-2');
    }
}
