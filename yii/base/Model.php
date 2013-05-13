<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use yii\helpers\StringHelper;
use yii\validators\RequiredValidator;
use yii\validators\Validator;

/**
 * Model is the base class for data models.
 *
 * Model implements the following commonly used features:
 *
 * - attribute declaration: by default, every public class member is considered as
 *   a model attribute
 * - attribute labels: each attribute may be associated with a label for display purpose
 * - massive attribute assignment
 * - scenario-based validation
 *
 * Model also raises the following events when performing data validation:
 *
 * - [[EVENT_BEFORE_VALIDATE]]: an event raised at the beginning of [[validate()]]
 * - [[EVENT_AFTER_VALIDATE]]: an event raised at the end of [[validate()]]
 *
 * You may directly use Model to store model data, or extend it with customization.
 * You may also customize Model by attaching [[ModelBehavior|model behaviors]].
 *
 * @property Vector $validators All the validators declared in the model.
 * @property array $activeValidators The validators applicable to the current [[scenario]].
 * @property array $errors Errors for all attributes or the specified attribute. Empty array is returned if no error.
 * @property array $attributes Attribute values (name => value).
 * @property string $scenario The scenario that this model is in.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Model extends Component implements \IteratorAggregate, \ArrayAccess
{
	/**
	 * @event ModelEvent an event raised at the beginning of [[validate()]]. You may set
	 * [[ModelEvent::isValid]] to be false to stop the validation.
	 */
	const EVENT_BEFORE_VALIDATE = 'beforeValidate';
	/**
	 * @event Event an event raised at the end of [[validate()]]
	 */
	const EVENT_AFTER_VALIDATE = 'afterValidate';

	/**
	 * @var array validation errors (attribute name => array of errors)
	 */
	private $_errors;
	/**
	 * @var Vector vector of validators
	 */
	private $_validators;
	/**
	 * @var string current scenario
	 */
	private $_scenario = 'default';

	/**
	 * Returns the validation rules for attributes.
	 *
	 * Validation rules are used by [[validate()]] to check if attribute values are valid.
	 * Child classes may override this method to declare different validation rules.
	 *
	 * Each rule is an array with the following structure:
	 *
	 * ~~~
	 * array(
	 *     'attribute list',
	 *     'validator type',
	 *     'on' => 'scenario name',
	 *     ...other parameters...
	 * )
	 * ~~~
	 *
	 * where
	 *
	 *  - attribute list: required, specifies the attributes (separated by commas) to be validated;
	 *  - validator type: required, specifies the validator to be used. It can be the name of a model
	 *    class method, the name of a built-in validator, or a validator class name (or its path alias).
	 *  - on: optional, specifies the [[scenario|scenarios]] (separated by commas) when the validation
	 *    rule can be applied. If this option is not set, the rule will apply to all scenarios.
	 *  - additional name-value pairs can be specified to initialize the corresponding validator properties.
	 *    Please refer to individual validator class API for possible properties.
	 *
	 * A validator can be either an object of a class extending [[Validator]], or a model class method
	 * (called *inline validator*) that has the following signature:
	 *
	 * ~~~
	 * // $params refers to validation parameters given in the rule
	 * function validatorName($attribute, $params)
	 * ~~~
	 *
	 * Yii also provides a set of [[Validator::builtInValidators|built-in validators]].
	 * They each has an alias name which can be used when specifying a validation rule.
	 *
	 * Below are some examples:
	 *
	 * ~~~
	 * array(
	 *     // built-in "required" validator
	 *     array('username', 'required'),
	 *     // built-in "length" validator customized with "min" and "max" properties
	 *     array('username', 'length', 'min' => 3, 'max' => 12),
	 *     // built-in "compare" validator that is used in "register" scenario only
	 *     array('password', 'compare', 'compareAttribute' => 'password2', 'on' => 'register'),
	 *     // an inline validator defined via the "authenticate()" method in the model class
	 *     array('password', 'authenticate', 'on' => 'login'),
	 *     // a validator of class "CaptchaValidator"
	 *     array('captcha', 'CaptchaValidator'),
	 * );
	 * ~~~
	 *
	 * Note, in order to inherit rules defined in the parent class, a child class needs to
	 * merge the parent rules with child rules using functions such as `array_merge()`.
	 *
	 * @return array validation rules
	 * @see scenarios
	 */
	public function rules()
	{
		return array();
	}

	/**
	 * Returns a list of scenarios and the corresponding active attributes.
	 * An active attribute is one that is subject to validation in the current scenario.
	 * The returned array should be in the following format:
	 *
	 * ~~~
	 * array(
	 *     'scenario1' => array('attribute11', 'attribute12', ...),
	 *     'scenario2' => array('attribute21', 'attribute22', ...),
	 *     ...
	 * )
	 * ~~~
	 *
	 * By default, an active attribute that is considered safe and can be massively assigned.
	 * If an attribute should NOT be massively assigned (thus considered unsafe),
	 * please prefix the attribute with an exclamation character (e.g. '!rank').
	 *
	 * The default implementation of this method will return a 'default' scenario
	 * which corresponds to all attributes listed in the validation rules applicable
	 * to the 'default' scenario.
	 *
	 * @return array a list of scenarios and the corresponding active attributes.
	 */
	public function scenarios()
	{
		$attributes = array();
		foreach ($this->getActiveValidators() as $validator) {
			if ($validator->isActive('default')) {
				foreach ($validator->attributes as $name) {
					$attributes[$name] = true;
				}
			}
		}
		return array(
			'default' => array_keys($attributes),
		);
	}

	/**
	 * Returns the form name that this model class should use.
	 *
	 * The form name is mainly used by [[\yii\web\ActiveForm]] to determine how to name
	 * the input fields for the attributes in a model. If the form name is "A" and an attribute
	 * name is "b", then the corresponding input name would be "A[b]". If the form name is
	 * an empty string, then the input name would be "b".
	 *
	 * By default, this method returns the model class name (without the namespace part)
	 * as the form name. You may override it when the model is used in different forms.
	 *
	 * @return string the form name of this model class.
	 */
	public function formName()
	{
		$class = get_class($this);
		$pos = strrpos($class, '\\');
		return $pos === false ? $class : substr($class, $pos + 1);
	}

	/**
	 * Returns the list of attribute names.
	 * By default, this method returns all public non-static properties of the class.
	 * You may override this method to change the default behavior.
	 * @return array list of attribute names.
	 */
	public function attributes()
	{
		$class = new \ReflectionClass($this);
		$names = array();
		foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
			$name = $property->getName();
			if (!$property->isStatic()) {
				$names[] = $name;
			}
		}
		return $names;
	}

	/**
	 * Returns the attribute labels.
	 *
	 * Attribute labels are mainly used for display purpose. For example, given an attribute
	 * `firstName`, we can declare a label `First Name` which is more user-friendly and can
	 * be displayed to end users.
	 *
	 * By default an attribute label is generated using [[generateAttributeLabel()]].
	 * This method allows you to explicitly specify attribute labels.
	 *
	 * Note, in order to inherit labels defined in the parent class, a child class needs to
	 * merge the parent labels with child labels using functions such as `array_merge()`.
	 *
	 * @return array attribute labels (name => label)
	 * @see generateAttributeLabel
	 */
	public function attributeLabels()
	{
		return array();
	}

	/**
	 * Performs the data validation.
	 *
	 * This method executes the validation rules applicable to the current [[scenario]].
	 * The following criteria are used to determine whether a rule is currently applicable:
	 *
	 * - the rule must be associated with the attributes relevant to the current scenario;
	 * - the rules must be effective for the current scenario.
	 *
	 * This method will call [[beforeValidate()]] and [[afterValidate()]] before and
	 * after the actual validation, respectively. If [[beforeValidate()]] returns false,
	 * the validation will be cancelled and [[afterValidate()]] will not be called.
	 *
	 * Errors found during the validation can be retrieved via [[getErrors()]]
	 * and [[getError()]].
	 *
	 * @param array $attributes list of attributes that should be validated.
	 * If this parameter is empty, it means any attribute listed in the applicable
	 * validation rules should be validated.
	 * @param boolean $clearErrors whether to call [[clearErrors()]] before performing validation
	 * @return boolean whether the validation is successful without any error.
	 */
	public function validate($attributes = null, $clearErrors = true)
	{
		if ($clearErrors) {
			$this->clearErrors();
		}
		if ($attributes === null) {
			$attributes = $this->activeAttributes();
		}
		if ($this->beforeValidate()) {
			foreach ($this->getActiveValidators() as $validator) {
				$validator->validate($this, $attributes);
			}
			$this->afterValidate();
			return !$this->hasErrors();
		}
		return false;
	}

	/**
	 * This method is invoked before validation starts.
	 * The default implementation raises a `beforeValidate` event.
	 * You may override this method to do preliminary checks before validation.
	 * Make sure the parent implementation is invoked so that the event can be raised.
	 * @return boolean whether the validation should be executed. Defaults to true.
	 * If false is returned, the validation will stop and the model is considered invalid.
	 */
	public function beforeValidate()
	{
		$event = new ModelEvent;
		$this->trigger(self::EVENT_BEFORE_VALIDATE, $event);
		return $event->isValid;
	}

	/**
	 * This method is invoked after validation ends.
	 * The default implementation raises an `afterValidate` event.
	 * You may override this method to do postprocessing after validation.
	 * Make sure the parent implementation is invoked so that the event can be raised.
	 */
	public function afterValidate()
	{
		$this->trigger(self::EVENT_AFTER_VALIDATE);
	}

	/**
	 * Returns all the validators declared in [[rules()]].
	 *
	 * This method differs from [[getActiveValidators()]] in that the latter
	 * only returns the validators applicable to the current [[scenario]].
	 *
	 * Because this method returns a [[Vector]] object, you may
	 * manipulate it by inserting or removing validators (useful in model behaviors).
	 * For example,
	 *
	 * ~~~
	 * $model->validators->add($newValidator);
	 * ~~~
	 *
	 * @return Vector all the validators declared in the model.
	 */
	public function getValidators()
	{
		if ($this->_validators === null) {
			$this->_validators = $this->createValidators();
		}
		return $this->_validators;
	}

	/**
	 * Returns the validators applicable to the current [[scenario]].
	 * @param string $attribute the name of the attribute whose applicable validators should be returned.
	 * If this is null, the validators for ALL attributes in the model will be returned.
	 * @return \yii\validators\Validator[] the validators applicable to the current [[scenario]].
	 */
	public function getActiveValidators($attribute = null)
	{
		$validators = array();
		$scenario = $this->getScenario();
		/** @var $validator Validator */
		foreach ($this->getValidators() as $validator) {
			if ($validator->isActive($scenario) && ($attribute === null || in_array($attribute, $validator->attributes, true))) {
				$validators[] = $validator;
			}
		}
		return $validators;
	}

	/**
	 * Creates validator objects based on the validation rules specified in [[rules()]].
	 * Unlike [[getValidators()]], each time this method is called, a new list of validators will be returned.
	 * @return Vector validators
	 * @throws InvalidConfigException if any validation rule configuration is invalid
	 */
	public function createValidators()
	{
		$validators = new Vector;
		foreach ($this->rules() as $rule) {
			if ($rule instanceof Validator) {
				$validators->add($rule);
			} elseif (is_array($rule) && isset($rule[0], $rule[1])) { // attributes, validator type
				$validator = Validator::createValidator($rule[1], $this, $rule[0], array_slice($rule, 2));
				$validators->add($validator);
			} else {
				throw new InvalidConfigException('Invalid validation rule: a rule must specify both attribute names and validator type.');
			}
		}
		return $validators;
	}

	/**
	 * Returns a value indicating whether the attribute is required.
	 * This is determined by checking if the attribute is associated with a
	 * [[\yii\validators\RequiredValidator|required]] validation rule in the
	 * current [[scenario]].
	 * @param string $attribute attribute name
	 * @return boolean whether the attribute is required
	 */
	public function isAttributeRequired($attribute)
	{
		foreach ($this->getActiveValidators($attribute) as $validator) {
			if ($validator instanceof RequiredValidator) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Returns a value indicating whether the attribute is safe for massive assignments.
	 * @param string $attribute attribute name
	 * @return boolean whether the attribute is safe for massive assignments
	 */
	public function isAttributeSafe($attribute)
	{
		return in_array($attribute, $this->safeAttributes(), true);
	}

	/**
	 * Returns the text label for the specified attribute.
	 * @param string $attribute the attribute name
	 * @return string the attribute label
	 * @see generateAttributeLabel
	 * @see attributeLabels
	 */
	public function getAttributeLabel($attribute)
	{
		$labels = $this->attributeLabels();
		return isset($labels[$attribute]) ? $labels[$attribute] : $this->generateAttributeLabel($attribute);
	}

	/**
	 * Returns a value indicating whether there is any validation error.
	 * @param string|null $attribute attribute name. Use null to check all attributes.
	 * @return boolean whether there is any error.
	 */
	public function hasErrors($attribute = null)
	{
		return $attribute === null ? !empty($this->_errors) : isset($this->_errors[$attribute]);
	}

	/**
	 * Returns the errors for all attribute or a single attribute.
	 * @param string $attribute attribute name. Use null to retrieve errors for all attributes.
	 * @return array errors for all attributes or the specified attribute. Empty array is returned if no error.
	 * Note that when returning errors for all attributes, the result is a two-dimensional array, like the following:
	 *
	 * ~~~
	 * array(
	 *     'username' => array(
	 *         'Username is required.',
	 *         'Username must contain only word characters.',
	 *     ),
	 *     'email' => array(
	 *         'Email address is invalid.',
	 *     )
	 * )
	 * ~~~
	 *
	 * @see getError
	 */
	public function getErrors($attribute = null)
	{
		if ($attribute === null) {
			return $this->_errors === null ? array() : $this->_errors;
		} else {
			return isset($this->_errors[$attribute]) ? $this->_errors[$attribute] : array();
		}
	}

	/**
	 * Returns the first error of every attribute in the model.
	 * @return array the first errors. An empty array will be returned if there is no error.
	 */
	public function getFirstErrors()
	{
		if (empty($this->_errors)) {
			return array();
		} else {
			$errors = array();
			foreach ($this->_errors as $attributeErrors) {
				if (isset($attributeErrors[0])) {
					$errors[] = $attributeErrors[0];
				}
			}
		}
		return $errors;
	}

	/**
	 * Returns the first error of the specified attribute.
	 * @param string $attribute attribute name.
	 * @return string the error message. Null is returned if no error.
	 * @see getErrors
	 */
	public function getFirstError($attribute)
	{
		return isset($this->_errors[$attribute]) ? reset($this->_errors[$attribute]) : null;
	}

	/**
	 * Adds a new error to the specified attribute.
	 * @param string $attribute attribute name
	 * @param string $error new error message
	 */
	public function addError($attribute, $error)
	{
		$this->_errors[$attribute][] = $error;
	}

	/**
	 * Removes errors for all attributes or a single attribute.
	 * @param string $attribute attribute name. Use null to remove errors for all attribute.
	 */
	public function clearErrors($attribute = null)
	{
		if ($attribute === null) {
			$this->_errors = array();
		} else {
			unset($this->_errors[$attribute]);
		}
	}

	/**
	 * Generates a user friendly attribute label based on the give attribute name.
	 * This is done by replacing underscores, dashes and dots with blanks and
	 * changing the first letter of each word to upper case.
	 * For example, 'department_name' or 'DepartmentName' will generate 'Department Name'.
	 * @param string $name the column name
	 * @return string the attribute label
	 */
	public function generateAttributeLabel($name)
	{
		return StringHelper::camel2words($name, true);
	}

	/**
	 * Returns attribute values.
	 * @param array $names list of attributes whose value needs to be returned.
	 * Defaults to null, meaning all attributes listed in [[attributes()]] will be returned.
	 * If it is an array, only the attributes in the array will be returned.
	 * @param array $except list of attributes whose value should NOT be returned.
	 * @return array attribute values (name => value).
	 */
	public function getAttributes($names = null, $except = array())
	{
		$values = array();
		if ($names === null) {
			$names = $this->attributes();
		}
		foreach ($names as $name) {
			$values[$name] = $this->$name;
		}
		foreach ($except as $name) {
			unset($values[$name]);
		}

		return $values;
	}

	/**
	 * Sets the attribute values in a massive way.
	 * @param array $values attribute values (name => value) to be assigned to the model.
	 * @param boolean $safeOnly whether the assignments should only be done to the safe attributes.
	 * A safe attribute is one that is associated with a validation rule in the current [[scenario]].
	 * @see safeAttributes()
	 * @see attributes()
	 */
	public function setAttributes($values, $safeOnly = true)
	{
		if (is_array($values)) {
			$attributes = array_flip($safeOnly ? $this->safeAttributes() : $this->attributes());
			foreach ($values as $name => $value) {
				if (isset($attributes[$name])) {
					$this->$name = $value;
				} elseif ($safeOnly) {
					$this->onUnsafeAttribute($name, $value);
				}
			}
		}
	}

	/**
	 * This method is invoked when an unsafe attribute is being massively assigned.
	 * The default implementation will log a warning message if YII_DEBUG is on.
	 * It does nothing otherwise.
	 * @param string $name the unsafe attribute name
	 * @param mixed $value the attribute value
	 */
	public function onUnsafeAttribute($name, $value)
	{
		if (YII_DEBUG) {
			\Yii::info("Failed to set unsafe attribute '$name' in '" . get_class($this) . "'.", __METHOD__);
		}
	}

	/**
	 * Returns the scenario that this model is used in.
	 *
	 * Scenario affects how validation is performed and which attributes can
	 * be massively assigned.
	 *
	 * @return string the scenario that this model is in. Defaults to 'default'.
	 */
	public function getScenario()
	{
		return $this->_scenario;
	}

	/**
	 * Sets the scenario for the model.
	 * @param string $value the scenario that this model is in.
	 * @see getScenario
	 */
	public function setScenario($value)
	{
		$this->_scenario = $value;
	}

	/**
	 * Returns the attribute names that are safe to be massively assigned in the current scenario.
	 * @return string[] safe attribute names
	 */
	public function safeAttributes()
	{
		$scenario = $this->getScenario();
		$scenarios = $this->scenarios();
		if (!isset($scenarios[$scenario])) {
			return array();
		}
		$attributes = array();
		if (isset($scenarios[$scenario]['attributes']) && is_array($scenarios[$scenario]['attributes'])) {
			$scenarios[$scenario] = $scenarios[$scenario]['attributes'];
		}
		foreach ($scenarios[$scenario] as $attribute) {
			if ($attribute[0] !== '!') {
				$attributes[] = $attribute;
			}
		}
		return $attributes;
	}

	/**
	 * Returns the attribute names that are subject to validation in the current scenario.
	 * @return string[] safe attribute names
	 */
	public function activeAttributes()
	{
		$scenario = $this->getScenario();
		$scenarios = $this->scenarios();
		if (!isset($scenarios[$scenario])) {
			return array();
		}
		if (isset($scenarios[$scenario]['attributes']) && is_array($scenarios[$scenario]['attributes'])) {
			$attributes = $scenarios[$scenario]['attributes'];
		} else {
			$attributes = $scenarios[$scenario];
		}
		foreach ($attributes as $i => $attribute) {
			if ($attribute[0] === '!') {
				$attributes[$i] = substr($attribute, 1);
			}
		}
		return $attributes;
	}

	/**
	 * Returns an iterator for traversing the attributes in the model.
	 * This method is required by the interface IteratorAggregate.
	 * @return DictionaryIterator an iterator for traversing the items in the list.
	 */
	public function getIterator()
	{
		$attributes = $this->getAttributes();
		return new DictionaryIterator($attributes);
	}

	/**
	 * Returns whether there is an element at the specified offset.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `isset($model[$offset])`.
	 * @param mixed $offset the offset to check on
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return $this->$offset !== null;
	}

	/**
	 * Returns the element at the specified offset.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `$value = $model[$offset];`.
	 * @param mixed $offset the offset to retrieve element.
	 * @return mixed the element at the offset, null if no element is found at the offset
	 */
	public function offsetGet($offset)
	{
		return $this->$offset;
	}

	/**
	 * Sets the element at the specified offset.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `$model[$offset] = $item;`.
	 * @param integer $offset the offset to set element
	 * @param mixed $item the element value
	 */
	public function offsetSet($offset, $item)
	{
		$this->$offset = $item;
	}

	/**
	 * Sets the element value at the specified offset to null.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `unset($model[$offset])`.
	 * @param mixed $offset the offset to unset element
	 */
	public function offsetUnset($offset)
	{
		$this->$offset = null;
	}
}
