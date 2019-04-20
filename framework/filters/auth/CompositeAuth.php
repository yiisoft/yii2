<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters\auth;

use Yii;
use yii\base\InvalidConfigException;

/**
 * CompositeAuth 是一个同时支持多种身份验证方法的操作筛选器。
 *
 * CompositeAuth 包含的身份验证方法是通过 [[authMethods]] 配置的，
 * 这是受支持的身份验证类配置的列表。
 *
 * 下面的示例演示如何支持三种身份验证方法:
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'compositeAuth' => [
 *             'class' => \yii\filters\auth\CompositeAuth::className(),
 *             'authMethods' => [
 *                 \yii\filters\auth\HttpBasicAuth::className(),
 *                 \yii\filters\auth\QueryParamAuth::className(),
 *             ],
 *         ],
 *     ];
 * }
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CompositeAuth extends AuthMethod
{
    /**
     * @var array 支持的身份验证方法。此属性应采用受支持
     * 的身份验证方法列表，每个都由身份验证类或配置表示。
     *
     * 如果此属性为空，则不会执行任何身份验证。
     *
     * 注意，auth 方法类必须实现 [[\yii\filters\auth\AuthInterface]] 接口。
     */
    public $authMethods = [];


    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        return empty($this->authMethods) ? true : parent::beforeAction($action);
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate($user, $request, $response)
    {
        foreach ($this->authMethods as $i => $auth) {
            if (!$auth instanceof AuthInterface) {
                $this->authMethods[$i] = $auth = Yii::createObject($auth);
                if (!$auth instanceof AuthInterface) {
                    throw new InvalidConfigException(get_class($auth) . ' must implement yii\filters\auth\AuthInterface');
                }
            }

            $identity = $auth->authenticate($user, $request, $response);
            if ($identity !== null) {
                return $identity;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function challenge($response)
    {
        foreach ($this->authMethods as $method) {
            /* @var $method AuthInterface */
            $method->challenge($response);
        }
    }
}
