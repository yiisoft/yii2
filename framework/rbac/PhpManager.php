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
use yii\helpers\VarDumper;

/**
 * PhpManager 表示一个授权管理器，
 * 它根据 PHP 脚本文件存储授权信息。
 *
 * 授权数据将保存到 [[itemFile]]，[[assignmentFile]]
 * 和 [[ruleFile]] 指定的三个文件中并从中加载。
 *
 * PhpManager 主要适用于不太大的授权数据
 * （例如，个人博客系统的授权数据）。
 * 对于更复杂的授权数据，应使用 [[DbManager]]。
 *
 * 请注意，PhpManager 与 facebooks [HHVM](http://hhvm.com/) 不兼容，
 * 因为它依赖于编写 php 文件并在之后包含它们，而 HHVM 不支持它们。
 *
 * 有关 PhpManager 的更多详细信息和用法信息，请参阅 [授权指南](guide:security-authorization)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @author Christophe Boulain <christophe.boulain@gmail.com>
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class PhpManager extends BaseManager
{
    /**
     * @var string 包含授权项的 PHP 脚本的路径。
     * 这可以是文件路径，也可以是文件的 [路径别名](guide:concept-aliases)。
     * 如果需要在线更改授权，请确保 Web 服务器进程可写入此文件。
     * @see loadFromFile()
     * @see saveToFile()
     */
    public $itemFile = '@app/rbac/items.php';
    /**
     * @var string 包含授权分配的 PHP 脚本的路径。
     * 这可以是文件路径，也可以是文件的 [路径别名](guide:concept-aliases)。
     * 如果需要在线更改授权，请确保 Web 服务器进程可写入此文件。
     * @see loadFromFile()
     * @see saveToFile()
     */
    public $assignmentFile = '@app/rbac/assignments.php';
    /**
     * @var string 包含授权规则的 PHP 脚本的路径。
     * 这可以是文件路径，也可以是文件的 [路径别名](guide:concept-aliases)。
     * 如果需要在线更改授权，请确保 Web 服务器进程可写入此文件。
     * @see loadFromFile()
     * @see saveToFile()
     */
    public $ruleFile = '@app/rbac/rules.php';

    /**
     * @var Item[]
     */
    protected $items = []; // itemName => item
    /**
     * @var array
     */
    protected $children = []; // itemName, childName => child
    /**
     * @var array
     */
    protected $assignments = []; // userId, itemName => assignment
    /**
     * @var Rule[]
     */
    protected $rules = []; // ruleName => rule


    /**
     * 初始化应用程序组件。
     * 此方法通过从 PHP
     * 脚本加载授权数据来覆盖父实现。
     */
    public function init()
    {
        parent::init();
        $this->itemFile = Yii::getAlias($this->itemFile);
        $this->assignmentFile = Yii::getAlias($this->assignmentFile);
        $this->ruleFile = Yii::getAlias($this->ruleFile);
        $this->load();
    }

    /**
     * {@inheritdoc}
     */
    public function checkAccess($userId, $permissionName, $params = [])
    {
        $assignments = $this->getAssignments($userId);

        if ($this->hasNoAssignments($assignments)) {
            return false;
        }

        return $this->checkAccessRecursive($userId, $permissionName, $params, $assignments);
    }

    /**
     * {@inheritdoc}
     */
    public function getAssignments($userId)
    {
        return isset($this->assignments[$userId]) ? $this->assignments[$userId] : [];
    }

    /**
     * 对指定用户执行访问检查。
     * 此方法由 [[checkAccess()]] 在内部调用。
     *
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
        if (!isset($this->items[$itemName])) {
            return false;
        }

        /* @var $item Item */
        $item = $this->items[$itemName];
        Yii::debug($item instanceof Role ? "Checking role: $itemName" : "Checking permission : $itemName", __METHOD__);

        if (!$this->executeRule($user, $item, $params)) {
            return false;
        }

        if (isset($assignments[$itemName]) || in_array($itemName, $this->defaultRoles)) {
            return true;
        }

        foreach ($this->children as $parentName => $children) {
            if (isset($children[$itemName]) && $this->checkAccessRecursive($user, $parentName, $params, $assignments)) {
                return true;
            }
        }

        return false;
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
        if (!isset($this->items[$parent->name], $this->items[$child->name])) {
            throw new InvalidArgumentException("Either '{$parent->name}' or '{$child->name}' does not exist.");
        }

        if ($parent->name === $child->name) {
            throw new InvalidArgumentException("Cannot add '{$parent->name} ' as a child of itself.");
        }
        if ($parent instanceof Permission && $child instanceof Role) {
            throw new InvalidArgumentException('Cannot add a role as a child of a permission.');
        }

        if ($this->detectLoop($parent, $child)) {
            throw new InvalidCallException("Cannot add '{$child->name}' as a child of '{$parent->name}'. A loop has been detected.");
        }
        if (isset($this->children[$parent->name][$child->name])) {
            throw new InvalidCallException("The item '{$parent->name}' already has a child '{$child->name}'.");
        }
        $this->children[$parent->name][$child->name] = $this->items[$child->name];
        $this->saveItems();

        return true;
    }

    /**
     * 检查授权项层次结构中是否存在循环。
     *
     * @param Item $parent 父项目
     * @param Item $child 要添加到层次结构的子项
     * @return bool 是否存在循环
     */
    protected function detectLoop($parent, $child)
    {
        if ($child->name === $parent->name) {
            return true;
        }
        if (!isset($this->children[$child->name], $this->items[$parent->name])) {
            return false;
        }
        foreach ($this->children[$child->name] as $grandchild) {
            /* @var $grandchild Item */
            if ($this->detectLoop($parent, $grandchild)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function removeChild($parent, $child)
    {
        if (isset($this->children[$parent->name][$child->name])) {
            unset($this->children[$parent->name][$child->name]);
            $this->saveItems();
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function removeChildren($parent)
    {
        if (isset($this->children[$parent->name])) {
            unset($this->children[$parent->name]);
            $this->saveItems();
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function hasChild($parent, $child)
    {
        return isset($this->children[$parent->name][$child->name]);
    }

    /**
     * {@inheritdoc}
     */
    public function assign($role, $userId)
    {
        if (!isset($this->items[$role->name])) {
            throw new InvalidArgumentException("Unknown role '{$role->name}'.");
        } elseif (isset($this->assignments[$userId][$role->name])) {
            throw new InvalidArgumentException("Authorization item '{$role->name}' has already been assigned to user '$userId'.");
        }

        $this->assignments[$userId][$role->name] = new Assignment([
            'userId' => $userId,
            'roleName' => $role->name,
            'createdAt' => time(),
        ]);
        $this->saveAssignments();

        return $this->assignments[$userId][$role->name];
    }

    /**
     * {@inheritdoc}
     */
    public function revoke($role, $userId)
    {
        if (isset($this->assignments[$userId][$role->name])) {
            unset($this->assignments[$userId][$role->name]);
            $this->saveAssignments();
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAll($userId)
    {
        if (isset($this->assignments[$userId]) && is_array($this->assignments[$userId])) {
            foreach ($this->assignments[$userId] as $itemName => $value) {
                unset($this->assignments[$userId][$itemName]);
            }
            $this->saveAssignments();
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssignment($roleName, $userId)
    {
        return isset($this->assignments[$userId][$roleName]) ? $this->assignments[$userId][$roleName] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems($type)
    {
        $items = [];

        foreach ($this->items as $name => $item) {
            /* @var $item Item */
            if ($item->type == $type) {
                $items[$name] = $item;
            }
        }

        return $items;
    }


    /**
     * {@inheritdoc}
     */
    public function removeItem($item)
    {
        if (isset($this->items[$item->name])) {
            foreach ($this->children as &$children) {
                unset($children[$item->name]);
            }
            foreach ($this->assignments as &$assignments) {
                unset($assignments[$item->name]);
            }
            unset($this->items[$item->name]);
            $this->saveItems();
            $this->saveAssignments();
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($name)
    {
        return isset($this->items[$name]) ? $this->items[$name] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function updateRule($name, $rule)
    {
        if ($rule->name !== $name) {
            unset($this->rules[$name]);
        }
        $this->rules[$rule->name] = $rule;
        $this->saveRules();
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getRule($name)
    {
        return isset($this->rules[$name]) ? $this->rules[$name] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * {@inheritdoc}
     * 此方法返回的角色包括通过 [[$defaultRoles]] 分配的角色。
     */
    public function getRolesByUser($userId)
    {
        $roles = $this->getDefaultRoleInstances();
        foreach ($this->getAssignments($userId) as $name => $assignment) {
            $role = $this->items[$assignment->roleName];
            if ($role->type === Item::TYPE_ROLE) {
                $roles[$name] = $role;
            }
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
        $this->getChildrenRecursive($roleName, $result);

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
        $result = [];
        $this->getChildrenRecursive($roleName, $result);
        if (empty($result)) {
            return [];
        }
        $permissions = [];
        foreach (array_keys($result) as $itemName) {
            if (isset($this->items[$itemName]) && $this->items[$itemName] instanceof Permission) {
                $permissions[$itemName] = $this->items[$itemName];
            }
        }

        return $permissions;
    }

    /**
     * 递归查找指定项的所有子项及子孙项。
     *
     * @param string $name 要查找其子项的项的名称。
     * @param array $result 子项和子孙项（在数组键中）
     */
    protected function getChildrenRecursive($name, &$result)
    {
        if (isset($this->children[$name])) {
            foreach ($this->children[$name] as $child) {
                $result[$child->name] = true;
                $this->getChildrenRecursive($child->name, $result);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissionsByUser($userId)
    {
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
        $permissions = [];
        foreach ($this->getAssignments($userId) as $name => $assignment) {
            $permission = $this->items[$assignment->roleName];
            if ($permission->type === Item::TYPE_PERMISSION) {
                $permissions[$name] = $permission;
            }
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
        $assignments = $this->getAssignments($userId);
        $result = [];
        foreach (array_keys($assignments) as $roleName) {
            $this->getChildrenRecursive($roleName, $result);
        }

        if (empty($result)) {
            return [];
        }

        $permissions = [];
        foreach (array_keys($result) as $itemName) {
            if (isset($this->items[$itemName]) && $this->items[$itemName] instanceof Permission) {
                $permissions[$itemName] = $this->items[$itemName];
            }
        }

        return $permissions;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren($name)
    {
        return isset($this->children[$name]) ? $this->children[$name] : [];
    }

    /**
     * {@inheritdoc}
     */
    public function removeAll()
    {
        $this->children = [];
        $this->items = [];
        $this->assignments = [];
        $this->rules = [];
        $this->save();
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
        $names = [];
        foreach ($this->items as $name => $item) {
            if ($item->type == $type) {
                unset($this->items[$name]);
                $names[$name] = true;
            }
        }
        if (empty($names)) {
            return;
        }

        foreach ($this->assignments as $i => $assignments) {
            foreach ($assignments as $n => $assignment) {
                if (isset($names[$assignment->roleName])) {
                    unset($this->assignments[$i][$n]);
                }
            }
        }
        foreach ($this->children as $name => $children) {
            if (isset($names[$name])) {
                unset($this->children[$name]);
            } else {
                foreach ($children as $childName => $item) {
                    if (isset($names[$childName])) {
                        unset($children[$childName]);
                    }
                }
                $this->children[$name] = $children;
            }
        }

        $this->saveItems();
    }

    /**
     * {@inheritdoc}
     */
    public function removeAllRules()
    {
        foreach ($this->items as $item) {
            $item->ruleName = null;
        }
        $this->rules = [];
        $this->saveRules();
    }

    /**
     * {@inheritdoc}
     */
    public function removeAllAssignments()
    {
        $this->assignments = [];
        $this->saveAssignments();
    }

    /**
     * {@inheritdoc}
     */
    protected function removeRule($rule)
    {
        if (isset($this->rules[$rule->name])) {
            unset($this->rules[$rule->name]);
            foreach ($this->items as $item) {
                if ($item->ruleName === $rule->name) {
                    $item->ruleName = null;
                }
            }
            $this->saveRules();
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function addRule($rule)
    {
        $this->rules[$rule->name] = $rule;
        $this->saveRules();
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function updateItem($name, $item)
    {
        if ($name !== $item->name) {
            if (isset($this->items[$item->name])) {
                throw new InvalidArgumentException("Unable to change the item name. The name '{$item->name}' is already used by another item.");
            }

            // Remove old item in case of renaming
            unset($this->items[$name]);

            if (isset($this->children[$name])) {
                $this->children[$item->name] = $this->children[$name];
                unset($this->children[$name]);
            }
            foreach ($this->children as &$children) {
                if (isset($children[$name])) {
                    $children[$item->name] = $children[$name];
                    unset($children[$name]);
                }
            }
            foreach ($this->assignments as &$assignments) {
                if (isset($assignments[$name])) {
                    $assignments[$item->name] = $assignments[$name];
                    $assignments[$item->name]->roleName = $item->name;
                    unset($assignments[$name]);
                }
            }
            $this->saveAssignments();
        }

        $this->items[$item->name] = $item;

        $this->saveItems();
        return true;
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

        $this->items[$item->name] = $item;

        $this->saveItems();

        return true;
    }

    /**
     * 从文件中加载授权数据。
     */
    protected function load()
    {
        $this->children = [];
        $this->rules = [];
        $this->assignments = [];
        $this->items = [];

        $items = $this->loadFromFile($this->itemFile);
        $itemsMtime = @filemtime($this->itemFile);
        $assignments = $this->loadFromFile($this->assignmentFile);
        $assignmentsMtime = @filemtime($this->assignmentFile);
        $rules = $this->loadFromFile($this->ruleFile);

        foreach ($items as $name => $item) {
            $class = $item['type'] == Item::TYPE_PERMISSION ? Permission::className() : Role::className();

            $this->items[$name] = new $class([
                'name' => $name,
                'description' => isset($item['description']) ? $item['description'] : null,
                'ruleName' => isset($item['ruleName']) ? $item['ruleName'] : null,
                'data' => isset($item['data']) ? $item['data'] : null,
                'createdAt' => $itemsMtime,
                'updatedAt' => $itemsMtime,
            ]);
        }

        foreach ($items as $name => $item) {
            if (isset($item['children'])) {
                foreach ($item['children'] as $childName) {
                    if (isset($this->items[$childName])) {
                        $this->children[$name][$childName] = $this->items[$childName];
                    }
                }
            }
        }

        foreach ($assignments as $userId => $roles) {
            foreach ($roles as $role) {
                $this->assignments[$userId][$role] = new Assignment([
                    'userId' => $userId,
                    'roleName' => $role,
                    'createdAt' => $assignmentsMtime,
                ]);
            }
        }

        foreach ($rules as $name => $ruleData) {
            $this->rules[$name] = unserialize($ruleData);
        }
    }

    /**
     * 将授权数据保存到文件中。
     */
    protected function save()
    {
        $this->saveItems();
        $this->saveAssignments();
        $this->saveRules();
    }

    /**
     * 从 PHP 脚本文件加载授权数据。
     *
     * @param string $file 文件路径。
     * @return array 授权数据
     * @see saveToFile()
     */
    protected function loadFromFile($file)
    {
        if (is_file($file)) {
            return require $file;
        }

        return [];
    }

    /**
     * 将授权数据保存到 PHP 脚本文件。
     *
     * @param array $data 授权数据
     * @param string $file 文件路径。
     * @see loadFromFile()
     */
    protected function saveToFile($data, $file)
    {
        file_put_contents($file, "<?php\nreturn " . VarDumper::export($data) . ";\n", LOCK_EX);
        $this->invalidateScriptCache($file);
    }

    /**
     * 使给定文件的预编译脚本缓存（例如 OPCache 或 APC ）失效。
     * @param string $file 文件路径。
     * @since 2.0.9
     */
    protected function invalidateScriptCache($file)
    {
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($file, true);
        }
        if (function_exists('apc_delete_file')) {
            @apc_delete_file($file);
        }
    }

    /**
     * 将授权项数据保存到文件中。
     */
    protected function saveItems()
    {
        $items = [];
        foreach ($this->items as $name => $item) {
            /* @var $item Item */
            $items[$name] = array_filter(
                [
                    'type' => $item->type,
                    'description' => $item->description,
                    'ruleName' => $item->ruleName,
                    'data' => $item->data,
                ]
            );
            if (isset($this->children[$name])) {
                foreach ($this->children[$name] as $child) {
                    /* @var $child Item */
                    $items[$name]['children'][] = $child->name;
                }
            }
        }
        $this->saveToFile($items, $this->itemFile);
    }

    /**
     * 将授权分配数据保存到文件中。
     */
    protected function saveAssignments()
    {
        $assignmentData = [];
        foreach ($this->assignments as $userId => $assignments) {
            foreach ($assignments as $name => $assignment) {
                /* @var $assignment Assignment */
                $assignmentData[$userId][] = $assignment->roleName;
            }
        }
        $this->saveToFile($assignmentData, $this->assignmentFile);
    }

    /**
     * 将授权规则数据保存到文件中。
     */
    protected function saveRules()
    {
        $rules = [];
        foreach ($this->rules as $name => $rule) {
            $rules[$name] = serialize($rule);
        }
        $this->saveToFile($rules, $this->ruleFile);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.7
     */
    public function getUserIdsByRole($roleName)
    {
        $result = [];
        foreach ($this->assignments as $userID => $assignments) {
            foreach ($assignments as $userAssignment) {
                if ($userAssignment->roleName === $roleName && $userAssignment->userId == $userID) {
                    $result[] = (string) $userID;
                }
            }
        }

        return $result;
    }
}
