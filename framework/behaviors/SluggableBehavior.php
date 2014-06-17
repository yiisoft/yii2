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
 * SluggableBehavior
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class SluggableBehavior extends AttributeBehavior
{
    /**
     * @inheritdoc
     */
    public $attributes = [
        BaseActiveRecord::EVENT_BEFORE_VALIDATE => 'slug',
    ];
    /**
     * @var string
     */
    public $attribute;

    /**
     * @inheritdoc
     */
    public function init()
    {
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
