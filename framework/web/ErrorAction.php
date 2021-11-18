<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\Action;
use yii\base\Exception;
use yii\base\UserException;

/**
 * ErrorAction displays application errors using a specified view.
 *
 * To use ErrorAction, you need to do the following steps:
 *
 * First, declare an action of ErrorAction type in the `actions()` method of your `SiteController`
 * class (or whatever controller you prefer), like the following:
 *
 * ```php
 * public function actions()
 * {
 *     return [
 *         'error' => ['class' => 'yii\web\ErrorAction'],
 *     ];
 * }
 * ```
 *
 * Then, create a view file for this action. If the route of your error action is `site/error`, then
 * the view file should be `views/site/error.php`. In this view file, the following variables are available:
 *
 * - `$name`: the error name
 * - `$message`: the error message
 * - `$exception`: the exception being handled
 *
 * Finally, configure the "errorHandler" application component as follows,
 *
 * ```php
 * 'errorHandler' => [
 *     'errorAction' => 'site/error',
 * ]
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Dmitry Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0
 */
class ErrorAction extends Action
{
    /**
     * @var string the view file to be rendered. If not set, it will take the value of [[id]].
     * That means, if you name the action as "error" in "SiteController", then the view name
     * would be "error", and the corresponding view file would be "views/site/error.php".
     */
    public $view;
    /**
     * @var string the name of the error when the exception name cannot be determined.
     * Defaults to "Error".
     */
    public $defaultName;
    /**
     * @var string the message to be displayed when the exception message contains sensitive information.
     * Defaults to "An internal server error occurred.".
     */
    public $defaultMessage;
    /**
     * @var string|false|null the name of the layout to be applied to this error action view.
     * If not set, the layout configured in the controller will be used.
     * @see \yii\base\Controller::$layout
     * @since 2.0.14
     */
    public $layout;

    /**
     * @var \Exception the exception object, normally is filled on [[init()]] method call.
     * @see findException() to know default way of obtaining exception.
     * @since 2.0.11
     */
    protected $exception;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->exception = $this->findException();

        if ($this->defaultMessage === null) {
            $this->defaultMessage = Yii::t('yii', 'An internal server error occurred.');
        }

        if ($this->defaultName === null) {
            $this->defaultName = Yii::t('yii', 'Error');
        }
    }

    /**
     * Runs the action.
     *
     * @return string result content
     */
    public function run()
    {
        if ($this->layout !== null) {
            $this->controller->layout = $this->layout;
        }

        Yii::$app->getResponse()->setStatusCodeByException($this->exception);

        if (Yii::$app->getRequest()->getIsAjax()) {
            return $this->renderAjaxResponse();
        }

        return $this->renderHtmlResponse();
    }

    /**
     * Builds string that represents the exception.
     * Normally used to generate a response to AJAX request.
     * @return string
     * @since 2.0.11
     */
    protected function renderAjaxResponse()
    {
        return $this->getExceptionName() . ': ' . $this->getExceptionMessage();
    }

    /**
     * Renders a view that represents the exception.
     * @return string
     * @since 2.0.11
     */
    protected function renderHtmlResponse()
    {
        return $this->controller->render($this->view ?: $this->id, $this->getViewRenderParams());
    }

    /**
     * Builds array of parameters that will be passed to the view.
     * @return array
     * @since 2.0.11
     */
    protected function getViewRenderParams()
    {
        return [
            'name' => $this->getExceptionName(),
            'message' => $this->getExceptionMessage(),
            'exception' => $this->exception,
        ];
    }

    /**
     * Gets exception from the [[yii\web\ErrorHandler|ErrorHandler]] component.
     * In case there is no exception in the component, treat as the action has been invoked
     * not from error handler, but by direct route, so '404 Not Found' error will be displayed.
     * @return \Exception
     * @since 2.0.11
     */
    protected function findException()
    {
        if (($exception = Yii::$app->getErrorHandler()->exception) === null) {
            $exception = new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }

        return $exception;
    }

    /**
     * Gets the code from the [[exception]].
     * @return mixed
     * @since 2.0.11
     */
    protected function getExceptionCode()
    {
        if ($this->exception instanceof HttpException) {
            return $this->exception->statusCode;
        }

        return $this->exception->getCode();
    }

    /**
     * Returns the exception name, followed by the code (if present).
     *
     * @return string
     * @since 2.0.11
     */
    protected function getExceptionName()
    {
        if ($this->exception instanceof Exception) {
            $name = $this->exception->getName();
        } else {
            $name = $this->defaultName;
        }

        if ($code = $this->getExceptionCode()) {
            $name .= " (#$code)";
        }

        return $name;
    }

    /**
     * Returns the [[exception]] message for [[yii\base\UserException]] only.
     * For other cases [[defaultMessage]] will be returned.
     * @return string
     * @since 2.0.11
     */
    protected function getExceptionMessage()
    {
        if ($this->exception instanceof UserException) {
            return $this->exception->getMessage();
        }

        return $this->defaultMessage;
    }
}
