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
 * SluggableBehavior 自动地给指定的属性填充值，这些值可以在 URL 中用作 slug。
 *
 * Note: 这个行为依赖于 php-intl 扩展来完成转译。如果没有安装这个扩展，
 * 将会退一步用 [[\yii\helpers\Inflector::$transliteration]] 来替换。
 *
 * 要使用 SluggableBehavior，把下面的代码插入到 ActiveRecord 类中：
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
 * 默认情况下，SluggableBehavior 会在关联的 AR 对象执行验证过程中填充 `slug` 属性的值，
 * 该值可以在 URL 中用作 slug。
 *
 * 由于属性值是被这个行为自动设置，所以它不必用户输入也因此没有必要验证。
 * 因此，`slug` 属性不应该出现在 [[\yii\base\Model::rules()|rules()]] 这个模型方法中。
 *
 * 如果你的属性名不是 `slug`，那么你可以像下面那样配置 [[slugAttribute]] 属性来调整：
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
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class SluggableBehavior extends AttributeBehavior
{
    /**
     * @var string 接收 slug 值的属性。
     */
    public $slugAttribute = 'slug';
    /**
     * @var string|array|null 单个属性或者属性列表，它们的值将会转译为 slug，
     * 如果是 `null` 那么将会用 $value 属性来生成 slug。
     */
    public $attribute;
    /**
     * @var callable|string|null 用来生成 slug 的值。它可以是一个匿名函数，
     * 或者是任意的值或者 null。如果是前者，匿名函数的返回值将会当作 slug。
     * 如果是 `null`，那么使用 `$attribute` 属性生成 slug。
     * 匿名函数的签名应该是这样的，
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
     * @var bool 如果之前已经生成过的话，是否需要生成一个全新的 slug。
     * 如果是 true，该行为不会生成新的 slug，即使 [[attribute]] 有变化了。
     * @since 2.0.2
     */
    public $immutable = false;
    /**
     * @var bool 是否确保生成的 slug 值在属主 AR 类的所有记录里是唯一的。
     * 如果设置为 true，行为将会自动验证 slug 的唯一性。验证失败的话，
     * 它还会在重复的 slug 基础上不断尝试生成一个唯一的 slug，直到它不再是重复的 slug 为止。
     */
    public $ensureUnique = false;
    /**
     * @var bool 如果 [[attribute]] 是 null 或者是一个空字符串时，是否跳过 slug 的生成过程。
     * 如果是 true，那么在 [[attribute]] 是 null 或者是一个空字符串时就不会生成一个新的 slug。
     * @since 2.0.13
     */
    public $skipOnEmpty = false;
    /**
     * @var array slug 的唯一性验证器配置数组。参数 'class' 可以忽略为空，
     * 默认情况下，将会使用 [[UniqueValidator]] 作为唯一性验证器。
     * @see UniqueValidator
     */
    public $uniqueValidator = [];
    /**
     * @var callable slug 唯一值生成器。当启用了 [[ensureUnique]] 并且生成了不唯一的 slug 时使用。
     * 唯一值生成器的函数签名应该是下面这样的：
     *
     * ```php
     * function ($baseSlug, $iteration, $model)
     * {
     *     // return uniqueSlug
     * }
     * ```
     *
     * 如果没有配置唯一值生成器，行为将给原来重复的 slug 填充后缀使之达到唯一性。
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
     * 检测是否有必要生成一个新的 slug。
     * 该方法是在 [[getValue]] 中调用，用来检测是否有必要生成一个新的slug。
     * 你可以覆盖它实现自定义的检测过程。
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
     * 该方法在 [[getValue]] 中调用，用来生成 slug。 
     * 你可以通过覆盖它来自定义 slug 的生成过程。
     * 默认的实现就是调用 [[\yii\helpers\Inflector::slug()]]，
     * 参数就是用连字符（`-`）拼接过的字符串。
     * @param array $slugParts 一个字符串数组，通过拼接和转译来生成 slug 值。
     * @return string 转译过的结果。
     */
    protected function generateSlug($slugParts)
    {
        return Inflector::slug(implode('-', $slugParts));
    }

    /**
     * 这个方法在 [[getValue]] 中调用，当 [[ensureUnique]] 为 true 时可以生成唯一的 slug。
     * 循环调用 [[generateUniqueSlug]] 直到生成了唯一的 slug，然后返回它。
     * @param string $slug 原始 slug 值。
     * @return string 唯一的 slug。
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
     * 检测给定的 slug 是否是唯一的。
     * @param string $slug slug 值。
     * @return bool 是否 slug 值是唯一的。
     */
    protected function validateSlug($slug)
    {
        /* @var $validator UniqueValidator */
        /* @var $model BaseActiveRecord */
        $validator = Yii::createObject(array_merge(
            [
                'class' => UniqueValidator::className(),
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
     * 用配置好的回调或者迭代填充的方法生成 slug。
     * @param string $baseSlug 原始 slug 值。
     * @param int $iteration 迭代数字
     * @return string 新的 slug 值。
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
     * 检测 $slugPart 是否是空字符串或者 null。
     *
     * @param string $slugPart 用来生成 slug 的属性列表里的一个属性。
     * @return bool 是否 $slugPart 为空。
     * @since 2.0.13
     */
    protected function isEmpty($slugPart)
    {
        return $slugPart === null || $slugPart === '';
    }
}
