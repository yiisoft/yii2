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
	public $moduleID;
	public $controllerClass;
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
			array('moduleID, controllerClass, modelClass, searchModelClass, baseControllerClass', 'filter', 'filter' => 'trim'),
			array('modelClass, controllerClass, baseControllerClass, indexWidgetType', 'required'),
			array('modelClass, controllerClass, baseControllerClass, searchModelClass', 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'),
			array('modelClass', 'validateClass', 'params' => array('extends' => ActiveRecord::className())),
			array('baseControllerClass', 'validateClass', 'params' => array('extends' => Controller::className())),
			array('controllerClass', 'match', 'pattern' => '/Controller$/', 'message' => 'Controller class name must be suffixed with "Controller".'),
			array('controllerClass, searchModelClass', 'validateNewClass'),
			array('enableSearch', 'boolean'),
			array('indexWidgetType', 'in', 'range' => array('grid', 'list')),
			array('modelClass', 'validateModelClass'),
			array('searchModelClass', 'validateSearchModelClass'),
			array('moduleID', 'validateModuleID'),
		));
	}

	public function attributeLabels()
	{
		return array_merge(parent::attributeLabels(), array(
			'modelClass' => 'Model Class',
			'moduleID' => 'Module ID',
			'controllerClass' => 'Controller Class',
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
			'controllerClass' => 'This is the name of the controller class to be generated. You should
				provide a fully qualified namespaced class, .e.g, <code>app\controllers\PostController</code>.',
			'baseControllerClass' => 'This is the class that the new CRUD controller class will extend from.
				You should provide a fully qualified class name, e.g., <code>yii\web\Controller</code>.',
			'moduleID' => 'This is the ID of the module that the generated controller will belong to.
				If not set, it means the controller will belong to the application.',
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
		return array('baseControllerClass', 'moduleID', 'indexWidgetType', 'enableSearch');
	}

	public function validateModelClass()
	{
		/** @var ActiveRecord $class */
		$class = $this->modelClass;
		$pk = $class::primaryKey();
		if (empty($pk)) {
			$this->addError('modelClass', "The table associated with $class must have primary key(s).");
		}
	}

	public function validateSearchModelClass()
	{
		if ($this->enableSearch && empty($this->searchModelClass)) {
			$this->addError('searchModelClass', 'Search Model Class cannot be empty.');
		}
	}

	public function validateModuleID()
	{
		if (!empty($this->moduleID)) {
			$module = Yii::$app->getModule($this->moduleID);
			if ($module === null) {
				$this->addError('moduleID', "Module '{$this->moduleID}' does not exist.");
			}
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
			if (!in_array($file, array('create.php', 'update.php', 'view.php'))) {
				continue;
			}
			if (is_file($templatePath . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
				$files[] = new CodeFile("$viewPath/$file", $this->render("views/$file"));
			}
		}

		if ($this->enableSearch) {

		}

		return $files;
	}

	/**
	 * @return string the controller ID (without the module ID prefix)
	 */
	public function getControllerID()
	{
		$pos = strrpos($this->controllerClass, '\\');
		$class = substr(substr($this->controllerClass, $pos + 1), 0, -10);
		return Inflector::camel2id($class);
	}

	/**
	 * @return string the controller class file path
	 */
	public function getControllerFile()
	{
		return Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->controllerClass, '\\')) . '.php');
	}

	/**
	 * @return string the action view file path
	 */
	public function getViewPath()
	{
		$module = empty($this->moduleID) ? Yii::$app : Yii::$app->getModule($this->moduleID);
		return $module->getViewPath() . '/' . $this->getControllerID() ;
	}

	public function getNameAttribute()
	{
		/** @var \yii\db\ActiveRecord $class */
		$class = $this->modelClass;
		foreach ($class::getTableSchema()->columnNames as $name) {
			if (!strcasecmp($name, 'name') || !strcasecmp($name, 'title')) {
				return $name;
			}
		}
		$pk = $class::primaryKey();
		return $pk[0];
	}
}
