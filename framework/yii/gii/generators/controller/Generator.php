<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii\generators\controller;

use Yii;

/**
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Generator extends \yii\gii\Generator
{
	public $controller;
	public $baseClass = 'yii\web\Controller';
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

	public function renderForm()
	{
		return Yii::$app->getView()->renderFile(__DIR__ . '/views/form.php', array(
			'model' => $this,
		));
	}

	public function rules()
	{
		return array_merge(parent::rules(), array(
			array('controller, actions, baseClass', 'filter', 'filter' => 'trim'),
			array('controller, baseClass', 'required'),
			array('controller', 'match', 'pattern' => '/^[\w+\\/]*$/', 'message' => '{attribute} should only contain word characters and slashes.'),
			array('actions', 'match', 'pattern' => '/^\w+[\w\s,]*$/', 'message' => '{attribute} should only contain word characters, spaces and commas.'),
			array('baseClass', 'match', 'pattern' => '/^[a-zA-Z_][\w\\\\]*$/', 'message' => '{attribute} should only contain word characters and backslashes.'),
			array('baseClass', 'validateReservedWord', 'skipOnError' => true),
			array('baseClass, actions', 'sticky'),
		));
	}

	public function attributeLabels()
	{
		return array_merge(parent::attributeLabels(), array(
			'baseClass' => 'Base Class',
			'controller' => 'Controller ID',
			'actions' => 'Action IDs',
		));
	}

	public function requiredTemplates()
	{
		return array(
			'controller.php',
			'view.php',
		);
	}

	public function successMessage()
	{
		$link = CHtml::link('try it now', Yii::app()->createUrl($this->controller), array('target' => '_blank'));
		return "The controller has been generated successfully. You may $link.";
	}

	public function prepare()
	{
		$this->files = array();
		$templatePath = $this->templatePath;

		$this->files[] = new CCodeFile(
			$this->controllerFile,
			$this->render($templatePath . '/controller.php')
		);

		foreach ($this->getActionIDs() as $action) {
			$this->files[] = new CCodeFile(
				$this->getViewFile($action),
				$this->render($templatePath . '/view.php', array('action' => $action))
			);
		}
	}

	public function getActionIDs()
	{
		$actions = preg_split('/[\s,]+/', $this->actions, -1, PREG_SPLIT_NO_EMPTY);
		$actions = array_unique($actions);
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
			if (($module = Yii::app()->getModule($id)) !== null) {
				return $module;
			}
		}
		return Yii::app();
	}

	public function getControllerID()
	{
		if ($this->getModule() !== Yii::app()) {
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

	public function getUniqueControllerID()
	{
		$id = $this->controller;
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
		$module = $this->getModule();
		return $module->getViewPath() . '/' . $this->getControllerID() . '/' . $action . '.php';
	}
}
