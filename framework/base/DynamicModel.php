<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use yii\validators\Validator;

/**
 * DynamicModel is a model class primarily used to support ad hoc data validation.
 *
 * The typical usage of DynamicModel is as follows,
 *
 * ```php
 * public function actionSearch($name, $email)
 * {
 *     $model = DynamicModel::validateData(compact('name', 'email'), [
 *         [['name', 'email'], 'string', 'max' => 128],
 *         ['email', 'email'],
 *     ]);
 *     if ($model->hasErrors()) {
 *         // validation fails
 *     } else {
 *         // validation succeeds
 *     }
 * }
 * ```
 *
 * The above example shows how to validate `$name` and `$email` with the help of DynamicModel.
 * The [[validateData()]] method creates an instance of DynamicModel, defines the attributes
 * using the given data (`name` and `email` in this example), and then calls [[Model::validate()]].
 *
 * You can check the validation result by [[hasErrors()]], like you do with a normal model.
 * You may also access the dynamic attributes defined through the model instance, e.g.,
 * `$model->name` and `$model->email`.
 *
 * Alternatively, you may use the following more "classic" syntax to perform ad-hoc data validation:
 *
 * ```php
 * $model = new DynamicModel(compact('name', 'email'));
 * $model->addRule(['name', 'email'], 'string', ['max' => 128])
 *     ->addRule('email', 'email')
 *     ->validate();
 * ```
 *
 * DynamicModel implements the above ad-hoc data validation feature by supporting the so-called
 * "dynamic attributes". It basically allows an attribute to be defined dynamically through its constructor
 * or [[defineAttribute()]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DynamicModel extends Model
{
    private $_attributes = [];
    /**
     * Array of the dynamic attribute labels.
     * Used to as form field labels and in validation errors.
     *
     * @see attributeLabels()
     * @see setAttributeLabels()
     * @see setAttributeLabel()
     * @since 2.0.35
     */
    private $_attributeLabels = [];


    /**
     * Constructors.
     * @param array $attributes the dynamic attributes (name-value pairs, or names) being defined
     * @param array $config the configuration array to be applied to this object.
     */
    public function __construct(array $attributes = [], $config = [])
    {
        foreach ($attributes as $name => $value) {
            if (is_int($name)) {
                $this->_attributes[$value] = null;
            } else {
                $this->_attributes[$name] = $value;
            }
        }
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        if ($this->hasAttribute($name)) {
            return $this->_attributes[$name];
        }

        return parent::__get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function __set($name, $value)
    {
        if ($this->hasAttribute($name)) {
            $this->_attributes[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __isset($name)
    {
        if ($this->hasAttribute($name)) {
            return isset($this->_attributes[$name]);
        }

        return parent::__isset($name);
    }

    /**
     * {@inheritdoc}
     */
    public function __unset($name)
    {
        if ($this->hasAttribute($name)) {
            unset($this->_attributes[$name]);
        } else {
            parent::__unset($name);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function canGetProperty($name, $checkVars = true, $checkBehaviors = true)
    {
        return parent::canGetProperty($name, $checkVars, $checkBehaviors) || $this->hasAttribute($name);
    }

    /**
     * {@inheritdoc}
     */
    public function canSetProperty($name, $checkVars = true, $checkBehaviors = true)
    {
        return parent::canSetProperty($name, $checkVars, $checkBehaviors) || $this->hasAttribute($name);
    }

    /**
     * Returns a value indicating whether the model has an attribute with the specified name.
     * @param string $name the name of the attribute
     * @return bool whether the model has an attribute with the specified name.
     * @since 2.0.16
     */
    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->_attributes);
    }

    /**
     * Defines an attribute.
     * @param string $name the attribute name
     * @param mixed $value the attribute value
     */
    public function defineAttribute($name, $value = null)
    {
        $this->_attributes[$name] = $value;
    }

    /**
     * Undefines an attribute.
     * @param string $name the attribute name
     */
    public function undefineAttribute($name)
    {
        unset($this->_attributes[$name]);
    }

    /**
     * Adds a validation rule to this model.
     * You can also directly manipulate [[validators]] to add or remove validation rules.
     * This method provides a shortcut.
     * @param string|array $attributes the attribute(s) to be validated by the rule
     * @param string|Validator|\Closure $validator the validator. This can be either:
     *  * a built-in validator name listed in [[builtInValidators]];
     *  * a method name of the model class;
     *  * an anonymous function;
     *  * a validator class name.
     *  * a Validator.
     * @param array $options the options (name-value pairs) to be applied to the validator
     * @return $this the model itself
     */
    public function addRule($attributes, $validator, $options = [])
    {
        $validators = $this->getValidators();

        if ($validator instanceof Validator) {
            $validator->attributes = (array)$attributes;
        } else {
            $validator = Validator::createValidator($validator, $this, (array)$attributes, $options);
        }

        $validators->append($validator);

        return $this;
    }

    /**
     * Validates the given data with the specified validation rules.
     * This method will create a DynamicModel instance, populate it with the data to be validated,
     * create the specified validation rules, and then validate the data using these rules.
     * @param array $data the data (name-value pairs) to be validated
     * @param array $rules the validation rules. Please refer to [[Model::rules()]] on the format of this parameter.
     * @return static the model instance that contains the data being validated
     * @throws InvalidConfigException if a validation rule is not specified correctly.
     */
    public static function validateData(array $data, $rules = [])
    {
        /* @var $model DynamicModel */
        $model = new static($data);
        if (!empty($rules)) {
            $validators = $model->getValidators();
            foreach ($rules as $rule) {
                if ($rule instanceof Validator) {
                    $validators->append($rule);
                } elseif (is_array($rule) && isset($rule[0], $rule[1])) { // attributes, validator type
                    $validator = Validator::createValidator($rule[1], $model, (array)$rule[0], array_slice($rule, 2));
                    $validators->append($validator);
                } else {
                    throw new InvalidConfigException('Invalid validation rule: a rule must specify both attribute names and validator type.');
                }
            }
        }

        $model->validate();

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function attributes()
    {
        return array_keys($this->_attributes);
    }

    /**
     * Sets the attribute labels in a massive way.
     *
     * @see attributeLabels()
     * @since 2.0.35
     *
     * @param array $labels Array of attribute labels
     * @return $this
     */
    public function setAttributeLabels(array $labels = [])
    {
        $this->_attributeLabels = $labels;

        return $this;
    }

    /**
     * Sets a label for an attribute.
     *
     * @see attributeLabels()
     * @since 2.0.35
     *
     * @param string $attribute Attribute name
     * @param string $label Attribute label value
     * @return $this
     */
    public function setAttributeLabel($attribute, $label)
    {
        $this->_attributeLabels[$attribute] = $label;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), $this->_attributeLabels);
    }
}
