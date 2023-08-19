<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\helpers;

use yii\helpers\ReplaceArrayValue;
use yiiunit\TestCase;

/**
 * @group helpers
 */
class ReplaceArrayValueTest extends TestCase
{
    public function testSetStateWithoutValue()
    {
        $this->expectException('yii\base\InvalidConfigException');
        $this->expectExceptionMessage('Failed to instantiate class "ReplaceArrayValue". Required parameter "value" is missing');
        $object = new ReplaceArrayValue('test');
        $object::__set_state([]);
    }

    public function testSetStateWithValue()
    {
        $object = new ReplaceArrayValue('test');
        $result = $object::__set_state(['value' => 'test2']);
        $this->assertInstanceOf('yii\helpers\ReplaceArrayValue', $result);
        $this->assertSame('test2', $result->value);
    }
}
