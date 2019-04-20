<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters;

use Closure;
use yii\base\Action;
use yii\base\Component;
use yii\base\Controller;
use yii\base\InvalidConfigException;
use yii\helpers\StringHelper;
use yii\web\Request;
use yii\web\User;

/**
 * 此类表示由 [[AccessControl]] 操作筛选器定义的访问规则。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AccessRule extends Component
{
    /**
     * @var bool 无论这是 'allow' 规则还是 'deny' 规则。
     */
    public $allow;
    /**
     * @var array 应用此规则的操作 IDs 的数组列表。比较区分大小写。
     * 如果未设置或为空，则表示此规则适用于所有操作。
     */
    public $actions;
    /**
     * @var array 应用此规则的控制器 IDs 的列表。
     *
     * 比较使用 [[\yii\base\Controller::uniqueId]]，因此每个控制器 ID 都带有前缀
     * 使用模块 ID (如果有的话)。对于应用程序中的 `product` 控制器，
     * 您可以指定此属性类似于 `['product']` 如果该控制器位于 `shop` 模块，
     * 则将是 `['shop/product']`。
     *
     * 比较区分大小写。
     *
     * 如果未设置或为空，则表示此规则适用于所有控制器。
     *
     * 自版本 2.0.12 起可以将控制器ID指定为通配符，例如 `module/*`。
     */
    public $controllers;
    /**
     * @var array 此规则适用的角色列表（需要正确配置的用户组件）。
     * 识别两个特殊角色，并通过 [[User::isGuest]] 检查它们：
     *
     * - `?`: 匹配来宾用户（尚未通过身份验证）
     * - `@`: 匹配经过身份验证的用户
     *
     * 如果使用 RBAC (Role-Based Access Control)，你还可以指定角色名称。
     * 在这种情况下，将调用 [[User::can()]] 检查访问权限。
     *
     * 请注意最好检查权限。
     *
     * 如果此属性未设置或为空，则表示无论角色如何，此规则都适用。
     * @see $permissions
     * @see $roleParams
     */
    public $roles;
    /** 
     * @var array 适用此规则的 RBAC （基于角色的访问控制）权限列表。
     * 将调用 [[User::can()]] 检查访问权限。
     * 
     * 如果此属性未设置或为空，则表示无论权限如何此规则都适用。
     * @since 2.0.12
     * @see $roles
     * @see $roleParams
     */
    public $permissions;
    /**
     * @var array|Closure 传递给 [[User::can()]] 函数的参数
     * 用于评估 [[$roles]] 中的用户权限。
     *
     * 如果这是一个数组，它将直接传递给 [[User::can()]]。例如
     * 从当前请求传递 ID，您可以使用以下内容:
     *
     * ```php
     * ['postId' => Yii::$app->request->get('id')]
     * ```
     *
     * 你还可以指定返回数组的闭包。只有在需要数组值的情况下，
     * 才能使用该值来计算数组值, 例如当需要加载模型时
     * 如下面的代码所示:
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
     * 对 [[AccessRule]] 实例的引用将作为第一个参数传递给闭包。
     *
     * @see $roles
     * @since 2.0.12
     */
    public $roleParams = [];
    /**
     * @var array 此规则应用于的用户 IP 地址列表。IP 地址
     * 的末尾可以包含通配符`*` ，以便与前缀相同的 IP 地址相匹配。
     * 例如，'192.168.*' 匹配网段 '192.168.' 中的所有 IP 地址。
     * 如果未设置或为空，则表示此规则适用于所有 IP 地址。
     * @see Request::userIP
     */
    public $ips;
    /**
     * @var array 此规则适用的请求方法（例如 `GET`, `POST`）的列表。
     * 如果未设置或为空，这意味着此规则适用于所有请求方法。
     * @see \yii\web\Request::method
     */
    public $verbs;
    /**
     * @var callable 将被调用以确定是否应用规则的回调。
     * 回调的签名应如下所示：
     *
     * ```php
     * function ($rule, $action)
     * ```
     *
     * 其中 `$rule` 是此规则，`$action` 是当前 [[Action|action]] 对象。
     * 回调应返回一个布尔值，指示是否应用此规则。
     */
    public $matchCallback;
    /**
     * @var callable 在此规则确定应拒绝对
     * 当前操作的访问时将调用的回调。当此规则匹配且
     * [[$allow]] 设置为 `false` 时就会出现这种情况。
     *
     * 如果未设置，则行为将由 [[AccessControl]],
     * 或者使用 [[AccessControl::denyAccess()]]
     * 或 [[AccessControl::$denyCallback]]，如果已设置。
     *
     * 回调的签名应如下所示：
     *
     * ```php
     * function ($rule, $action)
     * ```
     *
     * 其中 `$rule` 是此规则，`$action` 是当前 [[Action|action]] 对象。
     * @see AccessControl::$denyCallback
     */
    public $denyCallback;


    /**
     * 检查是否允许 Web 用户执行指定的操作。
     * @param Action $action 要执行的操作
     * @param User|false $user 在分离用户组件的情况下，用户对象或 `false`
     * @param Request $request
     * @return bool|null 如果允许用户，则为 `true` 如果用户被拒绝，则为 `false` 如果规则不适用于用户则为 `null`
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
     * @param Action $action 操作
     * @return bool 规则是否适用于操作
     */
    protected function matchAction($action)
    {
        return empty($this->actions) || in_array($action->id, $this->actions, true);
    }

    /**
     * @param Controller $controller 控制器
     * @return bool 规则是否适用于控制器
     */
    protected function matchController($controller)
    {
        if (empty($this->controllers)) {
            return true;
        }

        $id = $controller->getUniqueId();
        foreach ($this->controllers as $pattern) {
            if (StringHelper::matchWildcard($pattern, $id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param User $user 用户对象
     * @return bool 规则是否适用于角色
     * @throws InvalidConfigException 如果用户组件已分离
     */
    protected function matchRole($user)
    {
        $items = empty($this->roles) ? [] : $this->roles;

        if (!empty($this->permissions)) {
            $items = array_merge($items, $this->permissions);
        }

        if (empty($items)) {
            return true;
        }

        if ($user === false) {
            throw new InvalidConfigException('The user application component must be available to specify roles in AccessRule.');
        }

        foreach ($items as $item) {
            if ($item === '?') {
                if ($user->getIsGuest()) {
                    return true;
                }
            } elseif ($item === '@') {
                if (!$user->getIsGuest()) {
                    return true;
                }
            } else {
                if (!isset($roleParams)) {
                    $roleParams = $this->roleParams instanceof Closure ? call_user_func($this->roleParams, $this) : $this->roleParams;
                }
                if ($user->can($item, $roleParams)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param string|null $ip IP 地址
     * @return bool 规则是否适用于 IP 地址
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
     * @param string $verb 请求方法。
     * @return bool 规则是否适用于请求
     */
    protected function matchVerb($verb)
    {
        return empty($this->verbs) || in_array(strtoupper($verb), array_map('strtoupper', $this->verbs), true);
    }

    /**
     * @param Action $action 要执行的操作
     * @return bool 是否应该应用该规则
     */
    protected function matchCustom($action)
    {
        return empty($this->matchCallback) || call_user_func($this->matchCallback, $this, $action);
    }
}
