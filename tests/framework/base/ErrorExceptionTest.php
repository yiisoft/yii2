<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use yii\base\ErrorException;
use yiiunit\TestCase;

/**
 * @group base
 */
class ErrorExceptionTest extends TestCase
{
    public function testXdebugTrace()
    {
        if (!function_exists('xdebug_get_function_stack')) {
            $this->markTestSkipped('Xdebug are required.');
        }
        try {
            throw new ErrorException();
        } catch (ErrorException $e){
            $this->assertEquals(__FUNCTION__, $e->getTrace()[0]['function']);
        }
    }
}
