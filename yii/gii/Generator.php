<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii;

use Yii;
use yii\base\Model;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Generator extends Model
{
	/**
	 * @var string
	 */
	public $id;

	/**
	 * @return string name of the code generator
	 */
	public function getName()
	{
		return 'unknown';
	}

	public function getDescription()
	{
		return '';
	}

	public function getUrl()
	{
		return Yii::$app->controller->createUrl('default/view', array('id' => $this->id));
	}

	public function renderForm()
	{
		return '';
	}

	public function renderFileList()
	{
		return '';
	}

	const STATUS_NEW = 1;
	const STATUS_PREVIEW = 2;
	const STATUS_SUCCESS = 3;
	const STATUS_ERROR = 4;

	static $keywords = array(
		'__class__',
		'__dir__',
		'__file__',
		'__function__',
		'__line__',
		'__method__',
		'__namespace__',
		'abstract',
		'and',
		'array',
		'as',
		'break',
		'case',
		'catch',
		'cfunction',
		'class',
		'clone',
		'const',
		'continue',
		'declare',
		'default',
		'die',
		'do',
		'echo',
		'else',
		'elseif',
		'empty',
		'enddeclare',
		'endfor',
		'endforeach',
		'endif',
		'endswitch',
		'endwhile',
		'eval',
		'exception',
		'exit',
		'extends',
		'final',
		'final',
		'for',
		'foreach',
		'function',
		'global',
		'goto',
		'if',
		'implements',
		'include',
		'include_once',
		'instanceof',
		'interface',
		'isset',
		'list',
		'namespace',
		'new',
		'old_function',
		'or',
		'parent',
		'php_user_filter',
		'print',
		'private',
		'protected',
		'public',
		'require',
		'require_once',
		'return',
		'static',
		'switch',
		'this',
		'throw',
		'try',
		'unset',
		'use',
		'var',
		'while',
		'xor',
	);

	/**
	 * @var array user confirmations on whether to overwrite existing code files with the newly generated ones.
	 * The value of this property is internally managed by this class and {@link CCodeGenerator}.
	 */
	public $answers;
	/**
	 * @var string the name of the code template that the user has selected.
	 * The value of this property is internally managed by this class and {@link CCodeGenerator}.
	 */
	public $template;
	/**
	 * @var array a list of {@link CCodeFile} objects that represent the code files to be generated.
	 * The {@link prepare()} method is responsible to populate this property.
	 */
	public $files = array();
	/**
	 * @var integer the status of this model. T
	 * The value of this property is internally managed by {@link CCodeGenerator}.
	 */
	public $status = self::STATUS_NEW;

	private $_stickyAttributes = array();

	/**
	 * Prepares the code files to be generated.
	 * This is the main method that child classes should implement. It should contain the logic
	 * that populates the {@link files} property with a list of code files to be generated.
	 */
	public function prepare()
	{

	}

	/**
	 * Declares the model validation rules.
	 * Child classes must override this method in the following format:
	 * <pre>
	 * return array_merge(parent::rules(), array(
	 *     ...rules for the child class...
	 * ));
	 * </pre>
	 * @return array validation rules
	 */
	public function rules()
	{
		return array(
			array('template', 'required'),
			array('template', 'validateTemplate', 'skipOnError' => true),
			array('template', 'sticky'),
		);
	}

	/**
	 * Validates the template selection.
	 * This method validates whether the user selects an existing template
	 * and the template contains all required template files as specified in {@link requiredTemplates}.
	 * @param string $attribute the attribute to be validated
	 * @param array $params validation parameters
	 */
	public function validateTemplate($attribute, $params)
	{
		$templates = $this->templates;
		if (!isset($templates[$this->template])) {
			$this->addError('template', 'Invalid template selection.');
		} else {
			$templatePath = $this->templatePath;
			foreach ($this->requiredTemplates() as $template) {
				if (!is_file($templatePath . '/' . $template)) {
					$this->addError('template', "Unable to find the required code template file '$template'.");
				}
			}
		}
	}

	/**
	 * Checks if the named class exists (in a case sensitive manner).
	 * @param string $name class name to be checked
	 * @return boolean whether the class exists
	 */
	public function classExists($name)
	{
		return class_exists($name, false) && in_array($name, get_declared_classes());
	}

	/**
	 * Declares the model attribute labels.
	 * Child classes must override this method in the following format:
	 * <pre>
	 * return array_merge(parent::attributeLabels(), array(
	 *     ...labels for the child class attributes...
	 * ));
	 * </pre>
	 * @return array the attribute labels
	 */
	public function attributeLabels()
	{
		return array(
			'template' => 'Code Template',
		);
	}

	/**
	 * Returns a list of code templates that are required.
	 * Derived classes usually should override this method.
	 * @return array list of code templates that are required. They should be file paths
	 * relative to {@link templatePath}.
	 */
	public function requiredTemplates()
	{
		return array();
	}

	/**
	 * Saves the generated code into files.
	 */
	public function save()
	{
		$result = true;
		foreach ($this->files as $file) {
			if ($this->confirmed($file)) {
				$result = $file->save() && $result;
			}
		}
		return $result;
	}

	/**
	 * Returns the message to be displayed when the newly generated code is saved successfully.
	 * Child classes should override this method if the message needs to be customized.
	 * @return string the message to be displayed when the newly generated code is saved successfully.
	 */
	public function successMessage()
	{
		return 'The code has been generated successfully.';
	}

	/**
	 * Returns the message to be displayed when some error occurred during code file saving.
	 * Child classes should override this method if the message needs to be customized.
	 * @return string the message to be displayed when some error occurred during code file saving.
	 */
	public function errorMessage()
	{
		return 'There was some error when generating the code. Please check the following messages.';
	}

	/**
	 * Returns a list of available code templates (name=>directory).
	 * This method simply returns the {@link CCodeGenerator::templates} property value.
	 * @return array a list of available code templates (name=>directory).
	 */
	public function getTemplates()
	{
		return Yii::app()->controller->templates;
	}

	/**
	 * @return string the directory that contains the template files.
	 * @throws CHttpException if {@link templates} is empty or template selection is invalid
	 */
	public function getTemplatePath()
	{
		$templates = $this->getTemplates();
		if (isset($templates[$this->template])) {
			return $templates[$this->template];
		} elseif (empty($templates)) {
			throw new CHttpException(500, 'No templates are available.');
		} else {
			throw new CHttpException(500, 'Invalid template selection.');
		}

	}

	/**
	 * @param CCodeFile $file whether the code file should be saved
	 * @return bool whether the confirmation is found in {@link answers} with appropriate {@link operation}
	 */
	public function confirmed($file)
	{
		return $this->answers === null && $file->operation === CCodeFile::OP_NEW
		|| is_array($this->answers) && isset($this->answers[md5($file->path)]);
	}

	/**
	 * Generates the code using the specified code template file.
	 * This method is manly used in {@link generate} to generate code.
	 * @param string $templateFile the code template file path
	 * @param array $_params_ a set of parameters to be extracted and made available in the code template
	 * @throws CException is template file does not exist
	 * @return string the generated code
	 */
	public function render($templateFile, $_params_ = null)
	{
		if (!is_file($templateFile)) {
			throw new CException("The template file '$templateFile' does not exist.");
		}

		if (is_array($_params_)) {
			extract($_params_, EXTR_PREFIX_SAME, 'params');
		} else {
			$params = $_params_;
		}
		ob_start();
		ob_implicit_flush(false);
		require($templateFile);
		return ob_get_clean();
	}

	/**
	 * @return string the code generation result log.
	 */
	public function renderResults()
	{
		$output = 'Generating code using template "' . $this->templatePath . "\"...\n";
		foreach ($this->files as $file) {
			if ($file->error !== null) {
				$output .= "<span class=\"error\">generating {$file->relativePath}<br/>           {$file->error}</span>\n";
			} elseif ($file->operation === CCodeFile::OP_NEW && $this->confirmed($file)) {
				$output .= ' generated ' . $file->relativePath . "\n";
			} elseif ($file->operation === CCodeFile::OP_OVERWRITE && $this->confirmed($file)) {
				$output .= ' overwrote ' . $file->relativePath . "\n";
			} else {
				$output .= '   skipped ' . $file->relativePath . "\n";
			}
		}
		$output .= "done!\n";
		return $output;
	}

	/**
	 * The "sticky" validator.
	 * This validator does not really validate the attributes.
	 * It actually saves the attribute value in a file to make it sticky.
	 * @param string $attribute the attribute to be validated
	 * @param array $params the validation parameters
	 */
	public function sticky($attribute, $params)
	{
		if (!$this->hasErrors()) {
			$this->_stickyAttributes[$attribute] = $this->$attribute;
		}
	}

	/**
	 * Loads sticky attributes from a file and populates them into the model.
	 */
	public function loadStickyAttributes()
	{
		$this->_stickyAttributes = array();
		$path = $this->getStickyFile();
		if (is_file($path)) {
			$result = @include($path);
			if (is_array($result)) {
				$this->_stickyAttributes = $result;
				foreach ($this->_stickyAttributes as $name => $value) {
					if (property_exists($this, $name) || $this->canSetProperty($name)) {
						$this->$name = $value;
					}
				}
			}
		}
	}

	/**
	 * Saves sticky attributes into a file.
	 */
	public function saveStickyAttributes()
	{
		$path = $this->getStickyFile();
		@mkdir(dirname($path), 0755, true);
		file_put_contents($path, "<?php\nreturn " . var_export($this->_stickyAttributes, true) . ";\n");
	}

	/**
	 * @return string the file path that stores the sticky attribute values.
	 */
	public function getStickyFile()
	{
		return Yii::app()->runtimePath . '/gii-' . Yii::getVersion() . '/' . get_class($this) . '.php';
	}

	/**
	 * Validates an attribute to make sure it is not taking a PHP reserved keyword.
	 * @param string $attribute the attribute to be validated
	 * @param array $params validation parameters
	 */
	public function validateReservedWord($attribute, $params)
	{
		$value = $this->$attribute;
		if (in_array(strtolower($value), self::$keywords)) {
			$this->addError($attribute, $this->getAttributeLabel($attribute) . ' cannot take a reserved PHP keyword.');
		}
	}
}
