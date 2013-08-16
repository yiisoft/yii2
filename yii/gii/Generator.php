<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii;

use Yii;
use ReflectionClass;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\base\View;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class Generator extends Model
{
	public $templates = array();
	/**
	 * @var string the name of the code template that the user has selected.
	 * The value of this property is internally managed by this class and {@link CCodeGenerator}.
	 */
	public $template;

	/**
	 * @return string name of the code generator
	 */
	abstract public function getName();

	public function init()
	{
		parent::init();
		if (!isset($this->templates['default'])) {
			$this->templates['default'] = $this->getDefaultTemplate();
		}
	}

	/**
	 * Prepares the code files to be generated.
	 * This is the main method that child classes should implement. It should contain the logic
	 * that populates the {@link files} property with a list of code files to be generated.
	 */
	public function prepare()
	{
		return array();
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

	public function getViewFile()
	{
		$class = new ReflectionClass($this);
		return dirname($class->getFileName()) . '/views/form.php';
	}

	public function getDefaultTemplate()
	{
		$class = new ReflectionClass($this);
		return dirname($class->getFileName()) . '/templates';
	}

	public function getDescription()
	{
		return '';
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
			array('template', 'required', 'message' => 'A code template must be selected.'),
			array('template', 'validateTemplate', 'skipOnError' => true),
		);
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
	 * Saves the generated code into files.
	 */
	public function save($files, $answers = array())
	{
		$result = true;
		foreach ($files as $file) {
			if ($this->confirmed($file)) {
				$result = $file->save() && $result;
			}
		}
		return $result;
	}

	/**
	 * @return string the directory that contains the template files.
	 * @throws InvalidConfigException if {@link templates} is empty or template selection is invalid
	 */
	public function getTemplatePath()
	{
		if (isset($this->templates[$this->template])) {
			return $this->templates[$this->template];
		} else {
			throw new InvalidConfigException("Unknown template: {$this->template}");
		}
	}

	/**
	 * @param CodeFile $file whether the code file should be saved
	 * @return bool whether the confirmation is found in {@link answers} with appropriate {@link operation}
	 */
	public function confirmed($file)
	{
		return $this->answers === null && $file->operation === CodeFile::OP_NEW
		|| is_array($this->answers) && isset($this->answers[md5($file->path)]);
	}

	/**
	 * Generates the code using the specified code template file.
	 * This method is manly used in {@link generate} to generate code.
	 * @param string $templateFile the code template file path
	 * @param array $_params_ a set of parameters to be extracted and made available in the code template
	 * @return string the generated code
	 */
	public function render($templateFile, $params = array())
	{
		$view = new View;
		return $view->renderFile($templateFile, $params, $this);
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
			} elseif ($file->operation === CodeFile::OP_NEW && $this->confirmed($file)) {
				$output .= ' generated ' . $file->relativePath . "\n";
			} elseif ($file->operation === CodeFile::OP_OVERWRITE && $this->confirmed($file)) {
				$output .= ' overwrote ' . $file->relativePath . "\n";
			} else {
				$output .= '   skipped ' . $file->relativePath . "\n";
			}
		}
		$output .= "done!\n";
		return $output;
	}

	/**
	 * Validates the template selection.
	 * This method validates whether the user selects an existing template
	 * and the template contains all required template files as specified in {@link requiredTemplates}.
	 */
	public function validateTemplate()
	{
		$templates = $this->templates;
		if (!isset($templates[$this->template])) {
			$this->addError('template', 'Invalid template selection.');
		} else {
			$templatePath = $this->templates[$this->template];
			foreach ($this->requiredTemplates() as $template) {
				if (!is_file($templatePath . '/' . $template)) {
					$this->addError('template', "Unable to find the required code template file '$template'.");
				}
			}
		}
	}

	/**
	 * Validates an attribute to make sure it is not taking a PHP reserved keyword.
	 * @param string $attribute the attribute to be validated
	 * @param array $params validation parameters
	 */
	public function validateReservedWord($attribute, $params)
	{
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
		$value = $this->$attribute;
		if (in_array(strtolower($value), $keywords)) {
			$this->addError($attribute, $this->getAttributeLabel($attribute) . ' cannot take a reserved PHP keyword.');
		}
	}
}
