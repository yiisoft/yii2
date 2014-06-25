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
 * The authorization data will be saved to and loaded from a file
 * specified by [[authFile]], which defaults to 'protected/data/rbac.php'.
 *
 * PhpManager is mainly suitable for authorization data that is not too big
 * (for example, the authorization data for a personal blog system).
 * Use [[DbManager]] for more complex authorization data.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @author Christophe Boulain <christophe.boulain@gmail.com>
 * @since 2.0
 */
class PhpManager extends BaseManager
{
    /**
     * @var string the path of the PHP script that contains the authorization data.
     * This can be either a file path or a path alias to the file.
     * Make sure this file is writable by the Web server process if the authorization needs to be changed online.
     * @see loadFromFile()
     * @see saveToFile()
     */
    public $authFile = '@app/data/rbac.php';
    /**
     * @var Item[]
     */
    private $_items = []; // itemName => item
    /**
     * @var array
     */
    private $_children = []; // itemName, childName => child
    /**
     * @var Assignment[]
     */
    private $_assignments = []; // userId, itemName => assignment
    /**
     * @var Rule[]
     */
    private $_rules = []; // ruleName => rule


    /**
     * Initializes the application component.
     * This method overrides parent implementation by loading the authorization data
     * from PHP script.
     */
    public function init()
    {
        parent::init();
        $this->authFile = Yii::getAlias($this->authFile);
        $this->load();
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
     * @inheritdoc
     */
    public function getAssignments($userId)
    {
        return isset($this->_assignments[$userId]) ? $this->_assignments[$userId] : [];
    }

    /**
     * Performs access check for the specified user.
     * This method is internally called by [[checkAccess()]].
     *
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
        if (!isset($this->_items[$itemName])) {
            return false;
        }

        /* @var $item Item */
        $item = $this->_items[$itemName];
        Yii::trace($item instanceof Role ? "Checking role: $itemName" : "Checking permission : $itemName", __METHOD__);

        if (!$this->executeRule($user, $item, $params)) {
            return false;
        }

        if (isset($assignments[$itemName]) || in_array($itemName, $this->defaultRoles)) {
            return true;
        }

        foreach ($this->_children as $parentName => $children) {
            if (isset($children[$itemName]) && $this->checkAccessRecursive($user, $parentName, $params, $assignments)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function addChild($parent, $child)
    {
        if (!isset($this->_items[$parent->name], $this->_items[$child->name])) {
            throw new InvalidParamException("Either '{$parent->name}' or '{$child->name}' does not exist.");
        }

        if ($parent->name == $child->name) {
            throw new InvalidParamException("Cannot add '{$parent->name} ' as a child of itself.");
        }
        if ($parent instanceof Permission && $child instanceof Role) {
            throw new InvalidParamException("Cannot add a role as a child of a permission.");
        }

        if ($this->detectLoop($parent, $child)) {
            throw new InvalidCallException("Cannot add '{$child->name}' as a child of '{$parent->name}'. A loop has been detected.");
        }
        if (isset($this->_children[$parent->name][$child->name])) {
            throw new InvalidCallException("The item '{$parent->name}' already has a child '{$child->name}'.");
        }
        $this->_children[$parent->name][$child->name] = $this->_items[$child->name];
        $this->save();

        return true;
    }

    /**
     * Checks whether there is a loop in the authorization item hierarchy.
     *
     * @param Item $parent parent item
     * @param Item $child the child item that is to be added to the hierarchy
     * @return boolean whether a loop exists
     */
    protected function detectLoop($parent, $child)
    {
        if ($child->name === $parent->name) {
            return true;
        }
        if (!isset($this->_children[$child->name], $this->_items[$parent->name])) {
            return false;
        }
        foreach ($this->_children[$child->name] as $grandchild) {
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
        if (isset($this->_children[$parent->name][$child->name])) {
            unset($this->_children[$parent->name][$child->name]);
            $this->save();
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
        return isset($this->_children[$parent->name][$child->name]);
    }

    /**
     * @inheritdoc
     */
    public function assign($role, $userId, $ruleName = null, $data = null)
    {
        if (!isset($this->_items[$role->name])) {
            throw new InvalidParamException("Unknown role '{$role->name}'.");
        } elseif (isset($this->_assignments[$userId][$role->name])) {
            throw new InvalidParamException("Authorization item '{$role->name}' has already been assigned to user '$userId'.");
        } else {
            $this->_assignments[$userId][$role->name] = new Assignment([
                'userId' => $userId,
                'roleName' => $role->name,
                'createdAt' => time(),
            ]);
            $this->save();
            return $this->_assignments[$userId][$role->name];
        }
    }

    /**
     * @inheritdoc
     */
    public function revoke($role, $userId)
    {
        if (isset($this->_assignments[$userId][$role->name])) {
            unset($this->_assignments[$userId][$role->name]);
            $this->save();
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
        if (isset($this->_assignments[$userId]) && is_array($this->_assignments[$userId])) {
            foreach ($this->_assignments[$userId] as $itemName => $value) {
                unset($this->_assignments[$userId][$itemName]);
            }
            $this->save();
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
        return isset($this->_assignments[$userId][$roleName]) ? $this->_assignments[$userId][$roleName] : null;
    }

    /**
     * @inheritdoc
     */
    public function getItems($type)
    {
        $items = [];

        foreach ($this->_items as $name => $item) {
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
        if (isset($this->_items[$item->name])) {
            foreach ($this->_children as &$children) {
                unset($children[$item->name]);
            }
            foreach ($this->_assignments as &$assignments) {
                unset($assignments[$item->name]);
            }
            unset($this->_items[$item->name]);
            $this->save();
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
        return isset($this->_items[$name]) ? $this->_items[$name] : null;
    }

    /**
     * @inheritdoc
     */
    public function updateRule($name, $rule)
    {
        if ($rule->name !== $name) {
            unset($this->_rules[$name]);
        }
        $this->_rules[$rule->name] = $rule;
        $this->save();
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getRule($name)
    {
        return isset($this->_rules[$name]) ? $this->_rules[$name] : null;
    }

    /**
     * @inheritdoc
     */
    public function getRules()
    {
        return $this->_rules;
    }

    /**
     * @inheritdoc
     */
    public function getRolesByUser($userId)
    {
        $roles = [];
        foreach ($this->getAssignments($userId) as $name => $assignment) {
            $roles[$name] = $this->_items[$assignment->roleName];
        }

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
            if (isset($this->_items[$itemName]) && $this->_items[$itemName] instanceof Permission) {
                $permissions[$itemName] = $this->_items[$itemName];
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
        if (isset($this->_children[$name])) {
            foreach ($this->_children[$name] as $child) {
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
            if (isset($this->_items[$itemName]) && $this->_items[$itemName] instanceof Permission) {
                $permissions[$itemName] = $this->_items[$itemName];
            }
        }
        return $permissions;
    }

    /**
     * @inheritdoc
     */
    public function getChildren($name)
    {
        return isset($this->_children[$name]) ? $this->_children[$name] : [];
    }

    /**
     * @inheritdoc
     */
    public function removeAll()
    {
        $this->_children = [];
        $this->_items = [];
        $this->_assignments = [];
        $this->_rules = [];
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
     * @param integer $type the auth item type (either Item::TYPE_PERMISSION or Item::TYPE_ROLE)
     */
    protected function removeAllItems($type)
    {
        $names = [];
        foreach ($this->_items as $name => $item) {
            if ($item->type == $type) {
                unset($this->_items[$name]);
                $names[$name] = true;
            }
        }
        if (empty($names)) {
            return;
        }

        foreach ($this->_assignments as $i => $assignment) {
            if (isset($names[$assignment->roleName])) {
                unset($this->_assignments[$i]);
            }
        }
        foreach ($this->_children as $name => $children) {
            if (isset($names[$name])) {
                unset($this->_children[$name]);
            } else {
                foreach ($children as $childName => $item) {
                    if (isset($names[$childName])) {
                        unset($children[$childName]);
                    }
                }
                $this->_children[$name] = $children;
            }
        }

        $this->save();
    }

    /**
     * @inheritdoc
     */
    public function removeAllRules()
    {
        foreach ($this->_items as $item) {
            $item->ruleName = null;
        }
        $this->_rules = [];
        $this->save();
    }

    /**
     * @inheritdoc
     */
    public function removeAllAssignments()
    {
        $this->_assignments = [];
        $this->save();
    }

    /**
     * @inheritdoc
     */
    protected function removeRule($rule)
    {
        if (isset($this->_rules[$rule->name])) {
            unset($this->_rules[$rule->name]);
            foreach ($this->_items as $item) {
                if ($item->ruleName === $rule->name) {
                    $item->ruleName = null;
                }
            }
            $this->save();
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
        $this->_rules[$rule->name] = $rule;
        $this->save();
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function updateItem($name, $item)
    {
        $this->_items[$item->name] = $item;
        if ($name !== $item->name) {
            if (isset($this->_items[$item->name])) {
                throw new InvalidParamException("Unable to change the item name. The name '{$item->name}' is already used by another item.");
            }
            if (isset($this->_items[$name])) {
                unset ($this->_items[$name]);

                if (isset($this->_children[$name])) {
                    $this->_children[$item->name] = $this->_children[$name];
                    unset ($this->_children[$name]);
                }
                foreach ($this->_children as &$children) {
                    if (isset($children[$name])) {
                        $children[$item->name] = $children[$name];
                        unset ($children[$name]);
                    }
                }
                foreach ($this->_assignments as &$assignments) {
                    if (isset($assignments[$name])) {
                        $assignments[$item->name] = $assignments[$name];
                        unset($assignments[$name]);
                    }
                }
            }
        }
        $this->save();
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

        $this->_items[$item->name] = $item;

        $this->save();

        return true;

    }

    /**
     * Loads authorization data from persistent storage.
     */
    public function load()
    {
        $this->_children = [];
        $this->_rules = [];
        $this->_assignments = [];
        $this->_items = [];

        $data = $this->loadFromFile($this->authFile);

        if (isset($data['items'])) {
            foreach ($data['items'] as $name => $item) {
                $class = $item['type'] == Item::TYPE_PERMISSION ? Permission::className() : Role::className();

                $this->_items[$name] = new $class([
                    'name' => $name,
                    'description' => isset($item['description']) ? $item['description'] : null,
                    'ruleName' => isset($item['ruleName']) ? $item['ruleName'] : null,
                    'data' => isset($item['data']) ? $item['data'] : null,
                    'createdAt' => isset($item['createdAt']) ? $item['createdAt'] : null,
                    'updatedAt' => isset($item['updatedAt']) ? $item['updatedAt'] : null,
                ]);
            }

            foreach ($data['items'] as $name => $item) {
                if (isset($item['children'])) {
                    foreach ($item['children'] as $childName) {
                        if (isset($this->_items[$childName])) {
                            $this->_children[$name][$childName] = $this->_items[$childName];
                        }
                    }
                }
                if (isset($item['assignments'])) {
                    foreach ($item['assignments'] as $userId => $assignment) {
                        $this->_assignments[$userId][$name] = new Assignment([
                            'userId' => $userId,
                            'roleName' => $assignment['roleName'],
                            'createdAt' => isset($assignment['createdAt']) ? $assignment['createdAt'] : null,
                        ]);
                    }
                }
            }
        }

        if (isset($data['rules'])) {
            foreach ($data['rules'] as $name => $ruleData) {
                $this->_rules[$name] = unserialize($ruleData);
            }
        }
    }

    /**
     * Saves authorization data into persistent storage.
     */
    public function save()
    {
        $items = [];
        foreach ($this->_items as $name => $item) {
            /* @var $item Item */
            $items[$name] = array_filter([
                'type' => $item->type,
                'description' => $item->description,
                'ruleName' => $item->ruleName,
                'data' => $item->data,
            ]);
            if (isset($this->_children[$name])) {
                foreach ($this->_children[$name] as $child) {
                    /* @var $child Item */
                    $items[$name]['children'][] = $child->name;
                }
            }
        }

        foreach ($this->_assignments as $userId => $assignments) {
            foreach ($assignments as $name => $assignment) {
                /* @var $assignment Assignment */
                if (isset($items[$name])) {
                    $items[$name]['assignments'][$userId] = [
                        'roleName' => $assignment->roleName,
                    ];
                }
            }
        }

        $rules = [];
        foreach ($this->_rules as $name => $rule) {
            $rules[$name] = serialize($rule);
        }

        $this->saveToFile(['items' => $items, 'rules' => $rules], $this->authFile);
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
    }
}
