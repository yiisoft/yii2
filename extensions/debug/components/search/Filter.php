<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\components\search;

use yii\base\Component;
use yii\debug\components\search\matchers\MatcherInterface;

/**
 * Provides array filtering capabilities.
 *
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
class Filter extends Component
{
    /**
     * @var array rules for matching filters in the way: [:fieldName => [rule1, rule2,..]]
     */
    protected $rules = [];

    /**
     * Adds data filtering rule.
     *
     * @param string $name attribute name
     * @param MatcherInterface $rule
     */
    public function addMatcher($name, MatcherInterface $rule)
    {
        if ($rule->hasValue()) {
            $this->rules[$name][] = $rule;
        }
    }

    /**
     * Applies filter on a given array and returns filtered data.
     *
     * @param array $data data to filter
     * @return array filtered data
     */
    public function filter(array $data)
    {
        $filtered = [];

        foreach ($data as $row) {
            if ($this->passesFilter($row)) {
                $filtered[] = $row;
            }
        }

        return $filtered;
    }

    /**
     * Checks if the given data satisfies filters.
     *
     * @param array $row data
     * @return boolean if data passed filtering
     */
    private function passesFilter(array $row)
    {
        foreach ($row as $name => $value) {
            if (isset($this->rules[$name])) {
                // check all rules for a given attribute
                foreach ($this->rules[$name] as $rule) {
                    /* @var $rule MatcherInterface */
                    if (!$rule->match($value)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }
}
