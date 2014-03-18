<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\components\search\matchers;

use yii\base\Component;

/**
 * Base class for matchers that are used in a filter.
 *
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
abstract class Base extends Component implements MatcherInterface
{
    /**
     * @var mixed base value to check
     */
    protected $baseValue;

    /**
     * @inheritdoc
     */
    public function setValue($value)
    {
        $this->baseValue = $value;
    }

    /**
     * @inheritdoc
     */
    public function hasValue()
    {
        return !empty($this->baseValue) || ($this->baseValue === '0');
    }
}
