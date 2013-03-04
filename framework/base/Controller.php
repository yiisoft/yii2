<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;
use yii\util\StringHelper;

/**
 * Controller is the base class for classes containing controller logic.
 *
 * @property string $route the route (module ID, controller ID and action ID) of the current request.
 * @property string $uniqueId the controller ID that is prefixed with the module ID (if any).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Controller extends Component
{
	const EVENT_BEFORE_ACTION = 'beforeAction';
	const EVENT_AFTER_ACTION = 'afterAction';

	/**
	 * @var string the ID of this controller
	 */
	public $id;
	/**
	 * @var Module $module the module that this controller belongs to.
	 */
	public $module;
	/**
	 * @var string the ID of the action that is used when the action ID is not specified
	 * in the request. Defaults to 'index'.
	 */
	public $defaultAction = 'index';
	/**
	 * @var string|boolean the name of the layout to be applied to this controller's views.
	 * This property mainly affects the behavior of [[render()]].
	 * Defaults to null, meaning the actual layout value should inherit that from [[module]]'s layout value.
	 * If false, no layout will be applied.
	 */
	public $layout;
	/**
	 * @var Action the action that is currently being executed. This property will be set
	 * by [[run()]] when it is called by [[Application]] to run an action.
	 */
	public $action;

	/**
	 * @param string $id the ID of this controller
	 * @param Module $module the module that this controller belongs to.
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 */
	public function __construct($id, $module, $config = array())
	{
		$this->id = $id;
		$this->module = $module;
		parent::__construct($config);
	}

	/**
	 * Declares external actions for the controller.
	 * This method is meant to be overwritten to declare external actions for the controller.
	 * It should return an array, with array keys being action IDs, and array values the corresponding
	 * action class names or action configuration arrays. For example,
	 *
	 * ~~~
	 * return array(
	 *     'action1' => '@app/components/Action1',
	 *     'action2' => array(
	 *         'class' => '@app/components/Action2',
	 *         'property1' => 'value1',
	 *         'property2' => 'value2',
	 *     ),
	 * );
	 * ~~~
	 *
	 * [[\Yii::createObject()]] will be used later to create the requested action
	 * using the configuration provided here.
	 */
	public function actions()
	{
		return array();
	}

	/**
	 * Runs an action with the specified action ID and parameters.
	 * If the action ID is empty, the method will use [[defaultAction]].
	 * @param string $id the ID of the action to be executed.
	 * @param array $params the parameters (name-value pairs) to be passed to the action.
	 * @return integer the status of the action execution. 0 means normal, other values mean abnormal.
	 * @throws InvalidRouteException if the requested action ID cannot be resolved into an action successfully.
	 * @see createAction
	 */
	public function runAction($id, $params = array())
	{
		$action = $this->createAction($id);
		if ($action !== null) {
			$oldAction = $this->action;
			$this->action = $action;

			if ($this->beforeAction($action)) {
				$status = $action->runWithParams($params);
				$this->afterAction($action);
			} else {
				$status = 1;
			}

			$this->action = $oldAction;

			return $status;
		} else {
			throw new InvalidRouteException('Unable to resolve the request: ' . $this->getUniqueId() . '/' . $id);
		}
	}

	/**
	 * Runs a request specified in terms of a route.
	 * The route can be either an ID of an action within this controller or a complete route consisting
	 * of module IDs, controller ID and action ID. If the route starts with a slash '/', the parsing of
	 * the route will start from the application; otherwise, it will start from the parent module of this controller.
	 * @param string $route the route to be handled, e.g., 'view', 'comment/view', '/admin/comment/view'.
	 * @param array $params the parameters to be passed to the action.
	 * @return integer the status code returned by the action execution. 0 means normal, and other values mean abnormal.
	 * @see runAction
	 * @see forward
	 */
	public function run($route, $params = array())
	{
		$pos = strpos($route, '/');
		if ($pos === false) {
			return $this->runAction($route, $params);
		} elseif ($pos > 0) {
			return $this->module->runAction($route, $params);
		} else {
			return \Yii::$app->runAction(ltrim($route, '/'), $params);
		}
	}

	/**
	 * Binds the parameters to the action.
	 * This method is invoked by [[Action]] when it begins to run with the given parameters.
	 * This method will check the parameter names that the action requires and return
	 * the provided parameters according to the requirement. If there is any missing parameter,
	 * an exception will be thrown.
	 * @param Action $action the action to be bound with parameters
	 * @param array $params the parameters to be bound to the action
	 * @return array the valid parameters that the action can run with.
	 * @throws InvalidRequestException if there are missing parameters.
	 */
	public function bindActionParams($action, $params)
	{
		if ($action instanceof InlineAction) {
			$method = new \ReflectionMethod($this, $action->actionMethod);
		} else {
			$method = new \ReflectionMethod($action, 'run');
		}

		$args = array();
		$missing = array();
		foreach ($method->getParameters() as $param) {
			$name = $param->getName();
			if (array_key_exists($name, $params)) {
				$args[] = $params[$name];
				unset($params[$name]);
			} elseif ($param->isDefaultValueAvailable()) {
				$args[] = $param->getDefaultValue();
			} else {
				$missing[] = $name;
			}
		}

		if ($missing !== array()) {
			throw new InvalidRequestException(Yii::t('yii|Missing required parameters: {params}', array(
				'{params}' => implode(', ', $missing),
			)));
		}

		return $args;
	}

	/**
	 * Forwards the current execution flow to handle a new request specified by a route.
	 * The only difference between this method and [[run()]] is that after calling this method,
	 * the application will exit.
	 * @param string $route the route to be handled, e.g., 'view', 'comment/view', '/admin/comment/view'.
	 * @param array $params the parameters to be passed to the action.
	 * @return integer the status code returned by the action execution. 0 means normal, and other values mean abnormal.
	 * @see run
	 */
	public function forward($route, $params = array())
	{
		$status = $this->run($route, $params);
		exit($status);
	}

	/**
	 * Creates an action based on the given action ID.
	 * The method first checks if the action ID has been declared in [[actions()]]. If so,
	 * it will use the configuration declared there to create the action object.
	 * If not, it will look for a controller method whose name is in the format of `actionXyz`
	 * where `Xyz` stands for the action ID. If found, an [[InlineAction]] representing that
	 * method will be created and returned.
	 * @param string $id the action ID
	 * @return Action the newly created action instance. Null if the ID doesn't resolve into any action.
	 */
	public function createAction($id)
	{
		if ($id === '') {
			$id = $this->defaultAction;
		}

		$actionMap = $this->actions();
		if (isset($actionMap[$id])) {
			return Yii::createObject($actionMap[$id], $id, $this);
		} elseif (preg_match('/^[a-z0-9\\-_]+$/', $id)) {
			$methodName = 'action' . StringHelper::id2camel($id);
			if (method_exists($this, $methodName)) {
				$method = new \ReflectionMethod($this, $methodName);
				if ($method->getName() === $methodName) {
					return new InlineAction($id, $this, $methodName);
				}
			}
		}
		return null;
	}

	/**
	 * This method is invoked right before an action is to be executed (after all possible filters.)
	 * You may override this method to do last-minute preparation for the action.
	 * @param Action $action the action to be executed.
	 * @return boolean whether the action should continue to be executed.
	 */
	public function beforeAction($action)
	{
		$event = new ActionEvent($action);
		$this->trigger(self::EVENT_BEFORE_ACTION, $event);
		return $event->isValid;
	}

	/**
	 * This method is invoked right after an action is executed.
	 * You may override this method to do some postprocessing for the action.
	 * @param Action $action the action just executed.
	 */
	public function afterAction($action)
	{
		$this->trigger(self::EVENT_AFTER_ACTION, new ActionEvent($action));
	}

	/**
	 * Returns the request parameters that will be used for action parameter binding.
	 * Default implementation simply returns an empty array.
	 * Child classes may override this method to customize the parameters to be provided
	 * for action parameter binding (e.g. `$_GET`).
	 * @return array the request parameters (name-value pairs) to be used for action parameter binding
	 */
	public function getActionParams()
	{
		return array();
	}

	/**
	 * Validates the parameter being bound to actions.
	 * This method is invoked when parameters are being bound to the currently requested action.
	 * Child classes may override this method to throw exceptions when there are missing and/or unknown parameters.
	 * @param Action $action the currently requested action
	 * @param array $missingParams the names of the missing parameters
	 * @param array $unknownParams the unknown parameters (name=>value)
	 */
	public function validateActionParams($action, $missingParams, $unknownParams)
	{
	}

	/**
	 * @return string the controller ID that is prefixed with the module ID (if any).
	 */
	public function getUniqueId()
	{
		return $this->module instanceof Application ? $this->id : $this->module->getUniqueId() . '/' . $this->id;
	}

	/**
	 * Returns the route of the current request.
	 * @return string the route (module ID, controller ID and action ID) of the current request.
	 */
	public function getRoute()
	{
		return $this->action !== null ? $this->action->getUniqueId() : $this->getUniqueId();
	}

	/**
	 * Renders a view and applies layout if available.
	 *
	 * @param $view
	 * @param array $params
	 * @return string
	 */
	public function render($view, $params = array())
	{
		return $this->createView()->render($view, $params);
	}

	public function renderContent($content)
	{
		return $this->createView()->renderContent($content);
	}

	public function renderPartial($view, $params = array())
	{
		return $this->createView()->renderPartial($view, $params);
	}

	public function renderFile($file, $params = array())
	{
		return $this->createView()->renderFile($file, $params);
	}

	public function createView()
	{
		return new View($this);
	}

	/**
	 * Returns the directory containing view files for this controller.
	 * The default implementation returns the directory named as controller [[id]] under the [[module]]'s
	 * [[viewPath]] directory.
	 * @return string the directory containing the view files for this controller.
	 */
	public function getViewPath()
	{
		return $this->module->getViewPath() . DIRECTORY_SEPARATOR . $this->id;
	}
}
