<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\filters\auth;

use Yii;
use yii\base\Action;
use yii\base\ActionFilter;
use yii\base\Component;
use yii\base\Controller;
use yii\base\Module;
use yii\helpers\StringHelper;
use yii\web\IdentityInterface;
use yii\web\Request;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;
use yii\web\User;

/**
 * AuthMethod is a base class implementing the [[AuthInterface]] interface.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 *
 * @template T of Component
 * @extends ActionFilter<T>
 */
abstract class AuthMethod extends ActionFilter implements AuthInterface
{
    /**
     * @var User|null the user object representing the user authentication status. If not set, the `user` application component will be used.
     *
     * @phpstan-var User<IdentityInterface>
     * @psalm-var User<IdentityInterface>
     */
    public $user;
    /**
     * @var Request|null the current request. If not set, the `request` application component will be used.
     */
    public $request;
    /**
     * @var Response|null the response to be sent. If not set, the `response` application component will be used.
     */
    public $response;
    /**
     * @var array list of action IDs that this filter will be applied to, but auth failure will not lead to error.
     * It may be used for actions, that are allowed for public, but return some additional data for authenticated users.
     * Defaults to empty, meaning authentication is not optional for any action.
     * Since version 2.0.10 action IDs can be specified as wildcards, e.g. `site/*`.
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
     * Checks, whether authentication is optional for the given action.
     *
     * @param Action $action action to be checked.
     * @return bool whether authentication is optional or not.
     * @see optional
     * @since 2.0.7
     *
     * @phpstan-param Action<Controller<Module>> $action
     * @psalm-param Action<Controller<Module>> $action
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
