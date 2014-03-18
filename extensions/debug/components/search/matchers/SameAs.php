<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\components\search\matchers;

/**
 * Checks if the given value is exactly or partially same as the base one.
 *
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
class SameAs extends Base
{
    /**
     * @var boolean if partial match should be used.
     */
    public $partial = false;

    /**
     * @inheritdoc
     */
    public function match($value)
    {
        if (!$this->partial) {
            return (mb_strtolower($this->baseValue, 'utf8') == mb_strtolower($value, 'utf8'));
        } else {
            return (mb_strpos(mb_strtolower($value, 'utf8'), mb_strtolower($this->baseValue, 'utf8')) !== false);
        }
    }
}
