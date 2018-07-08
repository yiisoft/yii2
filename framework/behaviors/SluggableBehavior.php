<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\behaviors;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\validators\UniqueValidator;

/**
 * SluggableBehavior automatically fills the specified attribute with a value that can be used a slug in a URL.
 *
 * Note: This behavior relies on php-intl extension for transliteration. If it is not installed it
 * falls back to replacements defined in [[\yii\helpers\Inflector::$transliteration]].
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
 *             '__class' => SluggableBehavior::class,
 *             'attribute' => 'title',
 *             // 'slugAttribute' => 'slug',
 *         ],
 *     ];
 * }
 * ```
 *
 * By default, SluggableBehavior will fill the `slug` attribute with a value that can be used a slug in a URL
 * when the associated AR object is being validated.
 *
 * Because attribute values will be set automatically by this behavior, they are usually not user input and should therefore
 * not be validated, i.e. the `slug` attribute should not appear in the [[\yii\base\Model::rules()|rules()]] method of the model.
 *
 * If your attribute name is different, you may configure the [[slugAttribute]] property like the following:
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         [
 *             '__class' => SluggableBehavior::class,
 *             'slugAttribute' => 'alias',
 *         ],
 *     ];
 * }
 * ```
 *
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
     * @var string|array|null the attribute or list of attributes whose value will be converted into a slug
     * or `null` meaning that the `$value` property will be used to generate a slug.
     */
    public $attribute;
    /**
     * @var callable|string|null the value that will be used as a slug. This can be an anonymous function
     * or an arbitrary value or null. If the former, the return value of the function will be used as a slug.
     * If `null` then the `$attribute` property will be used to generate a slug.
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
     * @var bool whether to generate a new slug if it has already been generated before.
     * If true, the behavior will not generate a new slug even if [[attribute]] is changed.
     * @since 2.0.2
     */
    public $immutable = false;
    /**
     * @var bool whether to ensure generated slug value to be unique among owner class records.
     * If enabled behavior will validate slug uniqueness automatically. If validation fails it will attempt
     * generating unique slug value from based one until success.
     */
    public $ensureUnique = false;
    /**
     * @var bool whether to skip slug generation if [[attribute]] is null or an empty string.
     * If true, the behaviour will not generate a new slug if [[attribute]] is null or an empty string.
     * @since 2.0.13
     */
    public $skipOnEmpty = false;
    /**
     * @var array configuration for slug uniqueness validator. Parameter '__class' may be omitted - by default
     * [[UniqueValidator]] will be used.
     * @see UniqueValidator
     */
    public $uniqueValidator = [];
    /**
     * @var callable slug unique value generator. It is used in case [[ensureUnique]] enabled and generated
     * slug is not unique. This should be a PHP callable with following signature:
     *
     * ```php
     * function ($baseSlug, $iteration, $model)
     * {
     *     // return uniqueSlug
     * }
     * ```
     *
     * If not set unique slug will be generated adding incrementing suffix to the base slug.
     */
    public $uniqueSlugGenerator;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (empty($this->attributes)) {
            $this->attributes = [BaseActiveRecord::EVENT_BEFORE_VALIDATE => $this->slugAttribute];
        }

        if ($this->attribute === null && $this->value === null) {
            throw new InvalidConfigException('Either "attribute" or "value" property must be specified.');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getValue($event)
    {
        if (!$this->isNewSlugNeeded()) {
            return $this->owner->{$this->slugAttribute};
        }

        if ($this->attribute !== null) {
            $slugParts = [];
            foreach ((array) $this->attribute as $attribute) {
                $part = ArrayHelper::getValue($this->owner, $attribute);
                if ($this->skipOnEmpty && $this->isEmpty($part)) {
                    return $this->owner->{$this->slugAttribute};
                }
                $slugParts[] = $part;
            }
            $slug = $this->generateSlug($slugParts);
        } else {
            $slug = parent::getValue($event);
        }

        return $this->ensureUnique ? $this->makeUnique($slug) : $slug;
    }

    /**
     * Checks whether the new slug generation is needed
     * This method is called by [[getValue]] to check whether the new slug generation is needed.
     * You may override it to customize checking.
     * @return bool
     * @since 2.0.7
     */
    protected function isNewSlugNeeded()
    {
        if (empty($this->owner->{$this->slugAttribute})) {
            return true;
        }

        if ($this->immutable) {
            return false;
        }

        if ($this->attribute === null) {
            return true;
        }

        foreach ((array) $this->attribute as $attribute) {
            if ($this->owner->isAttributeChanged($attribute)) {
                return true;
            }
        }

        return false;
    }

    /**
     * This method is called by [[getValue]] to generate the slug.
     * You may override it to customize slug generation.
     * The default implementation calls [[\yii\helpers\Inflector::slug()]] on the input strings
     * concatenated by dashes (`-`).
     * @param array $slugParts an array of strings that should be concatenated and converted to generate the slug value.
     * @return string the conversion result.
     */
    protected function generateSlug($slugParts)
    {
        return Inflector::slug(implode('-', $slugParts));
    }

    /**
     * This method is called by [[getValue]] when [[ensureUnique]] is true to generate the unique slug.
     * Calls [[generateUniqueSlug]] until generated slug is unique and returns it.
     * @param string $slug basic slug value
     * @return string unique slug
     * @see getValue
     * @see generateUniqueSlug
     * @since 2.0.7
     */
    protected function makeUnique($slug)
    {
        $uniqueSlug = $slug;
        $iteration = 0;
        while (!$this->validateSlug($uniqueSlug)) {
            $iteration++;
            $uniqueSlug = $this->generateUniqueSlug($slug, $iteration);
        }

        return $uniqueSlug;
    }

    /**
     * Checks if given slug value is unique.
     * @param string $slug slug value
     * @return bool whether slug is unique.
     */
    protected function validateSlug($slug)
    {
        /* @var $validator UniqueValidator */
        /* @var $model BaseActiveRecord */
        $validator = Yii::createObject(array_merge(
            [
                '__class' => UniqueValidator::class
            ],
            $this->uniqueValidator
        ));

        $model = clone $this->owner;
        $model->clearErrors();
        $model->{$this->slugAttribute} = $slug;

        $validator->validateAttribute($model, $this->slugAttribute);
        return !$model->hasErrors();
    }

    /**
     * Generates slug using configured callback or increment of iteration.
     * @param string $baseSlug base slug value
     * @param int $iteration iteration number
     * @return string new slug value
     * @throws \yii\base\InvalidConfigException
     */
    protected function generateUniqueSlug($baseSlug, $iteration)
    {
        if (is_callable($this->uniqueSlugGenerator)) {
            return call_user_func($this->uniqueSlugGenerator, $baseSlug, $iteration, $this->owner);
        }

        return $baseSlug . '-' . ($iteration + 1);
    }

    /**
     * Checks if $slugPart is empty string or null.
     *
     * @param string $slugPart One of attributes that is used for slug generation.
     * @return bool whether $slugPart empty or not.
     * @since 2.0.13
     */
    protected function isEmpty($slugPart)
    {
        return $slugPart === null || $slugPart === '';
    }
}
