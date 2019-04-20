<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters;

use Yii;
use yii\base\ActionEvent;
use yii\base\Behavior;
use yii\web\Controller;
use yii\web\MethodNotAllowedHttpException;

/**
 * VerbFilter 是一个操作过滤器它通过 HTTP 请求方法进行过滤。
 *
 * 它允许为每个操作定义允许的 HTTP 请求方法
 * 并将在不允许该方法时引发 HTTP 405 错误。
 *
 * 要使用 VerbFilter，请在控制器类的 `behaviors()` 方法中声明它。
 * 例如，以下声明将为
 * REST CRUD 操作定义一组典型的允许请求方法。
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'verbs' => [
 *             'class' => \yii\filters\VerbFilter::className(),
 *             'actions' => [
 *                 'index'  => ['GET'],
 *                 'view'   => ['GET'],
 *                 'create' => ['GET', 'POST'],
 *                 'update' => ['GET', 'PUT', 'POST'],
 *                 'delete' => ['POST', 'DELETE'],
 *             ],
 *         ],
 *     ];
 * }
 * ```
 *
 * @see https://tools.ietf.org/html/rfc2616#section-14.7
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class VerbFilter extends Behavior
{
    /**
     * @var array 此属性为每个操作定义允许的请求方法。
     * 对于应该只支持有限的请求方法集的每个操作
     * 您添加一个条目，其中操作 id 作为数组键
     * 允许的方法数组（例如 GET， HEAD， PUT）作为值。
     * 如果未列出操作则认为所有请求方法都是允许的。
     *
     * 你可以用 `'*'` 来代表所有的行动。当一个动作是显式的
     * 指定的，它则优先于 `'*'` 所给出的规范。
     *
     * 例如，
     *
     * ```php
     * [
     *   'create' => ['GET', 'POST'],
     *   'update' => ['GET', 'PUT', 'POST'],
     *   'delete' => ['POST', 'DELETE'],
     *   '*' => ['GET'],
     * ]
     * ```
     */
    public $actions = [];


    /**
     * 声明 [[owner]]'s 事件的事件处理程序。
     * @return array 事件（数组键）和相应的事件处理程序方法（数组值）。
     */
    public function events()
    {
        return [Controller::EVENT_BEFORE_ACTION => 'beforeAction'];
    }

    /**
     * @param ActionEvent $event
     * @return bool
     * @throws MethodNotAllowedHttpException 当不允许请求方法时。
     */
    public function beforeAction($event)
    {
        $action = $event->action->id;
        if (isset($this->actions[$action])) {
            $verbs = $this->actions[$action];
        } elseif (isset($this->actions['*'])) {
            $verbs = $this->actions['*'];
        } else {
            return $event->isValid;
        }

        $verb = Yii::$app->getRequest()->getMethod();
        $allowed = array_map('strtoupper', $verbs);
        if (!in_array($verb, $allowed)) {
            $event->isValid = false;
            // https://tools.ietf.org/html/rfc2616#section-14.7
            Yii::$app->getResponse()->getHeaders()->set('Allow', implode(', ', $allowed));
            throw new MethodNotAllowedHttpException('Method Not Allowed. This URL can only handle the following request methods: ' . implode(', ', $allowed) . '.');
        }

        return $event->isValid;
    }
}
