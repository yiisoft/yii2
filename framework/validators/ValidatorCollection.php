<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use ArrayObject;
use Closure;
use ReflectionFunction;
use Yii;
use yii\base\Model;
use yii\base\Configurable;

/**
 * Collection of {@see Validator validators}.
 *
 * @property-write int $flags the flags to control the behaviour of the collection
 * @property-write string $iteratorClass specify the class that will be used for iteration of validators
 * @property-write Model $owner specify the owner model
 *
 * @since 2.0.46
 */
class ValidatorCollection extends ArrayObject implements Configurable
{
    /**
     * @var string[] The validation order, an array of validator class names. The omitted validators adding at end.
     */
    public $order = [
        'yii\validators\DefaultValueValidator',
        'yii\validators\RequiredValidator',
        'yii\validators\FilterValidator',
        'yii\captcha\CaptchaValidator',
    ];

    /**
     * @var Model The owner model.
     */
    protected $owner;

    /**
     * @inheritdoc
     * @param Model $model the owner model
     * @param array $config an array of configuration values:
     * - `order`: array, the validation order, an array of validator class names
     * - `flags`: integer, the flags(bitmask) to control the behaviour of the collection
     * - `iteratorClass`: string, specify the class that will be used for iteration of validators
     */
    public function __construct(Model $model, array $config = [])
    {
        parent::__construct();
        $this->owner = $model;
        Yii::configure($this, $config);
    }

    /**
     * Sets magic property: `flags`, `iteratorClass` or `owner`.
     *
     * @param string $name the property name
     * @param mixed $value the property value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->{'set' . $name}($value);
    }

    /**
     * Attaches collection to model.
     *
     * @param Model $model the owner model
     * @return void
     */
    public function setOwner(Model $model)
    {
        // re-bind callbacks to new owner/scope
        foreach ($this->getArrayCopy() as $validator) {
            foreach (\get_object_vars($validator) as $name => $value) {
                if (\is_scalar($value) || !\is_callable($value)) {
                    continue;
                }
                if (\is_object($value) && $value instanceof Closure) {
                    // skip static closures
                    if ((new ReflectionFunction($value))->getClosureThis() == $this->owner) {
                        $validator->{$name} = $value->bindTo($model, $model);
                    }
                } elseif (\is_array($value) && $value[0] == $this->owner) {
                    $validator->{$name} = [$model, $value[1]];
                }
            }
        }

        $this->owner = $model;
    }

    /**
     * Sorts collected validatators by [[order]].
     *
     * This method groups validators by classes by defined order and merges in flatten array.
     *
     * @return void
     */
    public function sortByOrder()
    {
        if (!empty($this->getArrayCopy())) {
            $validators = \array_fill_keys($this->order, []);
            foreach ($this->getArrayCopy() as $validator) {
                $class = \get_class($validator);
                $validators[$class][] = $validator;
            }
            $validators = \array_values(\array_filter($validators));
            $this->exchangeArray(\call_user_func_array('array_merge', $validators));
        }
    }

    /**
     * Finds validators instances given class.
     *
     * @param string $className the class name to search
     * @return Validator[]
     */
    public function getClassValidators($className)
    {
        $className = \ltrim($className, '\\');

        $validators = [];
        foreach ($this->getIterator() as $validator) {
            if ($className === \get_class($validator)) {
                $validators[] = $validator;
            }
        }

        return $validators;
    }

    /**
     * Finds validators for active scenario and attribute(s) of owner model.
     *
     * @param string|null $attribute the attribute name (optional)
     * @return Validator[]
     */
    public function getActiveValidators($attribute = null)
    {
        $attributes = $this->owner->activeAttributes();
        if ($attribute !== null) {
            if (!\in_array($attribute, $attributes, true)) {
                return [];
            }
            $attributes = [$attribute];
        }

        $scenario = $this->owner->getScenario();

        $validators = [];
        foreach ($this->getIterator() as $validator) {
            if ($validator->isActive($scenario) && $validator->getValidationAttributes($attributes) != []) {
                $validators[] = $validator;
            }
        }

        return $validators;
    }

    /**
     * Clones collected validators.
     *
     * @return void
     */
    public function __clone()
    {
        foreach ($this->getArrayCopy() as $index => $validator) {
            $this->offsetSet($index, clone $validator);
        }
    }
}
