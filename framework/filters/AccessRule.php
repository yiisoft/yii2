<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters;

use Closure;
use yii\base\Component;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\web\User;
use yii\web\Request;
use yii\base\Controller;

/**
 * This class represents an access rule defined by the [[AccessControl]] action filter
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AccessRule extends Component
{
    /**
     * @var bool whether this is an 'allow' rule or 'deny' rule.
     */
    public $allow;
    /**
     * @var array list of action IDs that this rule applies to. The comparison is case-sensitive.
     * If not set or empty, it means this rule applies to all actions.
     */
    public $actions;
    /**
     * @var array list of the controller IDs that this rule applies to.
     *
     * The comparison uses [[\yii\base\Controller::uniqueId]], so each controller ID is prefixed
     * with the module ID (if any). For a `product` controller in the application, you would specify
     * this property like `['product']` and if that controller is located in a `shop` module, this
     * would be `['shop/product']`.
     *
     * The comparison is case-sensitive.
     *
     * If not set or empty, it means this rule applies to all controllers.
     *
     * Since version 2.0.12 controller IDs can be specified as wildcards, e.g. `module/*`.
     */
    public $controllers;
    /**
     * @var array list of roles that this rule applies to (requires properly configured User component).
     * Two special roles are recognized, and they are checked via [[User::isGuest]]:
     *
     * - `?`: matches a guest user (not authenticated yet)
     * - `@`: matches an authenticated user
     *
     * If you are using RBAC (Role-Based Access Control), you may also specify role or permission names.
     * In this case, [[User::can()]] will be called to check access.
     *
     * If this property is not set or empty, it means this rule applies to all roles.
     * @see $roleParams
     */
    public $roles;
    /**
     * @var array|Closure parameters to pass to the [[User::can()]] function for evaluating
     * user permissions in [[$roles]].
     *
     * If this is an array, it will be passed directly to [[User::can()]]. For example for passing an
     * ID from the current request, you may use the following:
     *
     * ```php
     * ['postId' => Yii::$app->request->get('id')]
     * ```
     *
     * You may also specify a closure that returns an array. This can be used to
     * evaluate the array values only if they are needed, for example when a model needs to be
     * loaded like in the following code:
     *
     * ```php
     * 'rules' => [
     *     [
     *         'allow' => true,
     *         'actions' => ['update'],
     *         'roles' => ['updatePost'],
     *         'roleParams' => function($rule) {
     *             return ['post' => Post::findOne(Yii::$app->request->get('id'))];
     *         },
     *     ],
     * ],
     * ```
     *
     * A reference to the [[AccessRule]] instance will be passed to the closure as the first parameter.
     *
     * @see $roles
     * @since 2.0.12
     */
    public $roleParams = [];
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
     * If not set or empty, it means this rule applies to all request methods.
     * @see \yii\web\Request::method
     */
    public $verbs;
    /**
     * @var callable a callback that will be called to determine if the rule should be applied.
     * The signature of the callback should be as follows:
     *
     * ```php
     * function ($rule, $action)
     * ```
     *
     * where `$rule` is this rule, and `$action` is the current [[Action|action]] object.
     * The callback should return a boolean value indicating whether this rule should be applied.
     */
    public $matchCallback;
    /**
     * @var callable a callback that will be called if this rule determines the access to
     * the current action should be denied. If not set, the behavior will be determined by
     * [[AccessControl]].
     *
     * The signature of the callback should be as follows:
     *
     * ```php
     * function ($rule, $action)
     * ```
     *
     * where `$rule` is this rule, and `$action` is the current [[Action|action]] object.
     */
    public $denyCallback;


    /**
     * Checks whether the Web user is allowed to perform the specified action.
     * @param Action $action the action to be performed
     * @param User|false $user the user object or `false` in case of detached User component
     * @param Request $request
     * @return bool|null `true` if the user is allowed, `false` if the user is denied, `null` if the rule does not apply to the user
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
        }

        return null;
    }

    /**
     * @param Action $action the action
     * @return bool whether the rule applies to the action
     */
    protected function matchAction($action)
    {
        return empty($this->actions) || in_array($action->id, $this->actions, true);
    }

    /**
     * @param Controller $controller the controller
     * @return bool whether the rule applies to the controller
     */
    protected function matchController($controller)
    {
        if (empty($this->controllers)) {
            return true;
        }

        $id = $controller->getUniqueId();
        foreach ($this->controllers as $pattern) {
            if (fnmatch($pattern, $id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param User $user the user object
     * @return bool whether the rule applies to the role
     * @throws InvalidConfigException if User component is detached
     */
    protected function matchRole($user)
    {
        if (empty($this->roles)) {
            return true;
        }
        if ($user === false) {
            throw new InvalidConfigException('The user application component must be available to specify roles in AccessRule.');
        }
        foreach ($this->roles as $role) {
            if ($role === '?') {
                if ($user->getIsGuest()) {
                    return true;
                }
            } elseif ($role === '@') {
                if (!$user->getIsGuest()) {
                    return true;
                }
            } else {
                if (!isset($roleParams)) {
                    $roleParams = $this->roleParams instanceof Closure ? call_user_func($this->roleParams, $this) : $this->roleParams;
                }
                if ($user->can($role, $roleParams)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param string|null $ip the IP address
     * @return bool whether the rule applies to the IP address
     */
    protected function matchIP($ip)
    {
        if (empty($this->ips)) {
            return true;
        }
        foreach ($this->ips as $rule) {
            if ($rule === '*' ||
                $rule === $ip ||
                (
                    $ip !== null &&
                    ($pos = strpos($rule, '*')) !== false &&
                    strncmp($ip, $rule, $pos) === 0
                )
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $verb the request method.
     * @return bool whether the rule applies to the request
     */
    protected function matchVerb($verb)
    {
        return empty($this->verbs) || in_array(strtoupper($verb), array_map('strtoupper', $this->verbs), true);
    }

    /**
     * @param Action $action the action to be performed
     * @return bool whether the rule should be applied
     */
    protected function matchCustom($action)
    {
        return empty($this->matchCallback) || call_user_func($this->matchCallback, $this, $action);
    }
}
