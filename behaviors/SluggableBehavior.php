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
 * SluggableBehavior automatically fills the specified attribute with the transliterated and adjusted version to use in URLs.
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
     * @var string
     */
    public $slugAttribute = 'slug';
    /**
     * @var string
     */
    public $attribute;

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
            throw new InvalidConfigException('Either "attribute" or "value" properties must be specified.');
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
