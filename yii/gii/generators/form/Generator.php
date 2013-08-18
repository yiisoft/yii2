<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii\generators\form;

use Yii;
use yii\base\Model;
use yii\gii\CodeFile;

/**
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Generator extends \yii\gii\Generator
{
	public $modelClass;
	public $viewPath = '@app/views';
	public $viewName;
	public $scenarioName = 'default';


	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		return 'Form Generator';
	}

	/**
	 * @inheritdoc
	 */
	public function getDescription()
	{
		return 'This generator generates a view script file that displays a form to collect input for the specified model class.';
	}

	/**
	 * @inheritdoc
	 */
	public function generate()
	{
		$files = array();
		$files[] = new CodeFile(
			Yii::getAlias($this->viewPath) . '/' . $this->viewName . '.php',
			$this->render($this->getTemplatePath() . '/form.php')
		);
		return $files;
	}


	public function rules()
	{
		return array_merge(parent::rules(), array(
			array('modelClass, viewName, scenarioName', 'filter', 'filter' => 'trim'),
			array('modelClass, viewName, viewPath', 'required'),
			array('modelClass, viewPath', 'match', 'pattern' => '/^@?\w+[\\-\\/\w+]*$/', 'message' => 'Only word characters, dashes, slashes and @ are allowed.'),
			array('viewName', 'match', 'pattern' => '/^\w+[\\-\\/\w+]*$/', 'message' => 'Only word characters, dashes and slashes are allowed.'),
			array('modelClass', 'validateModel'),
			array('viewPath', 'validateViewPath'),
			array('scenarioName', 'match', 'pattern' => '/^\w+$/', 'message' => 'Only word characters are allowed.'),
		));
	}

	public function attributeLabels()
	{
		return array(
			'modelClass' => 'Model Class',
			'viewName' => 'View Name',
			'viewPath' => 'View Path',
			'scenarioName' => 'Scenario',
		);
	}

	public function requiredTemplates()
	{
		return array(
			'form.php',
			'action.php',
		);
	}

	/**
	 * @inheritdoc
	 */
	public function stickyAttributes()
	{
		return array('viewPath', 'scenarioName');
	}

	/**
	 * @inheritdoc
	 */
	public function hints()
	{
		return array(
		);
	}

	public function successMessage()
	{
		$output = <<<EOD
<p>The form has been generated successfully.</p>
<p>You may add the following code in an appropriate controller class to invoke the view:</p>
EOD;
		$code = "<?php\n" . $this->render($this->getTemplatePath() . '/action.php');
		return $output . highlight_string($code, true);
	}

	public function validateModel($attribute, $params)
	{
		try {
			if (class_exists($this->modelClass)) {
				if (!is_subclass_of($this->modelClass, Model::className())) {
					$this->addError('modelClass', "'{$this->modelClass}' must extend from Model or its child class.");
				}
			} else {
				$this->addError('modelClass', "Class '{$this->modelClass}' does not exist or has syntax error.");
			}
		} catch (\Exception $e) {
			$this->addError('modelClass', "Class '{$this->modelClass}' does not exist or has syntax error.");
			return;
		}
	}

	public function validateViewPath()
	{
		$path = Yii::getAlias($this->viewPath, false);
		if ($path === false || !is_dir($path)) {
			$this->addError('viewPath', 'View path does not exist.');
		}
	}

	public function getModelAttributes()
	{
		/** @var Model $model */
		$model = new $this->modelClass;
		$model->setScenario($this->scenarioName);
		return $model->safeAttributes();
	}
}
