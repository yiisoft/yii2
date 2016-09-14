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

        $this->assertEquals($filter->buildCondition('>=', $field_name, $value_int), ['>=', $field_name, $value_int]);
        $this->assertEquals($filter->buildCondition('<=', $field_name, $value_int), ['<=', $field_name, $value_int]);
        $this->assertEquals($filter->buildCondition('>', $field_name, $value_int), ['>', $field_name, $value_int]);
        $this->assertEquals($filter->buildCondition('<', $field_name, $value_int), ['<', $field_name, $value_int]);
        $this->assertEquals(
            $filter->buildCondition('!=', $field_name, $value_int),
            ['NOT', [$field_name => $value_int]]
        );
        $this->assertEquals(
            $filter->buildCondition('!=', $field_name, $value_array),
            ['NOT IN', $field_name, $value_array]
        );
        $this->assertEquals(
            $filter->buildCondition(null, $field_name, $value_int),
            [$field_name => $value_int]
        );
        $this->assertEquals(
            $filter->buildCondition('like', $field_name, $value_string),
            ['like', $field_name, $value_string, false]
        );
    }
}
