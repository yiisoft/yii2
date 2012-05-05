<?php
/**
 * Controller class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Controller is the base class for {@link CController} and {@link CWidget}.
 *
 * It provides the common functionalities shared by controllers who need to render views.
 *
 * Controller also implements the support for the following features:
 * <ul>
 * <li>{@link CClipWidget Clips} : a clip is a piece of captured output that can be inserted elsewhere.</li>
 * <li>{@link CWidget Widgets} : a widget is a self-contained sub-controller with its own view and model.</li>
 * <li>{@link COutputCache Fragment cache} : fragment cache selectively caches a portion of the output.</li>
 * </ul>
 *
 * To use a widget in a view, use the following in the view:
 * <pre>
 * $this->widget('path.to.widgetClass',array('property1'=>'value1',...));
 * </pre>
 * or
 * <pre>
 * $this->beginWidget('path.to.widgetClass',array('property1'=>'value1',...));
 * // ... display other contents here
 * $this->endWidget();
 * </pre>
 *
 * To create a clip, use the following:
 * <pre>
 * $this->beginClip('clipID');
 * // ... display the clip contents
 * $this->endClip();
 * </pre>
 * Then, in a different view or place, the captured clip can be inserted as:
 * <pre>
 * echo $this->clips['clipID'];
 * </pre>
 *
 * Note that $this in the code above refers to current controller so, for example,
 * if you need to access clip from a widget where $this refers to widget itself
 * you need to do it the following way:
 *
 * <pre>
 * echo $this->getController()->clips['clipID'];
 * </pre>
 *
 * To use fragment cache, do as follows,
 * <pre>
 * if($this->beginCache('cacheID',array('property1'=>'value1',...))
 * {
 *	 // ... display the content to be cached here
 *	$this->endCache();
 * }
 * </pre>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class Controller extends Component implements Initable
{
	/**
	 * @var string ID of this controller
	 */
	public $id;
	/**
	 * @var Module $module the module that this controller belongs to.
	 */
	public $module;
	/**
	 * @var string the name of the default action. Defaults to 'index'.
	 */
	public $defaultAction = 'index';
	/**
	 * @var Action the action that is currently being executed
	 */
	public $action;

	/**
	 * @param string $id ID of this controller
	 * @param Module $module the module that this controller belongs to.
	 */
	public function __construct($id, $module)
	{
		$this->id = $id;
		$this->module = $module;
	}

	/**
	 * Initializes the controller.
	 * This method is called by the application before the controller starts to execute.
	 * You may override this method to perform the needed initialization for the controller.
	 */
	public function init()
	{
	}

	/**
	 * Returns a list of external action classes.
	 * Array keys are action IDs, and array values are the corresponding
	 * action class names or action configuration arrays. For example,
	 *
	 * ~~~
	 * return array(
	 *     'action1'=>'@application/components/Action1',
	 *     'action2'=>array(
	 *         'class'=>'@application/components/Action2',
	 *         'property1'=>'value1',
	 *         'property2'=>'value2',
	 *     ),
	 * );
	 * ~~~
	 *
	 * [[\Yii::createObject()]] will be invoked to create the requested action
	 * using the configuration provided here.
	 *
	 * Derived classes may override this method to declare external actions.
	 *
	 * Note, in order to inherit actions defined in the parent class, a child class needs to
	 * merge the parent actions with child actions using functions like `array_merge()`.
	 *
	 * @return array list of external action classes
	 * @see createAction
	 */
	public function actions()
	{
		return array();
	}

	/**
	 * Creates an action with the specified ID and runs it.
	 * If the action does not exist, [[missingAction()]] will be invoked.
	 * @param string $actionID action ID
	 * @return integer the exit status of the action. 0 means normal, other values mean abnormal.
	 * @see createAction
	 * @see runAction
	 * @see missingAction
	 */
	public function run($actionID)
	{
		if (($action = $this->createAction($actionID)) !== null) {
			return $this->runAction($action);
		} else {
			$this->missingAction($actionID);
			return 1;
		}
	}

	/**
	 * Runs the action.
	 * @param Action $action action to run
	 * @return integer the exit status of the action. 0 means normal, other values mean abnormal.
	 */
	public function runAction($action)
	{
		$priorAction = $this->action;
		$this->action = $action;
		$exitStatus = 1;
		if ($this->authorize($action)) {
			$params = $action->normalizeParams($this->getActionParams());
			if ($params !== false) {
				if ($this->beforeAction($action)) {
					$exitStatus = (int)call_user_func_array(array($action, 'run'), $params);
					$this->afterAction($action);
				}
			} else {
				$this->invalidActionParams($action);
			}
		}
		$this->action = $priorAction;
		return $exitStatus;
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
	 * Creates the action instance based on the action ID.
	 * The action can be either an inline action or an object.
	 * The latter is created by looking up the action map specified in [[actions]].
	 * @param string $actionID ID of the action. If empty, it will take the value of [[defaultAction]].
	 * @return Action the action instance, null if the action does not exist.
	 * @see actions
	 */
	public function createAction($actionID)
	{
		if ($actionID === '') {
			$actionID = $this->defaultAction;
		}
		if (method_exists($this, 'action' . $actionID) && strcasecmp($actionID, 's')) {
			return new InlineAction($actionID, $this);
		} else {
			$actions = $this->actions();
			if (isset($actions[$actionID])) {
				return \Yii::createObject($actions[$actionID], $actionID, $this);
			}
		}
		return null;
	}

	/**
	 * This method is invoked when the request parameters do not satisfy the requirement of the specified action.
	 * The default implementation will throw an exception.
	 * @param Action $action the action being executed
	 * @throws Exception whenever this method is invoked
	 */
	public function invalidActionParams($action)
	{
		throw new Exception(\Yii::t('yii', 'Your request is invalid.'));
	}

	/**
	 * Handles the request whose action is not recognized.
	 * This method is invoked when the controller cannot find the requested action.
	 * The default implementation simply throws an exception.
	 * @param string $actionID the missing action name
	 * @throws Exception whenever this method is invoked
	 */
	public function missingAction($actionID)
	{
		throw new Exception(\Yii::t('yii', 'The system is unable to find the requested action "{action}".',
			array('{action}' => $actionID == '' ? $this->defaultAction : $actionID)));
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
		return $this->action !== null ? $this->getUniqueId() . '/' . $this->action->id : $this->getUniqueId();
	}

	/**
	 * Processes the request using another controller action.
	 * This is like {@link redirect}, but the user browser's URL remains unchanged.
	 * In most cases, you should call {@link redirect} instead of this method.
	 * @param string $route the route of the new controller action. This can be an action ID, or a complete route
	 * with module ID (optional in the current module), controller ID and action ID. If the former, the action is assumed
	 * to be located within the current controller.
	 * @param boolean $exit whether to end the application after this call. Defaults to true.
	 */
	public function forward($route, $exit = true)
	{
		if (strpos($route, '/') === false) {
			$status = $this->run($route);
		} else {
			if ($route[0] !== '/' && !$this->module instanceof Application) {
				$route = '/' . $this->module->getUniqueId() . '/' . $route;
			}
			$status = \Yii::$application->runController($route);
		}
		if ($exit) {
			\Yii::$application->end($status);
		}
	}

	/**
	 * This method is invoked when checking the access for the action to be executed.
	 * @param Action $action the action to be executed.
	 * @return boolean whether the action is allowed to be executed.
	 */
	public function authorize(Action $action)
	{
		$event = new ActionEvent($action);
		$this->trigger(__METHOD__, $event);
		return $event->isValid;
	}

	/**
	 * This method is invoked right before an action is to be executed (after all possible filters.)
	 * You may override this method to do last-minute preparation for the action.
	 * @param Action $action the action to be executed.
	 * @return boolean whether the action should continue to be executed.
	 */
	public function beforeAction(Action $action)
	{
		$event = new ActionEvent($action);
		$this->trigger(__METHOD__, $event);
		return $event->isValid;
	}

	/**
	 * This method is invoked right after an action is executed.
	 * You may override this method to do some postprocessing for the action.
	 * @param Action $action the action just executed.
	 */
	public function afterAction(Action $action)
	{
		$event = new ActionEvent($action);
		$this->trigger(__METHOD__, $event);
	}

	/**
	 * This method is invoked right before an action renders its result using [[render()]].
	 * @param Action $action the action to be executed.
	 * @return boolean whether the action should continue to render.
	 */
	public function beforeRender(Action $action)
	{
		$event = new ActionEvent($action);
		$this->trigger(__METHOD__, $event);
		return $event->isValid;
	}

	/**
	 * This method is invoked right after an action renders its result using [[render()]].
	 * @param Action $action the action just executed.
	 */
	public function afterRender(Action $action)
	{
		$event = new ActionEvent($action);
		$this->trigger(__METHOD__, $event);
	}
}
