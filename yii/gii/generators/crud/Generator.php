<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii\generators\crud;

use yii\db\ActiveRecord;
use yii\gii\CodeFile;
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
			array('modelClass, controllerID, baseControllerClass', 'filter', 'filter' => 'trim'),
			array('modelClass, controllerID, baseControllerClass', 'required'),
			array('modelClass', 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'),
			array('modelClass', 'validateClass', 'params' => array('extends' => ActiveRecord::className())),
			array('controllerID', 'match', 'pattern' => '/^[a-z\\-\\/]*$/', 'message' => 'Only a-z, dashes (-) and slashes (/) are allowed.'),
			array('baseControllerClass', 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'),
			array('baseControllerClass', 'validateClass', 'params' => array('extends' => Controller::className())),
		));
	}

	public function attributeLabels()
	{
		return array_merge(parent::attributeLabels(), array(
			'modelClass' => 'Model Class',
			'controllerID' => 'Controller ID',
			'baseControllerClass' => 'Base Controller Class',
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
		return array('baseControllerClass');
	}

	/**
	 * @inheritdoc
	 */
	public function generate()
	{
		$files = array();
		$files[] = new CodeFile(
			$this->controllerFile,
			$this->render('controller.php')
		);

		$files = scandir($this->getTemplatePath());
		foreach ($files as $file) {
			if (is_file($templatePath . '/' . $file) && CFileHelper::getExtension($file) === 'php' && $file !== 'controller.php') {
				$files[] = new CodeFile(
					$this->viewPath . DIRECTORY_SEPARATOR . $file,
					$this->render($templatePath . '/' . $file)
				);
			}
		}

		return $files;
	}
}
