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
			$this->templates['default'] = $this->defaultTemplate();
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

	public function stickyAttributes()
	{
		return array('template');
	}

	public function hints()
	{
		return array();
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

	public function formView()
	{
		$class = new ReflectionClass($this);
		return dirname($class->getFileName()) . '/views/form.php';
	}

	public function defaultTemplate()
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
			array('template', 'validateTemplate'),
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
	 * Loads sticky attributes from a file and populates them into the model.
	 */
	public function loadStickyAttributes()
	{
		$stickyAttributes = $this->stickyAttributes();
		$attributes[] = 'template';
		$path = $this->getStickyDataFile();
		if (is_file($path)) {
			$result = @include($path);
			if (is_array($result)) {
				foreach ($stickyAttributes as $name) {
					if (isset($result[$name])) {
						$this->$name = $result[$name];
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
		$stickyAttributes = $this->stickyAttributes();
		$stickyAttributes[] = 'template';
		$values = array();
		foreach ($stickyAttributes as $name) {
			$values[$name] = $this->$name;
		}
		$path = $this->getStickyDataFile();
		@mkdir(dirname($path), 0755, true);
		file_put_contents($path, "<?php\nreturn " . var_export($values, true) . ";\n");
	}

	/**
	 * @return string the file path that stores the sticky attribute values.
	 */
	public function getStickyDataFile()
	{
		return Yii::$app->getRuntimePath() . '/gii-' . Yii::getVersion() . '/' . str_replace('\\', '-',get_class($this)) . '.php';
	}

	/**
	 * Saves the generated code into files.
	 * @param CodeFile[] $files
	 * @param array $answers
	 * @param boolean $hasError
	 * @return string
	 */
	public function save($files, $answers, &$hasError)
	{
		$lines = array('Generating code using template "' . $this->templatePath . '"...');
		foreach ($files as $file) {;
			$relativePath = $file->getRelativePath();
			if (isset($answers[$file->id]) && $file->operation !== CodeFile::OP_SKIP) {
				$error = $file->save();
				if (is_string($error)) {
					$lines[] = "<span class=\"error\">generating $relativePath<br>           $error</span>";
				} elseif ($file->operation === CodeFile::OP_NEW) {
					$lines[] = " generated $relativePath";
				} else {
					$lines[] = " overwrote $relativePath";
				}
			} else {
				$lines[] = "   skipped $relativePath";
			}
		}
		$lines[] = "done!\n";
		return implode("\n", $lines);
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

	public function generateCode($template, $params = array())
	{
		$view = new View;
		$params['generator'] = $this;
		return $view->renderFile($template, $params, $this);
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
			'__trait__',
			'abstract',
			'and',
			'array',
			'as',
			'break',
			'case',
			'catch',
			'callable',
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
			'finally',
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
			'insteadof',
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
			'trait',
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
