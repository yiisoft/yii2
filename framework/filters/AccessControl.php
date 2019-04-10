<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters;

use Yii;
use yii\base\Action;
use yii\base\ActionFilter;
use yii\di\Instance;
use yii\web\ForbiddenHttpException;
use yii\web\User;

/**
 * AccessControl 基于一组规则提供简单的访问控制。
 *
 * AccessControl 是一个操作筛选器。它将检查其 [[rules]] 查找
 * 匹配当前上下文变量（例如用户IIp 地址、用户角色）的第一条规则。
 * 匹配规则将指定是允许还是拒绝访问所请求的控制器
 * 操作。如果没有匹配的规则，访问将被拒绝。
 *
 * 要使用 AccessControl，请在控制器类的 `behaviors()` 方法中声明它。
 * 例如，以下声明将允许经过身份验证的用户访问 "create"
 * 和 "update" 操作并拒绝所有其他用户访问这两个操作。
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'access' => [
 *             'class' => \yii\filters\AccessControl::className(),
 *             'only' => ['create', 'update'],
 *             'rules' => [
 *                 // deny all POST requests
 *                 [
 *                     'allow' => false,
 *                     'verbs' => ['POST']
 *                 ],
 *                 // allow authenticated users
 *                 [
 *                     'allow' => true,
 *                     'roles' => ['@'],
 *                 ],
 *                 // everything else is denied
 *             ],
 *         ],
 *     ];
 * }
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AccessControl extends ActionFilter
{
    /**
     * @var User|array|string|false 表示用户应用程序组件的身份验证状态或ID的用户对象。
     * 从版本 2.0.2 开始，也可以是用于创建对象的配置数组。
     * 从版本 2.0.12 开始，你可以将其设置为 “false”，以显式地为筛选器关闭此组件支持。
     */
    public $user = 'user';
    /**
     * @var callable 在拒绝当前用户访问时将调用的回调。
     * 如果没有匹配的规则或者是符合以下条件的规则，则会出现这种情况
     * [[AccessRule::$allow|$allow]] 设置成 `false` 匹配。
     * 如果未设置，[[denyAccess()]] 将会被调用。
     *
     * 回调的签名应如下所示:
     *
     * ```php
     * function ($rule, $action)
     * ```
     *
     * 其中 `$rule` 是拒绝用户的规则，`$action` 是当前 [[Action|action]] 对象。
     * 如果访问被拒绝则 `$rule` 可以为 `null` 因为所有规则都不匹配。
     */
    public $denyCallback;
    /**
     * @var array 访问规则的默认配置。单个规则配置
     * 在配置规则的相同属性时 via [[rules]] 将优先。
     */
    public $ruleConfig = ['class' => 'yii\filters\AccessRule'];
    /**
     * @var array 访问规则对象的列表或用于创建规则对象的配置数组。
     * 如果规则是通过配置数组指定的，它将首先与 [[ruleConfig]] 合并
     * 在用于创建规则对象之前。
     * @see 规则配置
     */
    public $rules = [];


    /**
     * 通过从配置实例化规则对象来初始化 [[rules]] 数组。
     */
    public function init()
    {
        parent::init();
        if ($this->user !== false) {
            $this->user = Instance::ensure($this->user, User::className());
        }
        foreach ($this->rules as $i => $rule) {
            if (is_array($rule)) {
                $this->rules[$i] = Yii::createObject(array_merge($this->ruleConfig, $rule));
            }
        }
    }

    /**
     * 此方法是在执行操作之前（在所有可能的筛选器之后。）调用
     * 您可以重写此方法以便为操作做最后一刻的准备。
     * @param Action $action 要执行的操作。
     * @return bool 是否应继续执行该操作。
     */
    public function beforeAction($action)
    {
        $user = $this->user;
        $request = Yii::$app->getRequest();
        /* @var $rule AccessRule */
        foreach ($this->rules as $rule) {
            if ($allow = $rule->allows($action, $user, $request)) {
                return true;
            } elseif ($allow === false) {
                if (isset($rule->denyCallback)) {
                    call_user_func($rule->denyCallback, $rule, $action);
                } elseif ($this->denyCallback !== null) {
                    call_user_func($this->denyCallback, $rule, $action);
                } else {
                    $this->denyAccess($user);
                }

                return false;
            }
        }
        if ($this->denyCallback !== null) {
            call_user_func($this->denyCallback, null, $action);
        } else {
            $this->denyAccess($user);
        }

        return false;
    }

    /**
     * 拒绝用户访问。
     * 如果用户是访客默认实现会将用户重定向到登录页面；
     * 如果用户已登录，则将引发 403 HTTP 异常。
     * @param User|false $user 在分离用户组件的情况下当前用户或布尔值 `false`
     * @throws ForbiddenHttpException 如果用户已经登录或者已分离用户组件。
     */
    protected function denyAccess($user)
    {
        if ($user !== false && $user->getIsGuest()) {
            $user->loginRequired();
        } else {
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }
}
