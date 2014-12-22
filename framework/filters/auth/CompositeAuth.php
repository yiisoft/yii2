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
 * CompositeAuth is an action filter that supports multiple authentication methods at the same time.
 *
 * The authentication methods contained by CompositeAuth are configured via [[authMethods]],
 * which is a list of supported authentication class configurations.
 *
 * The following example shows how to support three authentication methods:
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
     * @var array the supported authentication methods. This property should take a list of supported
     * authentication methods, each represented by an authentication class or configuration.
     *
     * If this property is empty, no authentication will be performed.
     *
     * Note that an auth method class must implement the [[\yii\filters\auth\AuthInterface]] interface.
     */
    public $authMethods = [];


    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        return empty($this->authMethods) ? true : parent::beforeAction($action);
    }

    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        foreach ($this->authMethods as $i => $auth) {
            $this->authMethods[$i] = $auth = Yii::createObject($auth);
            if (!$auth instanceof AuthInterface) {
                throw new InvalidConfigException(get_class($auth) . ' must implement yii\filters\auth\AuthInterface');
            }

            $identity = $auth->authenticate($user, $request, $response);
            if ($identity !== null) {
                return $identity;
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function challenge($response)
    {
        foreach ($this->authMethods as $method) {
            /** @var $method AuthInterface */
            $method->challenge($response);
        }
    }
}
