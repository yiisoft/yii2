<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web\stubs;

use yii\base\Object;
use yiiunit\framework\di\stubs\QuxInterface;

/**
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.0
 */
class OtherQux extends Object implements QuxInterface
{
    public $b;
    public function quxMethod()
    {
        return 'other_qux';
    }
}
