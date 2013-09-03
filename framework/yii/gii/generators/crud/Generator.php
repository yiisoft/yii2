<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii\generators\crud;

use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\gii\CodeFile;
use yii\helpers\Inflector;
use yii\web\Controller;

/**
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Generator extends \yii\gii\Generator
{
	public $modelClass;
	public $controllerID;
	public $baseControllerClass = 'yii\web\Controller';
	public $indexWidgetType = 'grid';
	public $enableSearch = true;
	public $searchModelClass;

	public function getName()
	{
		return 'CRUD Generator';
	}

	public function getDescription()
	{
		return 'This generator generates a controller and views that implement CRUD (Create, Read, Update, Delete)
			operations for the specified data model.';
	}

	public function rules()
	{
		return array_merge(parent::rules(), array(
			array('modelClass, searchModelClass, controllerID, baseControllerClass', 'filter', 'filter' => 'trim'),
			array('modelClass, controllerID, baseControllerClass', 'required'),
			array('modelClass, searchModelClass', 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'),
			array('modelClass', 'validateClass', 'params' => array('extends' => ActiveRecord::className())),
			array('controllerID', 'match', 'pattern' => '/^[a-z\\-\\/]*$/', 'message' => 'Only a-z, dashes (-) and slashes (/) are allowed.'),
			array('baseControllerClass', 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'),
			array('baseControllerClass', 'validateClass', 'params' => array('extends' => Controller::className())),
			array('enableSearch', 'boolean'),
			array('indexWidgetType', 'in', 'range' => array('grid', 'list')),
			array('searchModelClass', 'validateSearchModelClass'),
		));
	}

	public function attributeLabels()
	{
		return array_merge(parent::attributeLabels(), array(
			'modelClass' => 'Model Class',
			'controllerID' => 'Controller ID',
			'baseControllerClass' => 'Base Controller Class',
			'indexWidgetType' => 'Widget Used in Index Page',
			'enableSearch' => 'Enable Search',
			'searchModelClass' => 'Search Model Class',
		));
	}

	/**
	 * @inheritdoc
	 */
	public function hints()
	{
		return array(
			'modelClass' => 'This is the ActiveRecord class associated with the table that CRUD will be built upon.
				You should provide a fully qualified class name, e.g., <code>app\models\Post</code>.',
			'controllerID' => 'CRUD controllers are often named after the model class name that they are dealing with.
				Controller ID should be in lower case and may contain module ID(s) separated by slashes. For example:
 				<ul>
 					<li><code>order</code> generates <code>OrderController.php</code></li>
 					<li><code>order-item</code> generates <code>OrderItemController.php</code></li>
 					<li><code>admin/user</code> generates <code>UserController.php</code> within the <code>admin</code> module.</li>
				</ul>',
			'baseControllerClass' => 'This is the class that the new CRUD controller class will extend from.
				You should provide a fully qualified class name, e.g., <code>yii\web\Controller</code>.',
			'indexWidgetType' => 'This is the widget type to be used in the index page to display list of the models.
				You may choose either <code>GridView</code> or <code>ListView</code>',
			'enableSearch' => 'Whether to enable the search functionality on the index page. When search is enabled,
				a search form will be displayed on the index page, and the index page will display the search results.',
			'searchModelClass' => 'This is the class representing the data being collecting in the search form.
			 	A fully qualified namespaced class name is required, e.g., <code>app\models\PostSearchForm</code>.
				This is only used when search is enabled.',
		);
	}

	public function requiredTemplates()
	{
		return array(
			'controller.php',
		);
	}

	/**
	 * @inheritdoc
	 */
	public function stickyAttributes()
	{
		return array('baseControllerClass', 'indexWidgetType', 'enableSearch');
	}

	public function validateSearchModelClass()
	{
		if ($this->enableSearch && empty($this->searchModelClass)) {
			$this->addError('searchModelClass', 'Search Model Class cannot be empty.');
		}
	}

	/**
	 * @inheritdoc
	 */
	public function generate()
	{
		$files = array();
		$files[] = new CodeFile(
			$this->getControllerFile(),
			$this->render('controller.php')
		);
		$viewPath = $this->getViewPath();

		$templatePath = $this->getTemplatePath() . '/views';
		foreach (scandir($templatePath) as $file) {
			if (is_file($templatePath . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
				$files[] = new CodeFile("$viewPath/$file", $this->render("views/$file"));
			}
		}

		if ($this->enableSearch) {

		}

		return $files;
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
		if (($pos = strrpos($this->controllerID, '/')) !== false) {
			return substr($this->controllerID, $pos + 1);
		} else {
			return $this->controllerID;
		}
	}

	/**
	 * @return \yii\base\Module the module that the new controller belongs to
	 */
	public function getModule()
	{
		if (($pos = strpos($this->controllerID, '/')) !== false) {
			$id = substr($this->controllerID, 0, $pos);
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
	 * @return string the action view file path
	 */
	public function getViewPath()
	{
		$module = $this->getModule();
		return $module->getViewPath() . '/' . $this->getControllerID() ;
	}
}
