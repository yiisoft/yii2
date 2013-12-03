<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use yii\base\Component;
use yii\base\Action;

/**
 * This class represents an access rule defined by the [[AccessControl]] action filter
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AccessRule extends Component
{
	/**
	 * @var boolean whether this is an 'allow' rule or 'deny' rule.
	 */
	public $allow;
	/**
	 * @var array list of action IDs that this rule applies to. The comparison is case-sensitive.
	 * If not set or empty, it means this rule applies to all actions.
	 */
	public $actions;
	/**
	 * @var array list of controller IDs that this rule applies to. The comparison is case-sensitive.
	 * If not set or empty, it means this rule applies to all controllers.
	 */
	public $controllers;
	/**
	 * @var array list of roles that this rule applies to. Two special roles are recognized, and
	 * they are checked via [[User::isGuest]]:
	 *
	 * - `?`: matches a guest user (not authenticated yet)
	 * - `@`: matches an authenticated user
	 *
	 * Using additional role names requires RBAC (Role-Based Access Control), and
	 * [[User::checkAccess()]] will be called.
	 *
	 * If this property is not set or empty, it means this rule applies to all roles.
	 */
	public $roles;
	/**
	 * @var array list of user IP addresses that this rule applies to. An IP address
	 * can contain the wildcard `*` at the end so that it matches IP addresses with the same prefix.
	 * For example, '192.168.*' matches all IP addresses in the segment '192.168.'.
	 * If not set or empty, it means this rule applies to all IP addresses.
	 * @see Request::userIP
	 */
	public $ips;
	/**
	 * @var array list of request methods (e.g. `GET`, `POST`) that this rule applies to.
	 * The request methods must be specified in uppercase.
	 * If not set or empty, it means this rule applies to all request methods.
	 * @see Request::requestMethod
	 */
	public $verbs;
	/**
	 * @var callback a callback that will be called to determine if the rule should be applied.
	 * The signature of the callback should be as follows:
	 *
	 * ~~~
	 * function ($rule, $action)
	 * ~~~
	 *
	 * where `$rule` is this rule, and `$action` is the current [[Action|action]] object.
	 * The callback should return a boolean value indicating whether this rule should be applied.
	 */
	public $matchCallback;
	/**
	 * @var callback a callback that will be called if this rule determines the access to
	 * the current action should be denied. If not set, the behavior will be determined by
	 * [[AccessControl]].
	 *
	 * The signature of the callback should be as follows:
	 *
	 * ~~~
	 * function ($rule, $action)
	 * ~~~
	 *
	 * where `$rule` is this rule, and `$action` is the current [[Action|action]] object.
	 */
	public $denyCallback;


	/**
	 * Checks whether the Web user is allowed to perform the specified action.
	 * @param Action $action the action to be performed
	 * @param User $user the user object
	 * @param Request $request
	 * @return boolean|null true if the user is allowed, false if the user is denied, null if the rule does not apply to the user
	 */
	public function allows($action, $user, $request)
	{
		if ($this->matchAction($action)
			&& $this->matchRole($user)
			&& $this->matchIP($request->getUserIP())
			&& $this->matchVerb($request->getMethod())
			&& $this->matchController($action->controller)
			&& $this->matchCustom($action)
		) {
			return $this->allow ? true : false;
		} else {
			return null;
		}
	}

	/**
	 * @param Action $action the action
	 * @return boolean whether the rule applies to the action
	 */
	protected function matchAction($action)
	{
		return empty($this->actions) || in_array($action->id, $this->actions, true);
	}

	/**
	 * @param Controller $controller the controller
	 * @return boolean whether the rule applies to the controller
	 */
	protected function matchController($controller)
	{
		return empty($this->controllers) || in_array($controller->uniqueId, $this->controllers, true);
	}

	/**
	 * @param User $user the user object
	 * @return boolean whether the rule applies to the role
	 */
	protected function matchRole($user)
	{
		if (empty($this->roles)) {
			return true;
		}
		foreach ($this->roles as $role) {
			if ($role === '?' && $user->getIsGuest()) {
				return true;
			} elseif ($role === '@' && !$user->getIsGuest()) {
				return true;
			} elseif ($user->checkAccess($role)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param string $ip the IP address
	 * @return boolean whether the rule applies to the IP address
	 */
	protected function matchIP($ip)
	{
		if (empty($this->ips)) {
			return true;
		}
		foreach ($this->ips as $rule) {
			if ($rule === '*' || $rule === $ip || (($pos = strpos($rule, '*')) !== false && !strncmp($ip, $rule, $pos))) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param string $verb the request method
	 * @return boolean whether the rule applies to the request
	 */
	protected function matchVerb($verb)
	{
		return empty($this->verbs) || in_array($verb, $this->verbs, true);
	}

	/**
	 * @param Action $action the action to be performed
	 * @return boolean whether the rule should be applied
	 */
	protected function matchCustom($action)
	{
		return empty($this->matchCallback) || call_user_func($this->matchCallback, $this, $action);
	}
}
