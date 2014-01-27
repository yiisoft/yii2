<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii\generators\controller;

use Yii;
use yii\gii\CodeFile;
use yii\helpers\Html;
use yii\helpers\Inflector;

/**
 * This generator will generate a controller and one or a few action view files.
 *
 * @property array $actionIDs An array of action IDs entered by the user. This property is read-only.
 * @property string $controllerClass The controller class name without the namespace part. This property is
 * read-only.
 * @property string $controllerFile The controller class file path. This property is read-only.
 * @property string $controllerID The controller ID (without the module ID prefix). This property is
 * read-only.
 * @property \yii\base\Module $module The module that the new controller belongs to. This property is
 * read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Generator extends \yii\gii\Generator
{
	/**
	 * @var string the controller ID
	 */
	public $controller;
	/**
	 * @var string the base class of the controller
	 */
	public $baseClass = 'yii\web\Controller';
	/**
	 * @var string the namespace of the controller class
	 */
	public $ns;
	/**
	 * @var string list of action IDs separated by commas or spaces
	 */
	public $actions = 'index';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		$this->ns = \Yii::$app->controllerNamespace;
	}

	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		return 'Controller Generator';
	}

	/**
	 * @inheritdoc
	 */
	public function getDescription()
	{
		return 'This generator helps you to quickly generate a new controller class,
			one or several controller actions and their corresponding views.';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return array_merge(parent::rules(), [
			[['controller', 'actions', 'baseClass', 'ns'], 'filter', 'filter' => 'trim'],
			[['controller', 'baseClass'], 'required'],
			[['controller'], 'match', 'pattern' => '/^[a-z\\-\\/]*$/', 'message' => 'Only a-z, dashes (-) and slashes (/) are allowed.'],
			[['actions'], 'match', 'pattern' => '/^[a-z\\-,\\s]*$/', 'message' => 'Only a-z, dashes (-), spaces and commas are allowed.'],
			[['baseClass'], 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'],
			[['ns'], 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'],
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'baseClass' => 'Base Class',
			'controller' => 'Controller ID',
			'actions' => 'Action IDs',
			'ns' => 'Controller Namespace',
		];
	}

	/**
	 * @inheritdoc
	 */
	public function requiredTemplates()
	{
		return [
			'controller.php',
			'view.php',
		];
	}

	/**
	 * @inheritdoc
	 */
	public function stickyAttributes()
	{
		return ['ns', 'baseClass'];
	}

	/**
	 * @inheritdoc
	 */
	public function hints()
	{
		return [
			'controller' => 'Controller ID should be in lower case and may contain module ID(s) separated by slashes. For example:
				<ul>
					<li><code>order</code> generates <code>OrderController.php</code></li>
					<li><code>order-item</code> generates <code>OrderItemController.php</code></li>
					<li><code>admin/user</code> generates <code>UserController.php</code> within the <code>admin</code> module.</li>
				</ul>',
			'actions' => 'Provide one or multiple action IDs to generate empty action method(s) in the controller. Separate multiple action IDs with commas or spaces.
				Action IDs should be in lower case. For example:
				<ul>
					<li><code>index</code> generates <code>actionIndex()</code></li>
					<li><code>create-order</code> generates <code>actionCreateOrder()</code></li>
				</ul>',
			'ns' => 'This is the namespace that the new controller class will use.',
			'baseClass' => 'This is the class that the new controller class will extend from. Please make sure the class exists and can be autoloaded.',
		];
	}

	/**
	 * @inheritdoc
	 */
	public function successMessage()
	{
		$actions = $this->getActionIDs();
		if (in_array('index', $actions)) {
			$route = $this->controller . '/index';
		} else {
			$route = $this->controller . '/' . reset($actions);
		}
		$link = Html::a('try it now', Yii::$app->getUrlManager()->createUrl($route), ['target' => '_blank']);
		return "The controller has been generated successfully. You may $link.";
	}

	/**
	 * @inheritdoc
	 */
	public function generate()
	{
		$files = [];

		$files[] = new CodeFile(
			$this->getControllerFile(),
			$this->render('controller.php')
		);

		foreach ($this->getActionIDs() as $action) {
			$files[] = new CodeFile(
				$this->getViewFile($action),
				$this->render('view.php', ['action' => $action])
			);
		}

		return $files;
	}

	/**
	 * Normalizes [[actions]] into an array of action IDs.
	 * @return array an array of action IDs entered by the user
	 */
	public function getActionIDs()
	{
		$actions = array_unique(preg_split('/[\s,]+/', $this->actions, -1, PREG_SPLIT_NO_EMPTY));
		sort($actions);
		return $actions;
	}

	/**
	 * @return string the controller class name without the namespace part.
	 */
	public function getControllerClass()
	{
		return Inflector::id2camel($this->getControllerID()) . 'Controller';
	}

	/**
	 * @return string the controller ID (without the module ID prefix)
	 */
	public function getControllerID()
	{
		if (($pos = strrpos($this->controller, '/')) !== false) {
			return substr($this->controller, $pos + 1);
		} else {
			return $this->controller;
		}
	}

	/**
	 * @return \yii\base\Module the module that the new controller belongs to
	 */
	public function getModule()
	{
		if (($pos = strrpos($this->controller, '/')) !== false) {
			$id = substr($this->controller, 0, $pos);
			if (($module = Yii::$app->getModule($id)) !== null) {
				return $module;
			}
		}
		return Yii::$app;
	}

	/**
	 * @return string the controller class file path
	 */
	public function getControllerFile()
	{
		$module = $this->getModule();
		return $module->getControllerPath() . '/' . $this->getControllerClass() . '.php';
	}

	/**
	 * @param string $action the action ID
	 * @return string the action view file path
	 */
	public function getViewFile($action)
	{
		$module = $this->getModule();
		return $module->getViewPath() . '/' . $this->getControllerID() . '/' . $action . '.php';
	}
}
