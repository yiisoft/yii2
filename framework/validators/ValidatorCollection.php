<?php

namespace yii\validators;

use ArrayObject;
use Yii;
use yii\base\Configurable;

/**
 * Collection of {@see Validator validators}.
 *
 * @property-write int $flags the flags to control the behaviour of the collection
 * @property-write string $iteratorClass specify the class that will be used for iteration of validators
 */
class ValidatorCollection extends ArrayObject implements Configurable
{
    /**
     * @var string[] The validation order, an array of validator class names. The omitted validators adding at end.
     */
    public $order = [
        'yii\validators\RequiredValidator',
        'yii\validators\DefaultValueValidator',
        'yii\validators\FilterValidator',
        'yii\captcha\CaptchaValidator',
    ];

    /**
     * @param array $config an array of configuration values:
     * - `order`: array, the validation order, an array of validator class names
     * - `flags`: integer, the flags(bitmask) to control the behaviour of the collection
     * - `iteratorClass`: string, specify the class that will be used for iteration of validators
     */
    public function __construct(array $config = [])
    {
        parent::__construct();
        Yii::configure($this, $config);
    }

    /**
     * Sets magic property `flags` or `iteratorClass`.
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
     * Sorts collected validatators by [[order]].
     *
     * This method groups validators by classes by defined order and merges in flatten array.
     *
     * @return void
     */
    public function sortByOrder()
    {
        $validators = \array_fill_keys($this->order, []);
        foreach ($this->getArrayCopy() as $validator) {
            $class = \get_class($validator);
            $validators[$class][] = $validator;
        }
        $this->exchangeArray(\call_user_func_array('array_merge', $validators));
    }

    /**
     * Finds collected validators by given class name.
     *
     * @param string $className the class name to search
     * @return Validator[]
     */
    public function findByClass($className)
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
