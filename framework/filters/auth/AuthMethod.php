<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters\auth;

use Yii;
use yii\base\Action;
use yii\base\ActionFilter;
use yii\helpers\StringHelper;
use yii\web\Request;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;
use yii\web\User;

/**
 * AuthMethod 是实现 [[AuthInterface]] 接口的基类。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class AuthMethod extends ActionFilter implements AuthInterface
{
    /**
     * @var User 表示用户身份验证状态的用户对象。如果没有设置，这个用户将使用应用程序组件。
     */
    public $user;
    /**
     * @var Request 当前请求。如果没有设置，这个请求将使用应用程序组件。
     */
    public $request;
    /**
     * @var Response 要发送的响应。如果没有设置，这个响应将使用应用程序组件。
     */
    public $response;
    /**
     * @var array 此筛选器将应用于的操作行为 IDs 的数组列表，但是身份验证失败不会导致错误。
     * 它可能被用于行动，那是允许公开的，但是返回一些已验证用户的附加数据。
     * 默认空，意思是不可选认证的任何行动。
     * Since version 2.0.10 action IDs 可以指定为通配符，例如 `site/*`。
     * @see isOptional()
     * @since 2.0.7
     */
    public $optional = [];


    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        $response = $this->response ?: Yii::$app->getResponse();

        try {
            $identity = $this->authenticate(
                $this->user ?: Yii::$app->getUser(),
                $this->request ?: Yii::$app->getRequest(),
                $response
            );
        } catch (UnauthorizedHttpException $e) {
            if ($this->isOptional($action)) {
                return true;
            }

            throw $e;
        }

        if ($identity !== null || $this->isOptional($action)) {
            return true;
        }

        $this->challenge($response);
        $this->handleFailure($response);

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function challenge($response)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function handleFailure($response)
    {
        throw new UnauthorizedHttpException('Your request was made with invalid credentials.');
    }

    /**
     * 检查，给定操作的身份验证是否可选。
     *
     * @param Action $action 要检查的操作。
     * @return bool 身份验证是否可选。
     * @see optional
     * @since 2.0.7
     */
    protected function isOptional($action)
    {
        $id = $this->getActionId($action);
        foreach ($this->optional as $pattern) {
            if (StringHelper::matchWildcard($pattern, $id)) {
                return true;
            }
        }

        return false;
    }
}
