<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\models\search;

use yii\base\Model;
use yii\debug\components\search\Filter;
use yii\debug\components\search\matchers;

/**
 * Base search model
 *
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
class Base extends Model
{
    /**
     * Adds filtering condition for a given attribute
     *
     * @param Filter $filter filter instance
     * @param string $attribute attribute to filter
     * @param boolean $partial if partial match should be used
     */
    public function addCondition(Filter $filter, $attribute, $partial = false)
    {
        $value = $this->$attribute;

        if (mb_strpos($value, '>') !== false) {
            $value = intval(str_replace('>', '', $value));
            $filter->addMatcher($attribute, new matchers\GreaterThan(['value' => $value]));

        } elseif (mb_strpos($value, '<') !== false) {
            $value = intval(str_replace('<', '', $value));
            $filter->addMatcher($attribute, new matchers\LowerThan(['value' => $value]));
        } else {
            $filter->addMatcher($attribute, new matchers\SameAs(['value' => $value, 'partial' => $partial]));
        }
    }
}
