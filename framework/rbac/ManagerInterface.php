<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rbac;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface ManagerInterface
{
    /**
     * Checks if the user has the specified permission.
     * @param string|integer $userId the user ID. This should be either an integer or a string representing
     * the unique identifier of a user. See [[\yii\web\User::id]].
     * @param string $permissionName the name of the permission to be checked against
     * @param array $params name-value pairs that will be passed to the rules associated
     * with the roles and permissions assigned to the user.
     * @return boolean whether the user has the specified permission.
     * @throws \yii\base\InvalidParamException if $permissionName does not refer to an existing permission
     */
    public function checkAccess($userId, $permissionName, $params = []);

    /**
     * Creates a new Role object.
     * Note that the newly created role is not added to the RBAC system yet.
     * You must fill in the needed data and call [[add()]] to add it to the system.
     * @param string $name the role name
     * @return Role the new Role object
     */
    public function createRole($name);

    /**
     * Creates a new Permission object.
     * Note that the newly created permission is not added to the RBAC system yet.
     * You must fill in the needed data and call [[add()]] to add it to the system.
     * @param string $name the permission name
     * @return Permission the new Permission object
     */
    public function createPermission($name);

    /**
     * Adds a role, permission or rule to the RBAC system.
     * @param Role|Permission|Rule $object
     * @return boolean whether the role, permission or rule is successfully added to the system
     * @throws \Exception if data validation or saving fails (such as the name of the role or permission is not unique)
     */
    public function add($object);

    /**
     * Removes a role, permission or rule from the RBAC system.
     * @param Role|Permission|Rule $object
     * @return boolean whether the role, permission or rule is successfully removed
     */
    public function remove($object);

    /**
     * Updates the specified role, permission or rule in the system.
     * @param string $name the old name of the role, permission or rule
     * @param Role|Permission|Rule $object
     * @return boolean whether the update is successful
     * @throws \Exception if data validation or saving fails (such as the name of the role or permission is not unique)
     */
    public function update($name, $object);

    /**
     * Returns the named role.
     * @param string $name the role name.
     * @return Role the role corresponding to the specified name. Null is returned if no such role.
     */
    public function getRole($name);

    /**
     * Returns all roles in the system.
     * @return Role[] all roles in the system. The array is indexed by the role names.
     */
    public function getRoles();

    /**
     * Returns the roles that are assigned to the user via [[assign()]].
     * Note that child roles that are not assigned directly to the user will not be returned.
     * @param string|integer $userId the user ID (see [[\yii\web\User::id]])
     * @return Role[] all roles directly or indirectly assigned to the user. The array is indexed by the role names.
     */
    public function getRolesByUser($userId);

    /**
     * Returns the named permission.
     * @param string $name the permission name.
     * @return Permission the permission corresponding to the specified name. Null is returned if no such permission.
     */
    public function getPermission($name);

    /**
     * Returns all permissions in the system.
     * @return Permission[] all permissions in the system. The array is indexed by the permission names.
     */
    public function getPermissions();

    /**
     * Returns all permissions that the specified role represents.
     * @param string $roleName the role name
     * @return Permission[] all permissions that the role represents. The array is indexed by the permission names.
     */
    public function getPermissionsByRole($roleName);

    /**
     * Returns all permissions that the user has.
     * @param string|integer $userId the user ID (see [[\yii\web\User::id]])
     * @return Permission[] all permissions that the user has. The array is indexed by the permission names.
     */
    public function getPermissionsByUser($userId);

    /**
     * Returns the rule of the specified name.
     * @param string $name the rule name
     * @return Rule the rule object, or null if the specified name does not correspond to a rule.
     */
    public function getRule($name);

    /**
     * Returns all rules available in the system.
     * @return Rule[] the rules indexed by the rule names
     */
    public function getRules();

    /**
     * Adds an item as a child of another item.
     * @param Item $parent
     * @param Item $child
     * @throws \yii\base\Exception if the parent-child relationship already exists or if a loop has been detected.
     */
    public function addChild($parent, $child);

    /**
     * Removes a child from its parent.
     * Note, the child item is not deleted. Only the parent-child relationship is removed.
     * @param Item $parent
     * @param Item $child
     * @return boolean whether the removal is successful
     */
    public function removeChild($parent, $child);

    /**
     * Returns the child permissions and/or roles.
     * @param string $name the parent name
     * @return Item[] the child permissions and/or roles
     */
    public function getChildren($name);

    /**
     * Assigns a role to a user.
     *
     * @param Role $role
     * @param string|integer $userId the user ID (see [[\yii\web\User::id]])
     * @param Rule $rule the rule to be associated with this assignment. If not null, the rule
     * will be executed when [[allow()]] is called to check the user permission.
     * @param mixed $data additional data associated with this assignment.
     * @return Assignment the role assignment information.
     * @throws \Exception if the role has already been assigned to the user
     */
    public function assign($role, $userId, $rule = null, $data = null);

    /**
     * Revokes a role from a user.
     * @param Role $role
     * @param string|integer $userId the user ID (see [[\yii\web\User::id]])
     * @return boolean whether the revoking is successful
     */
    public function revoke($role, $userId);

    /**
     * Revokes all roles from a user.
     * @param mixed $userId the user ID (see [[\yii\web\User::id]])
     * @return boolean whether the revoking is successful
     */
    public function revokeAll($userId);

    /**
     * Returns the assignment information regarding a role and a user.
     * @param string|integer $userId the user ID (see [[\yii\web\User::id]])
     * @param string $roleName the role name
     * @return Assignment the assignment information. Null is returned if
     * the role is not assigned to the user.
     */
    public function getAssignment($roleName, $userId);

    /**
     * Returns all role assignment information for the specified user.
     * @param string|integer $userId the user ID (see [[\yii\web\User::id]])
     * @return Assignment[] the assignments indexed by role names. An empty array will be
     * returned if there is no role assigned to the user.
     */
    public function getAssignments($userId);

    /**
     * Removes all authorization data.
     */
    public function clearAll();

    /**
     * Removes all authorization assignments.
     */
    public function clearAssignments();
}
