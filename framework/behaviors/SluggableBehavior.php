<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\behaviors;

use yii\base\DynamicModel;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\BaseActiveRecord;
use yii\helpers\Inflector;

/**
 * SluggableBehavior automatically fills the specified attribute with a value that can be used a slug in a URL.
 *
 * To use SluggableBehavior, insert the following code to your ActiveRecord class:
 *
 * ```php
 * use yii\behaviors\SluggableBehavior;
 *
 * public function behaviors()
 * {
 *     return [
 *         [
 *             'class' => SluggableBehavior::className(),
 *             'attribute' => 'title',
 *             // 'slugAttribute' => 'slug',
 *         ],
 *     ];
 * }
 * ```
 *
 * By default, SluggableBehavior will fill the `slug` attribute with a value that can be used a slug in a URL
 * when the associated AR object is being validated. If your attribute name is different, you may configure
 * the [[slugAttribute]] property like the following:
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         [
 *             'class' => SluggableBehavior::className(),
 *             'slugAttribute' => 'alias',
 *         ],
 *     ];
 * }
 * ```
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class SluggableBehavior extends AttributeBehavior
{
    /**
     * @var string the attribute that will receive the slug value
     */
    public $slugAttribute = 'slug';
    /**
     * @var string|array the attribute or list of attributes whose value will be converted into a slug
     */
    public $attribute;
    /**
     * @var mixed the value that will be used as a slug. This can be an anonymous function
     * or an arbitrary value. If the former, the return value of the function will be used as a slug.
     * The signature of the function should be as follows,
     *
     * ```php
     * function ($event)
     * {
     *     // return slug
     * }
     * ```
     */
    public $value;
    /**
     * @var boolean whether to ensure generated slug value to be unique among owner class records.
     * If enabled behavior will validate slug uniqueness automatically. If validation fails it will attempt
     * generating unique slug value from based one until success.
     */
    public $unique = false;
    /**
     * @var array configuration for slug uniqueness validator. This configuration should not contain validator name
     * and validated attributes - only options in format 'name => value' are allowed.
     * For example:
     * [
     *     'filter' => ['type' => 1, 'status' => 2]
     * ]
     * @see yii\validators\UniqueValidator
     */
    public $uniqueValidatorConfig = [];
    /**
     * @var string|callable slug unique value generator. It is used in case [[unique]] enabled and generated
     * slug is not unique. This can be a PHP callable with following signature:
     *
     * ```php
     * function ($baseSlug, $iteration)
     * {
     *     // return uniqueSlug
     * }
     * ```
     *
     * Also one of the following predefined values can be used:
     *  - 'increment' - adds incrementing suffix to the base slug
     *  - 'uniqueid' - adds part of uniqueId hash string to the base slug
     *  - 'timestamp' - adds current UNIX timestamp to the base slug
     */
    public $uniqueSlugGenerator = 'increment';


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (empty($this->attributes)) {
            if ($this->unique) {
                $this->attributes = [BaseActiveRecord::EVENT_BEFORE_INSERT => $this->slugAttribute];
            } else {
                $this->attributes = [BaseActiveRecord::EVENT_BEFORE_VALIDATE => $this->slugAttribute];
            }
        }

        if ($this->attribute === null && $this->value === null) {
            throw new InvalidConfigException('Either "attribute" or "value" property must be specified.');
        }
    }

    /**
     * @inheritdoc
     */
    protected function getValue($event)
    {
        if ($this->attribute !== null) {
            if (is_array($this->attribute)) {
                $slugParts = [];
                foreach ($this->attribute as $attribute) {
                    $slugParts[] = $this->owner->{$attribute};
                }
                $this->value = Inflector::slug(implode('-', $slugParts));
            } else {
                $this->value = Inflector::slug($this->owner->{$this->attribute});
            }
        }
        $slug = parent::getValue($event);

        if ($this->unique) {
            $baseSlug = $slug;
            $iteration = 0;
            while (!$this->validateSlugUnique($slug)) {
                $iteration++;
                $slug = $this->generateUniqueSlug($baseSlug, $iteration);
            }
        }
        return $slug;
    }

    /**
     * Checks if given slug value is unique.
     * @param string $slug slug value
     * @return boolean whether slug is unique.
     */
    private function validateSlugUnique($slug)
    {
        $validator = array_merge(
            [
                ['slug'],
                'unique',
                'targetClass' => get_class($this->owner)
            ],
            $this->uniqueValidatorConfig
        );
        $model = DynamicModel::validateData(compact('slug'), [$validator]);
        return !$model->hasErrors();
    }

    /**
     * @param string $baseSlug base slug value
     * @param integer $iteration iteration number
     * @return string slug suffix
     * @throws \yii\base\InvalidConfigException
     */
    private function generateUniqueSlug($baseSlug, $iteration)
    {
        $generator = $this->uniqueSlugGenerator;
        switch ($generator) {
            case 'increment':
                return $this->generateUniqueSlugIncrement($baseSlug, $iteration);
            case 'uniqueid':
                return $this->generateUniqueSlugUniqueId($baseSlug, $iteration);
            case 'timestamp':
                return $this->generateSuffixSlugTimestamp($baseSlug, $iteration);
            default:
                if (is_callable($generator)) {
                    return call_user_func($generator, $baseSlug, $iteration);
                }
                throw new InvalidConfigException("Unrecognized slug unique suffix generator '{$generator}'.");
        }
    }

    /**
     * Generates slug using increment of iteration.
     * @param string $baseSlug base slug value
     * @param integer $iteration iteration number
     * @return string generated suffix.
     */
    protected function generateUniqueSlugIncrement($baseSlug, $iteration)
    {
        return $baseSlug . '-' . ($iteration + 1);
    }

    /**
     * Generates slug using unique id.
     * @param string $baseSlug base slug value
     * @param integer $iteration iteration number
     * @throws \yii\base\Exception
     * @return string generated suffix.
     */
    protected function generateUniqueSlugUniqueId($baseSlug, $iteration)
    {
        static $uniqueId;
        if ($iteration < 2) {
            $uniqueId = sha1(uniqid(get_class($this), true));
        }
        $subStringLength = 6 + $iteration;
        if ($subStringLength > strlen($uniqueId)) {
            throw new Exception('Unique id is exhausted.');
        }
        return $baseSlug . '-' . substr($uniqueId, 0, $subStringLength);
    }

    /**
     * Generates slug using current timestamp.
     * @param string $baseSlug base slug value
     * @param integer $iteration iteration number
     * @throws \yii\base\Exception
     * @return string generated suffix.
     */
    protected function generateSuffixSlugTimestamp($baseSlug, $iteration)
    {
        return $baseSlug . '-' . (time() + $iteration - 1);
    }
}
