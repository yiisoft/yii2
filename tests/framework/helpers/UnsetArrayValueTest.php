<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\helpers;

use yii\helpers\UnsetArrayValue;
use yiiunit\TestCase;

/**
 * @group helpers
 */
class UnsetArrayValueTest extends TestCase
{
    public function testSetState()
    {
        $object = new UnsetArrayValue();
        $result = $object::__set_state([]);
        $this->assertInstanceOf('yii\helpers\UnsetArrayValue', $result);
    }
}
