<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rbac;

use Yii;
use yii\db\Connection;
use yii\db\Query;
use yii\db\Expression;
use yii\base\InvalidCallException;
use yii\base\InvalidParamException;
use yii\di\Instance;

/**
 * DbManager represents an authorization manager that stores authorization information in database.
 *
 * The database connection is specified by [[db]]. The database schema could be initialized by applying migration:
 *
 * ```
 * yii migrate --migrationPath=@yii/rbac/migrations/
 * ```
 *
 * If you don't want to use migration and need SQL instead, files for all databases are in migrations directory.
 *
 * You may change the names of the three tables used to store the authorization data by setting [[itemTable]],
 * [[itemChildTable]] and [[assignmentTable]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class DbManager extends BaseManager
{
    /**
     * @var Connection|string the DB connection object or the application component ID of the DB connection.
     * After the DbManager object is created, if you want to change this property, you should only assign it
     * with a DB connection object.
     */
    public $db = 'db';
    /**
     * @var string the name of the table storing authorization items. Defaults to "auth_item".
     */
    public $itemTable = '{{%auth_item}}';
    /**
     * @var string the name of the table storing authorization item hierarchy. Defaults to "auth_item_child".
     */
    public $itemChildTable = '{{%auth_item_child}}';
    /**
     * @var string the name of the table storing authorization item assignments. Defaults to "auth_assignment".
     */
    public $assignmentTable = '{{%auth_assignment}}';
    /**
     * @var string the name of the table storing rules. Defaults to "auth_rule".
     */
    public $ruleTable = '{{%auth_rule}}';


    /**
     * Initializes the application component.
     * This method overrides the parent implementation by establishing the database connection.
     */
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::className());
    }

    /**
     * @inheritdoc
     */
    public function checkAccess($userId, $permissionName, $params = [])
    {
        $assignments = $this->getAssignments($userId);
        return $this->checkAccessRecursive($userId, $permissionName, $params, $assignments);
    }

    /**
     * Performs access check for the specified user.
     * This method is internally called by [[checkAccess()]].
     * @param string|integer $user the user ID. This should can be either an integer or a string representing
     * the unique identifier of a user. See [[\yii\web\User::id]].
     * @param string $itemName the name of the operation that need access check
     * @param array $params name-value pairs that would be passed to rules associated
     * with the tasks and roles assigned to the user. A param with name 'user' is added to this array,
     * which holds the value of `$userId`.
     * @param Assignment[] $assignments the assignments to the specified user
     * @return boolean whether the operations can be performed by the user.
     */
    protected function checkAccessRecursive($user, $itemName, $params, $assignments)
    {
        if (($item = $this->getItem($itemName)) === null) {
            return false;
        }

        Yii::trace($item instanceof Role ? "Checking role: $itemName" : "Checking permission: $itemName", __METHOD__);

        if (!$this->executeRule($user, $item, $params)) {
            return false;
        }

        if (isset($assignments[$itemName]) || in_array($itemName, $this->defaultRoles)) {
            return true;
        }

        $query = new Query;
        $parents = $query->select(['parent'])
            ->from($this->itemChildTable)
            ->where(['child' => $itemName])
            ->column($this->db);
        foreach ($parents as $parent) {
            if ($this->checkAccessRecursive($user, $parent, $params, $assignments)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    protected function getItem($name)
    {
        $row = (new Query)->from($this->itemTable)
            ->where(['name' => $name])
            ->one($this->db);

        if ($row === false) {
            return null;
        }

        if (!isset($row['data']) || ($data = @unserialize($row['data'])) === false) {
            $row['data'] = null;
        }

        return $this->populateItem($row);
    }

    /**
     * Returns a value indicating whether the database supports cascading update and delete.
     * The default implementation will return false for SQLite database and true for all other databases.
     * @return boolean whether the database supports cascading update and delete.
     */
    protected function supportsCascadeUpdate()
    {
        return strncmp($this->db->getDriverName(), 'sqlite', 6) !== 0;
    }

    /**
     * @inheritdoc
     */
    protected function addItem($item)
    {
        $time = time();
        if ($item->createdAt === null) {
            $item->createdAt = $time;
        }
        if ($item->updatedAt === null) {
            $item->updatedAt = $time;
        }
        $this->db->createCommand()
            ->insert($this->itemTable, [
                'name' => $item->name,
                'type' => $item->type,
                'description' => $item->description,
                'rule_name' => $item->ruleName,
                'data' => $item->data === null ? null : serialize($item->data),
                'created_at' => $item->createdAt,
                'updated_at' => $item->updatedAt,
            ])->execute();

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function removeItem($item)
    {
        if (!$this->supportsCascadeUpdate()) {
            $this->db->createCommand()
                ->delete($this->itemChildTable, ['or', '[[parent]]=:name', '[[child]]=:name'], [':name' => $item->name])
                ->execute();
            $this->db->createCommand()
                ->delete($this->assignmentTable, ['item_name' => $item->name])
                ->execute();
        }

        $this->db->createCommand()
            ->delete($this->itemTable, ['name' => $item->name])
            ->execute();

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function updateItem($name, $item)
    {
        if (!$this->supportsCascadeUpdate() && $item->name !== $name) {
            $this->db->createCommand()
                ->update($this->itemChildTable, ['parent' => $item->name], ['parent' => $name])
                ->execute();
            $this->db->createCommand()
                ->update($this->itemChildTable, ['child' => $item->name], ['child' => $name])
                ->execute();
            $this->db->createCommand()
                ->update($this->assignmentTable, ['item_name' => $item->name], ['item_name' => $name])
                ->execute();
        }

        $item->updatedAt = time();

        $this->db->createCommand()
            ->update($this->itemTable, [
                'name' => $item->name,
                'description' => $item->description,
                'rule_name' => $item->ruleName,
                'data' => $item->data === null ? null : serialize($item->data),
                'updated_at' => $item->updatedAt,
            ], [
                'name' => $name,
            ])->execute();

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function addRule($rule)
    {
        $time = time();
        if ($rule->createdAt === null) {
            $rule->createdAt = $time;
        }
        if ($rule->updatedAt === null) {
            $rule->updatedAt = $time;
        }
        $this->db->createCommand()
            ->insert($this->ruleTable, [
                'name' => $rule->name,
                'data' => serialize($rule),
                'created_at' => $rule->createdAt,
                'updated_at' => $rule->updatedAt,
            ])->execute();

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function updateRule($name, $rule)
    {
        if (!$this->supportsCascadeUpdate() && $rule->name !== $name) {
            $this->db->createCommand()
                ->update($this->itemTable, ['rule_name' => $rule->name], ['rule_name' => $name])
                ->execute();
        }

        $rule->updatedAt = time();

        $this->db->createCommand()
            ->update($this->ruleTable, [
                'name' => $rule->name,
                'data' => serialize($rule),
                'updated_at' => $rule->updatedAt,
            ], [
                'name' => $name,
            ])->execute();

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function removeRule($rule)
    {
        if (!$this->supportsCascadeUpdate()) {
            $this->db->createCommand()
                ->update($this->itemTable, ['rule_name' => null], ['rule_name' => $rule->name])
                ->execute();
        }

        $this->db->createCommand()
            ->delete($this->ruleTable, ['name' => $rule->name])
            ->execute();

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function getItems($type)
    {
        $query = (new Query)
            ->from($this->itemTable)
            ->where(['type' => $type]);

        $items = [];
        foreach ($query->all($this->db) as $row) {
            $items[$row['name']] = $this->populateItem($row);
        }

        return $items;
    }

    /**
     * Populates an auth item with the data fetched from database
     * @param array $row the data from the auth item table
     * @return Item the populated auth item instance (either Role or Permission)
     */
    protected function populateItem($row)
    {
        $class = $row['type'] == Item::TYPE_PERMISSION ? Permission::className() : Role::className();

        if (!isset($row['data']) || ($data = @unserialize($row['data'])) === false) {
            $data = null;
        }

        return new $class([
            'name' => $row['name'],
            'type' => $row['type'],
            'description' => $row['description'],
            'ruleName' => $row['rule_name'],
            'data' => $data,
            'createdAt' => $row['created_at'],
            'updatedAt' => $row['updated_at'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getRolesByUser($userId)
    {
        if (empty($userId)) {
            return [];
        }

        $query = (new Query)->select('b.*')
            ->from(['a' => $this->assignmentTable, 'b' => $this->itemTable])
            ->where('{{a}}.[[item_name]]={{b}}.[[name]]')
            ->andWhere(['a.user_id' => (string) $userId]);

        $roles = [];
        foreach ($query->all($this->db) as $row) {
            $roles[$row['name']] = $this->populateItem($row);
        }
        return $roles;
    }

    /**
     * @inheritdoc
     */
    public function getPermissionsByRole($roleName)
    {
        $childrenList = $this->getChildrenList();
        $result = [];
        $this->getChildrenRecursive($roleName, $childrenList, $result);
        if (empty($result)) {
            return [];
        }
        $query = (new Query)->from($this->itemTable)->where([
            'type' => Item::TYPE_PERMISSION,
            'name' => array_keys($result),
        ]);
        $permissions = [];
        foreach ($query->all($this->db) as $row) {
            $permissions[$row['name']] = $this->populateItem($row);
        }
        return $permissions;
    }

    /**
     * @inheritdoc
     */
    public function getPermissionsByUser($userId)
    {
        if (empty($userId)) {
            return [];
        }

        $query = (new Query)->select('item_name')
            ->from($this->assignmentTable)
            ->where(['user_id' => (string) $userId]);

        $childrenList = $this->getChildrenList();
        $result = [];
        foreach ($query->column($this->db) as $roleName) {
            $this->getChildrenRecursive($roleName, $childrenList, $result);
        }

        if (empty($result)) {
            return [];
        }

        $query = (new Query)->from($this->itemTable)->where([
            'type' => Item::TYPE_PERMISSION,
            'name' => array_keys($result),
        ]);
        $permissions = [];
        foreach ($query->all($this->db) as $row) {
            $permissions[$row['name']] = $this->populateItem($row);
        }
        return $permissions;
    }

    /**
     * Returns the children for every parent.
     * @return array the children list. Each array key is a parent item name,
     * and the corresponding array value is a list of child item names.
     */
    protected function getChildrenList()
    {
        $query = (new Query)->from($this->itemChildTable);
        $parents = [];
        foreach ($query->all($this->db) as $row) {
            $parents[$row['parent']][] = $row['child'];
        }
        return $parents;
    }

    /**
     * Recursively finds all children and grand children of the specified item.
     * @param string $name the name of the item whose children are to be looked for.
     * @param array $childrenList the child list built via [[getChildrenList()]]
     * @param array $result the children and grand children (in array keys)
     */
    protected function getChildrenRecursive($name, $childrenList, &$result)
    {
        if (isset($childrenList[$name])) {
            foreach ($childrenList[$name] as $child) {
                $result[$child] = true;
                $this->getChildrenRecursive($child, $childrenList, $result);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getRule($name)
    {
        $row = (new Query)->select(['data'])
            ->from($this->ruleTable)
            ->where(['name' => $name])
            ->one($this->db);
        return $row === false ? null : unserialize($row['data']);
    }

    /**
     * @inheritdoc
     */
    public function getRules()
    {
        $query = (new Query)->from($this->ruleTable);

        $rules = [];
        foreach ($query->all($this->db) as $row) {
            $rules[$row['name']] = unserialize($row['data']);
        }

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function getAssignment($roleName, $userId)
    {
        if (empty($userId)) {
            return null;
        }

        $row = (new Query)->from($this->assignmentTable)
            ->where(['user_id' => (string) $userId, 'item_name' => $roleName])
            ->one($this->db);

        if ($row === false) {
            return null;
        }

        return new Assignment([
            'userId' => $row['user_id'],
            'roleName' => $row['item_name'],
            'createdAt' => $row['created_at'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getAssignments($userId)
    {
        if (empty($userId)) {
            return [];
        }

        $query = (new Query)
            ->from($this->assignmentTable)
            ->where(['user_id' => (string) $userId]);

        $assignments = [];
        foreach ($query->all($this->db) as $row) {
            $assignments[$row['item_name']] = new Assignment([
                'userId' => $row['user_id'],
                'roleName' => $row['item_name'],
                'createdAt' => $row['created_at'],
            ]);
        }

        return $assignments;
    }

    /**
     * @inheritdoc
     */
    public function addChild($parent, $child)
    {
        if ($parent->name === $child->name) {
            throw new InvalidParamException("Cannot add '{$parent->name}' as a child of itself.");
        }

        if ($parent instanceof Permission && $child instanceof Role) {
            throw new InvalidParamException("Cannot add a role as a child of a permission.");
        }

        if ($this->detectLoop($parent, $child)) {
            throw new InvalidCallException("Cannot add '{$child->name}' as a child of '{$parent->name}'. A loop has been detected.");
        }

        $this->db->createCommand()
            ->insert($this->itemChildTable, ['parent' => $parent->name, 'child' => $child->name])
            ->execute();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function removeChild($parent, $child)
    {
        return $this->db->createCommand()
            ->delete($this->itemChildTable, ['parent' => $parent->name, 'child' => $child->name])
            ->execute() > 0;
    }

    /**
     * @inheritdoc
     */
    public function removeChildren($parent)
    {
        return $this->db->createCommand()
            ->delete($this->itemChildTable, ['parent' => $parent->name])
            ->execute() > 0;
    }

    /**
     * @inheritdoc
     */
    public function hasChild($parent, $child)
    {
        return (new Query)
            ->from($this->itemChildTable)
            ->where(['parent' => $parent->name, 'child' => $child->name])
            ->one($this->db) !== false;
    }

    /**
     * @inheritdoc
     */
    public function getChildren($name)
    {
        $query = (new Query)
            ->select(['name', 'type', 'description', 'rule_name', 'data', 'created_at', 'updated_at'])
            ->from([$this->itemTable, $this->itemChildTable])
            ->where(['parent' => $name, 'name' => new Expression('[[child]]')]);

        $children = [];
        foreach ($query->all($this->db) as $row) {
            $children[$row['name']] = $this->populateItem($row);
        }

        return $children;
    }

    /**
     * Checks whether there is a loop in the authorization item hierarchy.
     * @param Item $parent the parent item
     * @param Item $child the child item to be added to the hierarchy
     * @return boolean whether a loop exists
     */
    protected function detectLoop($parent, $child)
    {
        if ($child->name === $parent->name) {
            return true;
        }
        foreach ($this->getChildren($child->name) as $grandchild) {
            if ($this->detectLoop($parent, $grandchild)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function assign($role, $userId)
    {
        $assignment = new Assignment([
            'userId' => $userId,
            'roleName' => $role->name,
            'createdAt' => time(),
        ]);

        $this->db->createCommand()
            ->insert($this->assignmentTable, [
                'user_id' => $assignment->userId,
                'item_name' => $assignment->roleName,
                'created_at' => $assignment->createdAt,
            ])->execute();

        return $assignment;
    }

    /**
     * @inheritdoc
     */
    public function revoke($role, $userId)
    {
        if (empty($userId)) {
            return false;
        }

        return $this->db->createCommand()
            ->delete($this->assignmentTable, ['user_id' => (string) $userId, 'item_name' => $role->name])
            ->execute() > 0;
    }

    /**
     * @inheritdoc
     */
    public function revokeAll($userId)
    {
        if (empty($userId)) {
            return false;
        }

        return $this->db->createCommand()
            ->delete($this->assignmentTable, ['user_id' => (string) $userId])
            ->execute() > 0;
    }

    /**
     * @inheritdoc
     */
    public function removeAll()
    {
        $this->removeAllAssignments();
        $this->db->createCommand()->delete($this->itemChildTable)->execute();
        $this->db->createCommand()->delete($this->itemTable)->execute();
        $this->db->createCommand()->delete($this->ruleTable)->execute();
    }

    /**
     * @inheritdoc
     */
    public function removeAllPermissions()
    {
        $this->removeAllItems(Item::TYPE_PERMISSION);
    }

    /**
     * @inheritdoc
     */
    public function removeAllRoles()
    {
        $this->removeAllItems(Item::TYPE_ROLE);
    }

    /**
     * Removes all auth items of the specified type.
     * @param integer $type the auth item type (either Item::TYPE_PERMISSION or Item::TYPE_ROLE)
     */
    protected function removeAllItems($type)
    {
        if (!$this->supportsCascadeUpdate()) {
            $names = (new Query)
                ->select(['name'])
                ->from($this->itemTable)
                ->where(['type' => $type])
                ->column($this->db);
            if (empty($names)) {
                return;
            }
            $key = $type == Item::TYPE_PERMISSION ? 'child' : 'parent';
            $this->db->createCommand()
                ->delete($this->itemChildTable, [$key => $names])
                ->execute();
            $this->db->createCommand()
                ->delete($this->assignmentTable, ['item_name' => $names])
                ->execute();
        }
        $this->db->createCommand()
            ->delete($this->itemTable, ['type' => $type])
            ->execute();
    }

    /**
     * @inheritdoc
     */
    public function removeAllRules()
    {
        if (!$this->supportsCascadeUpdate()) {
            $this->db->createCommand()
                ->update($this->itemTable, ['ruleName' => null])
                ->execute();
        }

        $this->db->createCommand()->delete($this->ruleTable)->execute();
    }

    /**
     * @inheritdoc
     */
    public function removeAllAssignments()
    {
        $this->db->createCommand()->delete($this->assignmentTable)->execute();
    }
}
