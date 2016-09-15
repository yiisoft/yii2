<?php

namespace yiiunit\framework\data;

use yii\data\Filter;
use yiiunit\TestCase;

/**
 * @group data
 */
class FilterTest extends TestCase
{
    public function testBuildCondition()
    {
        $field_name   = 'some_field';
        $value_string = 'some_string';
        $value_int    = 8;
        $value_array  = [15, 16];
        $filter       = new Filter();

        $this->assertEquals(['>=', $field_name, $value_int], $filter->buildCondition('>=', $field_name, $value_int));
        $this->assertEquals(['<=', $field_name, $value_int], $filter->buildCondition('<=', $field_name, $value_int));
        $this->assertEquals(['>', $field_name, $value_int], $filter->buildCondition('>', $field_name, $value_int));
        $this->assertEquals(['<', $field_name, $value_int], $filter->buildCondition('<', $field_name, $value_int));
        $this->assertEquals(
            ['NOT', [$field_name => $value_int]],
            $filter->buildCondition('!=', $field_name, $value_int)
        );
        $this->assertEquals(
            ['NOT IN', $field_name, $value_array],
            $filter->buildCondition('!=', $field_name, $value_array)
        );
        $this->assertEquals(
            [$field_name => $value_int],
            $filter->buildCondition(null, $field_name, $value_int)
        );
        $this->assertEquals(
            ['like', $field_name, $value_string, false],
            $filter->buildCondition('like', $field_name, $value_string)
        );
    }
}
