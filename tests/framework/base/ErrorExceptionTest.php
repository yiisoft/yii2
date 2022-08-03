<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use yii\base\ErrorException;
use yiiunit\TestCase;

/**
 * @group base
 */
class ErrorExceptionTest extends TestCase
{
    private function isXdebugStackAvailable()
    {
        if (!function_exists('xdebug_get_function_stack')) {
            return false;
        }
        $version = phpversion('xdebug');
        if ($version === false) {
            return false;
        }
        if (version_compare($version, '3.0.0', '<')) {
            return true;
        }
        return false !== strpos(ini_get('xdebug.mode'), 'develop');
    }

    public function testXdebugTrace()
    {
        if (!$this->isXdebugStackAvailable()) {
            $this->markTestSkipped('Xdebug is required.');
        }
        try {
            throw new ErrorException();
        } catch (ErrorException $e){
            $this->assertEquals(__FUNCTION__, $e->getTrace()[0]['function']);
        }
    }
}
