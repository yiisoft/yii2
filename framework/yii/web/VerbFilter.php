<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\ActionEvent;
use yii\base\Behavior;

/**
 * VerbFilter is an action filter that filters by HTTP request methods.
 *
 * It allows to define allowed HTTP request methods for each action and will throw
 * an HTTP 405 error when the method is not allowed.
 *
 * To use VerbFilter, declare it in the `behaviors()` method of your controller class.
 * For example, the following declarations will define a typical set of allowed
 * request methods for REST CRUD actions.
 *
 * ~~~
 * public function behaviors()
 * {
 *     return [
 *         'verbs' => [
 *             'class' => \yii\web\VerbFilter::className(),
 *             'actions' => [
 *                 'index'  => ['get'],
 *                 'view'   => ['get'],
 *                 'create' => ['get', 'post'],
 *                 'update' => ['get', 'put', 'post'],
 *                 'delete' => ['post', 'delete'],
 *             ],
 *         ],
 *     ];
 * }
 * ~~~
 *
 * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.7
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class VerbFilter extends Behavior
{
	/**
	 * @var array this property defines the allowed request methods for each action.
	 * For each action that should only support limited set of request methods
	 * you add an entry with the action id as array key and an array of
	 * allowed methods (e.g. GET, HEAD, PUT) as the value.
	 * If an action is not listed all request methods are considered allowed.
	 *
	 * You can use '*' to stand for all actions. When an action is explicitly
	 * specified, it takes precedence over the specification given by '*'.
	 *
	 * For example,
	 *
	 * ~~~
	 * [
	 *   'create' => ['get', 'post'],
	 *   'update' => ['get', 'put', 'post'],
	 *   'delete' => ['post', 'delete'],
	 *   '*' => ['get'],
	 * ]
	 * ~~~
	 */
	public $actions = [];


	/**
	 * Declares event handlers for the [[owner]]'s events.
	 * @return array events (array keys) and the corresponding event handler methods (array values).
	 */
	public function events()
	{
		return [Controller::EVENT_BEFORE_ACTION => 'beforeAction'];
	}

	/**
	 * @param ActionEvent $event
	 * @return boolean
	 * @throws HttpException when the request method is not allowed.
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
		if (!in_array($verb, array_map('strtoupper', $verbs))) {
			$event->isValid = false;
			// http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.7
			Yii::$app->getResponse()->getHeaders()->set('Allow', implode(', ', $allowed));
			throw new MethodNotAllowedHttpException(Yii::t('yii', 'Method Not Allowed. This url can only handle the following request methods: {methods}.', [
				'methods' => implode(', ', $allowed),
			]));
		}

		return $event->isValid;
	}
}
