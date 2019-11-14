<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * UnknownClassException 表示使用未知类引起的异常。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UnknownClassException extends Exception
{
    /**
     * @return string 此异常的用户友好名称
     */
    public function getName()
    {
        return 'Unknown Class';
    }
}
