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
	 * @var string the name of the default action. Defaults to 'index'.
	 */
	public $defaultAction = 'index';

	private $_id;
	private $_action;
	private $_module;


	/**
	 * @param string $id id of this controller
	 * @param CWebModule $module the module that this controller belongs to.
	 */
	public function __construct($id, $module = null)
	{
		$this->_id = $id;
		$this->_module = $module;
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
	 * Returns the filter configurations.
	 *
	 * By overriding this method, child classes can specify filters to be applied to actions.
	 *
	 * This method returns an array of filter specifications. Each array element specify a single filter.
	 *
	 * For a method-based filter (called inline filter), it is specified as 'FilterName[ +|- Action1, Action2, ...]',
	 * where the '+' ('-') operators describe which actions should be (should not be) applied with the filter.
	 *
	 * For a class-based filter, it is specified as an array like the following:
	 * <pre>
	 * array(
	 *	 'FilterClass[ +|- Action1, Action2, ...]',
	 *	 'name1'=>'value1',
	 *	 'name2'=>'value2',
	 *	 ...
	 * )
	 * </pre>
	 * where the name-value pairs will be used to initialize the properties of the filter.
	 *
	 * Note, in order to inherit filters defined in the parent class, a child class needs to
	 * merge the parent filters with child filters using functions like array_merge().
	 *
	 * @return array a list of filter configurations.
	 * @see CFilter
	 */
	public function filters()
	{
		return array();
	}

	/**
	 * Returns a list of external action classes.
	 * Array keys are action IDs, and array values are the corresponding
	 * action class in dot syntax (e.g. 'edit'=>'application.controllers.article.EditArticle')
	 * or arrays representing the configuration of the actions, such as the following,
	 * <pre>
	 * return array(
	 *	 'action1'=>'path.to.Action1Class',
	 *	 'action2'=>array(
	 *		 'class'=>'path.to.Action2Class',
	 *		 'property1'=>'value1',
	 *		 'property2'=>'value2',
	 *	 ),
	 * );
	 * </pre>
	 * Derived classes may override this method to declare external actions.
	 *
	 * Note, in order to inherit actions defined in the parent class, a child class needs to
	 * merge the parent actions with child actions using functions like array_merge().
	 *
	 * You may import actions from an action provider
	 * (such as a widget, see {@link CWidget::actions}), like the following:
	 * <pre>
	 * return array(
	 *	 ...other actions...
	 *	 // import actions declared in ProviderClass::actions()
	 *	 // the action IDs will be prefixed with 'pro.'
	 *	 'pro.'=>'path.to.ProviderClass',
	 *	 // similar as above except that the imported actions are
	 *	 // configured with the specified initial property values
	 *	 'pro2.'=>array(
	 *		 'class'=>'path.to.ProviderClass',
	 *		 'action1'=>array(
	 *			 'property1'=>'value1',
	 *		 ),
	 *		 'action2'=>array(
	 *			 'property2'=>'value2',
	 *		 ),
	 *	 ),
	 * )
	 * </pre>
	 *
	 * In the above, we differentiate action providers from other action
	 * declarations by the array keys. For action providers, the array keys
	 * must contain a dot. As a result, an action ID 'pro2.action1' will
	 * be resolved as the 'action1' action declared in the 'ProviderClass'.
	 *
	 * @return array list of external action classes
	 * @see createAction
	 */
	public function actions()
	{
		return array();
	}

	/**
	 * Returns the access rules for this controller.
	 * Override this method if you use the {@link filterAccessControl accessControl} filter.
	 * @return array list of access rules. See {@link CAccessControlFilter} for details about rule specification.
	 */
	public function accessRules()
	{
		return array();
	}

	/**
	 * Runs the named action.
	 * Filters specified via {@link filters()} will be applied.
	 * @param string $actionID action ID
	 * @throws CHttpException if the action does not exist or the action name is not proper.
	 * @see filters
	 * @see createAction
	 * @see runAction
	 */
	public function run($actionID)
	{
		if (($action = $this->createAction($actionID)) !== null) {
			if (($parent = $this->getModule()) === null) {
				$parent = Yii::app();
			}
			if ($parent->beforeControllerAction($this, $action)) {
				$this->runActionWithFilters($action, $this->filters());
				$parent->afterControllerAction($this, $action);
			}
		}
		else
		{
			$this->missingAction($actionID);
		}
	}

	/**
	 * Runs an action with the specified filters.
	 * A filter chain will be created based on the specified filters
	 * and the action will be executed then.
	 * @param Action $action the action to be executed.
	 * @param array $filters list of filters to be applied to the action.
	 * @see filters
	 * @see createAction
	 * @see runAction
	 */
	public function runActionWithFilters($action, $filters)
	{
		if (empty($filters)) {
			$this->runAction($action);
		}
		else
		{
			$priorAction = $this->_action;
			$this->_action = $action;
			CFilterChain::create($this, $action, $filters)->run();
			$this->_action = $priorAction;
		}
	}

	/**
	 * Runs the action after passing through all filters.
	 * This method is invoked by {@link runActionWithFilters} after all possible filters have been executed
	 * and the action starts to run.
	 * @param Action $action action to run
	 */
	public function runAction($action)
	{
		$priorAction = $this->_action;
		$this->_action = $action;
		if ($this->beforeAction($action)) {
			$params = $action->normalizeParams($this->getActionParams());
			if ($params === false) {
				$this->invalidActionParams($action);
			} else {
				call_user_func_array(array($action, 'run'), $params);
				$this->afterAction($action);
			}
		}
		$this->_action = $priorAction;
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
	 * This method is invoked when the request parameters do not satisfy the requirement of the specified action.
	 * The default implementation will throw a 400 HTTP exception.
	 * @param Action $action the action being executed
	 * @throws HttpException a 400 HTTP exception
	 */
	public function invalidActionParams($action)
	{
		throw new HttpException(400, \Yii::t('yii', 'Your request is invalid.'));
	}

	/**
	 * Creates the action instance based on the action name.
	 * The action can be either an inline action or an object.
	 * The latter is created by looking up the action map specified in {@link actions}.
	 * @param string $actionID ID of the action. If empty, the {@link defaultAction default action} will be used.
	 * @return Action the action instance, null if the action does not exist.
	 * @see actions
	 */
	public function createAction($actionID)
	{
		if ($actionID === '') {
			$actionID = $this->defaultAction;
		}
		if (method_exists($this, 'action' . $actionID) && strcasecmp($actionID, 's')) // we have actions method
		{
			return new CInlineAction($this, $actionID);
		}
		else
		{
			$action = $this->createActionFromMap($this->actions(), $actionID, $actionID);
			if ($action !== null && !method_exists($action, 'run')) {
				throw new CException(Yii::t('yii', 'Action class {class} must implement the "run" method.', array('{class}' => get_class($action))));
			}
			return $action;
		}
	}

	/**
	 * Creates the action instance based on the action map.
	 * This method will check to see if the action ID appears in the given
	 * action map. If so, the corresponding configuration will be used to
	 * create the action instance.
	 * @param array $actionMap the action map
	 * @param string $actionID the action ID that has its prefix stripped off
	 * @param string $requestActionID the originally requested action ID
	 * @param array $config the action configuration that should be applied on top of the configuration specified in the map
	 * @return Action the action instance, null if the action does not exist.
	 */
	protected function createActionFromMap($actionMap, $actionID, $requestActionID, $config = array())
	{
		if (($pos = strpos($actionID, '.')) === false && isset($actionMap[$actionID])) {
			$baseConfig = is_array($actionMap[$actionID]) ? $actionMap[$actionID] : array('class' => $actionMap[$actionID]);
			return Yii::createComponent(empty($config) ? $baseConfig : array_merge($baseConfig, $config), $this, $requestActionID);
		}
		else {
			if ($pos === false) {
				return null;
			}
		}

		// the action is defined in a provider
		$prefix = substr($actionID, 0, $pos + 1);
		if (!isset($actionMap[$prefix])) {
			return null;
		}
		$actionID = (string)substr($actionID, $pos + 1);

		$provider = $actionMap[$prefix];
		if (is_string($provider)) {
			$providerType = $provider;
		}
		else {
			if (is_array($provider) && isset($provider['class'])) {
				$providerType = $provider['class'];
				if (isset($provider[$actionID])) {
					if (is_string($provider[$actionID])) {
						$config = array_merge(array('class' => $provider[$actionID]), $config);
					}
					else
					{
						$config = array_merge($provider[$actionID], $config);
					}
				}
			}
			else
			{
				throw new CException(Yii::t('yii', 'Object configuration must be an array containing a "class" element.'));
			}
		}

		$class = Yii::import($providerType, true);
		$map = call_user_func(array($class, 'actions'));

		return $this->createActionFromMap($map, $actionID, $requestActionID, $config);
	}

	/**
	 * Handles the request whose action is not recognized.
	 * This method is invoked when the controller cannot find the requested action.
	 * The default implementation simply throws an exception.
	 * @param string $actionID the missing action name
	 * @throws CHttpException whenever this method is invoked
	 */
	public function missingAction($actionID)
	{
		throw new CHttpException(404, Yii::t('yii', 'The system is unable to find the requested action "{action}".',
			array('{action}' => $actionID == '' ? $this->defaultAction : $actionID)));
	}

	/**
	 * @return Action the action currently being executed, null if no active action.
	 */
	public function getAction()
	{
		return $this->_action;
	}

	/**
	 * @param Action $value the action currently being executed.
	 */
	public function setAction($value)
	{
		$this->_action = $value;
	}

	/**
	 * @return string ID of the controller
	 */
	public function getId()
	{
		return $this->_id;
	}

	/**
	 * @return string the controller ID that is prefixed with the module ID (if any).
	 */
	public function getUniqueId()
	{
		return $this->_module ? $this->_module->getId() . '/' . $this->_id : $this->_id;
	}

	/**
	 * @return string the route (module ID, controller ID and action ID) of the current request.
	 * @since 1.1.0
	 */
	public function getRoute()
	{
		if (($action = $this->getAction()) !== null) {
			return $this->getUniqueId() . '/' . $action->getId();
		}
		else
		{
			return $this->getUniqueId();
		}
	}

	/**
	 * @return CWebModule the module that this controller belongs to. It returns null
	 * if the controller does not belong to any module
	 */
	public function getModule()
	{
		return $this->_module;
	}

	/**
	 * Processes the request using another controller action.
	 * This is like {@link redirect}, but the user browser's URL remains unchanged.
	 * In most cases, you should call {@link redirect} instead of this method.
	 * @param string $route the route of the new controller action. This can be an action ID, or a complete route
	 * with module ID (optional in the current module), controller ID and action ID. If the former, the action is assumed
	 * to be located within the current controller.
	 * @param boolean $exit whether to end the application after this call. Defaults to true.
	 * @since 1.1.0
	 */
	public function forward($route, $exit = true)
	{
		if (strpos($route, '/') === false) {
			$this->run($route);
		}
		else
		{
			if ($route[0] !== '/' && ($module = $this->getModule()) !== null) {
				$route = $module->getId() . '/' . $route;
			}
			Yii::app()->runController($route);
		}
		if ($exit) {
			Yii::app()->end();
		}
	}

	/**
	 * This method is invoked right before an action is to be executed (after all possible filters.)
	 * You may override this method to do last-minute preparation for the action.
	 * @param Action $action the action to be executed.
	 * @return boolean whether the action should be executed.
	 */
	protected function beforeAction($action)
	{
		return true;
	}

	/**
	 * This method is invoked right after an action is executed.
	 * You may override this method to do some postprocessing for the action.
	 * @param Action $action the action just executed.
	 */
	protected function afterAction($action)
	{
	}
}
