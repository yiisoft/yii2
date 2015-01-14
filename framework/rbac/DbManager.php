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
use yii\rbac\models\Assignment;
use yii\rbac\models\Item;
use yii\rbac\models\ItemChild;
use yii\rbac\models\Rule;

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
class DbManager extends extends Component implements ManagerInterface
{
    /**
     * @var array a list of role names that are assigned to every user automatically without calling [[assign()]].
     */
    public $defaultRoles = [];

    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     * After the DbManager object is created, if you want to change this property, you should only assign it
     * with a DB connection object.
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
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
    public function createRole($name)
    {
        return new Role(['name' => $name]);
    }

    /**
     * @inheritdoc
     */
    public function createRole($name)
    {
        return new Item([
            'name' => $name,
            'type' => Item::TYPE_ROLE,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function createPermission($name)
    {
        return new Item([
            'name' => $name,
            'type' => Item::TYPE_PERMISSION,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function checkAccess($userId, $permissionName, $params = [])
    {
        return $this->checkAccessRecursive(
            $userId,
            Item::findOne($permissionName),
            $params,
            $this->getAssignments($userId)
        );
    }

    /**
     * Performs access check for the specified user.
     * This method is internally called by [[checkAccess()]].
     * @param string|integer $user the user ID. This should can be either an integer or a string representing
     * the unique identifier of a user. See [[\yii\web\User::id]].
     * @param Item $item the item that need access check
     * @param array $params name-value pairs that would be passed to rules associated
     * with the tasks and roles assigned to the user. A param with name 'user' is added to this array,
     * which holds the value of `$userId`.
     * @param Assignment[] $assignments the assignments to the specified user
     * @return boolean whether the operations can be performed by the user.
     */
    protected function checkAccessRecursive($user, $item, $params, $assignments)
    {
        if ($item === null) {
            return false;
        }

        Yii::trace(
            $item->type == Item::TYPE_ROLE
                ? "Checking role: {$item->name}"
                : "Checking permission: {$item->name}",
            __METHOD__
        );

        if (!$this->executeRule($user, $item, $params)) {
            return false;
        }

        if (isset($assignments[$item->name]) || in_array($item->name, $this->defaultRoles)) {
            return true;
        }

        foreach ($item->parents as $parent) {
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
        $item = Item::findOne($name);

        if ($item === null) {
            return null;
        }

        if (!isset($item->data)
            || ($data = @unserialize($row->data)) === false
        ) {
            $item->data = null;
        }

        return $item;
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
        return (new Item([
            'name' => $item->name,
            'type' => $item->type,
            'description' => $item->description,
            'rule_name' => $item->rule_name,
            'data' => $item->data === null ? null : serialize($item->data),
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
        ]))->save();
    }

    /**
     * @inheritdoc
     */
    protected function removeItem($item)
    {
        if (!$this->supportsCascadeUpdate()) {
            ItemChild::deleteAll(
                ['or', '[[parent]]=:name', '[[child]]=:name'],
                [':name' => $item->name]
            );
            Assignment::deleteAll(['item_name' => $item->name]);
        }

        return Item::deleteAll(['name' => $item->name]) > 0;
    }

    /**
     * @inheritdoc
     */
    protected function updateItem($name, $item)
    {
        if (!$this->supportsCascadeUpdate() && $item->name !== $name) {
            ItemChild::updateAll(['parent' => $item->name], ['parent' => $name]);
            ItemChild::updateAll(['child' => $item->name], ['child' => $name]);
            Assignment::updateAll(['item_name' => $item->name], ['item_name' => $name]);
        }

        return Item::updateAll(
            [
                'name' => $item->name,
                'description' => $item->description,
                'rule_name' => $item->rule_name,
                'data' => $item->data === null ? null : serialize($item->data)
            ],
            [
                'name' => $name,
            ]
        ) > 0;
    }

    /**
     * @inheritdoc
     */
    protected function addRule($rule)
    {
        return (new Rule([
            'name' => $rule->name,
            'data' => serialize($rule),
        ]))->save();
    }

    /**
     * @inheritdoc
     */
    protected function updateRule($name, $rule)
    {
        if (!$this->supportsCascadeUpdate() && $rule->name !== $name) {
            Item::updateAll(['rule_name' => $rule->name], ['rule_name' => $name]);
        }

        return Rule::updateAll(
            [
                'name' => $rule->name,
                'data' => serialize($rule),
            ],
            [
                'name' => $name,
            ]
        ) > 0;

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function removeRule($rule)
    {
        if (!$this->supportsCascadeUpdate()) {
            Item::updateAll(['rule_name' => null], ['rule_name' => $rule->name]);
        }

        return Rule::deleteAll(['name' => $rule->name]) > 0;
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
        $result = [];
        $this->getChildrenRecursive(
            $roleName,
            $this->getChildrenList(),
            $result
        );
        if (empty($result)) {
            return [];
        }

        $permissions = [];
        foreach (Item::findAll([
            'type' => Item::TYPE_PERMISSION,
            'name' => array_keys($result),
        ]) as $permission) {
            $permissions[$permission->name] = $permission;
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

        $result = [];
        foreach (Assignment::findAll(['user_id' => $userId]) as $assignment) {
            $this->getChildrenRecursive(
                $assignment->name,
                $this->getChildrenList(),
                $result
            );
        }

        if (empty($result)) {
            return [];
        }

        $permissions = [];
        foreach (Item::findAll([
            'type' => Item::TYPE_PERMISSION,
            'name' => array_keys($result),
        ]); as $permission) {
            $permissions[$permission->name] = $permission;
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
        $parents = [];
        foreach (ItemChild::findAll() as $ic) {
            $parents[$ic->parent][] = $ic->child;
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
        $rule = Rule::findOne($name);
        return $rule === null ? null : unserialize($rule->data);
    }

    /**
     * @inheritdoc
     */
    public function getRules()
    {
        $rules = [];
        foreach (Rule::findAll() as $rule) {
            $rules[$rule->name] = unserialize($rule->data);
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

        return Assignment::findOne([
            'user_id' => $userId,
            'item_name' => $roleName
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

        $assignments = [];
        foreach (Assignment::findAll(['user_id' => $userId]) as $assignment) {
            $assignments[$assignment->item_name] = $assignment;
        }

        return $assignments;
    }

    /**
     * @inheritdoc
     */
    public function addChild($parent, $child)
    {
        if ($parent->name === $child->name) {
            throw new InvalidParamException(
                "Cannot add '{$parent->name}' as a child of itself."
            );
        }

        if ($parent->type == Item::TYPE_PERMISSION
            && $child->type == Item::TYPE_ROLE
        ) {
            throw new InvalidParamException(
                "Cannot add a role as a child of a permission."
            );
        }

        if ($this->detectLoop($parent, $child)) {
            throw new InvalidCallException("Cannot add '{$child->name}' "
                . "as a child of '{$parent->name}'. A loop has been detected."
            );
        }

        return (new ItemChild([
            'parent' => $parent->name,
            'child' => $child->name
        ]))->save();
    }

    /**
     * @inheritdoc
     */
    public function removeChild($parent, $child)
    {
        return ItemChild::deleteAll([
            'parent' => $parent->name,
            'child' => $child->name
        ]) > 0;
    }

    /**
     * @inheritdoc
     */
    public function removeChildren($parent)
    {
        return ItemChild::deleteAll(['parent' => $parent->name]) > 0;
    }

    /**
     * @inheritdoc
     */
    public function hasChild($parent, $child)
    {
        return ItemChild::findOne([
            'parent' => $parent->name,
            'child' => $child->name
        ]) !== false;
    }

    /**
     * @inheritdoc
     */
    public function getChildren($name)
    {
        $children = [];
        foreach (Item::findOne($name)->childrens as $child) {
            $children[$child->name] = $child;
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
            'user_id' => $userId,
            'item_name' => $role->name,
        ]);
        $assignment->save();

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

        return Assignment::delete([
            'user_id' => $userId,
            'item_name' => $role->name
        ]) > 0;
    }

    /**
     * @inheritdoc
     */
    public function revokeAll($userId)
    {
        if (empty($userId)) {
            return false;
        }

        return Assignment::deleteAll(['user_id' => $userId]) > 0;
    }

    /**
     * @inheritdoc
     */
    public function removeAll()
    {
        Assignment::deleteAll();
        ItemChild::deleteAll();
        ItemTable::deleteAll();
        Rule::deleteAll();
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

            $this->db->createCommand()
                ->delete($this->itemChildTable, [
                    'or',
                    ['child' => $names],
                    ['parent' => $names]
                ])->execute();

            $this->db->createCommand()
                ->delete($this->assignmentTable, ['item_name' => $names])
                ->execute();
        }

        Item::deleteAll(['type' => $type]);
    }

    /**
     * @inheritdoc
     */
    public function removeAllRules()
    {
        if (!$this->supportsCascadeUpdate()) {
            Itemm::updateAll('rule_name' => null);
        }

        Rule::deleteAll();
    }

    /**
     * @inheritdoc
     */
    public function removeAllAssignments()
    {
        Assignment::deleteAll();
    }
}
