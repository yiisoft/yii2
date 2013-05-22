<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * Controller is the base class for classes containing controller logic.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Controller extends Component
{
	/**
	 * @event ActionEvent an event raised right before executing a controller action.
	 * You may set [[ActionEvent::isValid]] to be false to cancel the action execution.
	 */
	const EVENT_BEFORE_ACTION = 'beforeAction';
	/**
	 * @event ActionEvent an event raised right after executing a controller action.
	 */
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
	 * @var View the view object that can be used to render views or view files.
	 */
	private $_view;


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
			$status = 1;
			if ($this->module->beforeAction($action)) {
				if ($this->beforeAction($action)) {
					$status = $action->runWithParams($params);
					$this->afterAction($action);
				}
				$this->module->afterAction($action);
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
			return Yii::$app->runAction(ltrim($route, '/'), $params);
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

		if (!empty($missing)) {
			throw new InvalidRequestException(Yii::t('yii', 'Missing required parameters: {params}', array(
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
		Yii::$app->end($status);
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
			$methodName = 'action' . str_replace(' ', '', ucwords(implode(' ', explode('-', $id))));
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
	 * @param array $unknownParams the unknown parameters (name => value)
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
	 * Populates one or multiple models from the given data array.
	 * @param array $data the data array. This is usually `$_POST` or `$_GET`, but can also be any valid array.
	 * @param Model $model the model to be populated. If there are more than one model to be populated,
	 * you may supply them as additional parameters.
	 * @return boolean whether at least one model is successfully populated with the data.
	 */
	public function populate($data, $model)
	{
		$success = false;
		if (!empty($data) && is_array($data)) {
			$models = func_get_args();
			array_shift($models);
			foreach ($models as $model) {
				/** @var Model $model */
				$scope = $model->formName();
				if ($scope == '') {
					$model->setAttributes($data);
					$success = true;
				} elseif (isset($data[$scope])) {
					$model->setAttributes($data[$scope]);
					$success = true;
				}
			}
		}
		return $success;
	}

	/**
	 * Renders a view and applies layout if available.
	 *
	 * The view to be rendered can be specified in one of the following formats:
	 *
	 * - path alias (e.g. "@app/views/site/index");
	 * - absolute path within application (e.g. "//site/index"): the view name starts with double slashes.
	 *   The actual view file will be looked for under the [[Application::viewPath|view path]] of the application.
	 * - absolute path within module (e.g. "/site/index"): the view name starts with a single slash.
	 *   The actual view file will be looked for under the [[Module::viewPath|view path]] of [[module]].
	 * - relative path (e.g. "index"): the actual view file will be looked for under [[viewPath]].
	 *
	 * To determine which layout should be applied, the following two steps are conducted:
	 *
	 * 1. In the first step, it determines the layout name and the context module:
	 *
	 * - If [[layout]] is specified as a string, use it as the layout name and [[module]] as the context module;
	 * - If [[layout]] is null, search through all ancestor modules of this controller and find the first
	 *   module whose [[Module::layout|layout]] is not null. The layout and the corresponding module
	 *   are used as the layout name and the context module, respectively. If such a module is not found
	 *   or the corresponding layout is not a string, it will return false, meaning no applicable layout.
	 *
	 * 2. In the second step, it determines the actual layout file according to the previously found layout name
	 *    and context module. The layout name can be
	 *
	 * - a path alias (e.g. "@app/views/layouts/main");
	 * - an absolute path (e.g. "/main"): the layout name starts with a slash. The actual layout file will be
	 *   looked for under the [[Application::layoutPath|layout path]] of the application;
	 * - a relative path (e.g. "main"): the actual layout layout file will be looked for under the
	 *   [[Module::viewPath|view path]] of the context module.
	 *
	 * If the layout name does not contain a file extension, it will use the default one `.php`.
	 *
	 * @param string $view the view name. Please refer to [[findViewFile()]] on how to specify a view name.
	 * @param array $params the parameters (name-value pairs) that should be made available in the view.
	 * These parameters will not be available in the layout.
	 * @return string the rendering result.
	 * @throws InvalidParamException if the view file or the layout file does not exist.
	 */
	public function render($view, $params = array())
	{
		$viewFile = $this->findViewFile($view);
		$output = $this->getView()->renderFile($viewFile, $params, $this);
		$layoutFile = $this->findLayoutFile();
		if ($layoutFile !== false) {
			return $this->getView()->renderFile($layoutFile, array('content' => $output), $this);
		} else {
			return $output;
		}
	}

	/**
	 * Renders a view.
	 * This method differs from [[render()]] in that it does not apply any layout.
	 * @param string $view the view name. Please refer to [[render()]] on how to specify a view name.
	 * @param array $params the parameters (name-value pairs) that should be made available in the view.
	 * @return string the rendering result.
	 * @throws InvalidParamException if the view file does not exist.
	 */
	public function renderPartial($view, $params = array())
	{
		$viewFile = $this->findViewFile($view);
		return $this->getView()->renderFile($viewFile, $params, $this);
	}

	/**
	 * Renders a view file.
	 * @param string $file the view file to be rendered. This can be either a file path or a path alias.
	 * @param array $params the parameters (name-value pairs) that should be made available in the view.
	 * @return string the rendering result.
	 * @throws InvalidParamException if the view file does not exist.
	 */
	public function renderFile($file, $params = array())
	{
		return $this->getView()->renderFile($file, $params, $this);
	}

	/**
	 * Returns the view object that can be used to render views or view files.
	 * The [[render()]], [[renderPartial()]] and [[renderFile()]] methods will use
	 * this view object to implement the actual view rendering.
	 * If not set, it will default to the "view" application component.
	 * @return View the view object that can be used to render views or view files.
	 */
	public function getView()
	{
		if ($this->_view === null) {
			$this->_view = Yii::$app->getView();
		}
		return $this->_view;
	}

	/**
	 * Sets the view object to be used by this controller.
	 * @param View $view the view object that can be used to render views or view files.
	 */
	public function setView($view)
	{
		$this->_view = $view;
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

	/**
	 * Finds the view file based on the given view name.
	 * @param string $view the view name or the path alias of the view file. Please refer to [[render()]]
	 * on how to specify this parameter.
	 * @return string the view file path. Note that the file may not exist.
	 */
	protected function findViewFile($view)
	{
		if (strncmp($view, '@', 1) === 0) {
			// e.g. "@app/views/main"
			$file = Yii::getAlias($view);
		} elseif (strncmp($view, '//', 2) === 0) {
			// e.g. "//layouts/main"
			$file = Yii::$app->getViewPath() . DIRECTORY_SEPARATOR . ltrim($view, '/');
		} elseif (strncmp($view, '/', 1) === 0) {
			// e.g. "/site/index"
			$file = $this->module->getViewPath() . DIRECTORY_SEPARATOR . ltrim($view, '/');
		} else {
			$file = $this->getViewPath() . DIRECTORY_SEPARATOR . $view;
		}

		return pathinfo($file, PATHINFO_EXTENSION) === '' ? $file . '.php' : $file;
	}

	/**
	 * Finds the applicable layout file.
	 * @return string|boolean the layout file path, or false if layout is not needed.
	 * Please refer to [[render()]] on how to specify this parameter.
	 * @throws InvalidParamException if an invalid path alias is used to specify the layout
	 */
	protected function findLayoutFile()
	{
		$module = $this->module;
		if (is_string($this->layout)) {
			$view = $this->layout;
		} elseif ($this->layout === null) {
			while ($module !== null && $module->layout === null) {
				$module = $module->module;
			}
			if ($module !== null && is_string($module->layout)) {
				$view = $module->layout;
			}
		}

		if (!isset($view)) {
			return false;
		}

		if (strncmp($view, '@', 1) === 0) {
			$file = Yii::getAlias($view);
		} elseif (strncmp($view, '/', 1) === 0) {
			$file = Yii::$app->getLayoutPath() . DIRECTORY_SEPARATOR . $view;
		} else {
			$file = $module->getLayoutPath() . DIRECTORY_SEPARATOR . $view;
		}

		if (pathinfo($file, PATHINFO_EXTENSION) === '') {
			$file .= '.php';
		}
		return $file;
	}
}
