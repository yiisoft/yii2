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

/**
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Generator extends \yii\gii\Generator
{
	public $controller;
	public $baseClass = 'yii\web\Controller';
	public $ns = 'app\controllers';
	public $actions = 'index';

	public function getName()
	{
		return 'Controller Generator';
	}

	public function getDescription()
	{
		return 'This generator helps you to quickly generate a new controller class,
			one or several controller actions and their corresponding views.';
	}

	public function rules()
	{
		return array_merge(parent::rules(), array(
			array('controller, actions, baseClass, ns', 'filter', 'filter' => 'trim'),
			array('controller, baseClass', 'required'),
			array('controller', 'match', 'pattern' => '/^[\w+\\/]*$/', 'message' => 'Only word characters and slashes are allowed.'),
			array('actions', 'match', 'pattern' => '/^\w+[\w\s,]*$/', 'message' => 'Only word characters, spaces and commas are allowed.'),
			array('baseClass', 'match', 'pattern' => '/^[a-zA-Z_][\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'),
			array('baseClass', 'validateReservedWord'),
			array('ns', 'match', 'pattern' => '/^[a-zA-Z_][\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'),
		));
	}

	public function attributeLabels()
	{
		return array(
			'baseClass' => 'Base Class',
			'controller' => 'Controller ID',
			'actions' => 'Action IDs',
			'ns' => 'Namespace',
		);
	}

	public function requiredTemplates()
	{
		return array(
			'controller.php',
			'view.php',
		);
	}

	public function stickyAttributes()
	{
		return array('ns', 'baseClass');
	}

	public function hints()
	{
		return array(
			'controller' => 'Controller ID should be in lower case and may contain module ID(s). For example:
				<ul>
					<li><code>order</code> generates <code>OrderController.php</code></li>
					<li><code>order-item</code> generates <code>OrderItemController.php</code></li>
					<li><code>admin/user</code> generates <code>UserController.php</code> within the <code>admin</code> module.</li>
				</ul>',
			'actions' => 'Provide one or multiple action IDs to generate empty action method(s) in the controller. Separate multiple action IDs with commas or spaces.',
			'ns' => 'This is the namespace that the new controller class will should use.',
			'baseClass' => 'This is the class that the new controller class will extend from. Please make sure the class exists and can be autoloaded.',
		);
	}

	public function successMessage()
	{
		$actions = $this->getActionIDs();
		if (in_array('index', $actions)) {
			$route = $this->controller . '/index';
		} else {
			$route = $this->controller . '/' . reset($actions);
		}
		$link = Html::a('try it now', Yii::$app->getUrlManager()->createUrl($route), array('target' => '_blank'));
		return "The controller has been generated successfully. You may $link.";
	}

	public function prepare()
	{
		$files = array();

		$templatePath = $this->getTemplatePath();

		$files[] = new CodeFile(
			$this->getControllerFile(),
			$this->generateCode($templatePath . '/controller.php')
		);

		foreach ($this->getActionIDs() as $action) {
			$files[] = new CodeFile(
				$this->getViewFile($action),
				$this->generateCode($templatePath . '/view.php', array('action' => $action))
			);
		}

		return $files;
	}

	public function getActionIDs()
	{
		$actions = array_unique(preg_split('/[\s,]+/', $this->actions, -1, PREG_SPLIT_NO_EMPTY));
		sort($actions);
		return $actions;
	}

	public function getControllerClass()
	{
		if (($pos = strrpos($this->controller, '/')) !== false) {
			return ucfirst(substr($this->controller, $pos + 1)) . 'Controller';
		} else {
			return ucfirst($this->controller) . 'Controller';
		}
	}

	public function getModule()
	{
		if (($pos = strpos($this->controller, '/')) !== false) {
			$id = substr($this->controller, 0, $pos);
			if (($module = Yii::$app->getModule($id)) !== null) {
				return $module;
			}
		}
		return Yii::$app;
	}

	public function getControllerID()
	{
		if ($this->getModule() !== Yii::$app) {
			$id = substr($this->controller, strpos($this->controller, '/') + 1);
		} else {
			$id = $this->controller;
		}
		if (($pos = strrpos($id, '/')) !== false) {
			$id[$pos + 1] = strtolower($id[$pos + 1]);
		} else {
			$id[0] = strtolower($id[0]);
		}
		return $id;
	}

	public function getControllerFile()
	{
		$module = $this->getModule();
		$id = $this->getControllerID();
		if (($pos = strrpos($id, '/')) !== false) {
			$id[$pos + 1] = strtoupper($id[$pos + 1]);
		} else {
			$id[0] = strtoupper($id[0]);
		}
		return $module->getControllerPath() . '/' . $id . 'Controller.php';
	}

	public function getViewFile($action)
	{
		$module=$this->getModule();
		return $module->getViewPath().'/'.$this->getControllerID().'/'.$action.'.php';
	}
}
