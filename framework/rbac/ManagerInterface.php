<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rbac;

/**
 * 有关 ManagerInterface 的更多详细信息和用法信息，请参阅 [授权指南](guide:security-authorization)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface ManagerInterface extends CheckAccessInterface
{
    /**
     * 创建一个新的 Role 对象。
     * 请注意，新创建的角色尚未添加到 RBAC 系统。
     * 你必须填写所需数据并调用 [[add()]] 将其添加到系统中。
     * @param string $name 角色名称
     * @return Role 新的 Role 对象
     */
    public function createRole($name);

    /**
     * 创建一个新的 Permission 对象。
     * 请注意，新创建的权限尚未添加到 RBAC 系统。
     * 你必须填写所需数据并调用 [[add()]] 将其添加到系统中。
     * @param string $name 权限名称
     * @return Permission 新的 Permission 对象
     */
    public function createPermission($name);

    /**
     * 向 RBAC 系统添加角色，权限或规则。
     * @param Role|Permission|Rule $object
     * @return bool 角色，权限或规则是否已成功添加到系统中
     * @throws \Exception 如果数据验证或保存失败（例如角色名称或权限不唯一）
     */
    public function add($object);

    /**
     * 从 RBAC 系统中删除角色，权限或规则。
     * @param Role|Permission|Rule $object
     * @return bool 是否成功删除了角色，权限或规则
     */
    public function remove($object);

    /**
     * 更新系统中指定的角色，权限或规则。
     * @param string $name 角色，权限或规则的旧名称
     * @param Role|Permission|Rule $object
     * @return bool 更新是否成功
     * @throws \Exception 如果数据验证或保存失败（例如角色名称或权限不唯一）
     */
    public function update($name, $object);

    /**
     * 返回指定的角色。
     * @param string $name 角色名称。
     * @return null|Role 角色与指定名称对应的角色。如果没有这样的角色，则返回 Null。
     */
    public function getRole($name);

    /**
     * 返回系统中的所有角色。
     * @return Role[] 系统中的所有角色。该数组由角色名称索引。
     */
    public function getRoles();

    /**
     * 返回通过 [[assign()]] 分配给用户的角色。
     * 请注意，不会返回未直接分配给用户的子角色。
     * @param string|int $userId 用户 ID（详见 [[\yii\web\User::id]]）
     * @return Role[] 直接分配给用户的所有角色。该数组由角色名称索引。
     */
    public function getRolesByUser($userId);

    /**
     * 返回指定角色的子角色。深度不受限制。
     * @param string $roleName 要为其提供子角色的角色的名称
     * @return Role[] 子角色。该数组由角色名称索引。
     * 第一个元素是父角色本身的一个实例。
     * @throws \yii\base\InvalidParamException 如果找不到通过 $roleName 获取的 Role
     * @since 2.0.10
     */
    public function getChildRoles($roleName);

    /**
     * 返回指定的权限。
     * @param string $name 权限名称。
     * @return null|Permission 权限对应于指定名称的权限。如果没有此类权限，则返回 Null。
     */
    public function getPermission($name);

    /**
     * 返回系统中的所有权限。
     * @return Permission[] 系统中的所有权限。该数组由权限名称索引。
     */
    public function getPermissions();

    /**
     * 返回指定角色所代表的所有权限。
     * @param string $roleName 角色名称
     * @return Permission[] 角色所代表的所有权限。该数组由权限名称索引。
     */
    public function getPermissionsByRole($roleName);

    /**
     * 返回用户拥有的所有权限。
     * @param string|int $userId 用户 ID（详见 [[\yii\web\User::id]]）
     * @return Permission[] 用户拥有的所有权限。该数组由权限名称索引。
     */
    public function getPermissionsByUser($userId);

    /**
     * 返回指定名称的规则。
     * @param string $name 规则名称
     * @return null|Rule 规则对象，如果指定的名称与规则不对应，则为 null。
     */
    public function getRule($name);

    /**
     * 返回系统中可用的所有规则。
     * @return Rule[] 由规则名称索引的规则
     */
    public function getRules();

    /**
     * 检查将孩子加入父项的可能性。
     * @param Item $parent 父项
     * @param Item $child 要添加到层次结构中的子项
     * @return bool 是否可以添加
     *
     * @since 2.0.8
     */
    public function canAddChild($parent, $child);

    /**
     * 将项目添加为另一项目的子项。
     * @param Item $parent
     * @param Item $child
     * @return bool 是否成功添加为子项
     * @throws \yii\base\Exception 如果父子关系已经存在或者检测到循环。
     */
    public function addChild($parent, $child);

    /**
     * 从父项中移除一个子项。
     * 注意，子项目不会被删除。仅删除父子关系。
     * @param Item $parent
     * @param Item $child
     * @return bool 是否删除成功
     */
    public function removeChild($parent, $child);

    /**
     * 从父项那里删除所有子项。
     * 注意，子项目不会被删除。仅删除父子关系。
     * @param Item $parent
     * @return bool 是否删除成功
     */
    public function removeChildren($parent);

    /**
     * 返回一个值，该值指示父项的子项是否已存在。
     * @param Item $parent
     * @param Item $child
     * @return bool 是否 `$child` 已经是 `$parent` 的孩子了
     */
    public function hasChild($parent, $child);

    /**
     * 返回子项权限和角色。
     * @param string $name 父项名
     * @return Item[] 子项权限和角色
     */
    public function getChildren($name);

    /**
     * 为用户分配角色。
     *
     * @param Role|Permission $role
     * @param string|int $userId 用户 ID（见 [[\yii\web\User::id]]）
     * @return Assignment 角色分配信息。
     * @throws \Exception 如果该角色已分配给用户
     */
    public function assign($role, $userId);

    /**
     * 撤消用户的角色。
     * @param Role|Permission $role
     * @param string|int $userId 用户 ID（详见 [[\yii\web\User::id]]）
     * @return bool 是否撤销成功
     */
    public function revoke($role, $userId);

    /**
     * 撤消用户的所有角色。
     * @param string|int $userId 用户 ID（详见 [[\yii\web\User::id]]）
     * @return bool是否撤销成功
     */
    public function revokeAll($userId);

    /**
     * 返回有关角色和用户的分配信息。
     * @param string $roleName 角色名称
     * @param string|int $userId 用户 ID（详见 [[\yii\web\User::id]]）
     * @return null|Assignment 分配信息。如果该角色没有分配给该用户，
     * 则返回 Null。
     */
    public function getAssignment($roleName, $userId);

    /**
     * 返回指定用户的所有角色分配信息。
     * @param string|int $userId 用户 ID（详见 [[\yii\web\User::id]]）
     * @return Assignment[] 由角色名称索引的分配。如果该用户没有分配角色，
     * 则返回空数组。
     */
    public function getAssignments($userId);

    /**
     * 返回分配给指定角色的所有用户 ID。
     * @param string $roleName
     * @return array 用户 ID 字符串数组
     * @since 2.0.7
     */
    public function getUserIdsByRole($roleName);

    /**
     * 删除所有授权数据，包括角色，权限，规则和分配。
     */
    public function removeAll();

    /**
     * 删除所有权限。
     * 所有父子关系将相应调整。
     */
    public function removeAllPermissions();

    /**
     * 删除所有角色。
     * 所有父子关系将相应调整。
     */
    public function removeAllRoles();

    /**
     * 删除所有规则。
     * 所有具有规则的角色和权限都将相应调整。
     */
    public function removeAllRules();

    /**
     * 删除所有角色分配。
     */
    public function removeAllAssignments();
}
