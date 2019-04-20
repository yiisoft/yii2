<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console;

use yii\base\UserException;

/**
 * Exception 表示由于不正确使用控制台命令而导致的异常。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Exception extends UserException
{
    /**
     * @return string 此异常的用户友好名称
     */
    public function getName()
    {
        return 'Error';
    }
}
