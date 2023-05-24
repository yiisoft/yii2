<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\Exception;
use yii\base\InlineAction;
use yii\helpers\Url;

/**
 * Controller is the base class of web controllers.
 *
 * For more details and usage information on Controller, see the [guide article on controllers](guide:structure-controllers).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Controller extends \yii\base\Controller
{
    /**
     * @var bool whether to enable CSRF validation for the actions in this controller.
     * CSRF validation is enabled only when both this property and [[\yii\web\Request::enableCsrfValidation]] are true.
     */
    public $enableCsrfValidation = true;
    /**
     * @var array the parameters bound to the current action.
     */
    public $actionParams = [];


    /**
     * Renders a view in response to an AJAX request.
     *
     * This method is similar to [[renderPartial()]] except that it will inject into
     * the rendering result with JS/CSS scripts and files which are registered with the view.
     * For this reason, you should use this method instead of [[renderPartial()]] to render
     * a view to respond to an AJAX request.
     *
     * @param string $view the view name. Please refer to [[render()]] on how to specify a view name.
     * @param array $params the parameters (name-value pairs) that should be made available in the view.
     * @return string the rendering result.
     */
    public function renderAjax($view, $params = [])
    {
        return $this->getView()->renderAjax($view, $params, $this);
    }

    /**
     * Send data formatted as JSON.
     *
     * This method is a shortcut for sending data formatted as JSON. It will return
     * the [[Application::getResponse()|response]] application component after configuring
     * the [[Response::$format|format]] and setting the [[Response::$data|data]] that should
     * be formatted. A common usage will be:
     *
     * ```php
     * return $this->asJson($data);
     * ```
     *
     * @param mixed $data the data that should be formatted.
     * @return Response a response that is configured to send `$data` formatted as JSON.
     * @since 2.0.11
     * @see Response::$format
     * @see Response::FORMAT_JSON
     * @see JsonResponseFormatter
     */
    public function asJson($data)
    {
        $this->response->format = Response::FORMAT_JSON;
        $this->response->data = $data;
        return $this->response;
    }

    /**
     * Send data formatted as XML.
     *
     * This method is a shortcut for sending data formatted as XML. It will return
     * the [[Application::getResponse()|response]] application component after configuring
     * the [[Response::$format|format]] and setting the [[Response::$data|data]] that should
     * be formatted. A common usage will be:
     *
     * ```php
     * return $this->asXml($data);
     * ```
     *
     * @param mixed $data the data that should be formatted.
     * @return Response a response that is configured to send `$data` formatted as XML.
     * @since 2.0.11
     * @see Response::$format
     * @see Response::FORMAT_XML
     * @see XmlResponseFormatter
     */
    public function asXml($data)
    {
        $this->response->format = Response::FORMAT_XML;
        $this->response->data = $data;
        return $this->response;
    }

    /**
     * Binds the parameters to the action.
     * This method is invoked by [[\yii\base\Action]] when it begins to run with the given parameters.
     * This method will check the parameter names that the action requires and return
     * the provided parameters according to the requirement. If there is any missing parameter,
     * an exception will be thrown.
     * @param \yii\base\Action $action the action to be bound with parameters
     * @param array $params the parameters to be bound to the action
     * @return array the valid parameters that the action can run with.
     * @throws BadRequestHttpException if there are missing or invalid parameters.
     */
    public function bindActionParams($action, $params)
    {
        if ($action instanceof InlineAction) {
            $method = new \ReflectionMethod($this, $action->actionMethod);
        } else {
            $method = new \ReflectionMethod($action, 'run');
        }

        $args = [];
        $missing = [];
        $actionParams = [];
        $requestedParams = [];
        foreach ($method->getParameters() as $param) {
            $name = $param->getName();
            if (array_key_exists($name, $params)) {
                $isValid = true;
                if (PHP_VERSION_ID >= 80000) {
                    $isArray = ($type = $param->getType()) instanceof \ReflectionNamedType && $type->getName() === 'array';
                } else {
                    $isArray = $param->isArray();
                }
                if ($isArray) {
                    $params[$name] = (array)$params[$name];
                } elseif (is_array($params[$name])) {
                    $isValid = false;
                } elseif (
                    PHP_VERSION_ID >= 70000
                    && ($type = $param->getType()) !== null
                    && $type->isBuiltin()
                    && ($params[$name] !== null || !$type->allowsNull())
                ) {
                    $typeName = PHP_VERSION_ID >= 70100 ? $type->getName() : (string)$type;

                    if ($params[$name] === '' && $type->allowsNull()) {
                        if ($typeName !== 'string') { // for old string behavior compatibility
                            $params[$name] = null;
                        }
                    } else {
                        switch ($typeName) {
                            case 'int':
                                $params[$name] = filter_var($params[$name], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
                                break;
                            case 'float':
                                $params[$name] = filter_var($params[$name], FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
                                break;
                            case 'bool':
                                $params[$name] = filter_var($params[$name], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                                break;
                        }
                        if ($params[$name] === null) {
                            $isValid = false;
                        }
                    }
                }
                if (!$isValid) {
                    throw new BadRequestHttpException(
                        Yii::t('yii', 'Invalid data received for parameter "{param}".', ['param' => $name])
                    );
                }
                $args[] = $actionParams[$name] = $params[$name];
                unset($params[$name]);
            } elseif (
                PHP_VERSION_ID >= 70100
                && ($type = $param->getType()) !== null
                && $type instanceof \ReflectionNamedType
                && !$type->isBuiltin()
            ) {
                try {
                    $this->bindInjectedParams($type, $name, $args, $requestedParams);
                } catch (HttpException $e) {
                    throw $e;
                } catch (Exception $e) {
                    throw new ServerErrorHttpException($e->getMessage(), 0, $e);
                }
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $actionParams[$name] = $param->getDefaultValue();
            } else {
                $missing[] = $name;
            }
        }

        if (!empty($missing)) {
            throw new BadRequestHttpException(
                Yii::t('yii', 'Missing required parameters: {params}', ['params' => implode(', ', $missing)])
            );
        }

        $this->actionParams = $actionParams;

        // We use a different array here, specifically one that doesn't contain service instances but descriptions instead.
        if (Yii::$app->requestedParams === null) {
            Yii::$app->requestedParams = array_merge($actionParams, $requestedParams);
        }

        return $args;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if ($this->enableCsrfValidation && Yii::$app->getErrorHandler()->exception === null && !$this->request->validateCsrfToken()) {
                throw new BadRequestHttpException(Yii::t('yii', 'Unable to verify your data submission.'));
            }

            return true;
        }

        return false;
    }

    /**
     * Redirects the browser to the specified URL.
     * This method is a shortcut to [[Response::redirect()]].
     *
     * You can use it in an action by returning the [[Response]] directly:
     *
     * ```php
     * // stop executing this action and redirect to login page
     * return $this->redirect(['login']);
     * ```
     *
     * @param string|array $url the URL to be redirected to. This can be in one of the following formats:
     *
     * - a string representing a URL (e.g. "https://example.com")
     * - a string representing a URL alias (e.g. "@example.com")
     * - an array in the format of `[$route, ...name-value pairs...]` (e.g. `['site/index', 'ref' => 1]`)
     *   [[Url::to()]] will be used to convert the array into a URL.
     *
     * Any relative URL that starts with a single forward slash "/" will be converted
     * into an absolute one by prepending it with the host info of the current request.
     *
     * @param int $statusCode the HTTP status code. Defaults to 302.
     * See <https://tools.ietf.org/html/rfc2616#section-10>
     * for details about HTTP status code
     * @return Response the current response object
     */
    public function redirect($url, $statusCode = 302)
    {
        // calling Url::to() here because Response::redirect() modifies route before calling Url::to()
        return $this->response->redirect(Url::to($url), $statusCode);
    }

    /**
     * Redirects the browser to the home page.
     *
     * You can use this method in an action by returning the [[Response]] directly:
     *
     * ```php
     * // stop executing this action and redirect to home page
     * return $this->goHome();
     * ```
     *
     * @return Response the current response object
     */
    public function goHome()
    {
        return $this->response->redirect(Yii::$app->getHomeUrl());
    }

    /**
     * Redirects the browser to the last visited page.
     *
     * You can use this method in an action by returning the [[Response]] directly:
     *
     * ```php
     * // stop executing this action and redirect to last visited page
     * return $this->goBack();
     * ```
     *
     * For this function to work you have to [[User::setReturnUrl()|set the return URL]] in appropriate places before.
     *
     * @param string|array|null $defaultUrl the default return URL in case it was not set previously.
     * If this is null and the return URL was not set previously, [[Application::homeUrl]] will be redirected to.
     * Please refer to [[User::setReturnUrl()]] on accepted format of the URL.
     * @return Response the current response object
     * @see User::getReturnUrl()
     */
    public function goBack($defaultUrl = null)
    {
        return $this->response->redirect(Yii::$app->getUser()->getReturnUrl($defaultUrl));
    }

    /**
     * Refreshes the current page.
     * This method is a shortcut to [[Response::refresh()]].
     *
     * You can use it in an action by returning the [[Response]] directly:
     *
     * ```php
     * // stop executing this action and refresh the current page
     * return $this->refresh();
     * ```
     *
     * @param string $anchor the anchor that should be appended to the redirection URL.
     * Defaults to empty. Make sure the anchor starts with '#' if you want to specify it.
     * @return Response the response object itself
     */
    public function refresh($anchor = '')
    {
        return $this->response->redirect($this->request->getUrl() . $anchor);
    }
}
