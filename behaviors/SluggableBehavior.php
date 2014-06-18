<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\behaviors;

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
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class SluggableBehavior extends AttributeBehavior
{
    /**
     * @var string the attribute that will receive the slug value
     */
    public $slugAttribute = 'slug';
    /**
     * @var string the attribute whose value will be converted into a slug
     */
    public $attribute;
    /**
     * @var string|callable the value that will be used as a slug. This can be an anonymous function
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
     * @inheritdoc
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
     * @inheritdoc
     */
    protected function getValue($event)
    {
        if ($this->attribute !== null) {
            $this->value = Inflector::slug($this->owner->{$this->attribute});
        }

        return parent::getValue($event);
    }
}
