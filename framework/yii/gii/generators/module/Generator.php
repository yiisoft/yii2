<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii\generators\module;

use Yii;
use yii\gii\CodeFile;
use yii\helpers\Html;
use yii\helpers\StringHelper;

/**
 * This generator will generate the skeleton code needed by a module.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Generator extends \yii\gii\Generator
{
	public $moduleClass;
	public $moduleID;

	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		return 'Module Generator';
	}

	/**
	 * @inheritdoc
	 */
	public function getDescription()
	{
		return 'This generator helps you to generate the skeleton code needed by a Yii module.';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return array_merge(parent::rules(), array(
			array('moduleID, moduleClass', 'filter', 'filter' => 'trim'),
			array('moduleID, moduleClass', 'required'),
			array('moduleID', 'match', 'pattern' => '/^[\w\\-]+$/', 'message' => 'Only word characters and dashes are allowed.'),
			array('moduleClass', 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'),
			array('moduleClass', 'validateModuleClass'),
		));
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return array(
			'moduleID' => 'Module ID',
			'moduleClass' => 'Module Class',
		);
	}

	/**
	 * @inheritdoc
	 */
	public function hints()
	{
		return array(
			'moduleID' => 'This refers to the ID of the module, e.g., <code>admin</code>.',
			'moduleClass' => 'This is the fully qualified class name of the module, e.g., <code>app\modules\admin\Module</code>.',
		);
	}

	/**
	 * @inheritdoc
	 */
	public function successMessage()
	{
		if (Yii::$app->hasModule($this->moduleID)) {
			$link = Html::a('try it now', Yii::$app->getUrlManager()->createUrl($this->moduleID), array('target' => '_blank'));
			return "The module has been generated successfully. You may $link.";
		}

		$output = <<<EOD
<p>The module has been generated successfully.</p>
<p>To access the module, you need to modify the application configuration as follows:</p>
EOD;
		$code = <<<EOD
<?php
return array(
	'modules'=>array(
		'{$this->moduleID}' => array(
			'class' => '{$this->moduleClass}',
		),
	),
    ......
);
EOD;

		return $output . '<pre>' . highlight_string($code, true) . '</pre>';
	}

	/**
	 * @inheritdoc
	 */
	public function requiredTemplates()
	{
		return array(
			'module.php',
			'controller.php',
			'view.php',
		);
	}

	/**
	 * @inheritdoc
	 */
	public function generate()
	{
		$files = array();
		$modulePath = $this->getModulePath();
		$templatePath = $this->getTemplatePath();
		$files[] = new CodeFile(
			$modulePath . '/' . StringHelper::basename($this->moduleClass) . '.php',
			$this->render("$templatePath/module.php")
		);
		$files[] = new CodeFile(
			$modulePath . '/controllers/DefaultController.php',
			$this->render("$templatePath/controller.php")
		);
		$files[] = new CodeFile(
			$modulePath . '/views/default/index.php',
			$this->render("$templatePath/view.php")
		);

		return $files;
	}

	/**
	 * Validates [[moduleClass]] to make sure it is a fully qualified class name.
	 */
	public function validateModuleClass()
	{
		if (strpos($this->moduleClass, '\\') === false || Yii::getAlias('@' . str_replace('\\', '/', $this->moduleClass)) === false) {
			$this->addError('moduleClass', 'Module class must be properly namespaced.');
		}
	}

	/**
	 * @return boolean the directory that contains the module class
	 */
	public function getModulePath()
	{
		return Yii::getAlias('@' . str_replace('\\', '/', substr($this->moduleClass, 0, strrpos($this->moduleClass, '\\'))));
	}

	/**
	 * @return string the controller namespace of the module.
	 */
	public function getControllerNamespace()
	{
		return substr($this->moduleClass, 0, strrpos($this->moduleClass, '\\')) . '\controllers';
	}
}
