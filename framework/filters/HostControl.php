<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters;

use Yii;
use yii\base\ActionFilter;
use yii\web\NotFoundHttpException;

/**
 * HostControl provides simple control over requested host name.
 *
 * This filter provides protection against ['host header' attacks](https://www.acunetix.com/vulnerabilities/web/host-header-attack),
 * allowing action execution only for specified host names.
 *
 * Application configuration example:
 *
 * ```php
 * return [
 *     'as hostControl' => [
 *         'class' => 'yii\filters\HostControl',
 *         'allowedHosts' => [
 *             'example.com',
 *             '*.example.com',
 *         ],
 *     ],
 *     // ...
 * ];
 * ```
 *
 * Controller configuration example:
 *
 * ```php
 * use yii\web\Controller;
 * use yii\filters\HostControl;
 *
 * class SiteController extends Controller
 * {
 *     public function behaviors()
 *     {
 *         return [
 *             'hostControl' => [
 *                 'class' => HostControl::className(),
 *                 'allowedHosts' => [
 *                     'example.com',
 *                     '*.example.com',
 *                 ],
 *             ],
 *         ];
 *     }
 *
 *     // ...
 * }
 * ```
 *
 * > Note: the best way to restrict allowed host names is usage of the web server 'virtual hosts' configuration.
 * This filter should be used only if this configuration is not available or compromised.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.11
 */
class HostControl extends ActionFilter
{
    /**
     * @var array|\Closure|null list of host names, which are allowed.
     * Each host can be specified as a wildcard pattern. For example:
     *
     * ```php
     * [
     *     'example.com',
     *     '*.example.com',
     * ]
     * ```
     *
     * This field can be specified as a PHP callback of following signature:
     *
     * ```php
     * function (\yii\base\Action $action) {
     *     //return array of strings
     * }
     * ```
     *
     * where `$action` is the current [[\yii\base\Action|action]] object.
     *
     * If this field is not set - no host name check will be performed.
     */
    public $allowedHosts;
    /**
     * @var callable a callback that will be called if the current host does not match [[allowedHosts]].
     * If not set, [[denyAccess()]] will be called.
     *
     * The signature of the callback should be as follows:
     *
     * ```php
     * function (\yii\base\Action $action)
     * ```
     *
     * where `$action` is the current [[\yii\base\Action|action]] object.
     *
     * > Note: while implementing your own host deny processing, make sure you avoid usage of the current requested
     * host name, creation of absolute URL links, caching page parts and so on.
     */
    public $denyCallback;
    /**
     * @var string|null fallback host info (e.g. `http://www.yiiframework.com`) used when [[\yii\web\Request::$hostInfo|Request::$hostInfo]] is invalid.
     * This value will replace [[\yii\web\Request::$hostInfo|Request::$hostInfo]] before [[$denyCallback]] is called to make sure that
     * an invalid host will not be used for further processing. You can set it to `null` to leave [[\yii\web\Request::$hostInfo|Request::$hostInfo]] untouched.
     * Default value is empty string (this will result creating relative URLs instead of absolute).
     * @see \yii\web\Request::getHostInfo()
     */
    public $fallbackHostInfo = '';


    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $allowedHosts = $this->allowedHosts;
        if ($allowedHosts instanceof \Closure) {
            $allowedHosts = call_user_func($allowedHosts, $action);
        }
        if ($allowedHosts === null) {
            return true;
        }

        if (!is_array($allowedHosts) && !$allowedHosts instanceof \Traversable) {
            $allowedHosts = (array) $allowedHosts;
        }

        $currentHost = Yii::$app->getRequest()->getHostName();

        foreach ($allowedHosts as $allowedHost) {
            if (fnmatch($allowedHost, $currentHost)) {
                return true;
            }
        }

        // replace invalid host info to prevent using it in further processing
        if ($this->fallbackHostInfo !== null) {
            Yii::$app->getRequest()->setHostInfo($this->fallbackHostInfo);
        }

        if ($this->denyCallback !== null) {
            call_user_func($this->denyCallback, $action);
        } else {
            $this->denyAccess($action);
        }

        return false;
    }

    /**
     * Denies the access.
     * The default implementation will display 404 page right away, terminating the program execution.
     * You may override this method, creating your own deny access handler. While doing so, make sure you
     * avoid usage of the current requested host name, creation of absolute URL links, caching page parts and so on.
     * @param \yii\base\Action $action the action to be executed.
     * @throws NotFoundHttpException
     */
    protected function denyAccess($action)
    {
        $exception = new NotFoundHttpException(Yii::t('yii', 'Page not found.'));

        // use regular error handling if $this->fallbackHostInfo was set
        if (!empty(Yii::$app->getRequest()->hostName)) {
            throw $exception;
        }

        $response = Yii::$app->getResponse();
        $errorHandler = Yii::$app->getErrorHandler();

        $response->setStatusCode($exception->statusCode, $exception->getMessage());
        $response->data = $errorHandler->renderFile($errorHandler->errorView, ['exception' => $exception]);
        $response->send();

        Yii::$app->end();
    }
}
