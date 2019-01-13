<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rbac;

use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidCallException;
use yii\caching\CacheInterface;
use yii\db\Connection;
use yii\db\Expression;
use yii\db\Query;
use yii\di\Instance;

/**
 * DbManager 表示将授权信息存储在数据库中的授权管理器。
 *
 * 数据库连接由 [[db]] 指定。可以使用迁移工具来初始化数据库模式：
 *
 * ```
 * yii migrate --migrationPath=@yii/rbac/migrations/
 * ```
 *
 * 如果你不想使用迁移工具而是需要 SQL，所有数据库的文件都位于 migrations 目录中。
 *
 * 你可以通过设置 [[itemTable]]，[[itemChildTable]]，[[assignmentTable]] 和 [[ruleTable]]
 * 来更改用于存储授权和规则数据的表的名称。
 *
 * 有关 DbManager 的更多详细信息和用法信息，请参阅 [授权指南](guide:security-authorization)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class DbManager extends BaseManager
{
    /**
     * @var Connection|array|string 数据库连接对象或数据库连接的应用程序组件 ID。
     * 创建 DbManager 对象后，如果要更改此属性，
     * 你应该只为它分配一个 DB 连接对象。
     * 从版本 2.0.2 开始，这也可以通过配置数组来创建。
     */
    public $db = 'db';
    /**
     * @var string 存储授权项的表的名称。默认为 “auth_item”。
     */
    public $itemTable = '{{%auth_item}}';
    /**
     * @var string 存储授权项层次结构下的表的名称。默认为 “auth_item_child”。
     */
    public $itemChildTable = '{{%auth_item_child}}';
    /**
     * @var string 存储授权项目分配的表的名称。默认为 “auth_assignment”。
     */
    public $assignmentTable = '{{%auth_assignment}}';
    /**
     * @var string 存储规则的表的名称。默认为 “auth_rule”。
     */
    public $ruleTable = '{{%auth_rule}}';
    /**
     * @var CacheInterface|array|string 用于提高 RBAC 性能的缓存。这可以是以下之一：
     *
     * - 一个应用程序组件 ID（例如 `cache`）
     * - 一个配置数组
     * - 一个 [[\yii\caching\Cache]] 对象
     *
     * 如果未设置，则表示未启用缓存。
     *
     * 请注意，通过启用 RBAC 缓存，所有认证项，规则和认证的父子关系项将被缓存并加载到内存中。
     * 这将提高 RBAC 权限检查的性能。 
     * 但是，它也会需要额外的内存，因此如果你的 RBAC 系统包含太多认证项，则可能不合适。
     * 在这种情况下，你应该寻求其他 RBAC 实现（例如，基于 Redis 存储的 RBAC）。
     *
     * 另请注意，如果你从此组件外部修改 RBAC 项目，规则或父子关系，
     * 你必须手动调用 [[invalidateCache()]] 以确保数据一致性。
     *
     * @since 2.0.3
     */
    public $cache;
    /**
     * @var string 用于在缓存中存储 RBAC 数据的键
     * @see cache
     * @since 2.0.3
     */
    public $cacheKey = 'rbac';

    /**
     * @var Item[] 所有认证项目（name => Item）
     */
    protected $items;
    /**
     * @var Rule[] 所有认证规则（name => Rule）
     */
    protected $rules;
    /**
     * @var array 认证项的父子关系（childName => list of parents）
     */
    protected $parents;


    /**
     * 初始化应用程序组件。
     * 此方法通过建立数据库连接来覆盖父实现。
     */
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::className());
        if ($this->cache !== null) {
            $this->cache = Instance::ensure($this->cache, 'yii\caching\CacheInterface');
        }
    }

    private $_checkAccessAssignments = [];

    /**
     * {@inheritdoc}
     */
    public function checkAccess($userId, $permissionName, $params = [])
    {
        if (isset($this->_checkAccessAssignments[(string) $userId])) {
            $assignments = $this->_checkAccessAssignments[(string) $userId];
        } else {
            $assignments = $this->getAssignments($userId);
            $this->_checkAccessAssignments[(string) $userId] = $assignments;
        }

        if ($this->hasNoAssignments($assignments)) {
            return false;
        }

        $this->loadFromCache();
        if ($this->items !== null) {
            return $this->checkAccessFromCache($userId, $permissionName, $params, $assignments);
        }

        return $this->checkAccessRecursive($userId, $permissionName, $params, $assignments);
    }

    /**
     * 根据从缓存加载的数据对指定用户执行访问检查。
     * 启用 [[cache]] 时，[[checkAccess()]] 在内部调用此方法。
     * @param string|int $user 用户 ID。这应该是整数或字符串，
     * 表示用户的唯一标识符。参阅 [[\yii\web\User::id]]。
     * @param string $itemName 需要访问检查的操作的名称
     * @param array $params 一个键值对，用于传递给与分配给用户任务和角色关联的规则。
     * 名为 'user' 的参数将添加到此数组中，
     * 该数组包含 `$userId` 的值。
     * @param Assignment[] $assignments 指定用户的分配
     * @return bool 用户是否可以执行操作。
     * @since 2.0.3
     */
    protected function checkAccessFromCache($user, $itemName, $params, $assignments)
    {
        if (!isset($this->items[$itemName])) {
            return false;
        }

        $item = $this->items[$itemName];

        Yii::debug($item instanceof Role ? "Checking role: $itemName" : "Checking permission: $itemName", __METHOD__);

        if (!$this->executeRule($user, $item, $params)) {
            return false;
        }

        if (isset($assignments[$itemName]) || in_array($itemName, $this->defaultRoles)) {
            return true;
        }

        if (!empty($this->parents[$itemName])) {
            foreach ($this->parents[$itemName] as $parent) {
                if ($this->checkAccessFromCache($user, $parent, $params, $assignments)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 对指定用户执行访问检查。
     * 此方法由 [[checkAccess()]] 在内部调用。
     * @param string|int $user 用户 ID。这应该是整数或字符串，
     * 表示用户的唯一标识符。参阅 [[\yii\web\User::id]]。
     * @param string $itemName 需要访问检查的操作的名称
     * @param array $params 一个键值对，用于传递给与分配给用户任务和角色关联的规则。
     * 名为 'user' 的参数将添加到此数组中，
     * 该数组包含 `$userId` 的值。
     * @param Assignment[] $assignments 指定用户的分配
     * @return bool 用户是否可以执行操作。
     */
    protected function checkAccessRecursive($user, $itemName, $params, $assignments)
    {
        if (($item = $this->getItem($itemName)) === null) {
            return false;
        }

        Yii::debug($item instanceof Role ? "Checking role: $itemName" : "Checking permission: $itemName", __METHOD__);

        if (!$this->executeRule($user, $item, $params)) {
            return false;
        }

        if (isset($assignments[$itemName]) || in_array($itemName, $this->defaultRoles)) {
            return true;
        }

        $query = new Query();
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
     * {@inheritdoc}
     */
    protected function getItem($name)
    {
        if (empty($name)) {
            return null;
        }

        if (!empty($this->items[$name])) {
            return $this->items[$name];
        }

        $row = (new Query())->from($this->itemTable)
            ->where(['name' => $name])
            ->one($this->db);

        if ($row === false) {
            return null;
        }

        return $this->populateItem($row);
    }

    /**
     * 返回一个值，指示数据库是否支持级联更新和删除。
     * 对于 SQLite 数据库，默认实现将返回 false，对于所有其他数据库，则返回 true。
     * @return bool 数据库是否支持级联更新和删除。
     */
    protected function supportsCascadeUpdate()
    {
        return strncmp($this->db->getDriverName(), 'sqlite', 6) !== 0;
    }

    /**
     * {@inheritdoc}
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

        $this->invalidateCache();

        return true;
    }

    /**
     * {@inheritdoc}
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

        $this->invalidateCache();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function updateItem($name, $item)
    {
        if ($item->name !== $name && !$this->supportsCascadeUpdate()) {
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

        $this->invalidateCache();

        return true;
    }

    /**
     * {@inheritdoc}
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

        $this->invalidateCache();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function updateRule($name, $rule)
    {
        if ($rule->name !== $name && !$this->supportsCascadeUpdate()) {
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

        $this->invalidateCache();

        return true;
    }

    /**
     * {@inheritdoc}
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

        $this->invalidateCache();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getItems($type)
    {
        $query = (new Query())
            ->from($this->itemTable)
            ->where(['type' => $type]);

        $items = [];
        foreach ($query->all($this->db) as $row) {
            $items[$row['name']] = $this->populateItem($row);
        }

        return $items;
    }

    /**
     * 从数据库获取数据以填充认证项。
     * @param array $row 来自认证项目表的数据
     * @return Item 填充的认证项目实例（角色或权限）
     */
    protected function populateItem($row)
    {
        $class = $row['type'] == Item::TYPE_PERMISSION ? Permission::className() : Role::className();

        if (!isset($row['data']) || ($data = @unserialize(is_resource($row['data']) ? stream_get_contents($row['data']) : $row['data'])) === false) {
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
     * {@inheritdoc}
     * 此方法返回的角色包括通过 [[$defaultRoles]] 分配的角色。
     */
    public function getRolesByUser($userId)
    {
        if ($this->isEmptyUserId($userId)) {
            return [];
        }

        $query = (new Query())->select('b.*')
            ->from(['a' => $this->assignmentTable, 'b' => $this->itemTable])
            ->where('{{a}}.[[item_name]]={{b}}.[[name]]')
            ->andWhere(['a.user_id' => (string) $userId])
            ->andWhere(['b.type' => Item::TYPE_ROLE]);

        $roles = $this->getDefaultRoleInstances();
        foreach ($query->all($this->db) as $row) {
            $roles[$row['name']] = $this->populateItem($row);
        }

        return $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildRoles($roleName)
    {
        $role = $this->getRole($roleName);

        if ($role === null) {
            throw new InvalidArgumentException("Role \"$roleName\" not found.");
        }

        $result = [];
        $this->getChildrenRecursive($roleName, $this->getChildrenList(), $result);

        $roles = [$roleName => $role];

        $roles += array_filter($this->getRoles(), function (Role $roleItem) use ($result) {
            return array_key_exists($roleItem->name, $result);
        });

        return $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissionsByRole($roleName)
    {
        $childrenList = $this->getChildrenList();
        $result = [];
        $this->getChildrenRecursive($roleName, $childrenList, $result);
        if (empty($result)) {
            return [];
        }
        $query = (new Query())->from($this->itemTable)->where([
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
     * {@inheritdoc}
     */
    public function getPermissionsByUser($userId)
    {
        if ($this->isEmptyUserId($userId)) {
            return [];
        }

        $directPermission = $this->getDirectPermissionsByUser($userId);
        $inheritedPermission = $this->getInheritedPermissionsByUser($userId);

        return array_merge($directPermission, $inheritedPermission);
    }

    /**
     * 返回直接分配给用户的所有权限。
     * @param string|int $userId 用户 ID（详见 [[\yii\web\User::id]]）
     * @return Permission[] 用户拥有的所有直接权限。该数组由权限名称索引。
     * @since 2.0.7
     */
    protected function getDirectPermissionsByUser($userId)
    {
        $query = (new Query())->select('b.*')
            ->from(['a' => $this->assignmentTable, 'b' => $this->itemTable])
            ->where('{{a}}.[[item_name]]={{b}}.[[name]]')
            ->andWhere(['a.user_id' => (string) $userId])
            ->andWhere(['b.type' => Item::TYPE_PERMISSION]);

        $permissions = [];
        foreach ($query->all($this->db) as $row) {
            $permissions[$row['name']] = $this->populateItem($row);
        }

        return $permissions;
    }

    /**
     * 返回用户从分配给他的角色继承的所有权限。
     * @param string|int $userId 用户 ID（详见 [[\yii\web\User::id]]）
     * @return Permission[] 用户拥有的所有继承权限。该数组由权限名称索引。
     * @since 2.0.7
     */
    protected function getInheritedPermissionsByUser($userId)
    {
        $query = (new Query())->select('item_name')
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

        $query = (new Query())->from($this->itemTable)->where([
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
     * 返回每个父项的子项。
     * @return array 孩子们的名单。每个数组键都是父项名称，
     * 相应的数组值是子项名称列表。
     */
    protected function getChildrenList()
    {
        $query = (new Query())->from($this->itemChildTable);
        $parents = [];
        foreach ($query->all($this->db) as $row) {
            $parents[$row['parent']][] = $row['child'];
        }

        return $parents;
    }

    /**
     * 递归查找指定项的所有子项及子孙项。
     * @param string $name 要查找其子项的项的名称。
     * @param array $childrenList 通过 [[getChildrenList()]] 构建的孩子项列表
     * @param array $result 子项和子孙项（在数组键中）
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
     * {@inheritdoc}
     */
    public function getRule($name)
    {
        if ($this->rules !== null) {
            return isset($this->rules[$name]) ? $this->rules[$name] : null;
        }

        $row = (new Query())->select(['data'])
            ->from($this->ruleTable)
            ->where(['name' => $name])
            ->one($this->db);
        if ($row === false) {
            return null;
        }
        $data = $row['data'];
        if (is_resource($data)) {
            $data = stream_get_contents($data);
        }

        return unserialize($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getRules()
    {
        if ($this->rules !== null) {
            return $this->rules;
        }

        $query = (new Query())->from($this->ruleTable);

        $rules = [];
        foreach ($query->all($this->db) as $row) {
            $data = $row['data'];
            if (is_resource($data)) {
                $data = stream_get_contents($data);
            }
            $rules[$row['name']] = unserialize($data);
        }

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssignment($roleName, $userId)
    {
        if ($this->isEmptyUserId($userId)) {
            return null;
        }

        $row = (new Query())->from($this->assignmentTable)
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
     * {@inheritdoc}
     */
    public function getAssignments($userId)
    {
        if ($this->isEmptyUserId($userId)) {
            return [];
        }

        $query = (new Query())
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
     * {@inheritdoc}
     * @since 2.0.8
     */
    public function canAddChild($parent, $child)
    {
        return !$this->detectLoop($parent, $child);
    }

    /**
     * {@inheritdoc}
     */
    public function addChild($parent, $child)
    {
        if ($parent->name === $child->name) {
            throw new InvalidArgumentException("Cannot add '{$parent->name}' as a child of itself.");
        }

        if ($parent instanceof Permission && $child instanceof Role) {
            throw new InvalidArgumentException('Cannot add a role as a child of a permission.');
        }

        if ($this->detectLoop($parent, $child)) {
            throw new InvalidCallException("Cannot add '{$child->name}' as a child of '{$parent->name}'. A loop has been detected.");
        }

        $this->db->createCommand()
            ->insert($this->itemChildTable, ['parent' => $parent->name, 'child' => $child->name])
            ->execute();

        $this->invalidateCache();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function removeChild($parent, $child)
    {
        $result = $this->db->createCommand()
            ->delete($this->itemChildTable, ['parent' => $parent->name, 'child' => $child->name])
            ->execute() > 0;

        $this->invalidateCache();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function removeChildren($parent)
    {
        $result = $this->db->createCommand()
            ->delete($this->itemChildTable, ['parent' => $parent->name])
            ->execute() > 0;

        $this->invalidateCache();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function hasChild($parent, $child)
    {
        return (new Query())
            ->from($this->itemChildTable)
            ->where(['parent' => $parent->name, 'child' => $child->name])
            ->one($this->db) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren($name)
    {
        $query = (new Query())
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
     * 检查授权项层次结构中是否存在循环。
     * @param Item $parent 父项目
     * @param Item $child 要添加到层次结构的子项
     * @return bool 是否存在循环
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
     * {@inheritdoc}
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

        unset($this->_checkAccessAssignments[(string) $userId]);
        return $assignment;
    }

    /**
     * {@inheritdoc}
     */
    public function revoke($role, $userId)
    {
        if ($this->isEmptyUserId($userId)) {
            return false;
        }

        unset($this->_checkAccessAssignments[(string) $userId]);
        return $this->db->createCommand()
            ->delete($this->assignmentTable, ['user_id' => (string) $userId, 'item_name' => $role->name])
            ->execute() > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAll($userId)
    {
        if ($this->isEmptyUserId($userId)) {
            return false;
        }

        unset($this->_checkAccessAssignments[(string) $userId]);
        return $this->db->createCommand()
            ->delete($this->assignmentTable, ['user_id' => (string) $userId])
            ->execute() > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function removeAll()
    {
        $this->removeAllAssignments();
        $this->db->createCommand()->delete($this->itemChildTable)->execute();
        $this->db->createCommand()->delete($this->itemTable)->execute();
        $this->db->createCommand()->delete($this->ruleTable)->execute();
        $this->invalidateCache();
    }

    /**
     * {@inheritdoc}
     */
    public function removeAllPermissions()
    {
        $this->removeAllItems(Item::TYPE_PERMISSION);
    }

    /**
     * {@inheritdoc}
     */
    public function removeAllRoles()
    {
        $this->removeAllItems(Item::TYPE_ROLE);
    }

    /**
     * 删除指定类型的所有认证项。
     * @param int $type 认证项目类型（该值为 Item::TYPE_PERMISSION 或者 Item::TYPE_ROLE）
     */
    protected function removeAllItems($type)
    {
        if (!$this->supportsCascadeUpdate()) {
            $names = (new Query())
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

        $this->invalidateCache();
    }

    /**
     * {@inheritdoc}
     */
    public function removeAllRules()
    {
        if (!$this->supportsCascadeUpdate()) {
            $this->db->createCommand()
                ->update($this->itemTable, ['rule_name' => null])
                ->execute();
        }

        $this->db->createCommand()->delete($this->ruleTable)->execute();

        $this->invalidateCache();
    }

    /**
     * {@inheritdoc}
     */
    public function removeAllAssignments()
    {
        $this->_checkAccessAssignments = [];
        $this->db->createCommand()->delete($this->assignmentTable)->execute();
    }

    public function invalidateCache()
    {
        if ($this->cache !== null) {
            $this->cache->delete($this->cacheKey);
            $this->items = null;
            $this->rules = null;
            $this->parents = null;
        }
        $this->_checkAccessAssignments = [];
    }

    public function loadFromCache()
    {
        if ($this->items !== null || !$this->cache instanceof CacheInterface) {
            return;
        }

        $data = $this->cache->get($this->cacheKey);
        if (is_array($data) && isset($data[0], $data[1], $data[2])) {
            list($this->items, $this->rules, $this->parents) = $data;
            return;
        }

        $query = (new Query())->from($this->itemTable);
        $this->items = [];
        foreach ($query->all($this->db) as $row) {
            $this->items[$row['name']] = $this->populateItem($row);
        }

        $query = (new Query())->from($this->ruleTable);
        $this->rules = [];
        foreach ($query->all($this->db) as $row) {
            $data = $row['data'];
            if (is_resource($data)) {
                $data = stream_get_contents($data);
            }
            $this->rules[$row['name']] = unserialize($data);
        }

        $query = (new Query())->from($this->itemChildTable);
        $this->parents = [];
        foreach ($query->all($this->db) as $row) {
            if (isset($this->items[$row['child']])) {
                $this->parents[$row['child']][] = $row['parent'];
            }
        }

        $this->cache->set($this->cacheKey, [$this->items, $this->rules, $this->parents]);
    }

    /**
     * 返回指定角色的所有角色分配信息。
     * @param string $roleName
     * @return string[] 用户 ID 数组。如果未将角色分配给任何用户，
     * 则将返回空数组。
     * @since 2.0.7
     */
    public function getUserIdsByRole($roleName)
    {
        if (empty($roleName)) {
            return [];
        }

        return (new Query())->select('[[user_id]]')
            ->from($this->assignmentTable)
            ->where(['item_name' => $roleName])->column($this->db);
    }

    /**
     * 检查 $userId 是否为空。
     * @param mixed $userId
     * @return bool
     */
    private function isEmptyUserId($userId)
    {
        return !isset($userId) || $userId === '';
    }
}
