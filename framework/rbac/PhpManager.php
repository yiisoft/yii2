<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rbac;

use yii\base\InvalidCallException;
use yii\base\InvalidParamException;
use Yii;
use yii\helpers\VarDumper;

/**
 * PhpManager represents an authorization manager that stores authorization
 * information in terms of a PHP script file.
 *
 * The authorization data will be saved to and loaded from three files
 * specified by [[itemFile]], [[assignmentFile]] and [[ruleFile]].
 *
 * PhpManager is mainly suitable for authorization data that is not too big
 * (for example, the authorization data for a personal blog system).
 * Use [[DbManager]] for more complex authorization data.
 *
 * Note that PhpManager is not compatible with facebooks [HHVM](http://hhvm.com/) because
 * it relies on writing php files and including them afterwards which is not supported by HHVM.
 *
 * For more details and usage information on PhpManager, see the [guide article on security authorization](guide:security-authorization).
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
     * @var string the path of the PHP script that contains the authorization items.
     * This can be either a file path or a path alias to the file.
     * Make sure this file is writable by the Web server process if the authorization needs to be changed online.
     * @see loadFromFile()
     * @see saveToFile()
     */
    public $itemFile = '@app/rbac/items.php';
    /**
     * @var string the path of the PHP script that contains the authorization assignments.
     * This can be either a file path or a path alias to the file.
     * Make sure this file is writable by the Web server process if the authorization needs to be changed online.
     * @see loadFromFile()
     * @see saveToFile()
     */
    public $assignmentFile = '@app/rbac/assignments.php';
    /**
     * @var string the path of the PHP script that contains the authorization rules.
     * This can be either a file path or a path alias to the file.
     * Make sure this file is writable by the Web server process if the authorization needs to be changed online.
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
     * Initializes the application component.
     * This method overrides parent implementation by loading the authorization data
     * from PHP script.
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
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getAssignments($userId)
    {
        return isset($this->assignments[$userId]) ? $this->assignments[$userId] : [];
    }

    /**
     * Performs access check for the specified user.
     * This method is internally called by [[checkAccess()]].
     *
     * @param string|int $user the user ID. This should can be either an integer or a string representing
     * the unique identifier of a user. See [[\yii\web\User::id]].
     * @param string $itemName the name of the operation that need access check
     * @param array $params name-value pairs that would be passed to rules associated
     * with the tasks and roles assigned to the user. A param with name 'user' is added to this array,
     * which holds the value of `$userId`.
     * @param Assignment[] $assignments the assignments to the specified user
     * @return bool whether the operations can be performed by the user.
     */
    protected function checkAccessRecursive($user, $itemName, $params, $assignments)
    {
        if (!isset($this->items[$itemName])) {
            return false;
        }

        /* @var $item Item */
        $item = $this->items[$itemName];
        Yii::trace($item instanceof Role ? "Checking role: $itemName" : "Checking permission : $itemName", __METHOD__);

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
     * @inheritdoc
     * @since 2.0.8
     */
    public function canAddChild($parent, $child)
    {
        return !$this->detectLoop($parent, $child);
    }

    /**
     * @inheritdoc
     */
    public function addChild($parent, $child)
    {
        if (!isset($this->items[$parent->name], $this->items[$child->name])) {
            throw new InvalidParamException("Either '{$parent->name}' or '{$child->name}' does not exist.");
        }

        if ($parent->name === $child->name) {
            throw new InvalidParamException("Cannot add '{$parent->name} ' as a child of itself.");
        }
        if ($parent instanceof Permission && $child instanceof Role) {
            throw new InvalidParamException('Cannot add a role as a child of a permission.');
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
     * Checks whether there is a loop in the authorization item hierarchy.
     *
     * @param Item $parent parent item
     * @param Item $child the child item that is to be added to the hierarchy
     * @return bool whether a loop exists
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
     * @inheritdoc
     */
    public function removeChild($parent, $child)
    {
        if (isset($this->children[$parent->name][$child->name])) {
            unset($this->children[$parent->name][$child->name]);
            $this->saveItems();
            return true;
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function removeChildren($parent)
    {
        if (isset($this->children[$parent->name])) {
            unset($this->children[$parent->name]);
            $this->saveItems();
            return true;
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function hasChild($parent, $child)
    {
        return isset($this->children[$parent->name][$child->name]);
    }

    /**
     * @inheritdoc
     */
    public function assign($role, $userId)
    {
        if (!isset($this->items[$role->name])) {
            throw new InvalidParamException("Unknown role '{$role->name}'.");
        } elseif (isset($this->assignments[$userId][$role->name])) {
            throw new InvalidParamException("Authorization item '{$role->name}' has already been assigned to user '$userId'.");
        } else {
            $this->assignments[$userId][$role->name] = new Assignment([
                'userId' => $userId,
                'roleName' => $role->name,
                'createdAt' => time(),
            ]);
            $this->saveAssignments();
            return $this->assignments[$userId][$role->name];
        }
    }

    /**
     * @inheritdoc
     */
    public function revoke($role, $userId)
    {
        if (isset($this->assignments[$userId][$role->name])) {
            unset($this->assignments[$userId][$role->name]);
            $this->saveAssignments();
            return true;
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function revokeAll($userId)
    {
        if (isset($this->assignments[$userId]) && is_array($this->assignments[$userId])) {
            foreach ($this->assignments[$userId] as $itemName => $value) {
                unset($this->assignments[$userId][$itemName]);
            }
            $this->saveAssignments();
            return true;
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function getAssignment($roleName, $userId)
    {
        return isset($this->assignments[$userId][$roleName]) ? $this->assignments[$userId][$roleName] : null;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
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
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function getItem($name)
    {
        return isset($this->items[$name]) ? $this->items[$name] : null;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getRule($name)
    {
        return isset($this->rules[$name]) ? $this->rules[$name] : null;
    }

    /**
     * @inheritdoc
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getChildRoles($roleName)
    {
        $role = $this->getRole($roleName);

        if ($role === null) {
            throw new InvalidParamException("Role \"$roleName\" not found.");
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
     * @inheritdoc
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
     * Recursively finds all children and grand children of the specified item.
     *
     * @param string $name the name of the item whose children are to be looked for.
     * @param array $result the children and grand children (in array keys)
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
     * @inheritdoc
     */
    public function getPermissionsByUser($userId)
    {
        $directPermission = $this->getDirectPermissionsByUser($userId);
        $inheritedPermission = $this->getInheritedPermissionsByUser($userId);

        return array_merge($directPermission, $inheritedPermission);
    }

    /**
     * Returns all permissions that are directly assigned to user.
     * @param string|int $userId the user ID (see [[\yii\web\User::id]])
     * @return Permission[] all direct permissions that the user has. The array is indexed by the permission names.
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
     * Returns all permissions that the user inherits from the roles assigned to him.
     * @param string|int $userId the user ID (see [[\yii\web\User::id]])
     * @return Permission[] all inherited permissions that the user has. The array is indexed by the permission names.
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
     * @inheritdoc
     */
    public function getChildren($name)
    {
        return isset($this->children[$name]) ? $this->children[$name] : [];
    }

    /**
     * @inheritdoc
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
     * @param int $type the auth item type (either Item::TYPE_PERMISSION or Item::TYPE_ROLE)
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
     * @inheritdoc
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
     * @inheritdoc
     */
    public function removeAllAssignments()
    {
        $this->assignments = [];
        $this->saveAssignments();
    }

    /**
     * @inheritdoc
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
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    protected function addRule($rule)
    {
        $this->rules[$rule->name] = $rule;
        $this->saveRules();
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function updateItem($name, $item)
    {
        if ($name !== $item->name) {
            if (isset($this->items[$item->name])) {
                throw new InvalidParamException("Unable to change the item name. The name '{$item->name}' is already used by another item.");
            } else {
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
        }

        $this->items[$item->name] = $item;

        $this->saveItems();
        return true;
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

        $this->items[$item->name] = $item;

        $this->saveItems();

        return true;

    }

    /**
     * Loads authorization data from persistent storage.
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
     * Saves authorization data into persistent storage.
     */
    protected function save()
    {
        $this->saveItems();
        $this->saveAssignments();
        $this->saveRules();
    }

    /**
     * Loads the authorization data from a PHP script file.
     *
     * @param string $file the file path.
     * @return array the authorization data
     * @see saveToFile()
     */
    protected function loadFromFile($file)
    {
        if (is_file($file)) {
            return require($file);
        } else {
            return [];
        }
    }

    /**
     * Saves the authorization data to a PHP script file.
     *
     * @param array $data the authorization data
     * @param string $file the file path.
     * @see loadFromFile()
     */
    protected function saveToFile($data, $file)
    {
        file_put_contents($file, "<?php\nreturn " . VarDumper::export($data) . ";\n", LOCK_EX);
        $this->invalidateScriptCache($file);
    }

    /**
     * Invalidates precompiled script cache (such as OPCache or APC) for the given file.
     * @param string $file the file path.
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
     * Saves items data into persistent storage.
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
     * Saves assignments data into persistent storage.
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
     * Saves rules data into persistent storage.
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
     * @inheritdoc
     * @since 2.0.7
     */
    public function getUserIdsByRole($roleName)
    {
        $result = [];
        foreach ($this->assignments as $userID => $assignments) {
            foreach ($assignments as $userAssignment) {
                if ($userAssignment->roleName === $roleName && $userAssignment->userId == $userID) {
                    $result[] = (string)$userID;
                }
            }
        }
        return $result;
    }
}
