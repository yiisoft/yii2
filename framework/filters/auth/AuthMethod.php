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
use yii\web\UnauthorizedHttpException;
use yii\web\User;
use yii\web\Request;
use yii\web\Response;

/**
 * AuthMethod is a base class implementing the [[AuthInterface]] interface.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class AuthMethod extends ActionFilter implements AuthInterface
{
    /**
     * @var User the user object representing the user authentication status. If not set, the `user` application component will be used.
     */
    public $user;
    /**
     * @var Request the current request. If not set, the `request` application component will be used.
     */
    public $request;
    /**
     * @var Response the response to be sent. If not set, the `response` application component will be used.
     */
    public $response;
    /**
     * @var array list of action IDs that this filter will be applied to, but auth failure will not lead to error.
     * It may be used for actions, that are allowed for public, but return some additional data for authenticated users.
     * @see isOptional
     * @since 2.0.7
     */
    public $optional = [];


    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $response = $this->response ? : Yii::$app->getResponse();

        try {
            $identity = $this->authenticate(
                $this->user ? : Yii::$app->getUser(),
                $this->request ? : Yii::$app->getRequest(),
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
        } else {
            $this->challenge($response);
            $this->handleFailure($response);
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function challenge($response)
    {
    }

    /**
     * @inheritdoc
     */
    public function handleFailure($response)
    {
        throw new UnauthorizedHttpException('You are requesting with an invalid credential.');
    }

    /**
     * Checks, whether the $action is optional
     *
     * @param Action $action
     * @return boolean
     * @see optional
     * @since 2.0.7
     */
    protected function isOptional($action) {
        $id = $this->getActionId($action);
        return in_array($id, $this->optional, true);
    }

    /**
     * {@inheritdoc}
     */
    protected function isActive($action)
    {
        return parent::isActive($action) || $this->isOptional($action);
    }
}
