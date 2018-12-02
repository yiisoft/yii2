<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rbac;

use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\base\InvalidValueException;

/**
 * BaseManager 是实现 RBAC 管理 [[ManagerInterface]] 的基类。
 *
 * 有关 DbManager 的更多详细信息和用法信息，请参阅 [授权指南](guide:security-authorization)。
 *
 * @property Role[] $defaultRoleInstances 默认的角色。该数组由角色名称索引。
 * 此属性是只读的。
 * @property string[] $defaultRoles 默认的角色。请注意，此属性的类型在 getter 和 setter 中有所不同。
 * 参阅 [[getDefaultRoles()]] 和 [[setDefaultRoles()]] 获取详细信息。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class BaseManager extends Component implements ManagerInterface
{
    /**
     * @var array 在不调用 [[assign()]] 的情况下自动分配给每个用户的角色名称列表。
     * 请注意，无论身份验证的状态如何，这些角色都将应用于用户。
     */
    protected $defaultRoles = [];


    /**
     * 返回指定的 auth 项。
     * @param string $name auth 项的名称。
     * @return Item 与指定名称对应的 auth 项。如果没有这样的项目，则返回 Null。
     */
    abstract protected function getItem($name);

    /**
     * 返回指定类型的项。
     * @param int $type auth 项类型（[[Item::TYPE_ROLE]] 或 [[Item::TYPE_PERMISSION]]。
     * @return Item[] 指定类型的 auth 项。
     */
    abstract protected function getItems($type);

    /**
     * 将一个 auth 项添加到 RBAC 系统。
     * @param Item $item 要添加的项目
     * @return bool 是否已成功将 auth 项添加到系统中
     * @throws \Exception 如果数据验证或保存失败（例如角色名称或权限不唯一）
     */
    abstract protected function addItem($item);

    /**
     * 向 RBAC 系统添加规则。
     * @param Rule $rule 要添加的规则
     * @return bool 规则是否已成功添加到系统中
     * @throws \Exception 如果数据验证或保存失败（例如规则名称不唯一）
     */
    abstract protected function addRule($rule);

    /**
     * 从 RBAC 系统中删除 auth 项。
     * @param Item $item 要删除的项目
     * @return bool 是否成功删除了角色或权限
     * @throws \Exception 如果数据验证或保存失败（例如角色名称或权限不唯一）
     */
    abstract protected function removeItem($item);

    /**
     * 从 RBAC 系统中删除规则。
     * @param Rule $rule 要删除的规则
     * @return bool 是否成功删除了规则
     * @throws \Exception 如果数据验证或保存失败（例如规则名称不唯一）
     */
    abstract protected function removeRule($rule);

    /**
     * 更新 RBAC 系统中的 auth 项。
     * @param string $name 要更新的项目名称
     * @param Item $item 更新的项目
     * @return bool 是否已成功更新 auth 项
     * @throws \Exception 如果数据验证或保存失败（例如角色名称或权限不唯一）
     */
    abstract protected function updateItem($name, $item);

    /**
     * 更新 RBAC 系统中的规则。
     * @param string $name 要更新的规则的名称
     * @param Rule $rule 更新的规则
     * @return bool 规则是否已成功更新
     * @throws \Exception 如果数据验证或保存失败（例如规则名称不唯一）
     */
    abstract protected function updateRule($name, $rule);

    /**
     * {@inheritdoc}
     */
    public function createRole($name)
    {
        $role = new Role();
        $role->name = $name;
        return $role;
    }

    /**
     * {@inheritdoc}
     */
    public function createPermission($name)
    {
        $permission = new Permission();
        $permission->name = $name;
        return $permission;
    }

    /**
     * {@inheritdoc}
     */
    public function add($object)
    {
        if ($object instanceof Item) {
            if ($object->ruleName && $this->getRule($object->ruleName) === null) {
                $rule = \Yii::createObject($object->ruleName);
                $rule->name = $object->ruleName;
                $this->addRule($rule);
            }

            return $this->addItem($object);
        } elseif ($object instanceof Rule) {
            return $this->addRule($object);
        }

        throw new InvalidArgumentException('Adding unsupported object type.');
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object)
    {
        if ($object instanceof Item) {
            return $this->removeItem($object);
        } elseif ($object instanceof Rule) {
            return $this->removeRule($object);
        }

        throw new InvalidArgumentException('Removing unsupported object type.');
    }

    /**
     * {@inheritdoc}
     */
    public function update($name, $object)
    {
        if ($object instanceof Item) {
            if ($object->ruleName && $this->getRule($object->ruleName) === null) {
                $rule = \Yii::createObject($object->ruleName);
                $rule->name = $object->ruleName;
                $this->addRule($rule);
            }

            return $this->updateItem($name, $object);
        } elseif ($object instanceof Rule) {
            return $this->updateRule($name, $object);
        }

        throw new InvalidArgumentException('Updating unsupported object type.');
    }

    /**
     * {@inheritdoc}
     */
    public function getRole($name)
    {
        $item = $this->getItem($name);
        return $item instanceof Item && $item->type == Item::TYPE_ROLE ? $item : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getPermission($name)
    {
        $item = $this->getItem($name);
        return $item instanceof Item && $item->type == Item::TYPE_PERMISSION ? $item : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->getItems(Item::TYPE_ROLE);
    }

    /**
     * 设置默认角色
     * @param string[]|\Closure $roles 角色的数组，或者返回角色数组的回调函数
     * @throws InvalidArgumentException 当 $roles 既不是数组也不是回调函数
     * @throws InvalidValueException 当回调函数返回不是数组时
     * @since 2.0.14
     */
    public function setDefaultRoles($roles)
    {
        if (is_array($roles)) {
            $this->defaultRoles = $roles;
        } elseif ($roles instanceof \Closure) {
            $roles = call_user_func($roles);
            if (!is_array($roles)) {
                throw new InvalidValueException('Default roles closure must return an array');
            }
            $this->defaultRoles = $roles;
        } else {
            throw new InvalidArgumentException('Default roles must be either an array or a callable');
        }
    }

    /**
     * 获取默认角色
     * @return string[] 默认角色
     * @since 2.0.14
     */
    public function getDefaultRoles()
    {
        return $this->defaultRoles;
    }

    /**
     * 将 defaultRoles 作为 Role 对象的数组返回。
     * @since 2.0.12
     * @return Role[] 默认角色。该数组由角色名称索引
     */
    public function getDefaultRoleInstances()
    {
        $result = [];
        foreach ($this->defaultRoles as $roleName) {
            $result[$roleName] = $this->createRole($roleName);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissions()
    {
        return $this->getItems(Item::TYPE_PERMISSION);
    }

    /**
     * 执行与指定 auth 项关联的规则。
     *
     * 如果项目未指定规则，则此方法将返回 true。
     * 否则返回 [[Rule::execute()]] 的值。
     *
     * @param string|int $user 用户 ID。这应该是整数或字符串，
     * 表示用户的唯一标识符。参阅 [[\yii\web\User::id]]。
     * @param Item $item 需要执行其规则的 auth 项
     * @param array $params 传递给 [[CheckAccessInterface::checkAccess()]] 的参数，并且也将传递给规则
     * @return bool [[Rule::execute()]] 的返回值。如果 auth 项目未指定规则，则返回 true。
     * @throws InvalidConfigException 如果 auth 项目有无效规则。
     */
    protected function executeRule($user, $item, $params)
    {
        if ($item->ruleName === null) {
            return true;
        }
        $rule = $this->getRule($item->ruleName);
        if ($rule instanceof Rule) {
            return $rule->execute($user, $item, $params);
        }

        throw new InvalidConfigException("Rule not found: {$item->ruleName}");
    }

    /**
     * 检查 $assignment 和 [[defaultRoles]] 数组是否都为空。
     *
     * @param Assignment[] 用户分配的 $assignments 数组
     * @return bool $assignment 和 [[defaultRoles]] 数组是否都为空
     * @since 2.0.11
     */
    protected function hasNoAssignments(array $assignments)
    {
        return empty($assignments) && empty($this->defaultRoles);
    }
}
