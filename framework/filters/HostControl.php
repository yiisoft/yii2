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
use yii\web\NotFoundHttpException;

/**
 * HostControl provides simple control over requested host name.
 *
 * This filter provides protection against 'host header' attacks, allowing action execution only for specified
 * host names.
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
 * This filter should be used only, if this configuration is not available or compromised.
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
     * function (Action $action) {
     *     //return array of strings
     * }
     * ```
     *
     * where `$action` is the current [[Action|action]] object.
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
     * function (Action $action)
     * ```
     *
     * where `$action` is the current [[Action|action]] object.
     */
    public $denyCallback;


    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * You may override this method to do last-minute preparation for the action.
     * @param Action $action the action to be executed.
     * @return bool whether the action should continue to be executed.
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
            $allowedHosts = (array)$allowedHosts;
        }

        $currentHost = Yii::$app->getRequest()->getHostName();

        foreach ($allowedHosts as $allowedHost) {
            if (fnmatch($allowedHost, $currentHost)) {
                return true;
            }
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
     * The default implementation throws a 404 HTTP exception.
     * @param Action $action the action to be executed.
     * @throws NotFoundHttpException
     */
    protected function denyAccess($action)
    {
        throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
    }
}